<?php
$page_title  = 'Dashboard';
$active_menu = 'dashboard';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

// ── MÉTRICAS ──
$total_produtos  = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM produtos"))['n'];
$total_pedidos   = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM pedidos"))['n'];
$total_clientes  = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM usuarios WHERE tipo='cliente'"))['n'];
$total_vendas    = (float)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COALESCE(SUM(total),0) n FROM pedidos WHERE status NOT IN('cancelado')"))['n'];
$sem_estoque     = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM produtos WHERE estoque=0"))['n'];
$total_produtores= (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM produtores"))['n'];
$n_sol_prod      = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtores WHERE status='pendente'"))['n'];
$n_sol_prd       = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtos WHERE status='pendente'"))['n'];

// ticket médio
$ticket = $total_pedidos > 0 ? $total_vendas / $total_pedidos : 0;

// ── TENDÊNCIAS (vs mês anterior) ──
$mes_ant_y  = date('Y', strtotime('-1 month'));
$mes_ant_m  = date('m', strtotime('-1 month'));
$vendas_ant = (float)mysqli_fetch_assoc(mysqli_query($conexao,
    "SELECT COALESCE(SUM(total),0) n FROM pedidos
     WHERE YEAR(data_pedido)=$mes_ant_y AND MONTH(data_pedido)=$mes_ant_m AND status NOT IN('cancelado')"))['n'];
$pedidos_ant = (int)mysqli_fetch_assoc(mysqli_query($conexao,
    "SELECT COUNT(*) n FROM pedidos
     WHERE YEAR(data_pedido)=$mes_ant_y AND MONTH(data_pedido)=$mes_ant_m"))['n'];
$mes_atual_y = date('Y'); $mes_atual_m = date('m');
$vendas_mes  = (float)mysqli_fetch_assoc(mysqli_query($conexao,
    "SELECT COALESCE(SUM(total),0) n FROM pedidos
     WHERE YEAR(data_pedido)=$mes_atual_y AND MONTH(data_pedido)=$mes_atual_m AND status NOT IN('cancelado')"))['n'];
$pedidos_mes = (int)mysqli_fetch_assoc(mysqli_query($conexao,
    "SELECT COUNT(*) n FROM pedidos
     WHERE YEAR(data_pedido)=$mes_atual_y AND MONTH(data_pedido)=$mes_atual_m"))['n'];
function trend($atual, $ant) {
    if ($ant == 0) return ['', 0];
    $pct = round(($atual - $ant) / $ant * 100);
    $arrow = $pct >= 0 ? '↑' : '↓';
    $color = $pct >= 0 ? '#16a34a' : '#dc2626';
    return ["<span style='font-size:11px;font-weight:700;color:$color;margin-left:6px;'>$arrow ".abs($pct)."%</span>", $pct];
}
[$trend_vendas]  = trend($vendas_mes,  $vendas_ant);
[$trend_pedidos] = trend($pedidos_mes, $pedidos_ant);

$hora = (int)date('H');
$saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
$primeiro_nome = explode(' ', $_SESSION['nome'])[0];
$data_pt = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
$meses_pt = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
$data_fmt = $data_pt[date('w')].", ".date('d')." de ".$meses_pt[(int)date('m')]." de ".date('Y');


// ── VENDAS POR MÊS (últimos 7 meses) ──
$meses_labels = [];
$meses_vals   = [];
$meses_qtd    = [];
for ($i = 6; $i >= 0; $i--) {
    $ts   = strtotime("-$i months");
    $y    = date('Y', $ts);
    $m    = date('m', $ts);
    $meses_labels[] = date('M/y', $ts);
    $row = mysqli_fetch_assoc(mysqli_query($conexao,
        "SELECT COALESCE(SUM(total),0) v, COUNT(*) q FROM pedidos
         WHERE YEAR(data_pedido)=$y AND MONTH(data_pedido)=$m AND status NOT IN('cancelado')"
    ));
    $meses_vals[]  = round((float)$row['v'], 2);
    $meses_qtd[]   = (int)$row['q'];
}

// ── VENDAS POR CATEGORIA ──
$cat_data = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT pr.categoria, COALESCE(SUM(pi.preco * pi.quantidade),0) AS total
     FROM pedido_itens pi
     JOIN produtos pr ON pr.id = pi.produto_id
     JOIN pedidos pe ON pe.id = pi.pedido_id
     WHERE pe.status NOT IN('cancelado')
     GROUP BY pr.categoria ORDER BY total DESC"
), MYSQLI_ASSOC);

// ── TOP 5 PRODUTOS ──
$top_prods = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT p.nome, p.imagem, SUM(pi.quantidade) AS vendidos, SUM(pi.preco*pi.quantidade) AS receita
     FROM pedido_itens pi JOIN produtos p ON p.id=pi.produto_id
     JOIN pedidos pe ON pe.id=pi.pedido_id WHERE pe.status NOT IN('cancelado')
     GROUP BY pi.produto_id ORDER BY vendidos DESC LIMIT 5"
), MYSQLI_ASSOC);

// ── ÚLTIMOS PEDIDOS ──
$ult_ped = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT pe.id, pe.total, pe.status, pe.data_pedido, u.nome AS usuario_nome
     FROM pedidos pe LEFT JOIN usuarios u ON u.id=pe.usuario_id
     ORDER BY pe.data_pedido DESC LIMIT 8"
), MYSQLI_ASSOC);

// ── PEDIDOS POR STATUS ──
$status_counts = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT status, COUNT(*) n FROM pedidos GROUP BY status"
), MYSQLI_ASSOC);
$sc = [];
foreach ($status_counts as $s) $sc[$s['status']] = $s['n'];

$topbar_action = '<a href="produtos.php?acao=novo" class="topbar-btn primary" style="display:inline-flex;align-items:center;gap:6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Novo Produto</a>';
include('layout.php');
$status_label = ['pendente'=>'Pendente','pago'=>'Pago','enviado'=>'Enviado','entregue'=>'Entregue','cancelado'=>'Cancelado'];
?>

<!-- GREETING BANNER -->
<div style="background:linear-gradient(110deg,var(--soil) 0%,#2C4A2E 100%);border-radius:14px;padding:24px 28px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:20px;overflow:hidden;position:relative;">
  <div style="position:absolute;right:-30px;top:-40px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.03);pointer-events:none;"></div>
  <div>
    <div style="font-size:12px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:4px;"><?php echo $data_fmt; ?></div>
    <div style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#fff;"><?php echo $saudacao ?>, <?php echo htmlspecialchars($primeiro_nome); ?> 👋</div>
    <div style="font-size:13px;color:rgba(255,255,255,.5);margin-top:4px;">
      <?php if ($n_sol_prod+$n_sol_prd > 0): ?>
        Você tem <strong style="color:#F59E0B;"><?php echo $n_sol_prod+$n_sol_prd; ?> solicitação(ões)</strong> aguardando revisão.
      <?php elseif ($sem_estoque > 0): ?>
        <strong style="color:#F59E0B;"><?php echo $sem_estoque; ?> produto(s)</strong> sem estoque — vale dar uma olhada.
      <?php else: ?>
        Tudo em ordem. Bom trabalho!
      <?php endif; ?>
    </div>
  </div>
  <div style="display:flex;gap:10px;flex-shrink:0;flex-wrap:wrap;position:relative;z-index:1;">
    <a href="produtos.php?acao=novo" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;background:var(--clay);color:#fff;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#b04319'" onmouseout="this.style.background='var(--clay)'">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Novo Produto
    </a>
    <?php if ($n_sol_prod+$n_sol_prd > 0): ?>
    <a href="sol_produtores.php" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;background:rgba(255,255,255,.1);color:#fff;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;border:1px solid rgba(255,255,255,.15);">
      Ver Solicitações <span style="background:var(--clay);border-radius:10px;padding:1px 6px;font-size:10px;"><?php echo $n_sol_prod+$n_sol_prd; ?></span>
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- MÉTRICAS -->
<div class="metrics">
    <div class="metric green">
        <div class="m-icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="m-label">Receita Total</div>
        <div class="m-val" style="font-size:20px;">R$ <?php echo number_format($total_vendas,2,',','.'); ?><?php echo $trend_vendas; ?></div>
        <div class="m-sub">pedidos confirmados</div>
    </div>
    <div class="metric blue">
        <div class="m-icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
        <div class="m-label">Pedidos</div>
        <div class="m-val"><?php echo $total_pedidos; ?><?php echo $trend_pedidos; ?></div>
        <div class="m-sub">ticket médio R$ <?php echo number_format($ticket,2,',','.'); ?></div>
    </div>
    <div class="metric green">
        <div class="m-icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <div class="m-label">Clientes</div>
        <div class="m-val"><?php echo $total_clientes; ?></div>
        <div class="m-sub"><?php echo $total_produtores; ?> produtores</div>
    </div>
    <div class="metric green">
        <div class="m-icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
        <div class="m-label">Produtos</div>
        <div class="m-val"><?php echo $total_produtos; ?></div>
        <div class="m-sub"><?php echo $sem_estoque > 0 ? "$sem_estoque sem estoque" : "todos com estoque"; ?></div>
    </div>
    <div class="metric <?php echo ($n_sol_prod+$n_sol_prd)>0?'orange':'green'; ?>">
        <div class="m-icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg></div>
        <div class="m-label">Solicitações</div>
        <div class="m-val"><?php echo $n_sol_prod + $n_sol_prd; ?></div>
        <div class="m-sub">aguardando análise</div>
    </div>
</div>

<!-- LINHA DE ALERTAS RÁPIDOS -->
<?php if ($sem_estoque > 0 || $n_sol_prod > 0 || $n_sol_prd > 0): ?>
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <?php if ($sem_estoque > 0): ?>
    <a href="produtos.php?filtro=sem_estoque" class="alert alert-warn" style="text-decoration:none;flex:1;min-width:200px;">
        <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#d97706;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> <strong><?php echo $sem_estoque; ?> produto(s)</strong> sem estoque — clique para gerenciar
    </a>
    <?php endif; ?>
    <?php if ($n_sol_prod > 0): ?>
    <a href="sol_produtores.php" class="alert alert-info" style="text-decoration:none;flex:1;min-width:200px;">
        <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-dasharray="2 2"/></svg> <strong><?php echo $n_sol_prod; ?> solicitação(ões)</strong> de novos produtores
    </a>
    <?php endif; ?>
    <?php if ($n_sol_prd > 0): ?>
    <a href="sol_produtos.php" class="alert alert-info" style="text-decoration:none;flex:1;min-width:200px;">
        <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> <strong><?php echo $n_sol_prd; ?> produto(s)</strong> aguardando aprovação
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- GRÁFICOS -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

    <!-- Receita Mensal + Volume -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3>Receita & Volume — últimos 7 meses</h3>
                <div class="sub">Barras = receita (R$) · Linha = quantidade de pedidos</div>
            </div>
        </div>
        <div style="padding:20px;">
            <canvas id="chartMensal" height="200"></canvas>
        </div>
    </div>

    <!-- Por Categoria -->
    <div class="card">
        <div class="card-header">
            <h3>Receita por Categoria</h3>
        </div>
        <div style="padding:20px;display:flex;align-items:center;justify-content:center;">
            <canvas id="chartCat" height="220" style="max-width:220px;"></canvas>
        </div>
        <div style="padding:0 20px 16px;">
            <?php
            $cat_colors = ['cafe'=>'#6B3A2A','mel'=>'#D4622A','pimenta'=>'#EF4444','farinha'=>'#D97706','cereais'=>'#10B981'];
            $cat_labels = ['cafe'=>'Cafés','mel'=>'Méis','pimenta'=>'Pimentas','farinha'=>'Farinhas','cereais'=>'Cereais'];
            $total_cat = array_sum(array_column($cat_data,'total')) ?: 1;
            foreach ($cat_data as $cd):
                $pct = round($cd['total']/$total_cat*100);
                $col = $cat_colors[$cd['categoria']] ?? '#9CA3AF';
                $lbl = $cat_labels[$cd['categoria']] ?? ucfirst($cd['categoria']);
            ?>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:10px;height:10px;border-radius:2px;background:<?php echo $col; ?>;flex-shrink:0;"></div>
                    <span style="font-size:12px;"><?php echo $lbl; ?></span>
                </div>
                <span style="font-size:12px;font-weight:600;"><?php echo $pct; ?>%</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Funil de Status + Top Produtos -->
<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:20px;margin-bottom:20px;">

    <!-- Status Funil -->
    <div class="card">
        <div class="card-header"><h3>Funil de Pedidos</h3></div>
        <div style="padding:20px;">
            <canvas id="chartFunil" height="220"></canvas>
        </div>
    </div>

    <!-- Top Produtos -->
    <div class="card">
        <div class="card-header">
            <h3><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polyline points="8 21 12 17 16 21"/><line x1="12" y1="17" x2="12" y2="12"/><path d="M7 4H4.5A1.5 1.5 0 0 0 3 5.5v0A5.5 5.5 0 0 0 8.5 11H8"/><path d="M17 4h2.5A1.5 1.5 0 0 1 21 5.5v0A5.5 5.5 0 0 1 15.5 11H16"/><rect x="7" y="2" width="10" height="9" rx="2" ry="2"/></svg> Top 5 Produtos Mais Vendidos</h3>
            <a href="produtos.php" style="font-size:12px;color:var(--green);font-weight:600;">Ver todos →</a>
        </div>
        <?php if (empty($top_prods)): ?>
        <div class="empty-state"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span><p>Sem vendas registradas ainda</p></div>
        <?php else: ?>
        <?php foreach ($top_prods as $i => $tp): ?>
        <div style="display:flex;align-items:center;gap:14px;padding:12px 20px;border-bottom:1px solid #f5f7f5;">
            <div style="width:24px;height:24px;border-radius:50%;background:<?php echo ['#D4622A','#2C4A2E','#3B82F6','#D97706','#9CA3AF'][$i]; ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">
                <?php echo $i+1; ?>
            </div>
            <img src="../<?php echo htmlspecialchars($tp['imagem']); ?>" class="img-thumb" onerror="this.src='../imagens/cafe1.jpg'" style="width:40px;height:40px;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($tp['nome']); ?></div>
                <div style="font-size:11px;color:var(--text-3);"><?php echo $tp['vendidos']; ?> un. · R$ <?php echo number_format($tp['receita'],2,',','.'); ?></div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:13px;font-weight:700;color:var(--green);">R$ <?php echo number_format($tp['receita'],2,',','.'); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Últimos Pedidos -->
<div class="card">
    <div class="card-header">
        <h3><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg> Últimos Pedidos</h3>
        <a href="pedidos.php" class="btn btn-gray">Ver todos</a>
    </div>
    <table class="adm-table">
        <thead><tr><th>#</th><th>Cliente</th><th>Total</th><th>Pagamento</th><th>Status</th><th>Data</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($ult_ped as $ped): ?>
        <tr>
            <td><strong style="font-family:monospace;">ORB-<?php echo str_pad($ped['id'],6,'0',STR_PAD_LEFT); ?></strong></td>
            <td><?php echo htmlspecialchars($ped['usuario_nome'] ?? '–'); ?></td>
            <td><strong>R$ <?php echo number_format($ped['total'],2,',','.'); ?></strong></td>
            <td style="font-size:11px;color:var(--text-3);">cartão</td>
            <td><span class="badge badge-<?php echo $ped['status']; ?>"><?php echo $status_label[$ped['status']] ?? $ped['status']; ?></span></td>
            <td style="color:var(--text-3);font-size:12px;"><?php echo date('d/m/Y H:i', strtotime($ped['data_pedido'])); ?></td>
            <td><a href="pedidos.php?id=<?php echo $ped['id']; ?>" class="btn btn-gray" style="padding:5px 10px;font-size:11px;">Ver</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Paleta
const GREENS = ['#1a3a1c','#2C4A2E','#3D6B40','#5a8a5e','#88b88a','#a3d9ab','#c8eecb'];
const WARM   = ['#6B3A2A','#D4622A','#EF4444','#D97706','#10B981','#3B82F6','#8B5CF6'];

// Gráfico mensal (barras + linha)
const ctxM = document.getElementById('chartMensal').getContext('2d');
new Chart(ctxM, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($meses_labels); ?>,
        datasets: [
            {
                label: 'Receita (R$)',
                data: <?php echo json_encode($meses_vals); ?>,
                backgroundColor: GREENS.map((c,i)=> `${c}cc`),
                borderColor: GREENS,
                borderWidth: 1.5,
                borderRadius: 6,
                yAxisID: 'y',
            },
            {
                label: 'Pedidos',
                data: <?php echo json_encode($meses_qtd); ?>,
                type: 'line',
                borderColor: '#D4622A',
                backgroundColor: 'rgba(212,98,42,.1)',
                borderWidth: 2.5,
                pointBackgroundColor: '#D4622A',
                pointRadius: 4,
                tension: 0.4,
                fill: true,
                yAxisID: 'y2',
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top', labels: { font: { family:'Inter', size:12 }, boxWidth:12, padding:16 } },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.datasetIndex===0
                        ? ` R$ ${ctx.parsed.y.toLocaleString('pt-BR',{minimumFractionDigits:2})}`
                        : ` ${ctx.parsed.y} pedido(s)`
                }
            }
        },
        scales: {
            y:  { position:'left',  grid:{color:'#f0f0f0'}, ticks:{callback:v=>'R$'+v.toLocaleString('pt-BR'), font:{size:11}} },
            y2: { position:'right', grid:{display:false},   ticks:{font:{size:11}}, min:0 },
            x:  { grid:{display:false}, ticks:{font:{size:11}} }
        }
    }
});

// Gráfico rosca categorias
const ctxC = document.getElementById('chartCat').getContext('2d');
new Chart(ctxC, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_map(fn($r)=>($cat_labels[$r['categoria']]??ucfirst($r['categoria'])), $cat_data)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_map(fn($r)=>round($r['total'],2), $cat_data)); ?>,
            backgroundColor: <?php echo json_encode(array_map(fn($r)=>$cat_colors[$r['categoria']]??'#9CA3AF', $cat_data)); ?>,
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 8,
        }]
    },
    options: {
        cutout: '65%',
        plugins: {
            legend: { display:false },
            tooltip: { callbacks: { label: ctx => ` R$ ${ctx.parsed.toLocaleString('pt-BR',{minimumFractionDigits:2})}` } }
        }
    }
});

// Gráfico funil status
const ctxF = document.getElementById('chartFunil').getContext('2d');
const statusOrdem = ['pendente','pago','enviado','entregue','cancelado'];
const statusCores = ['#F59E0B','#3B82F6','#8B5CF6','#10B981','#EF4444'];
const statusLabels = ['Pendente','Pago','Enviado','Entregue','Cancelado'];
const scData = <?php echo json_encode($sc); ?>;
new Chart(ctxF, {
    type: 'bar',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusOrdem.map(s=>scData[s]||0),
            backgroundColor: statusCores.map(c=>c+'cc'),
            borderColor: statusCores,
            borderWidth: 1.5,
            borderRadius: 8,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend:{display:false} },
        scales: {
            x: { grid:{color:'#f0f0f0'}, ticks:{precision:0,font:{size:11}} },
            y: { grid:{display:false}, ticks:{font:{size:12,weight:'600'}} }
        }
    }
});
</script>

</div></main></body></html>
