<?php
$page_title  = 'Pedidos';
$active_menu = 'pedidos';
$msg = ''; $msg_type = 'ok';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = $_POST['acao'] ?? '';
    if ($p === 'status') {
        $pid    = (int)($_POST['pedido_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $validos = ['pendente','pago','enviado','entregue','cancelado'];
        if (in_array($status, $validos)) {
            $s = mysqli_prepare($conexao,"UPDATE pedidos SET status=? WHERE id=?");
            mysqli_stmt_bind_param($s,'si',$status,$pid);
            mysqli_stmt_execute($s);
        }
        $msg = 'Status atualizado!';
    }
    if ($p === 'obs') {
        $pid = (int)($_POST['pedido_id'] ?? 0);
        $obs = trim($_POST['observacao'] ?? '');
        $s = mysqli_prepare($conexao,"UPDATE pedidos SET observacao=? WHERE id=?");
        mysqli_stmt_bind_param($s,'si',$obs,$pid);
        mysqli_stmt_execute($s);
        $msg = 'Observação salva!';
    }
}

$filtro  = $_GET['status'] ?? '';
$busca   = trim($_GET['q'] ?? '');
$where   = 'WHERE 1=1';
if ($filtro) $where .= " AND pe.status='".mysqli_real_escape_string($conexao,$filtro)."'";
if ($busca)  $where .= " AND (u.nome LIKE '%".mysqli_real_escape_string($conexao,$busca)."%' OR u.email LIKE '%".mysqli_real_escape_string($conexao,$busca)."%')";

$pedidos = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT pe.*, u.nome AS usuario_nome, u.email,
     COUNT(DISTINCT i.id) AS total_itens
     FROM pedidos pe
     LEFT JOIN usuarios u ON u.id=pe.usuario_id
     LEFT JOIN pedido_itens i ON i.pedido_id=pe.id
     $where GROUP BY pe.id ORDER BY pe.data_pedido DESC"
), MYSQLI_ASSOC);

// Ver detalhe de um pedido
$detalhe = null;
$detalhe_itens = [];
if (isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    $detalhe = mysqli_fetch_assoc(mysqli_query($conexao,
        "SELECT pe.*, u.nome AS usuario_nome, u.email, u.telefone,
         e.rua, e.numero, e.bairro, e.cidade, e.estado, e.cep
         FROM pedidos pe
         LEFT JOIN usuarios u ON u.id=pe.usuario_id
         LEFT JOIN enderecos e ON e.id=pe.endereco_id
         WHERE pe.id=$did"
    ));
    if ($detalhe) {
        $detalhe_itens = mysqli_fetch_all(mysqli_query($conexao,
            "SELECT pi.*, p.nome, p.imagem, p.categoria
             FROM pedido_itens pi LEFT JOIN produtos p ON p.id=pi.produto_id
             WHERE pi.pedido_id=$did"
        ), MYSQLI_ASSOC);
    }
}

$status_label = ['pendente'=>'Pendente','pago'=>'Pago','enviado'=>'Enviado','entregue'=>'Entregue','cancelado'=>'Cancelado'];
$status_cores  = ['pendente'=>'badge-pendente','pago'=>'badge-pago','enviado'=>'badge-enviado','entregue'=>'badge-entregue','cancelado'=>'badge-cancelado'];

// Contagens para abas
$counts = [];
foreach (['pendente','pago','enviado','entregue','cancelado'] as $s) {
    $counts[$s] = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM pedidos WHERE status='$s'"))['n'];
}

include('layout.php');
?>

<?php if ($detalhe): ?>
<!-- DETALHE DO PEDIDO -->
<div style="display:flex;gap:8px;margin-bottom:20px;">
    <a href="pedidos.php" class="btn btn-gray">← Voltar</a>
    <span style="font-size:18px;font-weight:700;">Pedido ORB-<?php echo str_pad($detalhe['id'],6,'0',STR_PAD_LEFT); ?></span>
    <span class="badge <?php echo $status_cores[$detalhe['status']]; ?>" style="font-size:13px;"><?php echo $status_label[$detalhe['status']]; ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">
    <div>
        <!-- Itens -->
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><h3><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Itens do Pedido</h3></div>
            <table class="adm-table">
                <thead><tr><th>Produto</th><th>Categoria</th><th>Preço Unit.</th><th>Qtd</th><th>Subtotal</th></tr></thead>
                <tbody>
                <?php foreach ($detalhe_itens as $item): ?>
                <tr>
                    <td style="display:flex;align-items:center;gap:10px;">
                        <img src="../<?php echo htmlspecialchars($item['imagem']??''); ?>" class="img-thumb" style="width:36px;height:36px;" onerror="this.src='../imagens/cafe1.jpg'">
                        <span><?php echo htmlspecialchars($item['nome']??'Produto removido'); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($item['categoria']??'–'); ?></td>
                    <td>R$ <?php echo number_format($item['preco'],2,',','.'); ?></td>
                    <td><?php echo $item['quantidade']; ?></td>
                    <td><strong>R$ <?php echo number_format($item['preco']*$item['quantidade'],2,',','.'); ?></strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:24px;">
                <span style="font-size:13px;color:var(--text-3);">Subtotal: R$ <?php echo number_format($detalhe['total']-$detalhe['frete'],2,',','.'); ?></span>
                <span style="font-size:13px;color:var(--text-3);">Frete: R$ <?php echo number_format($detalhe['frete'],2,',','.'); ?></span>
                <span style="font-size:15px;font-weight:700;color:var(--green);">Total: R$ <?php echo number_format($detalhe['total'],2,',','.'); ?></span>
            </div>
        </div>

        <!-- Obs -->
        <div class="card" style="padding:20px;">
            <h4 style="margin-bottom:12px;font-size:14px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Observação Interna</h4>
            <form method="POST">
                <input type="hidden" name="acao" value="obs">
                <input type="hidden" name="pedido_id" value="<?php echo $detalhe['id']; ?>">
                <textarea name="observacao" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:9px;font-family:'Inter',sans-serif;font-size:13px;resize:vertical;min-height:80px;outline:none;" placeholder="Anotações internas…"><?php echo htmlspecialchars($detalhe['observacao']??''); ?></textarea>
                <button type="submit" class="btn btn-green" style="margin-top:10px;">Salvar nota</button>
            </form>
        </div>
    </div>

    <!-- Sidebar detalhe -->
    <aside>
        <div class="card" style="padding:20px;margin-bottom:16px;">
            <h4 style="margin-bottom:14px;font-size:13px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-3);">Cliente</h4>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div class="avatar-circle"><?php echo mb_strtoupper(mb_substr($detalhe['usuario_nome']??'?',0,1)); ?></div>
                <div>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($detalhe['usuario_nome']??'–'); ?></div>
                    <div style="font-size:12px;color:var(--text-3);"><?php echo htmlspecialchars($detalhe['email']??''); ?></div>
                </div>
            </div>
            <?php if ($detalhe['rua']): ?>
            <div style="font-size:12px;color:var(--text-2);background:var(--bg);padding:10px;border-radius:8px;line-height:1.7;">
                <?php echo htmlspecialchars($detalhe['rua']); ?>, <?php echo htmlspecialchars($detalhe['numero']??''); ?><br>
                <?php echo htmlspecialchars($detalhe['bairro']??''); ?> — <?php echo htmlspecialchars($detalhe['cidade']??''); ?>/<?php echo htmlspecialchars($detalhe['estado']??''); ?><br>
                CEP: <?php echo htmlspecialchars($detalhe['cep']??''); ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="card" style="padding:20px;">
            <h4 style="margin-bottom:14px;font-size:13px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-3);">Alterar Status</h4>
            <form method="POST">
                <input type="hidden" name="acao" value="status">
                <input type="hidden" name="pedido_id" value="<?php echo $detalhe['id']; ?>">
                <select name="status" style="width:100%;padding:9px 13px;border:1px solid var(--border);border-radius:9px;font-family:'Inter',sans-serif;font-size:13px;outline:none;background:var(--card);margin-bottom:12px;">
                    <?php foreach ($status_label as $v=>$l): ?>
                    <option value="<?php echo $v; ?>" <?php echo $v===$detalhe['status']?'selected':''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-green" style="width:100%;">Atualizar Status</button>
            </form>
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);font-size:12px;color:var(--text-3);">
                <div>Pedido em: <?php echo date('d/m/Y H:i', strtotime($detalhe['data_pedido'])); ?></div>
                <div>Pagamento: <?php echo ucfirst($detalhe['pagamento']??'–'); ?></div>
            </div>
        </div>
    </aside>
</div>

<?php else: ?>
<!-- LISTA DE PEDIDOS -->
<div style="display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="pedidos.php" class="btn btn-<?php echo !$filtro?'green':'gray'; ?>">Todos (<?php echo array_sum($counts); ?>)</a>
    <?php foreach ($counts as $s=>$n): ?>
    <a href="pedidos.php?status=<?php echo $s; ?>" class="btn btn-<?php echo $filtro===$s?'green':'gray'; ?>">
        <?php echo $status_label[$s]; ?> (<?php echo $n; ?>)
    </a>
    <?php endforeach; ?>
</div>

<form method="GET" style="display:flex;gap:10px;margin-bottom:16px;">
    <input type="text" name="q" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Buscar por cliente ou e-mail…" style="padding:8px 14px;border:1px solid var(--border);border-radius:9px;font-size:13px;outline:none;background:var(--card);min-width:260px;">
    <?php if ($filtro): ?><input type="hidden" name="status" value="<?php echo $filtro; ?>"><?php endif; ?>
    <button class="btn btn-gray">Buscar</button>
</form>

<div class="card">
    <div class="card-header">
        <h3>Pedidos <span style="color:var(--text-3);font-weight:400;">(<?php echo count($pedidos); ?>)</span></h3>
    </div>
    <table class="adm-table">
        <thead><tr><th>#</th><th>Cliente</th><th>Itens</th><th>Total</th><th>Pagamento</th><th>Status</th><th>Data</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($pedidos as $ped): ?>
        <tr>
            <td><strong style="font-family:monospace;font-size:12px;">ORB-<?php echo str_pad($ped['id'],6,'0',STR_PAD_LEFT); ?></strong></td>
            <td>
                <div style="font-weight:500;"><?php echo htmlspecialchars($ped['usuario_nome']??'–'); ?></div>
                <div style="font-size:11px;color:var(--text-3);"><?php echo htmlspecialchars($ped['email']??''); ?></div>
            </td>
            <td style="color:var(--text-3);"><?php echo $ped['total_itens']; ?> iten(s)</td>
            <td><strong>R$ <?php echo number_format($ped['total'],2,',','.'); ?></strong></td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo ucfirst($ped['pagamento']??'–'); ?></td>
            <td>
                <form method="POST" style="display:flex;gap:5px;align-items:center;">
                    <input type="hidden" name="acao" value="status">
                    <input type="hidden" name="pedido_id" value="<?php echo $ped['id']; ?>">
                    <select name="status" style="padding:4px 8px;border:1px solid var(--border);border-radius:6px;font-size:11px;outline:none;background:var(--card);">
                        <?php foreach ($status_label as $v=>$l): ?>
                        <option value="<?php echo $v; ?>" <?php echo $v===$ped['status']?'selected':''; ?>><?php echo $l; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-green" style="padding:4px 8px;font-size:11px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></button>
                </form>
            </td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo date('d/m/Y H:i', strtotime($ped['data_pedido'])); ?></td>
            <td><a href="pedidos.php?id=<?php echo $ped['id']; ?>" class="btn btn-blue" style="padding:5px 10px;font-size:11px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg> Detalhe</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($pedidos)): ?>
    <div class="empty-state"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></span><p>Nenhum pedido encontrado</p></div>
    <?php endif; ?>
</div>
<?php endif; ?>

</div></main></body></html>
