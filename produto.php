<?php
include("conexao.php");

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }

$stmt = mysqli_prepare($conexao,
    "SELECT p.*,
            pr.nome AS produtor_nome, pr.foto AS produtor_foto, pr.fazenda, pr.regiao AS produtor_regiao, pr.estado, pr.historia AS produtor_historia,
            COALESCE(ROUND(AVG(a.nota),1), 0) AS media_nota,
            COUNT(a.id) AS total_avaliacoes
     FROM produtos p
     LEFT JOIN produtores pr ON pr.id = p.produtor_id
     LEFT JOIN avaliacoes a  ON a.produto_id = p.id
     WHERE p.id = ?
     GROUP BY p.id"
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$p) { header("Location: index.php"); exit; }

$rel = mysqli_prepare($conexao,
    "SELECT id, nome, preco, imagem, regiao, estoque FROM produtos
     WHERE categoria = ? AND id != ?
     ORDER BY RAND() LIMIT 4"
);
mysqli_stmt_bind_param($rel, 'si', $p['categoria'], $id);
mysqli_stmt_execute($rel);
$relacionados = mysqli_fetch_all(mysqli_stmt_get_result($rel), MYSQLI_ASSOC);

$av_stmt = mysqli_prepare($conexao,
    "SELECT a.nota, a.comentario, a.criado_em, u.nome
     FROM avaliacoes a JOIN usuarios u ON u.id = a.usuario_id
     WHERE a.produto_id = ?
     ORDER BY a.criado_em DESC LIMIT 10"
);
mysqli_stmt_bind_param($av_stmt, 'i', $id);
mysqli_stmt_execute($av_stmt);
$avaliacoes = mysqli_fetch_all(mysqli_stmt_get_result($av_stmt), MYSQLI_ASSOC);

$ja_avaliou = false;
if (isset($_SESSION['id'])) {
    $chk = mysqli_prepare($conexao, "SELECT id FROM avaliacoes WHERE usuario_id = ? AND produto_id = ?");
    mysqli_stmt_bind_param($chk, 'ii', $_SESSION['id'], $id);
    mysqli_stmt_execute($chk);
    $ja_avaliou = (bool)mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($p['nome']); ?> — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Hero da página de produto */
        .produto-hero {
            background: linear-gradient(160deg, rgba(10,31,13,.84) 0%, rgba(28,51,32,.80) 50%, rgba(44,74,46,.84) 100%),
                        url('imagens/bg-campo3.png') center/cover;
            padding: 36px 48px 40px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .produto-hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        .produto-hero-breadcrumb {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px; color: rgba(255,255,255,.6);
            margin-bottom: 10px;
        }
        .produto-hero-breadcrumb a { color: rgba(255,255,255,.6); transition: color .15s; }
        .produto-hero-breadcrumb a:hover { color: #fff; }
        .produto-hero-breadcrumb svg { width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2; }
        .produto-hero h1 { font-size: 24px; font-weight: 700; line-height: 1.25; color: #fff; max-width: 600px; }
        .produto-hero-meta { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
        .produto-hero-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
            color: rgba(255,255,255,.85); font-size: 11px; font-weight: 500;
            padding: 4px 12px; border-radius: 20px;
        }
        .produto-hero-price {
            font-size: 28px; font-weight: 800; color: #a3d9ab;
            flex-shrink: 0;
        }
        .produto-hero-price small { font-size: 13px; font-weight: 400; color: rgba(255,255,255,.6); display: block; margin-bottom: 2px; }
        .produto-hero-wave { position: absolute; bottom: -1px; left: 0; right: 0; }

        .produto-wrap { max-width: 1200px; margin: 0 auto; padding: 40px 48px 80px; }

        /* Breadcrumb */
        .breadcrumb-bar { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #9CA3AF; margin-bottom: 36px; }
        .breadcrumb-bar a { color: #9CA3AF; transition: color .15s; }
        .breadcrumb-bar a:hover { color: #2C4A2E; }
        .breadcrumb-bar svg { width: 12px; height: 12px; stroke: #C4B5A0; fill: none; stroke-width: 2; }

        /* Layout principal */
        .produto-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; margin-bottom: 72px; }
        .produto-imgs { position: sticky; top: 100px; }

        /* Imagem */
        .img-principal {
            width: 100%; padding-top: 88%; position: relative;
            border-radius: 20px; overflow: hidden;
            background: #FDFAF5; border: 1.5px solid #E8DCC8;
        }
        .img-principal img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .qr-chip {
            position: absolute; bottom: 16px; right: 16px;
            background: rgba(255,255,255,.95); border-radius: 10px;
            padding: 8px 14px; display: flex; align-items: center; gap: 7px;
            font-size: 11px; font-weight: 700; color: #2C4A2E;
            cursor: pointer; box-shadow: 0 3px 14px rgba(0,0,0,.13);
            border: 1px solid rgba(44,74,46,.15);
            transition: all .18s;
        }
        .qr-chip:hover { background: #2C4A2E; color: #fff; }
        .qr-chip svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; }
        .badge-categoria {
            position: absolute; top: 14px; left: 14px;
            background: rgba(255,255,255,.92);
            border-radius: 8px; padding: 5px 12px;
            font-size: 11px; font-weight: 700; color: #2C4A2E;
            letter-spacing: .04em; text-transform: uppercase;
            border: 1px solid rgba(44,74,46,.15);
        }

        /* Info */
        .produto-chips {
            display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 18px;
        }
        .chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f0f7f1; color: #2C4A2E;
            border: 1px solid #c8e0cc;
            font-size: 11px; font-weight: 600;
            padding: 5px 12px; border-radius: 20px;
        }
        .chip svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2; }

        .produto-nome {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(22px, 3vw, 30px);
            font-weight: 700; color: #1C1208;
            line-height: 1.25; margin-bottom: 14px;
        }

        /* Estrelas */
        .stars-row { display: flex; align-items: center; gap: 7px; margin-bottom: 22px; }
        .star-icon { font-size: 17px; color: #F59E0B; }
        .star-icon.empty { color: #E5E7EB; }
        .stars-row .meta { font-size: 12.5px; color: #9CA3AF; }

        /* Preço */
        .preco-bloco { margin-bottom: 6px; }
        .produto-preco {
            font-size: 38px; font-weight: 800;
            color: #1C3320; line-height: 1;
        }
        .produto-preco sup { font-size: 18px; font-weight: 600; vertical-align: super; }
        .produto-peso { font-size: 13px; color: #9CA3AF; margin-top: 4px; margin-bottom: 20px; }

        /* Estoque */
        .estoque-tag {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 700;
            padding: 6px 14px; border-radius: 20px; margin-bottom: 22px;
        }
        .estoque-tag svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2.5; }
        .estoque-tag.ok   { background: #dcfce7; color: #166534; }
        .estoque-tag.low  { background: #fef9c3; color: #854d0e; }
        .estoque-tag.zero { background: #fee2e2; color: #991b1b; }

        /* Quantidade */
        .qty-row {
            display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
        }
        .qty-label { font-size: 12px; font-weight: 600; color: #7A6248; text-transform: uppercase; letter-spacing: .06em; }
        .qty-ctrl {
            display: flex; align-items: center;
            border: 1.5px solid #E8DCC8; border-radius: 10px;
            overflow: hidden; background: #fff;
        }
        .qty-ctrl button {
            width: 36px; height: 36px; border: none; background: none;
            font-size: 18px; color: #7A6248; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        .qty-ctrl button:hover { background: #f0f7f1; color: #2C4A2E; }
        .qty-ctrl span { font-size: 14px; font-weight: 700; min-width: 32px; text-align: center; color: #1C1208; }

        /* Botões */
        .btn-comprar {
            width: 100%; padding: 16px;
            background: linear-gradient(135deg, #1C3320, #2C4A2E);
            color: #fff; border: none; border-radius: 14px;
            font-size: 15px; font-weight: 700; margin-bottom: 10px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: all .2s;
        }
        .btn-comprar svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; }
        .btn-comprar:hover { background: linear-gradient(135deg, #243d28, #3D6B40); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(44,74,46,.25); }
        .btn-comprar:disabled { background: #D1D5DB; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-fav-full {
            width: 100%; padding: 13px;
            background: transparent; border: 1.5px solid #E8DCC8;
            border-radius: 14px; font-size: 13.5px; font-weight: 600;
            color: #7A6248; margin-bottom: 28px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all .2s;
        }
        .btn-fav-full svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }
        .btn-fav-full:hover, .btn-fav-full.active { border-color: #EF4444; color: #EF4444; background: #FEF2F2; }

        /* Tabs */
        .info-tabs { display: flex; gap: 0; border-bottom: 1.5px solid #E8DCC8; margin-bottom: 24px; }
        .tab-btn {
            background: none; border: none; padding: 12px 18px;
            font-size: 13px; font-weight: 600; color: #9CA3AF;
            border-bottom: 2px solid transparent; margin-bottom: -1.5px;
            transition: color .2s, border-color .2s; cursor: pointer;
        }
        .tab-btn.active { color: #2C4A2E; border-bottom-color: #2C4A2E; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }
        .tab-text { font-size: 14px; line-height: 1.85; color: #4a3728; }

        /* Card produtor */
        .produtor-card-box { background: #fff; border: 1.5px solid #E8DCC8; border-radius: 18px; overflow: hidden; }
        .produtor-card-top { background: linear-gradient(135deg, rgba(28,51,32,.90), rgba(44,74,46,.90)),url('imagens/bg-campo2.png') center/cover; padding: 22px 20px 32px; position: relative; }
        .produtor-foto-row { display: flex; align-items: center; gap: 14px; }
        .produtor-foto {
            width: 56px; height: 56px; border-radius: 50%;
            border: 3px solid rgba(255,255,255,.25); overflow: hidden;
            background: linear-gradient(135deg, #D4622A, #e8834e);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .produtor-foto img { width: 100%; height: 100%; object-fit: cover; }
        .produtor-foto-fb { font-size: 22px; font-weight: 700; color: #fff; }
        .produtor-nome-box h3 { font-size: 15px; font-weight: 700; color: #fff; margin: 0 0 3px; }
        .produtor-nome-box p { font-size: 11px; color: rgba(255,255,255,.6); margin: 0; }
        .produtor-chips { position: absolute; bottom: -14px; left: 20px; display: flex; gap: 6px; }
        .produtor-chip {
            background: #fff; border: 1px solid #E8DCC8;
            border-radius: 20px; font-size: 11px; font-weight: 600;
            color: #2C4A2E; padding: 4px 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            display: inline-flex; align-items: center; gap: 5px;
        }
        .produtor-chip svg { width: 11px; height: 11px; stroke: currentColor; fill: none; stroke-width: 2; }
        .produtor-hist { padding: 28px 20px 16px; font-size: 13px; line-height: 1.75; color: #555; }
        .produtor-link {
            display: block; margin: 0 20px 20px;
            padding: 11px; background: #f0f7f1;
            border: 1px solid #c8e0cc; border-radius: 10px;
            font-size: 12px; font-weight: 700; color: #2C4A2E;
            text-align: center; transition: background .2s;
        }
        .produtor-link:hover { background: #daeedd; }

        /* Avaliações */
        .secao-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px; font-weight: 700; color: #1C1208;
            margin-bottom: 24px;
            display: flex; align-items: center; gap: 10px;
        }
        .secao-title svg { width: 20px; height: 20px; stroke: #F59E0B; fill: #FEF3C7; stroke-width: 1.5; }
        .av-card { background: #FDFAF5; border: 1.5px solid #E8DCC8; border-radius: 14px; padding: 18px 20px; margin-bottom: 12px; }
        .av-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .av-autor { font-size: 13px; font-weight: 700; color: #1C1208; }
        .av-data { font-size: 11px; color: #C4B5A0; }
        .av-stars { margin-bottom: 8px; }
        .av-stars span { font-size: 15px; }
        .av-texto { font-size: 13px; line-height: 1.65; color: #5a4535; }
        .form-av { background: #fff; border: 1.5px solid #E8DCC8; border-radius: 18px; padding: 26px; margin-top: 24px; }
        .form-av h3 { font-size: 15px; font-weight: 700; color: #1C1208; margin-bottom: 18px; }
        .star-select { display: flex; gap: 6px; margin-bottom: 16px; }
        .star-select span { font-size: 32px; cursor: pointer; color: #E5E7EB; transition: color .12s, transform .12s; }
        .star-select span:hover, .star-select span.selected { color: #F59E0B; transform: scale(1.15); }
        .form-av textarea {
            width: 100%; border: 1.5px solid #E8DCC8; border-radius: 10px;
            padding: 12px 14px; font-family: 'Poppins', sans-serif;
            font-size: 13px; resize: vertical; min-height: 80px;
            outline: none; background: #FDFAF5; margin-bottom: 14px;
            transition: border-color .18s;
        }
        .form-av textarea:focus { border-color: #2C4A2E; }
        .btn-avaliar {
            padding: 12px 28px; background: #2C4A2E; color: #fff;
            border: none; border-radius: 10px; font-weight: 700;
            font-size: 13px; transition: background .2s; cursor: pointer;
        }
        .btn-avaliar:hover { background: #3D6B40; }

        /* Relacionados */
        .secao-relacionados {
            margin-top: 72px;
            background: linear-gradient(rgba(247,243,236,.88), rgba(247,243,236,.88)),
                        url('imagens/bg-campo5.png') center/cover fixed;
            border-radius: 20px;
            padding: 36px 32px;
        }

        /* QR Modal */
        .modal-qr {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.65); z-index: 2000;
            align-items: center; justify-content: center;
        }
        .modal-qr-box {
            background: #fff; border-radius: 22px; padding: 40px 36px;
            max-width: 400px; width: 90%; text-align: center;
            animation: popIn .28s ease;
        }
        .modal-qr-icon {
            width: 64px; height: 64px; background: #f0f7f1;
            border-radius: 18px; margin: 0 auto 20px;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-qr-icon svg { width: 30px; height: 30px; stroke: #2C4A2E; fill: none; stroke-width: 1.8; }
        .modal-qr-box h2 { font-family: 'Playfair Display', serif; font-size: 21px; margin-bottom: 10px; color: #1C1208; }
        .modal-qr-box p { font-size: 13px; color: #7A6248; line-height: 1.65; margin-bottom: 20px; }

        @media(max-width:768px){
            .produto-wrap { padding: 20px 18px 60px; }
            .produto-grid { grid-template-columns: 1fr; gap: 32px; }
            .produto-imgs { position: static; }
        }
        @keyframes popIn { from{opacity:0;transform:scale(.88)} to{opacity:1;transform:scale(1)} }
    </style>
</head>
<body data-logado="<?php echo isset($_SESSION['id']) ? '1' : '0'; ?>">

<?php include("includes/header.php"); ?>

<!-- HERO DO PRODUTO -->
<div class="produto-hero">
    <div class="produto-hero-inner">
        <div>
            <div class="produto-hero-breadcrumb">
                <a href="index.php">Home</a>
                <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                <a href="<?php echo $p['categoria']; ?>.php"><?php echo ucfirst($p['categoria']); ?>s</a>
                <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                <span style="color:rgba(255,255,255,.85);"><?php echo htmlspecialchars($p['nome']); ?></span>
            </div>
            <h1><?php echo htmlspecialchars($p['nome']); ?></h1>
            <div class="produto-hero-meta">
                <?php if ($p['regiao']): ?>
                <span class="produto-hero-chip">
                    <svg viewBox="0 0 24 24" style="width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?php echo htmlspecialchars($p['regiao']); ?>
                </span>
                <?php endif; ?>
                <?php if ($p['categoria']): ?>
                <span class="produto-hero-chip">
                    <?php echo ucfirst($p['categoria']); ?>
                </span>
                <?php endif; ?>
                <?php if ($p['total_avaliacoes'] > 0): ?>
                <span class="produto-hero-chip">
                    <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> <?php echo $p['media_nota']; ?> (<?php echo $p['total_avaliacoes']; ?> avaliações)
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="produto-hero-price">
            <small>a partir de</small>
            R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?>
        </div>
    </div>
    <svg class="produto-hero-wave" viewBox="0 0 1440 36" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0 36 C360 0 1080 0 1440 36 L1440 36 L0 36Z" fill="#F7F3EC"/>
    </svg>
</div>

<div class="produto-wrap">

    <div class="produto-grid">
        <!-- IMAGEM -->
        <div class="produto-imgs">
            <div class="img-principal">
                <img src="<?php echo htmlspecialchars($p['imagem']); ?>"
                     alt="<?php echo htmlspecialchars($p['nome']); ?>"
                     onerror="this.src='imagens/cafe1.jpg'">
                <div class="badge-categoria"><?php echo ucfirst($p['categoria']); ?></div>
                <div class="qr-chip" onclick="document.getElementById('modal-qr').style.display='flex'">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/></svg>
                    Ver Origem
                </div>
            </div>
        </div>

        <!-- INFO -->
        <div class="produto-info">
            <!-- Chips de origem -->
            <div class="produto-chips">
                <?php if ($p['regiao']): ?>
                <span class="chip">
                    <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?php echo htmlspecialchars($p['regiao']); ?>
                </span>
                <?php endif; ?>
                <?php if ($p['origem']): ?>
                <span class="chip">
                    <svg viewBox="0 0 24 24"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/></svg>
                    <?php echo htmlspecialchars($p['origem']); ?>
                </span>
                <?php endif; ?>
                <?php if ($p['peso']): ?>
                <span class="chip">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?php echo htmlspecialchars($p['peso']); ?>
                </span>
                <?php endif; ?>
            </div>

            <h1 class="produto-nome"><?php echo htmlspecialchars($p['nome']); ?></h1>

            <!-- Estrelas -->
            <?php if ($p['total_avaliacoes'] > 0): ?>
            <div class="stars-row">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                    <span class="star-icon <?php echo $s <= round($p['media_nota']) ? '' : 'empty'; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                <?php endfor; ?>
                <span class="meta"><?php echo $p['media_nota']; ?> &middot; <?php echo $p['total_avaliacoes']; ?> avaliações</span>
            </div>
            <?php else: ?>
            <div class="stars-row">
                <span class="star-icon empty"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span><span class="star-icon empty"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span><span class="star-icon empty"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                <span class="star-icon empty"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span><span class="star-icon empty"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                <span class="meta">Sem avaliações — seja o primeiro</span>
            </div>
            <?php endif; ?>

            <!-- Preço -->
            <div class="preco-bloco">
                <div class="produto-preco">
                    <sup>R$</sup><?php echo number_format($p['preco'], 2, ',', '.'); ?>
                </div>
            </div>

            <!-- Estoque -->
            <?php
            $est = (int)$p['estoque'];
            if ($est <= 0):
            ?><span class="estoque-tag zero"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Sem estoque</span>
            <?php elseif ($est <= 5): ?>
            <span class="estoque-tag low"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg> Últimas <?php echo $est; ?> unidades</span>
            <?php else: ?>
            <span class="estoque-tag ok"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Em estoque &middot; <?php echo $est; ?> un.</span>
            <?php endif; ?>

            <?php if ($est > 0): ?>
            <!-- Quantidade -->
            <div class="qty-row">
                <span class="qty-label">Qtd.</span>
                <div class="qty-ctrl">
                    <button type="button" onclick="changeQtyLocal(-1)">−</button>
                    <span id="qty-display">1</span>
                    <button type="button" onclick="changeQtyLocal(1)">+</button>
                </div>
            </div>

            <button class="btn-comprar" id="btn-add"
                    onclick="addToCartQty(<?php echo $p['id']; ?>,'<?php echo addslashes($p['nome']); ?>',<?php echo $p['preco']; ?>,'<?php echo addslashes($p['imagem']); ?>')">
                <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                Adicionar ao Carrinho
            </button>
            <?php else: ?>
            <button class="btn-comprar" disabled>
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Produto Esgotado
            </button>
            <?php endif; ?>

            <button class="btn-fav-full fav-btn" data-id="<?php echo $p['id']; ?>"
                    onclick="toggleFav(<?php echo $p['id']; ?>, this)">
                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Adicionar aos Favoritos
            </button>

            <!-- TABS -->
            <div class="info-tabs">
                <button class="tab-btn active" onclick="switchTab('descricao', this)">Descrição</button>
                <button class="tab-btn" onclick="switchTab('historia', this)">História</button>
                <?php if ($p['produtor_nome']): ?>
                <button class="tab-btn" onclick="switchTab('produtor', this)">Produtor</button>
                <?php endif; ?>
            </div>

            <div class="tab-panel active" id="tab-descricao">
                <div class="tab-text"><?php echo nl2br(htmlspecialchars($p['descricao'])); ?></div>
            </div>

            <div class="tab-panel" id="tab-historia">
                <div class="tab-text">
                    <?php echo $p['historia'] ? nl2br(htmlspecialchars($p['historia'])) : '<span style="color:#C4B5A0;">Em breve.</span>'; ?>
                </div>
            </div>

            <?php if ($p['produtor_nome']): ?>
            <div class="tab-panel" id="tab-produtor">
                <div class="produtor-card-box">
                    <div class="produtor-card-top">
                        <div class="produtor-foto-row">
                            <div class="produtor-foto">
                                <?php if (!empty($p['produtor_foto'])): ?>
                                    <img src="<?php echo htmlspecialchars($p['produtor_foto']); ?>"
                                         alt="<?php echo htmlspecialchars($p['produtor_nome']); ?>"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                    <div class="produtor-foto-fb" style="display:none;"><?php echo mb_strtoupper(mb_substr($p['produtor_nome'],0,1)); ?></div>
                                <?php else: ?>
                                    <div class="produtor-foto-fb"><?php echo mb_strtoupper(mb_substr($p['produtor_nome'],0,1)); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="produtor-nome-box">
                                <h3><?php echo htmlspecialchars($p['produtor_nome']); ?></h3>
                                <p><?php echo htmlspecialchars($p['fazenda'] ?? ''); ?></p>
                            </div>
                        </div>
                        <div class="produtor-chips">
                            <span class="produtor-chip">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?php echo htmlspecialchars($p['produtor_regiao']); ?>, <?php echo htmlspecialchars($p['estado']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="produtor-hist">
                        <?php echo nl2br(htmlspecialchars(mb_substr($p['produtor_historia'] ?? '', 0, 320))); ?>…
                    </div>
                    <a href="produtor.php?id=<?php echo $p['produtor_id']; ?>" class="produtor-link">
                        Ver história completa e todos os produtos →
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AVALIAÇÕES -->
    <section style="margin-top:64px;">
        <h2 class="secao-title">
            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Avaliações dos Clientes
        </h2>

        <?php if (!empty($avaliacoes)): ?>
            <?php foreach ($avaliacoes as $av): ?>
            <div class="av-card">
                <div class="av-header">
                    <span class="av-autor"><?php echo htmlspecialchars($av['nome']); ?></span>
                    <span class="av-data"><?php echo date('d/m/Y', strtotime($av['criado_em'])); ?></span>
                </div>
                <div class="av-stars">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <span style="color:<?php echo $s <= $av['nota'] ? '#F59E0B' : '#E5E7EB'; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                    <?php endfor; ?>
                </div>
                <?php if ($av['comentario']): ?>
                <div class="av-texto"><?php echo htmlspecialchars($av['comentario']); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#C4B5A0;font-size:14px;margin-bottom:24px;">Seja o primeiro a avaliar este produto.</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['id']) && !$ja_avaliou): ?>
        <div class="form-av">
            <h3>Sua Avaliação</h3>
            <div class="star-select" id="star-select">
                <?php for($s=1;$s<=5;$s++) echo '<span data-nota="'.$s.'" onclick="selecionarNota('.$s.')"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>'; ?>
            </div>
            <input type="hidden" id="nota-val" value="0">
            <textarea id="comentario-av" placeholder="Conte como foi sua experiência com este produto…"></textarea>
            <button class="btn-avaliar" onclick="enviarAvaliacao()">Publicar Avaliação</button>
        </div>
        <?php elseif (isset($_SESSION['id']) && $ja_avaliou): ?>
        <p style="color:#7A6248;font-size:13px;background:#FDFAF5;padding:14px 18px;border-radius:10px;border:1.5px solid #E8DCC8;display:flex;align-items:center;gap:8px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="#2C4A2E" stroke-width="2.5" style="width:16px;height:16px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
            Você já avaliou este produto. Obrigado pelo feedback!
        </p>
        <?php else: ?>
        <p style="color:#7A6248;font-size:13px;background:#FDFAF5;padding:14px 18px;border-radius:10px;border:1.5px solid #E8DCC8;">
            <a href="login.php?voltar=produto.php?id=<?php echo $id; ?>" style="color:#2C4A2E;font-weight:700;">Faça login</a> para deixar sua avaliação.
        </p>
        <?php endif; ?>
    </section>

    <!-- RELACIONADOS -->
    <?php if (!empty($relacionados)): ?>
        <h2 class="secao-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="#2C4A2E" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Você também pode gostar
        </h2>
        <div class="products-grid">
            <?php foreach ($relacionados as $rel): ?>
            <div class="product-card">
                <div class="img-wrap">
                    <a href="produto.php?id=<?php echo $rel['id']; ?>">
                        <img src="<?php echo htmlspecialchars($rel['imagem']); ?>"
                             alt="<?php echo htmlspecialchars($rel['nome']); ?>"
                             onerror="this.src='imagens/cafe1.jpg'" loading="lazy">
                    </a>
                </div>
                <div class="product-info">
                    <?php if ($rel['regiao']): ?>
                    <div class="product-origin"><?php echo htmlspecialchars($rel['regiao']); ?></div>
                    <?php endif; ?>
                    <div class="product-name">
                        <a href="produto.php?id=<?php echo $rel['id']; ?>"><?php echo htmlspecialchars($rel['nome']); ?></a>
                    </div>
                    <div class="product-footer">
                        <div class="product-price">R$ <?php echo number_format($rel['preco'], 2, ',', '.'); ?></div>
                        <div style="display:flex;gap:6px;">
                            <?php if ($rel['estoque'] > 0): ?>
                            <button class="add-btn" onclick="addToCart(<?php echo $rel['id']; ?>,'<?php echo addslashes($rel['nome']); ?>',<?php echo $rel['preco']; ?>,'<?php echo addslashes($rel['imagem']); ?>')">Adicionar</button>
                            <?php else: ?>
                            <a href="produto.php?id=<?php echo $rel['id']; ?>" class="add-btn">Ver →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- MODAL QR -->
<div id="modal-qr" class="modal-qr">
    <div class="modal-qr-box">
        <div class="modal-qr-icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/><line x1="20" y1="14" x2="20" y2="14"/><line x1="20" y1="20" x2="20" y2="20"/><line x1="14" y1="20" x2="14" y2="20"/></svg>
        </div>
        <h2>Rastreabilidade</h2>
        <p>
            Este produto vem de <strong><?php echo htmlspecialchars($p['origem'] ?? $p['regiao'] ?? 'Brasil'); ?></strong>.
            <?php if ($p['produtor_nome']): ?>
            <br>Produzido por <strong><?php echo htmlspecialchars($p['produtor_nome']); ?></strong><?php if ($p['fazenda']): ?>, fazenda <?php echo htmlspecialchars($p['fazenda']); ?><?php endif; ?>.
            <?php endif; ?>
        </p>
        <?php if ($p['produtor_id']): ?>
        <a href="produtor.php?id=<?php echo $p['produtor_id']; ?>"
           style="display:inline-block;padding:12px 26px;background:#2C4A2E;color:#fff;border-radius:10px;font-weight:700;font-size:13px;margin-bottom:14px;">
            Ver Fazenda Completa
        </a><br>
        <?php endif; ?>
        <button onclick="document.getElementById('modal-qr').style.display='none'"
                style="background:none;border:none;color:#C4B5A0;font-size:13px;cursor:pointer;margin-top:8px;">
            Fechar
        </button>
    </div>
</div>

<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
<script>
let qtyLocal = 1;
function changeQtyLocal(d) {
    qtyLocal = Math.max(1, qtyLocal + d);
    document.getElementById('qty-display').textContent = qtyLocal;
}
async function addToCartQty(pid, nome, preco, imagem) {
    if (!estaLogado()) { pedirLogin(); return; }
    const btn = document.getElementById('btn-add');
    btn.disabled = true;
    for (let i = 0; i < qtyLocal; i++) {
        await apiPost('adicionar', { produto_id: pid, quantidade: 1 });
    }
    btn.disabled = false;
    showToast(`${nome} adicionado ao carrinho`, 'success');
    await renderCart();
    document.getElementById('cart-sidebar')?.classList.add('active');
    document.getElementById('cart-overlay')?.classList.add('active');
}
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}
let notaSelecionada = 0;
function selecionarNota(nota) {
    notaSelecionada = nota;
    document.getElementById('nota-val').value = nota;
    document.querySelectorAll('#star-select span').forEach((s, i) => {
        s.classList.toggle('selected', i < nota);
    });
}
async function enviarAvaliacao() {
    if (notaSelecionada === 0) { showToast('Selecione uma nota de 1 a 5 estrelas.', 'warning'); return; }
    const comentario = document.getElementById('comentario-av').value;
    const fd = new FormData();
    fd.append('action', 'salvar');
    fd.append('produto_id', <?php echo $id; ?>);
    fd.append('nota', notaSelecionada);
    fd.append('comentario', comentario);
    const res = await fetch('api/avaliacoes.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.ok) { showToast('Avaliação publicada!', 'success'); setTimeout(() => location.reload(), 1200); }
    else { showToast(data.erro || 'Erro ao salvar.', 'error'); }
}
</script>
<?php include("includes/footer.php"); ?>
</body>
</html>