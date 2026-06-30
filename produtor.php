<?php
include("conexao.php");
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }
$stmt = mysqli_prepare($conexao,"SELECT * FROM produtores WHERE id=?");
mysqli_stmt_bind_param($stmt,'i',$id);
mysqli_stmt_execute($stmt);
$prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$prod) { header("Location: index.php"); exit; }

$sp = mysqli_prepare($conexao,
    "SELECT p.*, COALESCE(ROUND(AVG(a.nota),1),0) AS media_nota
     FROM produtos p LEFT JOIN avaliacoes a ON a.produto_id=p.id
     WHERE p.produtor_id=? GROUP BY p.id ORDER BY p.destaque DESC, p.id ASC"
);
mysqli_stmt_bind_param($sp,'i',$id);
mysqli_stmt_execute($sp);
$produtos = mysqli_fetch_all(mysqli_stmt_get_result($sp), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($prod['nome']); ?> — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ── HERO ── */
        .prod-hero {
            background: linear-gradient(160deg,rgba(10,31,13,.85),rgba(28,51,32,.83) 40%,rgba(44,74,46,.85) 100%),
                        url('imagens/bg-campo1.png') center/cover;
            padding: 0 48px 0;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .prod-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 90% 50%, rgba(44,74,46,.4) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 10% 30%, rgba(212,98,42,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .prod-hero-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: flex-start;
            gap: 40px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
            padding-top: 56px;
        }
        .prod-foto-wrap {
            flex-shrink: 0;
            width: 120px; height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,.2);
            overflow: hidden;
            background: linear-gradient(135deg,#D4622A,#e8834e);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 40px rgba(0,0,0,.4), 0 0 0 1px rgba(255,255,255,.08);
            margin-bottom: -20px;
        }
        .prod-foto-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
        .prod-foto-fallback { font-size: 46px; font-weight: 700; color: #fff; }
        .prod-hero-info { flex: 1; min-width: 240px; padding-bottom: 28px; }
        .prod-hero-eyebrow {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .12em; color: #a3d9ab;
            background: rgba(163,217,171,.12); border: 1px solid rgba(163,217,171,.25);
            padding: 4px 12px; border-radius: 20px; margin-bottom: 12px;
        }
        .prod-hero-info h1 { font-size: 34px; font-weight: 700; margin-bottom: 14px; line-height: 1.15; }
        .prod-hero-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
        .hero-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
            color: rgba(255,255,255,.85); font-size: 12px;
            padding: 5px 14px; border-radius: 20px; font-weight: 500;
        }
        .prod-hero-stats {
            display: inline-flex; gap: 0;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 14px; overflow: hidden;
        }
        .hero-stat {
            padding: 14px 24px; text-align: center;
            border-right: 1px solid rgba(255,255,255,.08);
        }
        .hero-stat:last-child { border-right: none; }
        .hero-stat strong { display: block; font-size: 22px; font-weight: 800; color: #a3d9ab; line-height: 1; }
        .hero-stat span { font-size: 10px; color: rgba(255,255,255,.5); margin-top: 3px; display: block; text-transform: uppercase; letter-spacing: .05em; }
        .prod-hero-wave {
            position: absolute; bottom: -1px; left: 0; right: 0;
        }

        /* ── BODY ── */
        .prod-body {
            max-width: 1100px;
            margin: 0 auto;
            padding: 48px 48px 80px;
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 48px;
        }

        /* ── HISTÓRIA ── */
        .historia-card {
            background: #fff;
            border: 1px solid #E8DCC8;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 40px;
        }
        .historia-card-header {
            background: #FDFAF5;
            border-bottom: 1px solid #E8DCC8;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px; font-weight: 700; color: #1C1208;
        }
        .historia-card-header span { font-size: 15px; font-weight: 700; color: #1C1208; }
        .historia-card-body { padding: 24px; }
        .historia-text { font-size: 14px; line-height: 1.9; color: #444; }

        /* ── SIDEBAR ── */
        .prod-sidebar { display: flex; flex-direction: column; gap: 20px; }

        .info-card {
            background: #fff;
            border: 1px solid #E8DCC8;
            border-radius: 16px;
            overflow: hidden;
        }
        .info-card-header {
            background: #FDFAF5;
            border-bottom: 1px solid #E8DCC8;
            padding: 14px 20px;
            font-size: 13px;
            font-weight: 700;
            color: #1C1208;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-card-body { padding: 20px; }

        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #F5EFE6;
            font-size: 13px;
        }
        .info-row:last-child { border-bottom: none; padding-bottom: 0; }
        .info-row-label { color: #9CA3AF; min-width: 72px; flex-shrink: 0; }
        .info-row-val { color: #1C1208; font-weight: 600; }

        .map-frame {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #E8DCC8;
            margin-top: 14px;
        }
        .map-frame img { width: 100%; display: block; }
        .btn-maps {
            display: block;
            margin-top: 12px;
            padding: 11px 20px;
            background: #2C4A2E;
            color: #fff;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            transition: background .2s;
        }
        .btn-maps:hover { background: #3D6B40; color: #fff; }

        /* ── PRODUTOS ── */
        .produtos-section { margin-top: 8px; }
        .produtos-section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1C1208;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #E8DCC8;
        }

        @media(max-width:768px){
            .prod-hero { padding: 32px 18px 40px; }
            .prod-hero-info h1 { font-size: 24px; }
            .prod-body { grid-template-columns: 1fr; padding: 24px 18px 60px; }
            .prod-sidebar { order: -1; }
        }
    </style>
</head>
<body data-logado="<?php echo isset($_SESSION['id'])?'1':'0'; ?>">
<?php include("includes/header.php"); ?>

<!-- HERO -->
<div class="prod-hero">
    <div class="prod-hero-inner">
        <div class="prod-foto-wrap">
            <?php if (!empty($prod['foto'])): ?>
                <img src="<?php echo htmlspecialchars($prod['foto']); ?>"
                     alt="<?php echo htmlspecialchars($prod['nome']); ?>"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <div class="prod-foto-fallback" style="display:none;"><?php echo mb_strtoupper(mb_substr($prod['nome'],0,1)); ?></div>
            <?php else: ?>
                <div class="prod-foto-fallback"><?php echo mb_strtoupper(mb_substr($prod['nome'],0,1)); ?></div>
            <?php endif; ?>
        </div>
        <div class="prod-hero-info">
            <div class="prod-hero-eyebrow">
                <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.5;"><polyline points="20 6 9 17 4 12"/></svg>
                Produtor Verificado
            </div>
            <h1><?php echo htmlspecialchars($prod['nome']); ?></h1>
            <div class="prod-hero-chips">
                <?php if ($prod['fazenda']): ?><span class="hero-chip"><svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> <?php echo htmlspecialchars($prod['fazenda']); ?></span><?php endif; ?>
                <?php if ($prod['regiao']): ?><span class="hero-chip"><svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <?php echo htmlspecialchars($prod['regiao']); ?></span><?php endif; ?>
                <?php if ($prod['estado']): ?><span class="hero-chip"><svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg> <?php echo htmlspecialchars($prod['estado']); ?></span><?php endif; ?>
            </div>
            <div class="prod-hero-stats">
                <?php
                $total_estoque = array_sum(array_column($produtos,'estoque'));
                $em_destaque   = count(array_filter($produtos, fn($p)=>$p['destaque']));
                $categorias    = array_unique(array_column($produtos,'categoria'));
                ?>
                <div class="hero-stat">
                    <strong><?php echo count($produtos); ?></strong>
                    <span>produto<?php echo count($produtos)!=1?'s':''; ?></span>
                </div>
                <?php if ($em_destaque > 0): ?>
                <div class="hero-stat">
                    <strong><?php echo $em_destaque; ?></strong>
                    <span>em destaque</span>
                </div>
                <?php endif; ?>
                <?php if (count($categorias) > 0): ?>
                <div class="hero-stat">
                    <strong><?php echo count($categorias); ?></strong>
                    <span>categoria<?php echo count($categorias)!=1?'s':''; ?></span>
                </div>
                <?php endif; ?>
                <?php if ($total_estoque > 0): ?>
                <div class="hero-stat">
                    <strong><?php echo $total_estoque; ?></strong>
                    <span>em estoque</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <svg class="prod-hero-wave" viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0 40 C360 0 1080 0 1440 40 L1440 40 L0 40Z" fill="#F7F3EC"/>
    </svg>
</div>

<div class="prod-background">

<div class="prod-body" style="background:#F7F3EC;">
    <!-- COLUNA PRINCIPAL -->
    <div>
        <div class="breadcrumb" style="display:flex;align-items:center;gap:6px;font-size:12px;color:#9CA3AF;margin-bottom:28px;">
            <a href="index.php" style="color:#9CA3AF;">Home</a> ›
            <span><?php echo htmlspecialchars($prod['nome']); ?></span>
        </div>

        <div class="historia-card">
            <div class="historia-card-header">
                <svg style="width:16px;height:16px;stroke:#2C4A2E;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg><span>História do Produtor</span>
            </div>
            <div class="historia-card-body">
                <div class="historia-text">
                    <?php echo nl2br(htmlspecialchars($prod['historia'] ?? 'Em breve.')); ?>
                </div>
            </div>
        </div>

        <style>
            .prod-background {
    background:
        linear-gradient(
            rgba(247,243,236,.22),
            rgba(247,243,236,.22)
        ),
        url('imagens/bg-campo6.png');

    background-size: cover;
    background-position: center;
    background-attachment: fixed;

    min-height: 100vh;
}
        </style>

        <?php if (!empty($produtos)): ?>
        <div class="produtos-section">
            <div class="produtos-section-title">
                <svg style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;margin-right:8px;vertical-align:middle;" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>Produtos de <?php echo htmlspecialchars(explode(' ',$prod['nome'])[0]); ?>
            </div>
            <div class="products-grid">
                <?php foreach ($produtos as $p): ?>
                <div class="product-card" data-produto-id="<?php echo $p['id']; ?>">
                    <div class="img-wrap">
                        <a href="produto.php?id=<?php echo $p['id']; ?>">
                            <img src="<?php echo htmlspecialchars($p['imagem']); ?>"
                                 alt="<?php echo htmlspecialchars($p['nome']); ?>"
                                 loading="lazy" onerror="this.src='imagens/cafe1.jpg'">
                        </a>
                        <?php if ($p['destaque']): ?><span class="badge-destaque">Destaque</span><?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><a href="produto.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></a></div>
                        <div class="product-desc"><?php echo htmlspecialchars($p['descricao']); ?></div>
                        <div class="product-footer">
                            <div class="product-price">R$ <?php echo number_format($p['preco'],2,',','.'); ?></div>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <button class="fav-btn" data-id="<?php echo $p['id']; ?>" onclick="toggleFav(<?php echo $p['id']; ?>,this)">&#9825;</button>
                                <?php if ($p['estoque']>0): ?>
                                <button class="add-btn" onclick="addToCart(<?php echo $p['id']; ?>,'<?php echo addslashes($p['nome']); ?>',<?php echo $p['preco']; ?>,'<?php echo addslashes($p['imagem']); ?>')">Adicionar</button>
                                <?php else: ?><button class="add-btn" disabled>Esgotado</button><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- SIDEBAR -->
    <aside class="prod-sidebar">
        <!-- Localização -->
        <div class="info-card">
            <div class="info-card-header"><svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Localização</div>
            <div class="info-card-body">
                <div class="info-row">
                    <span class="info-row-label">Fazenda</span>
                    <span class="info-row-val"><?php echo htmlspecialchars($prod['fazenda'] ?? '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Região</span>
                    <span class="info-row-val"><?php echo htmlspecialchars($prod['regiao'] ?? '—'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Estado</span>
                    <span class="info-row-val"><?php echo htmlspecialchars($prod['estado'] ?? '—'); ?></span>
                </div>
                <?php if ($prod['latitude'] && $prod['longitude']): ?>
                <div class="info-row">
                    <span class="info-row-label">GPS</span>
                    <span class="info-row-val" style="font-size:11px;font-family:monospace;"><?php echo $prod['latitude']; ?>, <?php echo $prod['longitude']; ?></span>
                </div>
                <div class="map-frame">
                    <img src="https://staticmap.openstreetmap.de/staticmap.php?center=<?php echo $prod['latitude']; ?>,<?php echo $prod['longitude']; ?>&zoom=10&size=280x180&maptype=mapnik&markers=<?php echo $prod['latitude']; ?>,<?php echo $prod['longitude']; ?>,red-pushpin"
                         alt="Mapa" onerror="this.closest('.map-frame').style.display='none'">
                </div>
                <?php endif; ?>
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode(($prod['fazenda']??'').' '.($prod['regiao']??'').' '.($prod['estado']??'')); ?>"
                   target="_blank" class="btn-maps">
                    <svg style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg> Abrir no Google Maps
                </a>
            </div>
        </div>

        <!-- Produtos resumo -->
        <?php if (!empty($produtos)): ?>
        <div class="info-card">
            <div class="info-card-header"><svg style="width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg> Produtos (<?php echo count($produtos); ?>)</div>
            <div class="info-card-body" style="padding:0;">
                <?php foreach (array_slice($produtos,0,4) as $p): ?>
                <a href="produto.php?id=<?php echo $p['id']; ?>"
                   style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid #F5EFE6;text-decoration:none;transition:background .15s;"
                   onmouseover="this.style.background='#FDFAF5'" onmouseout="this.style.background='transparent'">
                    <img src="<?php echo htmlspecialchars($p['imagem']); ?>"
                         style="width:40px;height:40px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid #E8DCC8;"
                         onerror="this.src='imagens/cafe1.jpg'">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:600;color:#1C1208;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($p['nome']); ?></div>
                        <div style="font-size:11px;color:#2C4A2E;font-weight:700;margin-top:2px;">R$ <?php echo number_format($p['preco'],2,',','.'); ?></div>
                    </div>
                    <?php if ($p['destaque']): ?><span style="font-size:10px;background:#FEF3E2;color:#D4622A;padding:2px 7px;border-radius:10px;font-weight:700;white-space:nowrap;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span><?php endif; ?>
                </a>
                <?php endforeach; ?>
                <?php if (count($produtos) > 4): ?>
                <div style="padding:12px 16px;font-size:12px;color:#9CA3AF;text-align:center;">+<?php echo count($produtos)-4; ?> mais produtos</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </aside>
</div>

<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
</body></html>