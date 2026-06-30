<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include("conexao.php");
if (!isset($_SESSION['id'])) { header("Location: login.php?voltar=perfil.php"); exit; }
$uid = (int)$_SESSION['id'];

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'alterar_foto') {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $arquivo = $_FILES['foto'];
            $extensoesPermitidas = ['jpg','jpeg','png','gif','webp'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            if (!in_array($extensao, $extensoesPermitidas)) {
                $msg = 'ERRO: Apenas imagens JPG, PNG, WEBP e GIF são permitidas.';
            } elseif ($arquivo['size'] > 3 * 1024 * 1024) {
                $msg = 'ERRO: A foto deve ter no máximo 3MB.';
            } else {
                $pastaDestino = "uploads/perfis/";
                if (!is_dir($pastaDestino)) { mkdir($pastaDestino, 0777, true); }
                // Remover foto antiga
                $fotoAtual = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT foto FROM usuarios WHERE id=$uid"))['foto'] ?? '';
                if ($fotoAtual && file_exists($fotoAtual)) { @unlink($fotoAtual); }
                $novoNome    = "perfil_" . $uid . "_" . time() . "." . $extensao;
                $caminhoFinal = $pastaDestino . $novoNome;
                if (move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
                    mysqli_query($conexao,"UPDATE usuarios SET foto='".mysqli_real_escape_string($conexao,$caminhoFinal)."' WHERE id=$uid");
                    $_SESSION['usuario_foto'] = $caminhoFinal;
                    $msg = 'Foto de perfil atualizada!';
                } else { $msg = 'ERRO: Falha ao salvar a imagem. Verifique permissões da pasta uploads/.'; }
            }
        } else { $msg = 'ERRO: Selecione uma imagem válida.'; }
    }

    if ($acao === 'editar_perfil') {
        $nome     = htmlspecialchars(trim($_POST['nome'] ?? ''));
        $telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''));
        if ($nome) {
            mysqli_query($conexao,"UPDATE usuarios SET nome='".mysqli_real_escape_string($conexao,$nome)."', telefone='".mysqli_real_escape_string($conexao,$telefone)."' WHERE id=$uid");
            $_SESSION['nome'] = $nome;
            $msg = 'Perfil atualizado com sucesso!';
        }
    }

    if ($acao === 'trocar_senha') {
        $atual = $_POST['senha_atual'] ?? '';
        $nova  = $_POST['senha_nova']  ?? '';
        $conf  = $_POST['senha_conf']  ?? '';
        $u = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT senha FROM usuarios WHERE id=$uid"));
        if (!password_verify($atual, $u['senha'])) {
            $msg = 'ERRO: Senha atual incorreta.';
        } elseif (strlen($nova) < 8) {
            $msg = 'ERRO: A nova senha precisa ter ao menos 8 caracteres.';
        } elseif ($nova !== $conf) {
            $msg = 'ERRO: As senhas não coincidem.';
        } else {
            $hash = password_hash($nova, PASSWORD_BCRYPT);
            mysqli_query($conexao,"UPDATE usuarios SET senha='$hash' WHERE id=$uid");
            $msg = 'Senha alterada com sucesso!';
        }
    }

    if ($acao === 'preferencias') { $msg = 'Preferências salvas!'; }

    if ($acao === 'add_endereco') {
        $cep    = htmlspecialchars($_POST['cep']    ?? '');
        $rua    = htmlspecialchars($_POST['rua']    ?? '');
        $num    = htmlspecialchars($_POST['numero'] ?? '');
        $comp   = htmlspecialchars($_POST['complemento'] ?? '');
        $bairro = htmlspecialchars($_POST['bairro'] ?? '');
        $cidade = htmlspecialchars($_POST['cidade'] ?? '');
        $estado = htmlspecialchars($_POST['estado'] ?? '');
        $ap     = htmlspecialchars($_POST['apelido'] ?? 'Casa');
        $s = mysqli_prepare($conexao,"INSERT INTO enderecos (usuario_id,apelido,cep,rua,numero,complemento,bairro,cidade,estado) VALUES (?,?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($s,'sssssssss',$uid,$ap,$cep,$rua,$num,$comp,$bairro,$cidade,$estado);
        mysqli_stmt_execute($s);
        $msg = 'Endereço adicionado!';
    }

    if ($acao === 'del_endereco') {
        $eid = (int)($_POST['eid'] ?? 0);
        mysqli_query($conexao,"DELETE FROM enderecos WHERE id=$eid AND usuario_id=$uid");
        $msg = 'Endereço removido.';
    }
}

$usuario         = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM usuarios WHERE id=$uid"));
$enderecos       = mysqli_fetch_all(mysqli_query($conexao,"SELECT * FROM enderecos WHERE usuario_id=$uid ORDER BY id"), MYSQLI_ASSOC);
$pedidos_count   = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) as total FROM pedidos WHERE usuario_id=$uid"))['total'] ?? 0;
$favoritos_count = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) as total FROM favoritos WHERE usuario_id=$uid"))['total'] ?? 0;

// Sincronizar sessão com banco
if (!empty($usuario['foto']) && file_exists($usuario['foto'])) {
    $_SESSION['usuario_foto'] = $usuario['foto'];
} else {
    $_SESSION['usuario_foto'] = '';
    // Limpar referência inválida no banco
    if (!empty($usuario['foto'])) {
        mysqli_query($conexao,"UPDATE usuarios SET foto=NULL WHERE id=$uid");
        $usuario['foto'] = '';
    }
}

$foto_valida   = !empty($usuario['foto']) && file_exists($usuario['foto']);
$inicial       = mb_strtoupper(mb_substr($usuario['nome'], 0, 1));
$membro_desde  = date('M Y', strtotime($usuario['criado_em'] ?? 'now'));

// Painel ativo após POST
$painel_ativo = 'dados';
if ($msg) {
    if (str_contains($msg,'Endereço'))  $painel_ativo = 'enderecos';
    elseif (str_contains($msg,'Senha')) $painel_ativo = 'senha';
    elseif (str_contains($msg,'Prefer')) $painel_ativo = 'notificacoes';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ─── VARIÁVEIS ─────────────────────────────────── */
        :root {
            --g-dark:  #1C3320;
            --g-mid:   #2C4A2E;
            --g-light: #3D6B40;
            --orange:  #D4622A;
            --cream:   #FDFAF5;
            --border:  #E8DCC8;
            --text:    #1C1208;
            --text-2:  #4a3728;
            --text-3:  #7A6248;
            --text-4:  #9CA3AF;
        }

        /* ─── HERO ──────────────────────────────────────── */
        .perfil-hero {
            background: linear-gradient(150deg, rgba(13,38,16,.86) 0%, rgba(28,51,32,.84) 55%, rgba(42,61,26,.86) 100%),
                        url('imagens/bg-campo6.png') center/cover;
            padding: 52px 48px 0;
            position: relative; overflow: hidden;
        }
        .perfil-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 55% 90% at 0% 60%, rgba(212,98,42,.13) 0%, transparent 65%),
                radial-gradient(ellipse 35% 50% at 100% 0%, rgba(44,74,46,.35) 0%, transparent 70%);
            pointer-events: none;
        }
        /* grain sutil */
        .perfil-hero::after {
            content: '';
            position: absolute; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.025'/%3E%3C/svg%3E");
            pointer-events: none;
        }
        .hero-inner {
    max-width: 1080px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 32px;
    position: relative;
    z-index: 1;
}

        /* ─── AVATAR ────────────────────────────────────── */
        .avatar-wrap {
            flex-shrink: 0;
            width: 104px; height: 104px;
            margin-bottom: -52px;          /* metade da altura → metade fica no hero */
            position: relative;
        }
        .avatar-circle {
            width: 104px; height: 104px;
            border-radius: 50%;
            border: 4px solid #fff;
            overflow: hidden;
            background: linear-gradient(135deg, #2C4A2E, #D4622A);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 28px rgba(0,0,0,.35);
            position: relative;
        }
        /* IMAGEM: sempre cobre todo o círculo sem distorcer */
        .avatar-circle img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover; object-position: center;
            display: block; border-radius: 0; /* herda do pai */
        }
        .avatar-initial {
            font-size: 40px; font-weight: 700; color: #fff;
            line-height: 1; user-select: none; position: relative; z-index: 1;
        }
        /* Botão editar foto */
        .avatar-edit {
            position: absolute; bottom: 3px; right: 3px;
            width: 30px; height: 30px;
            background: #fff; border: 2px solid #E8DCC8;
            border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,.2);
            transition: background .2s, border-color .2s;
            z-index: 10;
        }
        .avatar-edit:hover { background: #f0f7f1; border-color: #a8d5b0; }
        .avatar-edit svg { width: 13px; height: 13px; stroke: #2C4A2E; fill: none; stroke-width: 2.5; }

        /* ─── HERO INFO ──────────────────────────────────── */
        .hero-info { flex: 1; padding-bottom: 22px; min-width: 0; }
        .hero-info h1 {
            font-size: 22px; font-weight: 700; color: #fff;
            margin-bottom: 4px; line-height: 1.2;
        }
        .hero-email { font-size: 13px; color: rgba(255,255,255,.55); margin-bottom: 14px; }
        .hero-badges { display: flex; gap: 7px; flex-wrap: wrap; margin-bottom: 18px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18);
            color: rgba(255,255,255,.82); font-size: 11px; font-weight: 500;
            padding: 4px 12px; border-radius: 20px;
        }
        .hero-badge svg { width: 11px; height: 11px; stroke: currentColor; fill: none; stroke-width: 2; }
        .hero-badge.admin { background: rgba(212,98,42,.2); border-color: rgba(212,98,42,.35); color: #f0a070; }
        .hero-stats {
            display: inline-flex; gap: 0;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 14px; overflow: hidden;
        }
        .hero-stat {
            padding: 12px 22px; text-align: center;
            border-right: 1px solid rgba(255,255,255,.1);
        }
        .hero-stat:last-child { border-right: none; }
        .hero-stat strong { display: block; font-size: 19px; font-weight: 800; color: #a3d9ab; line-height: 1; }
        .hero-stat span   { font-size: 10px; color: rgba(255,255,255,.45); text-transform: uppercase; letter-spacing: .06em; margin-top: 3px; display: block; }

        /* Onda divisória hero → body */
        .hero-wave svg { display: block; }

        /* ─── LAYOUT BODY ────────────────────────────────── */
        .settings-body {
            max-width: 1080px; margin: 0 auto;
            padding: 72px 48px 80px;
            display: grid;
            grid-template-columns: 210px 1fr;
            gap: 32px;
            background: transparent;
        }
        .page-bg { background: #F5F0E8; }

        /* ─── NAV SIDEBAR ────────────────────────────────── */
        .settings-nav {
            height: fit-content;
            position: sticky; top: 90px;
        }
        .nav-group-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .12em; color: var(--text-4);
            padding: 0 12px; margin-bottom: 4px; margin-top: 18px;
        }
        .nav-group-label:first-child { margin-top: 0; }
        .nav-item {
            display: flex; align-items: center; gap: 9px;
            padding: 9px 12px; border-radius: 10px;
            font-size: 13px; color: var(--text-2);
            cursor: pointer; transition: all .18s;
            border: none; background: none; width: 100%;
            text-align: left; font-family: 'Poppins', sans-serif;
            text-decoration: none;
        }
        .nav-item svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; flex-shrink: 0; opacity: .65; }
        .nav-item:hover { background: #ede8df; color: var(--g-mid); }
        .nav-item.active { background: #e6f0e7; color: var(--g-mid); font-weight: 600; }
        .nav-item.active svg { opacity: 1; }
        .nav-item.red { color: #dc2626; }
        .nav-item.red:hover { background: #fef2f2; }
        .nav-hr { height: 1px; background: var(--border); margin: 10px 0; }

        /* ─── CARDS ──────────────────────────────────────── */
        .card {
            background: #fff; border: 1px solid var(--border);
            border-radius: 16px; overflow: hidden; margin-bottom: 18px;
        }
        .card-head {
            padding: 16px 22px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 12px;
        }
        .card-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: linear-gradient(135deg, #edf7ee, #d6edda);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .card-icon svg { width: 15px; height: 15px; stroke: var(--g-mid); fill: none; stroke-width: 2; }
        .card-head-text h3 { font-size: 13.5px; font-weight: 700; color: var(--text); margin: 0 0 1px; }
        .card-head-text p  { font-size: 11.5px; color: var(--text-4); margin: 0; }
        .card-body { padding: 22px; }

        /* ─── FORMULÁRIOS ────────────────────────────────── */
        .form-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .form-grid.c1 { grid-template-columns: 1fr; }
        .form-grid.c3 { grid-template-columns: 1fr 1fr 1fr; }
        .field { display: flex; flex-direction: column; gap: 5px; }
        .field label {
            font-size: 10.5px; font-weight: 700; color: var(--text-3);
            text-transform: uppercase; letter-spacing: .05em;
        }
        .field input, .field select, .field textarea {
            padding: 10px 13px; border: 1.5px solid var(--border);
            border-radius: 10px; font-family: 'Poppins', sans-serif;
            font-size: 13px; color: var(--text); outline: none;
            background: var(--cream); transition: border-color .18s, background .18s;
        }
        .field input:focus, .field select:focus, .field textarea:focus {
            border-color: var(--g-mid); background: #fff;
        }
        .field input:disabled { opacity: .5; cursor: not-allowed; background: #f4f4f4; }
        .field textarea { resize: vertical; min-height: 80px; }
        .field .hint { font-size: 11px; color: var(--text-4); }

        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 44px; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: var(--text-4); padding: 4px;
        }
        .pw-toggle svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; display: block; }

        .pwd-bar { display: flex; gap: 3px; margin: 6px 0 3px; }
        .pwd-bar span { flex: 1; height: 3px; border-radius: 2px; background: var(--border); transition: background .25s; }
        .pwd-label { font-size: 11px; color: var(--text-4); }

        /* Botões */
        .btn-green {
            padding: 10px 24px; background: var(--g-mid); color: #fff;
            border: none; border-radius: 10px; font-family: 'Poppins', sans-serif;
            font-size: 13px; font-weight: 600; cursor: pointer; transition: all .18s;
            display: inline-flex; align-items: center; gap: 7px;
        }
        .btn-green svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2.5; }
        .btn-green:hover { background: var(--g-light); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(44,74,46,.22); }
        .btn-ghost {
            padding: 10px 20px; background: transparent; color: var(--g-mid);
            border: 1.5px solid var(--g-mid); border-radius: 10px;
            font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all .18s;
        }
        .btn-ghost:hover { background: #edf7ee; }
        .btn-danger {
            padding: 10px 20px; background: #fef2f2; color: #dc2626;
            border: 1.5px solid #fecaca; border-radius: 10px;
            font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .18s;
        }
        .btn-danger:hover { background: #fee2e2; }
        .row-end { display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px; }

        /* Alert */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 16px; border-radius: 11px; font-size: 13px;
            margin-bottom: 18px; line-height: 1.5;
        }
        .alert svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2.5; flex-shrink: 0; margin-top: 1px; }
        .alert-ok  { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* Painéis */
        .panel { display: none; }
        .panel.active { display: block; }

        /* Endereços */
        .end-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .end-card {
            background: var(--cream); border: 1.5px solid var(--border);
            border-radius: 12px; padding: 15px 17px; position: relative;
            transition: border-color .18s;
        }
        .end-card:hover { border-color: #b0d8b5; }
        .end-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
            color: var(--g-mid); display: flex; align-items: center; gap: 5px; margin-bottom: 7px;
        }
        .end-label svg { width: 11px; height: 11px; stroke: currentColor; fill: none; stroke-width: 2.5; }
        .end-addr { font-size: 12.5px; color: var(--text-2); line-height: 1.65; }
        .end-del {
            margin-top: 10px; background: none; border: none;
            font-size: 11px; color: #EF4444; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 4px; padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        .end-del svg { width: 11px; height: 11px; stroke: currentColor; fill: none; stroke-width: 2.5; }
        .empty-box {
            text-align: center; padding: 30px; color: var(--text-4);
        }
        .empty-box svg { width: 38px; height: 38px; stroke: #D1D5DB; fill: none; stroke-width: 1.5; margin-bottom: 10px; }
        .empty-box p { font-size: 13px; }
        .add-addr-toggle {
            display: flex; align-items: center; gap: 7px;
            font-size: 13px; font-weight: 600; color: var(--g-mid);
            cursor: pointer; padding: 8px 0; border: none; background: none;
            font-family: 'Poppins', sans-serif;
        }
        .add-addr-toggle svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2.5; }
        .add-addr-form { display: none; margin-top: 16px; padding-top: 18px; border-top: 1px solid var(--border); }
        .add-addr-form.open { display: block; }

        /* Toggles */
        .tog-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 13px 0; border-bottom: 1px solid var(--border);
        }
        .tog-row:last-of-type { border-bottom: none; }
        .tog-info h4 { font-size: 13px; font-weight: 600; color: var(--text); margin: 0 0 2px; }
        .tog-info p  { font-size: 12px; color: var(--text-4); margin: 0; }
        .sw { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
        .sw input { opacity: 0; width: 0; height: 0; }
        .sw-track {
            position: absolute; inset: 0;
            background: #D1D5DB; border-radius: 24px;
            cursor: pointer; transition: background .2s;
        }
        .sw-track::before {
            content: ''; position: absolute;
            width: 18px; height: 18px; left: 3px; bottom: 3px;
            background: #fff; border-radius: 50%;
            transition: transform .2s; box-shadow: 0 1px 4px rgba(0,0,0,.2);
        }
        .sw input:checked + .sw-track { background: var(--g-mid); }
        .sw input:checked + .sw-track::before { transform: translateX(18px); }

        /* Segurança */
        .sec-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 13px 0; border-bottom: 1px solid var(--border);
        }
        .sec-row:last-child { border-bottom: none; }
        .sec-left  { display: flex; align-items: center; gap: 12px; }
        .sec-icon  { width: 36px; height: 36px; background: #f4f4f4; border-radius: 9px; display: flex; align-items: center; justify-content: center; }
        .sec-icon svg { width: 15px; height: 15px; stroke: #6B7280; fill: none; stroke-width: 2; }
        .sec-text h4 { font-size: 13px; font-weight: 600; color: var(--text); }
        .sec-text p  { font-size: 11.5px; color: var(--text-4); }
        .badge-ok   { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: #f0fdf4; color: #166534; }
        .badge-warn { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: #fffbeb; color: #92400e; }

        @media(max-width: 860px) {
            .perfil-hero  { padding: 32px 20px 0; }
            .settings-body { grid-template-columns: 1fr; padding: 64px 20px 60px; gap: 20px; }
            .settings-nav  { position: static; display: flex; flex-wrap: wrap; gap: 4px; }
            .nav-group-label, .nav-hr { display: none; }
            .nav-item { padding: 7px 11px; font-size: 12px; }
            .form-grid { grid-template-columns: 1fr; }
            .form-grid.c3 { grid-template-columns: 1fr 1fr; }
            .end-grid  { grid-template-columns: 1fr; }
            .hero-stats { flex-wrap: wrap; }
        }
    </style>
</head>
<body data-logado="1">
<?php include("includes/header.php"); ?>

<!-- ════ HERO ════ -->
<div class="perfil-hero">
    <div class="hero-inner">

        <!-- AVATAR -->
        <div class="avatar-wrap">
            <div class="avatar-circle">
                <?php if ($foto_valida): ?>
                    <img src="<?php echo htmlspecialchars($usuario['foto']); ?>"
                         alt="Foto de <?php echo htmlspecialchars($usuario['nome']); ?>">
                <?php else: ?>
                    <span class="avatar-initial"><?php echo $inicial; ?></span>
                <?php endif; ?>
            </div>

            <!-- Botão editar foto: label aponta para o input hidden -->
            <form id="formFoto" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="acao" value="alterar_foto">
                <label for="inputFoto" class="avatar-edit" title="Alterar foto de perfil">
                    <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </label>
                <input type="file" name="foto" id="inputFoto" accept="image/*"
                       style="display:none;"
                       onchange="this.form.submit();">
            </form>
        </div>

        <!-- INFO -->
        <div class="hero-info">
            <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
            <div class="hero-email"><?php echo htmlspecialchars($usuario['email']); ?></div>
            <div class="hero-badges">
                <span class="hero-badge">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Membro desde <?php echo $membro_desde; ?>
                </span>
                <?php if ($usuario['tipo'] === 'admin'): ?>
                <span class="hero-badge admin">
                    <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Administrador
                </span>
                <?php endif; ?>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <strong><?php echo $pedidos_count; ?></strong>
                    <span>Pedidos</span>
                </div>
                <div class="hero-stat">
                    <strong><?php echo $favoritos_count; ?></strong>
                    <span>Favoritos</span>
                </div>
                <div class="hero-stat">
                    <strong><?php echo count($enderecos); ?></strong>
                    <span>Endereços</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Onda de separação -->
    <div class="hero-wave">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" style="width:100%;display:block;">
            <path d="M0 40 C480 0 960 0 1440 40 L1440 40 L0 40Z" fill="#F5F0E8"/>
        </svg>
    </div>
</div>

<!-- ════ BODY ════ -->
<div class="page-bg">
<div class="settings-body">

    <!-- NAV -->
    <nav class="settings-nav">
        <div class="nav-group-label">Conta</div>
        <button class="nav-item <?php echo $painel_ativo==='dados'?'active':''; ?>" onclick="mudarPainel('dados',this)">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Dados Pessoais
        </button>
        <button class="nav-item <?php echo $painel_ativo==='senha'?'active':''; ?>" onclick="mudarPainel('senha',this)">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Segurança
        </button>
        <button class="nav-item <?php echo $painel_ativo==='notificacoes'?'active':''; ?>" onclick="mudarPainel('notificacoes',this)">
            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Notificações
        </button>

        <div class="nav-hr"></div>
        <div class="nav-group-label">Atividade</div>
        <a href="pedidos.php" class="nav-item">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Meus Pedidos
        </a>
        <a href="favoritos.php" class="nav-item">
            <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            Favoritos
        </a>
        <button class="nav-item <?php echo $painel_ativo==='enderecos'?'active':''; ?>" onclick="mudarPainel('enderecos',this)">
            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Endereços
        </button>

        <div class="nav-hr"></div>
        <a href="logout.php" class="nav-item red">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair da Conta
        </a>
    </nav>

    <!-- CONTEÚDO -->
    <div>

        <?php if ($msg): ?>
        <div class="alert <?php echo str_contains($msg,'ERRO') ? 'alert-err' : 'alert-ok'; ?>">
            <?php if (str_contains($msg,'ERRO')): ?>
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php echo str_replace('ERRO: ','',$msg); ?>
            <?php else: ?>
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo $msg; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ── DADOS PESSOAIS ── -->
        <div class="panel <?php echo $painel_ativo==='dados'?'active':''; ?>" id="panel-dados">
            <div class="card">
                <div class="card-head">
                    <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                    <div class="card-head-text">
                        <h3>Informações Pessoais</h3>
                        <p>Seus dados de identificação na plataforma</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="acao" value="editar_perfil">
                        <div class="form-grid">
                            <div class="field">
                                <label>Nome Completo</label>
                                <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                            </div>
                            <div class="field">
                                <label>Telefone / WhatsApp</label>
                                <input type="text" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        <div class="form-grid c1">
                            <div class="field">
                                <label>E-mail</label>
                                <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                                <span class="hint">O e-mail não pode ser alterado. Contate o suporte se necessário.</span>
                            </div>
                        </div>
                        <div class="row-end">
                            <button type="submit" class="btn-green">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── SEGURANÇA ── -->
        <div class="panel <?php echo $painel_ativo==='senha'?'active':''; ?>" id="panel-senha">
            <div class="card">
                <div class="card-head">
                    <div class="card-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                    <div class="card-head-text">
                        <h3>Alterar Senha</h3>
                        <p>Use uma senha forte com pelo menos 8 caracteres</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="acao" value="trocar_senha">
                        <div class="form-grid c1">
                            <div class="field">
                                <label>Senha Atual</label>
                                <div class="pw-wrap">
                                    <input type="password" name="senha_atual" id="p-atual" required placeholder="Sua senha atual">
                                    <button type="button" class="pw-toggle" onclick="togglePw('p-atual',this)">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="field">
                                <label>Nova Senha</label>
                                <div class="pw-wrap">
                                    <input type="password" name="senha_nova" id="p-nova" required minlength="8" placeholder="Mínimo 8 caracteres" oninput="calcForca(this.value)">
                                    <button type="button" class="pw-toggle" onclick="togglePw('p-nova',this)">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <div id="forca-wrap" style="display:none;">
                                    <div class="pwd-bar">
                                        <span id="b1"></span><span id="b2"></span><span id="b3"></span><span id="b4"></span>
                                    </div>
                                    <div class="pwd-label" id="forca-label"></div>
                                </div>
                            </div>
                            <div class="field">
                                <label>Confirmar Nova Senha</label>
                                <div class="pw-wrap">
                                    <input type="password" name="senha_conf" id="p-conf" required placeholder="Repita a nova senha">
                                    <button type="button" class="pw-toggle" onclick="togglePw('p-conf',this)">
                                        <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row-end">
                            <button type="submit" class="btn-green">
                                <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                    <div class="card-head-text">
                        <h3>Segurança da Conta</h3>
                        <p>Visão geral da proteção ativa</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="sec-row">
                        <div class="sec-left">
                            <div class="sec-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                            <div class="sec-text"><h4>Senha</h4><p>Autenticação bcrypt ativa</p></div>
                        </div>
                        <span class="badge-ok">Ativa</span>
                    </div>
                    <div class="sec-row">
                        <div class="sec-left">
                            <div class="sec-icon"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                            <div class="sec-text"><h4>E-mail verificado</h4><p><?php echo htmlspecialchars($usuario['email']); ?></p></div>
                        </div>
                        <span class="badge-ok">Verificado</span>
                    </div>
                    <div class="sec-row">
                        <div class="sec-left">
                            <div class="sec-icon"><svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg></div>
                            <div class="sec-text"><h4>Autenticação em dois fatores</h4><p>Proteção extra via celular</p></div>
                        </div>
                        <span class="badge-warn">Em breve</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── NOTIFICAÇÕES ── -->
        <div class="panel <?php echo $painel_ativo==='notificacoes'?'active':''; ?>" id="panel-notificacoes">
            <div class="card">
                <div class="card-head">
                    <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
                    <div class="card-head-text">
                        <h3>Preferências de Notificação</h3>
                        <p>Controle o que você recebe por e-mail</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="acao" value="preferencias">
                        <div class="tog-row">
                            <div class="tog-info"><h4>Atualizações de pedidos</h4><p>Confirmação, envio e entrega</p></div>
                            <label class="sw"><input type="checkbox" name="notif_pedidos" checked><span class="sw-track"></span></label>
                        </div>
                        <div class="tog-row">
                            <div class="tog-info"><h4>Newsletter Origem Brasil</h4><p>Novos produtos e histórias do campo</p></div>
                            <label class="sw"><input type="checkbox" name="newsletter"><span class="sw-track"></span></label>
                        </div>
                        <div class="tog-row">
                            <div class="tog-info"><h4>Promoções e ofertas</h4><p>Descontos exclusivos em primeira mão</p></div>
                            <label class="sw"><input type="checkbox" name="notif_promo"><span class="sw-track"></span></label>
                        </div>
                        <div class="tog-row">
                            <div class="tog-info"><h4>Produtos favoritos</h4><p>Avise quando voltarem ao estoque</p></div>
                            <label class="sw"><input type="checkbox" name="notif_fav" checked><span class="sw-track"></span></label>
                        </div>
                        <div class="row-end" style="margin-top:16px;">
                            <button type="submit" class="btn-green">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Salvar Preferências
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ── ENDEREÇOS ── -->
        <div class="panel <?php echo $painel_ativo==='enderecos'?'active':''; ?>" id="panel-enderecos">
            <div class="card">
                <div class="card-head">
                    <div class="card-icon"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                    <div class="card-head-text">
                        <h3>Meus Endereços</h3>
                        <p>Endereços salvos para entrega</p>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($enderecos)): ?>
                    <div class="empty-box">
                        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <p>Nenhum endereço cadastrado ainda.</p>
                    </div>
                    <?php else: ?>
                    <div class="end-grid">
                        <?php foreach ($enderecos as $e): ?>
                        <div class="end-card">
                            <div class="end-label">
                                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                <?php echo htmlspecialchars($e['apelido']); ?>
                            </div>
                            <div class="end-addr">
                                <?php echo htmlspecialchars($e['rua']); ?>, <?php echo htmlspecialchars($e['numero']); ?>
                                <?php if (!empty($e['complemento'])): ?> — <?php echo htmlspecialchars($e['complemento']); ?><?php endif; ?><br>
                                <?php echo htmlspecialchars($e['bairro']); ?> · <?php echo htmlspecialchars($e['cidade']); ?>/<?php echo htmlspecialchars($e['estado']); ?><br>
                                CEP <?php echo htmlspecialchars($e['cep']); ?>
                            </div>
                            <form method="POST" onsubmit="return confirm('Remover este endereço?')" style="display:inline;">
                                <input type="hidden" name="acao" value="del_endereco">
                                <input type="hidden" name="eid" value="<?php echo $e['id']; ?>">
                                <button type="submit" class="end-del">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    Remover
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <button type="button" class="add-addr-toggle" id="btnAddAddr"
                            onclick="document.getElementById('formAddr').classList.add('open');this.style.display='none';">
                        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Adicionar novo endereço
                    </button>

                    <div class="add-addr-form" id="formAddr">
                        <form method="POST">
                            <input type="hidden" name="acao" value="add_endereco">
                            <div class="form-grid">
                                <div class="field">
                                    <label>Apelido</label>
                                    <input type="text" name="apelido" value="Casa" placeholder="Casa, Trabalho…">
                                </div>
                                <div class="field">
                                    <label>CEP</label>
                                    <input type="text" name="cep" id="cep-in" placeholder="00000-000" oninput="viaCep(this.value)">
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="field">
                                    <label>Rua / Avenida</label>
                                    <input type="text" name="rua" id="rua-in" required>
                                </div>
                                <div class="field">
                                    <label>Número</label>
                                    <input type="text" name="numero" required>
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="field">
                                    <label>Complemento</label>
                                    <input type="text" name="complemento" placeholder="Apto, bloco… (opcional)">
                                </div>
                                <div class="field">
                                    <label>Bairro</label>
                                    <input type="text" name="bairro" id="bairro-in" required>
                                </div>
                            </div>
                            <div class="form-grid">
                                <div class="field">
                                    <label>Cidade</label>
                                    <input type="text" name="cidade" id="cidade-in" required>
                                </div>
                                <div class="field">
                                    <label>Estado</label>
                                    <select name="estado" id="estado-in">
                                        <?php foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?>
                                        <option value="<?php echo $uf; ?>"><?php echo $uf; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row-end">
                                <button type="button" class="btn-ghost"
                                        onclick="document.getElementById('formAddr').classList.remove('open');document.getElementById('btnAddAddr').style.display='';">
                                    Cancelar
                                </button>
                                <button type="submit" class="btn-green">
                                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Salvar Endereço
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /conteúdo -->
</div><!-- /settings-body -->
</div><!-- /page-bg -->

<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
<script>
/* Troca de painel */
function mudarPainel(id, btn) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + id).classList.add('active');
    btn.classList.add('active');
}

/* Mostrar/ocultar senha */
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    btn.querySelector('svg').innerHTML = show
        ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}

/* Força da senha */
function calcForca(v) {
    const wrap = document.getElementById('forca-wrap');
    const label = document.getElementById('forca-label');
    const bars  = ['b1','b2','b3','b4'].map(id => document.getElementById(id));
    if (!v) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    let s = 0;
    if (v.length >= 8)  s++;
    if (v.length >= 12) s++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
    if (/[0-9]/.test(v) && /[^a-zA-Z0-9]/.test(v)) s++;
    const cores  = ['#EF4444','#f97316','#eab308','#22c55e'];
    const textos = ['Muito fraca','Fraca','Boa','Forte'];
    bars.forEach((b, i) => { b.style.background = i < s ? cores[s-1] : '#E8DCC8'; });
    label.textContent  = textos[s-1] || '';
    label.style.color  = cores[s-1] || '#9CA3AF';
}

/* ViaCEP */
async function viaCep(cep) {
    const c = cep.replace(/\D/g,'');
    if (c.length !== 8) return;
    try {
        const r = await fetch(`https://viacep.com.br/ws/${c}/json/`);
        const d = await r.json();
        if (!d.erro) {
            document.getElementById('rua-in').value    = d.logradouro || '';
            document.getElementById('bairro-in').value = d.bairro     || '';
            document.getElementById('cidade-in').value = d.localidade || '';
            const sel = document.getElementById('estado-in');
            for (const o of sel.options) if (o.value === d.uf) { o.selected = true; break; }
        }
    } catch(_) {}
}
</script>
</body>
</html>