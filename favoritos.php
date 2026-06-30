<?php
include("conexao.php");
if (!isset($_SESSION['id'])) { header("Location: login.php?voltar=favoritos.php"); exit; }
$uid = (int)$_SESSION['id'];
$stmt = mysqli_prepare($conexao,
    "SELECT p.*, f.id as fav_id FROM favoritos f
     JOIN produtos p ON p.id = f.produto_id
     WHERE f.usuario_id = ? ORDER BY f.id DESC"
);
mysqli_stmt_bind_param($stmt,'i',$uid);
mysqli_stmt_execute($stmt);
$favs = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Favoritos — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-logado="1">
<?php include("includes/header.php"); ?>
<div class="page-title-block">
    <div class="breadcrumb"><a href="index.php">Home</a> › <span>Favoritos</span></div>
    <h1><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#ef4444;fill:#ef4444;stroke-width:2;" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> Meus Favoritos</h1>
</div>
<div style="max-width:1200px;margin:0 auto;padding:40px 48px 80px;">
    <?php if (empty($favs)): ?>
    <div style="text-align:center;padding:80px 20px;">
        <span style="font-size:64px;display:block;margin-bottom:16px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#ef4444;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/><line x1="12" y1="5.67" x2="12" y2="14"/></svg></span>
        <h2 style="font-size:20px;color:#1C1208;margin-bottom:10px;">Nenhum favorito ainda</h2>
        <p style="color:#9CA3AF;margin-bottom:24px;">Explore nossos produtos e clique em <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> para salvar.</p>
        <a href="cafes.php" style="display:inline-block;padding:12px 28px;background:#2C4A2E;color:#fff;border-radius:10px;font-weight:600;">Ver Produtos</a>
    </div>
    <?php else: ?>
    <p style="font-size:13px;color:#7A6248;margin-bottom:28px;"><?php echo count($favs); ?> produto<?php echo count($favs)!==1?'s':''; ?> salvo<?php echo count($favs)!==1?'s':''; ?></p>
    <div class="products-grid">
        <?php foreach ($favs as $p): ?>
        <div class="product-card" data-produto-id="<?php echo $p['id']; ?>">
            <div class="img-wrap">
                <a href="produto.php?id=<?php echo $p['id']; ?>">
                    <img src="<?php echo htmlspecialchars($p['imagem']); ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>" loading="lazy" onerror="this.src='imagens/cafe/cafe1.jpg'">
                </a>
                <?php if ($p['estoque']==0): ?><span class="badge-sem-estoque">Esgotado</span><?php endif; ?>
            </div>
            <div class="product-info">
                <?php if ($p['regiao']): ?><div class="product-origin"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <?php echo htmlspecialchars($p['regiao']); ?></div><?php endif; ?>
                <div class="product-name"><a href="produto.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></a></div>
                <div class="product-desc"><?php echo htmlspecialchars($p['descricao']); ?></div>
                <div class="product-footer">
                    <div class="product-price">R$ <?php echo number_format($p['preco'],2,',','.'); ?></div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <button class="fav-btn active" data-id="<?php echo $p['id']; ?>"
                                onclick="toggleFav(<?php echo $p['id']; ?>,this);setTimeout(()=>location.reload(),800)"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:currentColor;stroke-width:2;" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
                        <?php if ($p['estoque']>0): ?>
                        <button class="add-btn" onclick="addToCart(<?php echo $p['id']; ?>,'<?php echo addslashes($p['nome']); ?>',<?php echo $p['preco']; ?>,'<?php echo addslashes($p['imagem']); ?>')">Adicionar</button>
                        <?php else: ?><button class="add-btn" disabled>Esgotado</button><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
</body></html>
