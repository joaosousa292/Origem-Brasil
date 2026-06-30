<?php
include("conexao.php");
if (!isset($_SESSION['id'])) { header("Location: login.php?voltar=pedidos.php"); exit; }
$uid = (int)$_SESSION['id'];

$pedidos_stmt = mysqli_prepare($conexao,
    "SELECT p.*, COUNT(i.id) AS total_itens
     FROM pedidos p
     LEFT JOIN pedido_itens i ON i.pedido_id = p.id
     WHERE p.usuario_id = ?
     GROUP BY p.id ORDER BY p.data_pedido DESC"
);
mysqli_stmt_bind_param($pedidos_stmt,'i',$uid);
mysqli_stmt_execute($pedidos_stmt);
$pedidos = mysqli_fetch_all(mysqli_stmt_get_result($pedidos_stmt), MYSQLI_ASSOC);

// Busca itens de um pedido específico
$detalhe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$itens_detalhe = [];
if ($detalhe_id) {
    // Garante que pertence ao usuário
    $chk = mysqli_prepare($conexao,"SELECT id FROM pedidos WHERE id=? AND usuario_id=?");
    mysqli_stmt_bind_param($chk,'ii',$detalhe_id,$uid);
    mysqli_stmt_execute($chk);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
        $si = mysqli_prepare($conexao,
            "SELECT i.*, p.nome, p.imagem FROM pedido_itens i
             LEFT JOIN produtos p ON p.id = i.produto_id
             WHERE i.pedido_id = ?"
        );
        mysqli_stmt_bind_param($si,'i',$detalhe_id);
        mysqli_stmt_execute($si);
        $itens_detalhe = mysqli_fetch_all(mysqli_stmt_get_result($si), MYSQLI_ASSOC);
    }
}

$status_label = ['pendente'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Pendente','pago'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg> Pago','enviado'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Enviado','entregue'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Entregue','cancelado'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Cancelado'];
$status_color = ['pendente'=>'#fef3c7;color:#92400e','pago'=>'#dcfce7;color:#166534','enviado'=>'#dbeafe;color:#1e40af','entregue'=>'#f0fdf4;color:#166534','cancelado'=>'#fee2e2;color:#991b1b'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pedidos-wrap{max-width:900px;margin:0 auto;padding:40px 48px 80px;}
        .pedido-card{background:#fff;border:1px solid #E8DCC8;border-radius:16px;margin-bottom:16px;overflow:hidden;}
        .pedido-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;cursor:pointer;gap:12px;flex-wrap:wrap;}
        .pedido-header:hover{background:#FDFAF5;}
        .pedido-num{font-size:14px;font-weight:700;color:#1C1208;}
        .pedido-data{font-size:12px;color:#9CA3AF;}
        .pedido-total{font-size:15px;font-weight:700;color:#2C4A2E;}
        .status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;}
        .pedido-body{border-top:1px solid #E8DCC8;padding:18px 22px;display:none;}
        .pedido-body.open{display:block;}
        .item-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f5f0ea;}
        .item-row:last-child{border-bottom:none;}
        .item-row img{width:44px;height:44px;object-fit:cover;border-radius:8px;background:#FDFAF5;}
        .item-row .info{flex:1;}
        .item-row .info .nome{font-size:13px;font-weight:500;}
        .item-row .info .qty{font-size:12px;color:#9CA3AF;}
        .item-row .preco{font-size:13px;font-weight:600;color:#2C4A2E;}
        @media(max-width:600px){.pedidos-wrap{padding:24px 16px 60px;}}
    </style>
</head>
<body data-logado="1">
<?php include("includes/header.php"); ?>
<div class="page-title-block">
    <div class="breadcrumb"><a href="index.php">Home</a> › <span>Meus Pedidos</span></div>
    <h1><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Meus Pedidos</h1>
</div>
<div class="pedidos-wrap">
    <?php if (empty($pedidos)): ?>
    <div style="text-align:center;padding:80px 20px;">
        <span style="font-size:64px;display:block;margin-bottom:16px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></span>
        <h2 style="font-size:20px;margin-bottom:10px;">Nenhum pedido ainda</h2>
        <p style="color:#9CA3AF;margin-bottom:24px;">Explore nossos produtos e faça seu primeiro pedido!</p>
        <a href="cafes.php" style="display:inline-block;padding:12px 28px;background:#2C4A2E;color:#fff;border-radius:10px;font-weight:600;">Ver Produtos</a>
    </div>
    <?php else: ?>
    <p style="font-size:13px;color:#7A6248;margin-bottom:24px;"><?php echo count($pedidos); ?> pedido<?php echo count($pedidos)!==1?'s':''; ?></p>
    <?php foreach ($pedidos as $ped):
        $s = $ped['status'];
        $cor = $status_color[$s] ?? '#f3f4f6;color:#374151';
    ?>
    <div class="pedido-card">
        <div class="pedido-header" onclick="togglePedido(<?php echo $ped['id']; ?>)">
            <div>
                <div class="pedido-num">Pedido #ORB-<?php echo str_pad($ped['id'],6,'0',STR_PAD_LEFT); ?></div>
                <div class="pedido-data"><?php echo date('d/m/Y \à\s H:i', strtotime($ped['data_pedido'])); ?> · <?php echo $ped['total_itens']; ?> ite<?php echo $ped['total_itens']==1?'m':'ns'; ?></div>
            </div>
            <span class="status-badge" style="background:<?php echo $cor; ?>">
                <?php echo $status_label[$s] ?? $s; ?>
            </span>
            <div class="pedido-total">R$ <?php echo number_format($ped['total'],2,',','.'); ?></div>
            <span style="color:#9CA3AF;font-size:18px;" id="arrow-<?php echo $ped['id']; ?>">▾</span>
        </div>
        <div class="pedido-body" id="body-<?php echo $ped['id']; ?>">
            <?php
            $si2 = mysqli_prepare($conexao,
                "SELECT i.*, p.nome, p.imagem FROM pedido_itens i
                 LEFT JOIN produtos p ON p.id = i.produto_id
                 WHERE i.pedido_id = ?"
            );
            mysqli_stmt_bind_param($si2,'i',$ped['id']);
            mysqli_stmt_execute($si2);
            $itens2 = mysqli_fetch_all(mysqli_stmt_get_result($si2), MYSQLI_ASSOC);
            foreach ($itens2 as $item): ?>
            <div class="item-row">
                <img src="<?php echo htmlspecialchars($item['imagem'] ?? 'imagens/cafe1.jpg'); ?>" onerror="this.src='imagens/cafe1.jpg'">
                <div class="info">
                    <div class="nome"><?php echo htmlspecialchars($item['nome'] ?? 'Produto'); ?></div>
                    <div class="qty">Quantidade: <?php echo $item['quantidade']; ?> · Unit: R$ <?php echo number_format($item['preco'],2,',','.'); ?></div>
                </div>
                <div class="preco">R$ <?php echo number_format($item['preco']*$item['quantidade'],2,',','.'); ?></div>
            </div>
            <?php endforeach; ?>
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #E8DCC8;display:flex;justify-content:space-between;font-size:13px;color:#7A6248;">
                <span>Frete: R$ <?php echo number_format($ped['frete'],2,',','.'); ?></span>
                <span style="font-weight:700;color:#1C1208;">Total: R$ <?php echo number_format($ped['total'],2,',','.'); ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
<script>
function togglePedido(id) {
    const body  = document.getElementById('body-' + id);
    const arrow = document.getElementById('arrow-' + id);
    const open  = body.classList.toggle('open');
    if (arrow) arrow.textContent = open ? '▴' : '▾';
}
// Abre detalhe se vier via ?id=
<?php if ($detalhe_id): ?>
togglePedido(<?php echo $detalhe_id; ?>);
<?php endif; ?>
</script>
</body></html>
