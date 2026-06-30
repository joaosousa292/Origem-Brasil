<?php
include("conexao.php");
include("includes/product_card.php");

// Categorias disponíveis — as 4 originais (com descrição/ícone customizados)
// mais quaisquer categorias novas aprovadas pelo admin (solicitadas por produtores)
$categorias = [
    'cafe'     => ['label' => 'Cafés Especiais',    'desc' => 'Grãos selecionados direto dos produtores brasileiros',    'icon' => 'coffee'],
    'mel'      => ['label' => 'Méis Artesanais',    'desc' => 'Mel puro de abelhas nativas e europeias do Brasil',       'icon' => 'mel'],
    'pimenta'  => ['label' => 'Pimentas Especiais', 'desc' => 'Pimentas cultivadas com técnicas artesanais brasileiras', 'icon' => 'pimenta'],
    'farinha'  => ['label' => 'Farinhas Naturais',  'desc' => 'Farinhas orgânicas e funcionais da agricultura familiar', 'icon' => 'farinha'],
];

$catsExtra = mysqli_query($conexao,
    "SELECT slug, label, descricao FROM categorias WHERE slug NOT IN ('cafe','mel','pimenta','farinha') ORDER BY label ASC"
);
if ($catsExtra) {
    while ($row = mysqli_fetch_assoc($catsExtra)) {
        $categorias[$row['slug']] = [
            'label' => $row['label'],
            'desc'  => $row['descricao'] ?: 'Selecionados com cuidado por produtores parceiros da Origem Brasil',
            'icon'  => 'novo',
        ];
    }
}

// Categoria ativa
$cat_slug  = $_GET['categoria'] ?? 'cafe';
if (!array_key_exists($cat_slug, $categorias)) $cat_slug = array_key_first($categorias);
$cat_info  = $categorias[$cat_slug];

// Filtros
$ordem     = $_GET['ordem']    ?? 'destaque';
$preco_max = isset($_GET['preco_max']) ? (float)$_GET['preco_max'] : 999;
$q         = htmlspecialchars(trim($_GET['q'] ?? ''));

$ordemSQL = match($ordem) {
    'menor_preco' => 'p.preco ASC',
    'maior_preco' => 'p.preco DESC',
    'avaliacao'   => 'media_nota DESC',
    'novidades'   => 'p.criado_em DESC',
    default       => 'p.destaque DESC, p.id ASC'
};

$sql = "SELECT p.*, COALESCE(ROUND(AVG(a.nota),1),0) AS media_nota, COUNT(a.id) AS total_av
        FROM produtos p LEFT JOIN avaliacoes a ON a.produto_id = p.id
        WHERE p.categoria = ? AND p.preco <= ?";
if ($q) $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ? OR p.regiao LIKE ?)";
$sql .= " GROUP BY p.id ORDER BY $ordemSQL";

$stmt = mysqli_prepare($conexao, $sql);
if ($q) {
    $like = "%$q%";
    mysqli_stmt_bind_param($stmt, 'sdsss', $cat_slug, $preco_max, $like, $like, $like);
} else {
    mysqli_stmt_bind_param($stmt, 'sd', $cat_slug, $preco_max);
}
mysqli_stmt_execute($stmt);
$produtos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$total = count($produtos);

// Ícones SVG inline por categoria (categorias novas usam um ícone genérico)
$cat_icons = [
    'cafe'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>',
    'mel'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;"><path d="M12 2C8.5 2 6 5 6 8c0 4 6 14 6 14s6-10 6-14c0-3-2.5-6-6-6z"/><circle cx="12" cy="8" r="2"/></svg>',
    'pimenta' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;"><path d="M12 22c0 0-7-6-7-12a7 7 0 0 1 14 0c0 6-7 12-7 12z"/><path d="M12 2c0 0 3-1 4 2"/></svg>',
    'farinha' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>',
    'novo'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $cat_info['label']; ?> — Origem Brasil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* CATEGORY TABS no topo da página */
        .cat-switcher-bar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 0 40px;
            position: sticky;
            top: 113px; /* abaixo do header */
            z-index: 100;
        }
        .cat-switcher {
            display: flex;
            align-items: center;
            gap: 4px;
            max-width: 1320px;
            margin: 0 auto;
        }
        .cat-tab {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 20px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-3);
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: color .2s, border-color .2s;
            text-decoration: none;
            white-space: nowrap;
        }
        .cat-tab:hover { color: var(--green); }
        .cat-tab.active {
            color: var(--green);
            font-weight: 700;
            border-bottom-color: var(--green);
        }
        .cat-tab svg { flex-shrink: 0; transition: transform .2s; }
        .cat-tab:hover svg { transform: scale(1.1); }
        .cat-tab .cat-count {
            font-size: 10px;
            background: var(--green-light);
            color: var(--green-2);
            padding: 2px 7px;
            border-radius: 10px;
            font-weight: 600;
        }
        .cat-tab.active .cat-count {
            background: var(--green);
            color: #fff;
        }
        /* Badge "Em breve" para categorias sem produto */
        .cat-tab .badge-soon {
            font-size: 9px;
            background: #f3f4f6;
            color: var(--text-4);
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* EMPTY STATE para categoria sem produtos */
        .empty-cat {
            text-align: center;
            padding: 100px 20px;
        }
        .empty-cat-icon {
            width: 80px; height: 80px;
            background: var(--cream);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
        }
        .empty-cat-icon svg { width: 36px; height: 36px; stroke: var(--text-4); fill: none; stroke-width: 1.5; }
        .empty-cat h3 { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 10px; }
        .empty-cat p  { font-size: 14px; color: var(--text-3); max-width: 360px; margin: 0 auto 28px; line-height: 1.65; }
        .empty-cat a  {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 26px;
            background: var(--green); color: #fff;
            border-radius: var(--radius); font-weight: 600; font-size: 13px;
        }

        @media (max-width: 768px) {
            .cat-switcher-bar { padding: 0 12px; top: 100px; overflow-x: auto; }
            .cat-tab { padding: 12px 14px; font-size: 12px; }
            .cat-tab span.cat-label { display: none; }
        }
    </style>
</head>
<body data-logado="<?php echo isset($_SESSION['id']) ? '1' : '0'; ?>">

<?php include("includes/header.php"); ?>



<!-- TÍTULO DA PÁGINA -->
<div class="page-title-block">
    <div class="breadcrumb">
        <a href="index.php">Início</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="produtos.php">Produtos</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><polyline points="9 18 15 12 9 6"/></svg>
        <span><?php echo $cat_info['label']; ?></span>
    </div>
    <h1><?php echo $cat_info['label']; ?></h1>
    <p style="font-size:14px;opacity:.75;margin-top:6px;"><?php echo $cat_info['desc']; ?></p>
</div>

<!-- CATEGORY SWITCHER BAR -->
<div class="cat-switcher-bar">
    <div class="cat-switcher">
        <?php foreach ($categorias as $slug => $info):
            // Conta produtos desta categoria
            $cnt_stmt = mysqli_prepare($conexao, "SELECT COUNT(*) as n FROM produtos WHERE categoria = ?");
            mysqli_stmt_bind_param($cnt_stmt, 's', $slug);
            mysqli_stmt_execute($cnt_stmt);
            $cnt = mysqli_fetch_assoc(mysqli_stmt_get_result($cnt_stmt))['n'];
            $isActive = $slug === $cat_slug;
        ?>
            <a href="produtos.php?categoria=<?php echo $slug; ?>"
            class="cat-tab <?php echo $isActive ? 'active' : ''; ?>">
                <?php echo $cat_icons[$slug] ?? $cat_icons['novo']; ?>
                <span class="cat-label"><?php echo $info['label']; ?></span>
                <?php if ($cnt > 0): ?>
                    <span class="cat-count"><?php echo $cnt; ?></span>
                <?php else: ?>
                    <span class="badge-soon">Em breve</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- CATÁLOGO -->
<div class="catalog-layout">

    <!-- SIDEBAR FILTROS -->
    <aside class="sidebar">
        <form method="GET" action="produtos.php">
            <input type="hidden" name="categoria" value="<?php echo $cat_slug; ?>">
            <div class="sidebar-title">Filtros</div>

            <div class="filter-group">
                <span class="filter-label">Preço máximo</span>
                <div class="price-range">
                    <input type="range" name="preco_max" min="10" max="300" step="5"
                           value="<?php echo min($preco_max < 999 ? $preco_max : 300, 300); ?>"
                           oninput="document.getElementById('pv').textContent = 'R$ ' + this.value">
                    <div class="price-range-vals">
                        <span>R$ 10</span>
                        <span id="pv">R$ <?php echo min($preco_max < 999 ? $preco_max : 300, 300); ?></span>
                        <span>R$ 300</span>
                    </div>
                </div>
            </div>

            <div class="filter-group">
                <span class="filter-label">Ordenar por</span>
                <select name="ordem" class="sort-select">
                    <option value="destaque"    <?php echo $ordem==='destaque'   ?'selected':''; ?>>Destaques</option>
                    <option value="menor_preco" <?php echo $ordem==='menor_preco'?'selected':''; ?>>Menor preço</option>
                    <option value="maior_preco" <?php echo $ordem==='maior_preco'?'selected':''; ?>>Maior preço</option>
                    <option value="avaliacao"   <?php echo $ordem==='avaliacao'  ?'selected':''; ?>>Melhor avaliados</option>
                    <option value="novidades"   <?php echo $ordem==='novidades'  ?'selected':''; ?>>Novidades</option>
                </select>
            </div>

            <!-- Mini-nav de categorias na sidebar também -->
            <div class="filter-group" style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
                <span class="filter-label">Categorias</span>
                <?php foreach ($categorias as $slug => $info): ?>
                <a href="produtos.php?categoria=<?php echo $slug; ?>"
                   style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;font-size:13px;margin-bottom:4px;
                          color:<?php echo $slug===$cat_slug?'var(--green)':'var(--text-3)'; ?>;
                          background:<?php echo $slug===$cat_slug?'var(--green-light)':'transparent'; ?>;
                          font-weight:<?php echo $slug===$cat_slug?'600':'400'; ?>;
                          transition:all .2s;text-decoration:none;">
                    <?php echo $cat_icons[$slug] ?? $cat_icons['novo']; ?>
                    <?php echo $info['label']; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="filter-btn">Aplicar Filtros</button>
            <a href="produtos.php?categoria=<?php echo $cat_slug; ?>"
               style="display:block;text-align:center;font-size:12px;color:var(--text-4);margin-top:10px;">
               Limpar filtros
            </a>
        </form>
    </aside>

    <!-- GRID DE PRODUTOS -->
    <main>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--text-3);">
                <strong><?php echo $total; ?></strong>
                produto<?php echo $total !== 1 ? 's' : ''; ?>
                encontrado<?php echo $total !== 1 ? 's' : ''; ?>
                em <strong style="color:var(--green)"><?php echo $cat_info['label']; ?></strong>
            </span>
            <?php if ($q): ?>
            <span style="font-size:13px;color:var(--green-2);">
                Busca: <strong>"<?php echo $q; ?>"</strong>
                <a href="produtos.php?categoria=<?php echo $cat_slug; ?>"
                   style="color:var(--text-4);margin-left:6px;font-size:11px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> limpar</a>
            </span>
            <?php endif; ?>
        </div>

        <?php if (empty($produtos)): ?>
        <div class="empty-cat">
            <div class="empty-cat-icon">
                <?php echo $cat_icons[$cat_slug] ?? $cat_icons['novo']; ?>
            </div>
            <h3>Nenhum produto ainda</h3>
            <p>
                Estamos selecionando os melhores
                <strong><?php echo strtolower($cat_info['label']); ?></strong>
                de produtores parceiros. Em breve novidades aqui!
            </p>
            <a href="produtos.php?categoria=cafe">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:15px;height:15px;"><polyline points="15 18 9 12 15 6"/></svg>
                Ver Cafés Disponíveis
            </a>
        </div>

        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($produtos as $p) renderCard($p); ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
</body>
</html>
