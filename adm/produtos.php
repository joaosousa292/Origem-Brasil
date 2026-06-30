<?php
$page_title  = 'Produtos';
$active_menu = 'produtos';
$msg = ''; $msg_type = 'ok';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

$produtores_todos = mysqli_fetch_all(mysqli_query($conexao,"SELECT id,nome FROM produtores ORDER BY nome"), MYSQLI_ASSOC);
$acao = $_GET['acao'] ?? 'lista';

// Categorias do site (4 fixas + quaisquer novas aprovadas a partir de produtores)
$categorias = [
    'cafe'    => ['label'=>'Cafés Especiais',  'svg'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>'],
    'mel'     => ['label'=>'Méis Artesanais',  'svg'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>'],
    'pimenta' => ['label'=>'Pimentas',          'svg'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/></svg>'],
    'farinha' => ['label'=>'Farinhas',          'svg'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>'],
    'cereais' => ['label'=>'Cereais',           'svg'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 0 1 0 20"/><path d="M12 2a10 10 0 0 0 0 20"/><path d="M12 2v20"/><path d="M2 12h20"/></svg>'],
];
$resCatsAdm = mysqli_query($conexao, "SELECT slug, label FROM categorias WHERE slug NOT IN ('cafe','mel','pimenta','farinha','cereais') ORDER BY label ASC");
if ($resCatsAdm) {
    while ($rowCatAdm = mysqli_fetch_assoc($resCatsAdm)) {
        $categorias[$rowCatAdm['slug']] = [
            'label' => $rowCatAdm['label'],
            'svg'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p_acao = $_POST['acao'] ?? '';

    if ($p_acao === 'salvar') {
        $pid     = (int)($_POST['id'] ?? 0);
        $nome    = trim($_POST['nome'] ?? '');
        $desc    = trim($_POST['descricao'] ?? '');
        $hist    = trim($_POST['historia'] ?? '');
        $preco   = (float)str_replace(',','.',($_POST['preco'] ?? 0));
        $cat     = $_POST['categoria'] ?? 'cafe';
        $estoque = (int)($_POST['estoque'] ?? 0);
        $prod_id = (int)($_POST['produtor_id'] ?? 0) ?: null;
        $regiao  = trim($_POST['regiao'] ?? '');
        $origem  = trim($_POST['origem'] ?? '');
        $peso    = trim($_POST['peso'] ?? '');
        $dest    = isset($_POST['destaque']) ? 1 : 0;
        $imagem  = trim($_POST['imagem_atual'] ?? 'imagens/cafe1.jpg');

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $dir = '../uploads/produtos/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $novo = $dir . uniqid('prod_') . '.' . $ext;
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $novo)) $imagem = ltrim($novo,'../');
            }
        }

        if ($pid) {
            $s = mysqli_prepare($conexao,"UPDATE produtos SET nome=?,descricao=?,historia=?,preco=?,categoria=?,imagem=?,estoque=?,produtor_id=?,regiao=?,origem=?,peso=?,destaque=? WHERE id=?");
            mysqli_stmt_bind_param($s,'sssdssiissiii',$nome,$desc,$hist,$preco,$cat,$imagem,$estoque,$prod_id,$regiao,$origem,$peso,$dest,$pid);
            $msg = 'Produto atualizado!';
        } else {
            $s = mysqli_prepare($conexao,"INSERT INTO produtos (nome,descricao,historia,preco,categoria,imagem,estoque,produtor_id,regiao,origem,peso,destaque) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($s,'sssdssiissii',$nome,$desc,$hist,$preco,$cat,$imagem,$estoque,$prod_id,$regiao,$origem,$peso,$dest);
            $msg = 'Produto cadastrado!';
        }
        mysqli_stmt_execute($s);
        $acao = 'lista';
    }

    if ($p_acao === 'excluir') {
        $pid = (int)($_POST['id'] ?? 0);
        mysqli_query($conexao,"DELETE FROM produtos WHERE id=$pid");
        $msg = 'Produto excluído.'; $msg_type = 'warn';
        $acao = 'lista';
    }

    if ($p_acao === 'toggle_destaque') {
        $pid = (int)($_POST['id'] ?? 0);
        mysqli_query($conexao,"UPDATE produtos SET destaque = 1 - destaque WHERE id=$pid");
        header("Location: produtos.php"); exit;
    }
}

$produto_edit = [];
if ($acao === 'form' && isset($_GET['id'])) {
    $eid = (int)$_GET['id'];
    $produto_edit = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM produtos WHERE id=$eid")) ?? [];
}

$filtro   = $_GET['filtro'] ?? '';
$busca    = trim($_GET['q'] ?? '');
$cat_filtro = $_GET['cat'] ?? '';
$where    = 'WHERE 1=1';
if ($filtro === 'sem_estoque') $where .= " AND p.estoque=0";
if ($filtro === 'destaque')    $where .= " AND p.destaque=1";
if ($cat_filtro)               $where .= " AND p.categoria='" . mysqli_real_escape_string($conexao,$cat_filtro) . "'";
if ($busca)                    $where .= " AND p.nome LIKE '%" . mysqli_real_escape_string($conexao,$busca) . "%'";

$lista = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT p.*, pr.nome AS produtor_nome,
     COALESCE(ROUND(AVG(a.nota),1),0) AS media,
     COUNT(DISTINCT a.id) AS n_av
     FROM produtos p
     LEFT JOIN produtores pr ON pr.id=p.produtor_id
     LEFT JOIN avaliacoes a ON a.produto_id=p.id
     $where GROUP BY p.id ORDER BY p.id DESC"
), MYSQLI_ASSOC);

// contagem por categoria
$cats_count = [];
$res_cats = mysqli_query($conexao,"SELECT categoria, COUNT(*) as n FROM produtos GROUP BY categoria");
while($row = mysqli_fetch_assoc($res_cats)) $cats_count[$row['categoria']] = $row['n'];

$topbar_action = ($acao==='lista')
    ? '<a href="produtos.php?acao=form" class="topbar-btn primary">+ Novo Produto</a>'
    : '<a href="produtos.php" class="topbar-btn">← Voltar</a>';

include('layout.php');
?>

<style>
/* ── PRODUTOS ADM — VISUAL RÚSTICO ─────────────────── */
.cat-tabs {
    display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px;
}
.cat-tab {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 16px; border-radius: 10px;
    border: 1.5px solid var(--border);
    background: var(--card); color: var(--text-2);
    font-size: 12px; font-weight: 600; text-decoration: none;
    transition: all .18s; white-space: nowrap;
}
.cat-tab svg { width: 16px; height: 16px; flex-shrink: 0; }
.cat-tab .count { background: var(--cream); color: var(--text-3); border-radius: 6px; padding: 1px 7px; font-size: 11px; }
.cat-tab:hover, .cat-tab.active {
    border-color: var(--green); color: var(--green);
    background: #f0f7f1;
}
.cat-tab.active .count { background: #d6edda; color: var(--green); }

.prod-search-row {
    display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;
}
.prod-search-row input {
    flex: 1; min-width: 180px;
    padding: 9px 14px; border: 1.5px solid var(--border);
    border-radius: 10px; font-size: 13px; outline: none;
    background: var(--card); color: var(--text);
    transition: border-color .18s;
}
.prod-search-row input:focus { border-color: var(--green); }
.prod-search-row select {
    padding: 9px 14px; border: 1.5px solid var(--border);
    border-radius: 10px; font-size: 13px; outline: none;
    background: var(--card); color: var(--text);
}

/* Tabela de produtos */
.prod-table { width: 100%; border-collapse: collapse; }
.prod-table thead th {
    padding: 10px 14px; text-align: left;
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--text-3);
    border-bottom: 1.5px solid var(--border);
    background: var(--cream);
}
.prod-table thead th:first-child { border-radius: 10px 0 0 0; }
.prod-table thead th:last-child  { border-radius: 0 10px 0 0; }
.prod-table tbody tr {
    transition: background .15s;
    border-bottom: 1px solid var(--border-light);
}
.prod-table tbody tr:last-child { border-bottom: none; }
.prod-table tbody tr:hover { background: #fafaf7; }
.prod-table td { padding: 12px 14px; vertical-align: middle; }

.prod-thumb {
    width: 48px; height: 48px; object-fit: cover;
    border-radius: 10px; border: 1px solid var(--border);
    background: var(--cream);
}

/* Badge de categoria com ícone */
.cat-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 8px;
    font-size: 11px; font-weight: 600;
}
.cat-badge svg { width: 12px; height: 12px; }
.cat-badge.cafe    { background: #fdf3e7; color: #92400e; }
.cat-badge.mel     { background: #fefce8; color: #854d0e; }
.cat-badge.pimenta { background: #fef2f2; color: #991b1b; }
.cat-badge.farinha { background: #f5f0e8; color: #78350f; }
.cat-badge.cereais { background: #f0fdf4; color: #166534; }

/* Estoque pill */
.estoque-pill {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 700; padding: 3px 10px;
    border-radius: 20px;
}
.estoque-pill.ok   { background: #dcfce7; color: #166534; }
.estoque-pill.low  { background: #fef9c3; color: #854d0e; }
.estoque-pill.zero { background: #fee2e2; color: #991b1b; }
.estoque-pill svg  { width: 11px; height: 11px; }

/* Estrelas */
.stars-mini { color: #F59E0B; font-size: 12px; letter-spacing: 1px; }
.stars-mini .empty { color: #E5E7EB; }

/* Destaque toggle */
.destaque-btn {
    width: 32px; height: 32px; border-radius: 8px;
    border: 1.5px solid var(--border); background: var(--card);
    display: flex; align-items: center; justify-content: center;
    transition: all .18s; cursor: pointer;
}
.destaque-btn:hover { border-color: #F59E0B; background: #fefce8; }
.destaque-btn.on    { border-color: #F59E0B; background: #fefce8; }
.destaque-btn svg   { width: 15px; height: 15px; }

/* Ações */
.action-btns { display: flex; gap: 5px; }
.act-btn {
    width: 30px; height: 30px; border-radius: 7px;
    border: 1.5px solid var(--border); background: var(--card);
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .18s;
}
.act-btn svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; }
.act-btn:hover      { border-color: var(--green); color: var(--green); background: #f0f7f1; }
.act-btn.red:hover  { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.act-btn.blue:hover { border-color: #3b82f6; color: #3b82f6; background: #eff6ff; }
</style>

<?php if ($acao === 'lista'): ?>

<!-- ABAS DE CATEGORIA -->
<?php
$total_all = array_sum($cats_count);
?>
<div class="cat-tabs">
    <a href="produtos.php" class="cat-tab <?php echo !$cat_filtro ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Todos <span class="count"><?php echo $total_all; ?></span>
    </a>
    <?php foreach ($categorias as $slug => $info): ?>
    <a href="produtos.php?cat=<?php echo $slug; ?><?php echo $filtro ? '&filtro='.$filtro : ''; ?>" class="cat-tab <?php echo $cat_filtro===$slug ? 'active' : ''; ?>">
        <?php echo $info['svg']; ?>
        <?php echo $info['label']; ?> <span class="count"><?php echo $cats_count[$slug] ?? 0; ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- BUSCA E FILTROS -->
<form method="GET" class="prod-search-row">
    <?php if ($cat_filtro): ?><input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat_filtro); ?>"><?php endif; ?>
    <input type="text" name="q" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Buscar produto…">
    <select name="filtro" onchange="this.form.submit()">
        <option value="">Todos (<?php echo count($lista); ?>)</option>
        <option value="sem_estoque" <?php echo $filtro==='sem_estoque'?'selected':''; ?>>Sem estoque</option>
        <option value="destaque"    <?php echo $filtro==='destaque'?'selected':''; ?>>Em destaque</option>
    </select>
    <button class="btn btn-gray">Buscar</button>
</form>

<!-- TABELA -->
<div class="card">
    <div class="card-header">
        <h3>
            <?php echo $cat_filtro ? ($categorias[$cat_filtro]['label'] ?? ucfirst($cat_filtro)) : 'Todos os Produtos'; ?>
            <span style="color:var(--text-3);font-weight:400;font-size:13px;"> — <?php echo count($lista); ?> produto<?php echo count($lista)!=1?'s':''; ?></span>
        </h3>
    </div>
    <table class="prod-table">
        <thead>
            <tr>
                <th style="width:60px;">Foto</th>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th>Avaliação</th>
                <th style="width:40px;text-align:center;">Dest.</th>
                <th>Produtor</th>
                <th style="width:100px;">Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $p):
            $est = (int)$p['estoque'];
            $est_class = $est <= 0 ? 'zero' : ($est <= 5 ? 'low' : 'ok');
            $cat_slug  = $p['categoria'];
            $cat_info  = $categorias[$cat_slug] ?? ['label'=>ucfirst($cat_slug),'svg'=>''];
        ?>
        <tr>
            <td>
                <img src="../<?php echo htmlspecialchars($p['imagem']); ?>" class="prod-thumb"
                     onerror="this.src='../imagens/cafe1.jpg'">
            </td>
            <td>
                <div style="font-weight:600;font-size:13px;color:var(--text);"><?php echo htmlspecialchars($p['nome']); ?></div>
                <?php if ($p['regiao']): ?><div style="font-size:11px;color:var(--text-3);margin-top:2px;"><?php echo htmlspecialchars($p['regiao']); ?></div><?php endif; ?>
            </td>
            <td>
                <span class="cat-badge <?php echo $cat_slug; ?>">
                    <?php echo $cat_info['svg']; ?>
                    <?php echo $cat_info['label']; ?>
                </span>
            </td>
            <td>
                <strong style="font-size:13px;color:var(--text);">R$ <?php echo number_format($p['preco'],2,',','.'); ?></strong>
            </td>
            <td>
                <span class="estoque-pill <?php echo $est_class; ?>">
                    <?php if ($est<=0): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Esgotado
                    <?php elseif ($est<=5): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                    <?php echo $est; ?> un.
                    <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php echo $est; ?> un.
                    <?php endif; ?>
                </span>
            </td>
            <td>
                <?php if ($p['n_av']>0): ?>
                <div class="stars-mini">
                    <?php for($s=1;$s<=5;$s++) echo $s<=round($p['media'])?'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>':'<span class="empty"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>'; ?>
                </div>
                <div style="font-size:11px;color:var(--text-3);margin-top:2px;"><?php echo $p['media']; ?> (<?php echo $p['n_av']; ?>)</div>
                <?php else: ?>
                <span style="font-size:11px;color:var(--text-4);">—</span>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="acao" value="toggle_destaque">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <button type="submit" class="destaque-btn <?php echo $p['destaque']?'on':''; ?>" title="<?php echo $p['destaque']?'Remover destaque':'Adicionar destaque'; ?>">
                        <?php if ($p['destaque']): ?>
                        <svg viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <?php endif; ?>
                    </button>
                </form>
            </td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo htmlspecialchars($p['produtor_nome']??'—'); ?></td>
            <td>
                <div class="action-btns">
                    <a href="produto_detalhe.php?id=<?php echo $p['id']; ?>" class="act-btn blue" title="Visualizar">
                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <a href="produtos.php?acao=form&id=<?php echo $p['id']; ?>" class="act-btn" title="Editar">
                        <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <form method="POST" onsubmit="return confirm('Excluir <?php echo addslashes($p['nome']); ?>?')" style="display:inline;">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="act-btn red" title="Excluir">
                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($lista)): ?>
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;stroke:var(--text-4);margin-bottom:12px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        <p>Nenhum produto encontrado</p>
    </div>
    <?php endif; ?>
</div>

<?php elseif ($acao === 'form'):
$pe = $produto_edit; $editing = !empty($pe); ?>
<div class="card" style="padding:28px;">
    <h3 style="margin-bottom:24px;font-size:16px;display:flex;align-items:center;gap:10px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;stroke:var(--green);"><?php echo $editing ? '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>' : '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>'; ?></svg>
        <?php echo $editing ? 'Editar Produto' : 'Novo Produto'; ?>
    </h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="salvar">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo $pe['id']; ?>"><?php endif; ?>
        <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($pe['imagem'] ?? 'imagens/cafe1.jpg'); ?>">

        <div class="form-grid" style="gap:20px;margin-bottom:20px;">
            <div class="field"><label>Nome *</label><input type="text" name="nome" value="<?php echo htmlspecialchars($pe['nome']??''); ?>" required></div>
            <div class="field"><label>Categoria *</label>
                <select name="categoria">
                    <?php foreach ($categorias as $v=>$info): ?>
                    <option value="<?php echo $v; ?>" <?php echo ($pe['categoria']??'')===$v?'selected':''; ?>><?php echo htmlspecialchars($info['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Preço (R$) *</label><input type="number" name="preco" step="0.01" min="0" value="<?php echo $pe['preco']??''; ?>" required></div>
            <div class="field"><label>Estoque</label><input type="number" name="estoque" min="0" value="<?php echo $pe['estoque']??'0'; ?>"></div>
            <div class="field"><label>Produtor</label>
                <select name="produtor_id">
                    <option value="">— Nenhum —</option>
                    <?php foreach ($produtores_todos as $pt): ?>
                    <option value="<?php echo $pt['id']; ?>" <?php echo ($pe['produtor_id']??'')==$pt['id']?'selected':''; ?>><?php echo htmlspecialchars($pt['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Peso / Qtd</label><input type="text" name="peso" value="<?php echo htmlspecialchars($pe['peso']??''); ?>" placeholder="500g"></div>
            <div class="field"><label>Região</label><input type="text" name="regiao" value="<?php echo htmlspecialchars($pe['regiao']??''); ?>" placeholder="Cerrado Mineiro"></div>
            <div class="field"><label>Origem</label><input type="text" name="origem" value="<?php echo htmlspecialchars($pe['origem']??''); ?>" placeholder="Patos de Minas, MG"></div>
            <div class="field form-full"><label>Descrição Curta</label><textarea name="descricao"><?php echo htmlspecialchars($pe['descricao']??''); ?></textarea></div>
            <div class="field form-full"><label>História / Storytelling</label><textarea name="historia" style="min-height:110px;"><?php echo htmlspecialchars($pe['historia']??''); ?></textarea></div>
            <div class="field form-full">
                <label>Imagem do Produto</label>
                <?php if ($editing && !empty($pe['imagem'])): ?>
                <img src="../<?php echo htmlspecialchars($pe['imagem']); ?>" style="height:80px;border-radius:10px;margin-bottom:8px;display:block;object-fit:cover;" onerror="this.style.display='none'">
                <?php endif; ?>
                <input type="file" name="imagem" accept="image/*">
                <span class="hint">JPG, PNG ou WebP. Deixe vazio para manter a imagem atual.</span>
            </div>
            <div class="field" style="flex-direction:row;align-items:center;gap:10px;">
                <input type="checkbox" name="destaque" id="destaque" <?php echo ($pe['destaque']??0)?'checked':''; ?> style="width:18px;height:18px;accent-color:var(--green);">
                <label for="destaque" style="text-transform:none;font-size:13px;cursor:pointer;">Produto em destaque</label>
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-green btn-lg"><?php echo $editing ? 'Salvar Alterações' : 'Cadastrar Produto'; ?></button>
            <a href="produtos.php" class="btn btn-gray btn-lg">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

</div></main></body></html>
