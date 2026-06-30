<?php
include("conexao.php");
if (isset($_SESSION['id'])) { header("Location: index.php"); exit; }
$erro  = $_GET['erro']  ?? '';
$voltar = htmlspecialchars($_GET['voltar'] ?? 'index.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<!-- PAINEL ESQUERDO: imagem -->
<div class="auth-visual">
    <div class="auth-visual-content">

        <a href="index.php" class="auth-visual-logo">
            <div class="auth-visual-logo-icon">
                <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2A7.2 7.2 0 0 1 6 16c.03-2 4-3.1 6-3.1s5.97 1.1 6 3.1a7.2 7.2 0 0 1-6 3.2z"/></svg>
            </div>
            <div class="auth-visual-logo-text">
                <strong>Origem Brasil</strong>
                <span>Do campo à mesa</span>
            </div>
        </a>

        <div class="auth-visual-body">
            <div class="auth-visual-tag"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 10 10"/><path d="M8 5.07A10 10 0 0 1 22 12h-3"/><path d="M12 12a5 5 0 0 0 5-5"/><path d="M12 12a5 5 0 0 1-5-5"/></svg> Rastreabilidade garantida</div>
            <h1 class="auth-visual-headline">
                Produtos frescos,<br><em>direto da origem</em>
            </h1>
            <p class="auth-visual-desc">
                Conectamos você diretamente a produtores rurais brasileiros. 
                Cada item com procedência verificada e qualidade assegurada.
            </p>
            <div class="auth-badges">
                <div class="auth-badge">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Origem verificada
                </div>
                <div class="auth-badge">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Compra segura
                </div>
                <div class="auth-badge">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Entrega rápida
                </div>
            </div>
        </div>

    </div>
</div>

<!-- PAINEL DIREITO: formulário -->
<div class="auth-form-panel">
    <div class="card">

        <!-- Logo visível só no mobile -->
        <div class="mobile-logo">
            <a href="index.php">Origem Brasil</a>
            <p>Do campo à mesa com rastreabilidade</p>
        </div>

        <div class="form-header">
            <h2>Bem-vindo de volta</h2>
            <p>Entre na sua conta para continuar</p>
        </div>

        <?php if ($erro): ?>
        <div class="erro-msg">
            <?php
            if ($erro === 'senha')   echo '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Senha incorreta. Tente novamente.';
            elseif ($erro === 'usuario') echo '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> E-mail não encontrado.';
            elseif ($erro === 'campos')  echo '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Preencha todos os campos.';
            else echo '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Erro ao fazer login.';
            ?>
        </div>
        <?php endif; ?>

        <form action="validar_login.php" method="POST">
            <input type="hidden" name="voltar" value="<?php echo $voltar; ?>">
            <div class="field">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="seu@email.com" required autofocus>
            </div>
            <div class="field">
                <label>Senha</label>
                <input type="password" name="senha" placeholder="••••••••" required>
            </div>
            <a href="esquece.php" class="forgot">Esqueceu a senha?</a>
            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <div class="divider">ou</div>
        <p class="link-cadastro">Não tem conta? <a href="cadastro.php">Criar conta grátis</a></p>
        <p class="link-voltar"><a href="index.php">← Continuar como visitante</a></p>

    </div>
</div>

</body>
</html>