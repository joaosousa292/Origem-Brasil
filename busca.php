<?php
include("conexao.php");
$q = htmlspecialchars(trim($_GET['q'] ?? ''));
$produtos = [];
if ($q) {
    $like = "%$q%";
    $stmt = mysqli_prepare($conexao,
        "SELECT p.*, COALESCE(ROUND(AVG(a.nota),1),0) AS media_nota
         FROM produtos p
         LEFT JOIN avaliacoes a ON a.produto_id = p.id
         WHERE p.nome LIKE ? OR p.descricao LIKE ? OR p.regiao LIKE ? OR p.categoria LIKE ?
         GROUP BY p.id ORDER BY p.destaque DESC, p.nome ASC"
    );
    mysqli_stmt_bind_param($stmt,'ssss',$like,$like,$like,$like);
    mysqli_stmt_execute($stmt);
    $produtos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}
$total = count($produtos);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca<?php echo $q ? ": $q" : ''; ?> — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .busca-wrap{max-width:1100px;margin:0 auto;padding:48px 48px 80px;}
        .busca-form{display:flex;gap:12px;margin-bottom:40px;}
        .busca-form input{flex:1;padding:14px 18px;border:1.5px solid #E8DCC8;border-radius:12px;font-family:'Poppins',sans-serif;font-size:15px;outline:none;background:#FDFAF5;}
        .busca-form input:focus{border-color:#2C4A2E;}
        .busca-form button{padding:14px 28px;background:#2C4A2E;color:#fff;border:none;border-radius:12px;font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s;}
        .busca-form button:hover{background:#3D6B40;}
        @media(max-width:600px){.busca-wrap{padding:28px 16px 60px;}}
    </style>
</head>
<body data-logado="<?php echo isset($_SESSION['id'])?'1':'0'; ?>">
<?php include("includes/header.php"); ?>
<div class="page-title-block">
    <div class="breadcrumb"><a href="index.php">Home</a> › <span>Busca</span></div>
    <h1><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Buscar Produtos</h1>
</div>
<div class="busca-wrap">
    <form class="busca-form" method="GET" action="busca.php">
        <input type="text" name="q" placeholder="Buscar por nome, região, categoria…" value="<?php echo $q; ?>" autofocus>
        <button type="submit">Buscar</button>
    </form>

    <?php if ($q): ?>
        <p style="font-size:14px;color:#7A6248;margin-bottom:28px;">
            <?php echo $total; ?> resultado<?php echo $total!==1?'s':''; ?> para <strong>"<?php echo $q; ?>"</strong>
        </p>
        <?php if (empty($produtos)): ?>
            <div style="text-align:center;padding:48px 0;">
                <span style="font-size:52px;display:block;margin-bottom:14px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></span>
                <p style="color:#9CA3AF;font-size:15px;">Nenhum produto encontrado. Tente outros termos.</p>
            </div>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($produtos as $p): ?>
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
                            <button class="fav-btn" data-id="<?php echo $p['id']; ?>" onclick="toggleFav(<?php echo $p['id']; ?>,this)"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
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
    <?php else: ?>
        <div style="text-align:center;padding:48px 0;color:#9CA3AF;">
            <span style="font-size:52px;display:block;margin-bottom:14px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
            <p>Digite algo para pesquisar…</p>
        </div>
    <?php endif; ?>
</div>
<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
</body></html>
