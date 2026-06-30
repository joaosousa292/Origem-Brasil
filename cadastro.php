<?php
include("conexao.php");
if (isset($_SESSION['id'])) { header("Location: index.php"); exit; }
$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta — Origem Brasil</title>
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
            <div class="auth-visual-tag"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> Cadastro gratuito</div>
            <h1 class="auth-visual-headline">
                Sua primeira compra<br><em>com desconto especial</em>
            </h1>
            <p class="auth-visual-desc">
                Junte-se a milhares de brasileiros que já consomem 
                produtos com rastreabilidade e procedência garantida.
            </p>
            <div class="auth-badges">
                <div class="auth-badge">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Sem taxa de cadastro
                </div>
                <div class="auth-badge">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Dados protegidos
                </div>
                <div class="auth-badge">
                    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    Produtores verificados
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
            <p>Crie sua conta gratuitamente</p>
        </div>

        <div class="form-header">
            <h2>Criar sua conta</h2>
            <p>Preencha os dados abaixo para começar</p>
        </div>

        <?php if ($erro === 'email'): ?>
            <div class="erro-msg"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Este e-mail já está cadastrado.</div>
        <?php elseif ($erro === 'senha'): ?>
            <div class="erro-msg"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> As senhas não coincidem.</div>
        <?php elseif ($erro === 'campos'): ?>
            <div class="erro-msg"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Preencha todos os campos obrigatórios.</div>
        <?php elseif ($erro === 'curta'): ?>
            <div class="erro-msg"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> A senha deve ter pelo menos 6 caracteres.</div>
        <?php endif; ?>

        <form action="salvar_usuario.php" method="POST">
            <div class="field">
                <label>Nome Completo</label>
                <input type="text" name="nome" placeholder="Seu nome completo" required>
            </div>
            <div class="field">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="seu@email.com" required>
            </div>
            <div class="field">
                <label>Senha (mín. 6 caracteres)</label>
                <input type="password" name="senha" id="senha" placeholder="••••••••" required minlength="6"
                       oninput="checkStrength(this.value)">
                <div class="strength" id="strength-bar"></div>
            </div>
            <div class="field">
                <label>Confirmar Senha</label>
                <input type="password" name="confirma_senha" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Criar Conta Grátis</button>
        </form>

        <div class="divider">ou</div>
        <p class="link-cadastro">Já tem conta? <a href="login.php">Fazer login</a></p>

    </div>
</div>

<script>
function checkStrength(v) {
    const bar = document.getElementById('strength-bar');
    if (!bar) return;
    let score = 0;
    if (v.length >= 6) score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const colors = ['#ef4444','#f97316','#eab308','#84cc16','#22c55e'];
    const widths = ['20%','40%','60%','80%','100%'];
    bar.style.background = colors[Math.min(score,4)];
    bar.style.width = widths[Math.min(score,4)];
}
</script>
</body>
</html>