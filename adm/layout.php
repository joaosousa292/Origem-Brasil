<?php
// adm/layout.php — layout compartilhado do painel admin
// Requer: $page_title, $active_menu definidos antes do include

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php?voltar=adm/dashboard.php"); exit;
}

// Badges de notificação
$n_sol_prod  = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtores WHERE status='pendente'"))['n'];
$n_sol_prd   = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtos  WHERE status='pendente'"))['n'];
$n_pedidos   = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM pedidos WHERE status='pendente'"))['n'];
$total_alerts = $n_sol_prod + $n_sol_prd + $n_pedidos;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($page_title ?? 'Admin'); ?> — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --sidebar:#0f1f12;
            --sidebar-hover:rgba(255,255,255,.08);
            --sidebar-active:rgba(163,217,171,.15);
            --sidebar-active-border:#a3d9ab;
            --green:#2C4A2E;
            --green-2:#3D6B40;
            --green-light:#f0f7f1;
            --orange:#D4622A;
            --white:#fff;
            --bg:#f4f6f3;
            --card:#fff;
            --border:#e8ece8;
            --text:#1C1208;
            --text-2:#4B5563;
            --text-3:#9CA3AF;
            --radius:12px;
            --shadow:0 1px 4px rgba(0,0,0,.08),0 4px 16px rgba(0,0,0,.06);
        }
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:14px;}
        a{text-decoration:none;color:inherit;}
        button{font-family:'Inter',sans-serif;cursor:pointer;}

        /* ── SIDEBAR ── */
        .sidebar{
            width:248px;min-height:100vh;background:var(--sidebar);
            display:flex;flex-direction:column;flex-shrink:0;
            position:sticky;top:0;height:100vh;overflow-y:auto;
        }
        .sidebar-logo{
            padding:20px 18px 16px;
            border-bottom:1px solid rgba(255,255,255,.06);
        }
        .sidebar-logo .brand{font-size:16px;font-weight:700;color:#fff;display:flex;align-items:center;gap:8px;}
        .sidebar-logo .brand .dot{width:8px;height:8px;background:#a3d9ab;border-radius:50%;flex-shrink:0;}
        .sidebar-logo small{font-size:10px;color:rgba(255,255,255,.35);margin-top:3px;display:block;margin-left:16px;}

        .sidebar-section{padding:12px 10px 4px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.25);}
        .sidebar-nav{padding:4px 8px;}
        .sidebar-nav a{
            display:flex;align-items:center;gap:10px;
            padding:9px 12px;border-radius:9px;
            font-size:13px;font-weight:500;color:rgba(255,255,255,.65);
            transition:all .15s;margin-bottom:1px;position:relative;
        }
        .sidebar-nav a:hover{background:var(--sidebar-hover);color:#fff;}
        .sidebar-nav a.active{background:var(--sidebar-active);color:#a3d9ab;border-left:2px solid var(--sidebar-active-border);padding-left:10px;}
        .sidebar-nav a .icon{width:16px;height:16px;flex-shrink:0;opacity:.7;}
        .sidebar-nav a.active .icon{opacity:1;}
        .badge-pill{
            margin-left:auto;background:#EF4444;color:#fff;
            font-size:10px;font-weight:700;padding:1px 6px;border-radius:20px;min-width:18px;text-align:center;
        }
        .badge-pill.orange{background:var(--orange);}
        .sidebar-divider{height:1px;background:rgba(255,255,255,.06);margin:8px 10px;}
        .sidebar-footer{
            padding:14px 18px;margin-top:auto;
            border-top:1px solid rgba(255,255,255,.06);
        }
        .sidebar-footer .avatar{
            width:32px;height:32px;border-radius:50%;
            background:linear-gradient(135deg,var(--green),var(--green-2));
            display:flex;align-items:center;justify-content:center;
            font-size:13px;font-weight:700;color:#fff;flex-shrink:0;
        }
        .sidebar-footer .info{flex:1;min-width:0;}
        .sidebar-footer .info strong{display:block;font-size:12px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .sidebar-footer .info span{font-size:10px;color:rgba(255,255,255,.35);}
        .sidebar-footer > div{display:flex;align-items:center;gap:10px;}

        /* ── MAIN ── */
        .main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
        .topbar{
            background:var(--card);border-bottom:1px solid var(--border);
            padding:0 28px;height:60px;
            display:flex;align-items:center;justify-content:space-between;
            position:sticky;top:0;z-index:100;
        }
        .topbar-left{display:flex;align-items:center;gap:12px;}
        .topbar-title{font-size:16px;font-weight:700;color:var(--text);}
        .topbar-breadcrumb{font-size:12px;color:var(--text-3);}
        .topbar-right{display:flex;align-items:center;gap:8px;}
        .topbar-btn{
            padding:7px 14px;border:1px solid var(--border);background:var(--card);
            border-radius:9px;font-size:12px;font-weight:600;color:var(--text-2);
            display:flex;align-items:center;gap:6px;transition:all .15s;
        }
        .topbar-btn:hover{background:var(--bg);}
        .topbar-btn.primary{background:var(--green);color:#fff;border-color:var(--green);}
        .topbar-btn.primary:hover{background:var(--green-2);}

        .content{flex:1;padding:24px 28px;overflow-y:auto;}

        /* ── CARDS MÉTRICA ── */
        .metrics{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;}
        .metric{
            background:var(--card);border:1px solid var(--border);border-radius:var(--radius);
            padding:18px 20px;position:relative;overflow:hidden;
        }
        .metric .m-label{font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;}
        .metric .m-val{font-size:26px;font-weight:800;color:var(--text);line-height:1;}
        .metric .m-sub{font-size:11px;color:var(--text-3);margin-top:5px;}
        .metric .m-icon{
            position:absolute;right:14px;top:14px;
            width:36px;height:36px;border-radius:10px;
            display:flex;align-items:center;justify-content:center;font-size:16px;
        }
        .metric.green .m-val{color:var(--green);}
        .metric.orange .m-val{color:var(--orange);}
        .metric.red .m-val{color:#EF4444;}
        .metric.blue .m-val{color:#3B82F6;}
        .metric.green .m-icon{background:#f0f7f1;}
        .metric.orange .m-icon{background:#fff3ed;}
        .metric.red .m-icon{background:#fef2f2;}
        .metric.blue .m-icon{background:#eff6ff;}

        /* ── TABLE CARD ── */
        .card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:20px;}
        .card-header{
            padding:16px 20px;border-bottom:1px solid var(--border);
            display:flex;align-items:center;justify-content:space-between;
        }
        .card-header h3{font-size:14px;font-weight:700;}
        .card-header .sub{font-size:12px;color:var(--text-3);}
        table.adm-table{width:100%;border-collapse:collapse;}
        .adm-table th{
            font-size:10px;text-transform:uppercase;letter-spacing:.08em;
            color:var(--text-3);font-weight:600;padding:11px 16px;
            background:#f9fbf9;border-bottom:1px solid var(--border);text-align:left;
        }
        .adm-table td{font-size:13px;padding:12px 16px;border-bottom:1px solid #f5f7f5;vertical-align:middle;}
        .adm-table tr:last-child td{border-bottom:none;}
        .adm-table tr:hover td{background:#fafcfa;}

        /* BADGES */
        .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;}
        .badge-pendente{background:#fef3c7;color:#92400e;}
        .badge-pago{background:#dcfce7;color:#166534;}
        .badge-enviado{background:#dbeafe;color:#1e40af;}
        .badge-entregue{background:#f0fdf4;color:#166534;}
        .badge-cancelado{background:#fee2e2;color:#991b1b;}
        .badge-aprovado{background:#dcfce7;color:#166534;}
        .badge-rejeitado{background:#fee2e2;color:#991b1b;}
        .badge-admin{background:#ede9fe;color:#5b21b6;}
        .badge-cliente{background:#f3f4f6;color:#374151;}
        .badge-ativo{background:#dcfce7;color:#166534;}
        .badge-inativo{background:#f3f4f6;color:#6B7280;}

        /* BTNS */
        .btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;}
        .btn-green{background:var(--green);color:#fff;} .btn-green:hover{background:var(--green-2);}
        .btn-red{background:#fee2e2;color:#991b1b;} .btn-red:hover{background:#fecaca;}
        .btn-blue{background:#dbeafe;color:#1e40af;} .btn-blue:hover{background:#bfdbfe;}
        .btn-gray{background:var(--bg);color:var(--text-2);border:1px solid var(--border);} .btn-gray:hover{background:var(--border);}
        .btn-orange{background:#fff3ed;color:var(--orange);} .btn-orange:hover{background:#fed7aa;}
        .btn-lg{padding:11px 24px;font-size:14px;border-radius:10px;}

        /* FORMULÁRIOS */
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .form-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
        .form-full{grid-column:1/-1;}
        .field{display:flex;flex-direction:column;gap:5px;}
        .field label{font-size:11px;font-weight:600;color:var(--text-2);text-transform:uppercase;letter-spacing:.05em;}
        .field input,.field select,.field textarea{
            padding:9px 13px;border:1px solid var(--border);border-radius:9px;
            font-family:'Inter',sans-serif;font-size:13px;outline:none;background:#fafcfa;
            transition:border-color .15s,background .15s;color:var(--text);
        }
        .field input:focus,.field select:focus,.field textarea:focus{border-color:var(--green);background:#fff;}
        .field textarea{resize:vertical;min-height:80px;}
        .field .hint{font-size:11px;color:var(--text-3);}

        /* ALERTS */
        .alert{padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;}
        .alert-ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
        .alert-err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
        .alert-warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;}
        .alert-info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;}

        /* MISC */
        .img-thumb{width:48px;height:48px;object-fit:cover;border-radius:8px;background:var(--bg);border:1px solid var(--border);}
        .avatar-circle{
            width:32px;height:32px;border-radius:50%;
            background:linear-gradient(135deg,var(--green),var(--green-2));
            display:flex;align-items:center;justify-content:center;
            font-size:12px;font-weight:700;color:#fff;flex-shrink:0;
        }
        .empty-state{text-align:center;padding:48px 20px;color:var(--text-3);}
        .empty-state .icon{font-size:40px;margin-bottom:12px;display:block;}
        .empty-state p{font-size:14px;}

        /* TABS */
        .tabs{display:flex;gap:2px;background:var(--bg);border-radius:10px;padding:3px;margin-bottom:20px;border:1px solid var(--border);}
        .tab-btn{padding:8px 18px;border:none;background:none;border-radius:8px;font-size:13px;font-weight:500;color:var(--text-2);cursor:pointer;transition:all .15s;}
        .tab-btn.active{background:var(--card);color:var(--text);font-weight:600;box-shadow:0 1px 3px rgba(0,0,0,.08);}

        /* RESPONSIVE */
        @media(max-width:1100px){.metrics{grid-template-columns:repeat(3,1fr);}}
        @media(max-width:768px){.sidebar{display:none;}.metrics{grid-template-columns:repeat(2,1fr);}.content{padding:16px;}}
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="brand"><span class="dot"></span> Origem Brasil</div>
        <small>Painel Administrativo</small>
    </div>

    <div class="sidebar-section">Principal</div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo ($active_menu??'')==='dashboard'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
            <?php if ($total_alerts > 0): ?><span class="badge-pill"><?php echo $total_alerts; ?></span><?php endif; ?>
        </a>
    </nav>

    <div class="sidebar-section">Catálogo</div>
    <nav class="sidebar-nav">
        <a href="produtos.php" class="<?php echo ($active_menu??'')==='produtos'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Produtos
        </a>
        <a href="produtores.php" class="<?php echo ($active_menu??'')==='produtores'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Produtores
        </a>
        <a href="categorias.php" class="<?php echo ($active_menu??'')==='categorias'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Categorias
        </a>
    </nav>

    <div class="sidebar-section">Vendas</div>
    <nav class="sidebar-nav">
        <a href="pedidos.php" class="<?php echo ($active_menu??'')==='pedidos'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Pedidos
            <?php if ($n_pedidos > 0): ?><span class="badge-pill orange"><?php echo $n_pedidos; ?></span><?php endif; ?>
        </a>
    </nav>

    <div class="sidebar-section">Usuários</div>
    <nav class="sidebar-nav">
        <a href="clientes.php" class="<?php echo ($active_menu??'')==='clientes'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Clientes
        </a>
        <a href="admins.php" class="<?php echo ($active_menu??'')==='admins'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Administradores
        </a>
    </nav>

    <div class="sidebar-section">Solicitações</div>
    <nav class="sidebar-nav">
        <a href="sol_produtores.php" class="<?php echo ($active_menu??'')==='sol_produtores'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Novos Produtores
            <?php if ($n_sol_prod > 0): ?><span class="badge-pill"><?php echo $n_sol_prod; ?></span><?php endif; ?>
        </a>
        <a href="sol_produtos.php" class="<?php echo ($active_menu??'')==='sol_produtos'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><line x1="12" y1="22.08" x2="12" y2="12"/><line x1="3.27" y1="6.96" x2="12" y2="12.01"/><line x1="20.73" y1="6.96" x2="12" y2="12.01"/><line x1="12" y1="2" x2="12" y2="7"/></svg>
            Novos Produtos
            <?php if ($n_sol_prd > 0): ?><span class="badge-pill"><?php echo $n_sol_prd; ?></span><?php endif; ?>
        </a>
    </nav>

    <div class="sidebar-section">Sistema</div>
    <nav class="sidebar-nav">
        <a href="configuracoes.php" class="<?php echo ($active_menu??'')==='configuracoes'?'active':''; ?>">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Configurações
        </a>
        <div class="sidebar-divider"></div>
        <a href="../index.php" target="_blank">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Ver Loja
        </a>
        <a href="../logout.php" style="color:rgba(239,68,68,.75);">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair
        </a>
    </nav>

    <div class="sidebar-footer">
        <div>
            <div class="avatar"><?php echo mb_strtoupper(mb_substr($_SESSION['nome'],0,1)); ?></div>
            <div class="info">
                <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
                <span>Administrador</span>
            </div>
        </div>
    </div>
</aside>

<main class="main">
    <div class="topbar">
        <div class="topbar-left">
            <span class="topbar-title"><?php echo htmlspecialchars($page_title ?? 'Admin'); ?></span>
        </div>
        <div class="topbar-right">
            <?php if (!empty($topbar_action)): ?>
                <?php echo $topbar_action; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="content">
        <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msg_type ?? 'ok'; ?>">
            <?php echo $msg_type==='ok'?'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>':($msg_type==='err'?'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>':'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#d97706;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'); ?>
            <?php echo htmlspecialchars($msg); ?>
        </div>
        <?php endif; ?>
