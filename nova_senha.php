<?php

session_start();
include("conexao.php");

if (
    !isset($_SESSION['codigo_validado']) ||
    !isset($_SESSION['email_recuperacao'])
) {
    header("Location: esquece.php");
    exit;
}

$email = $_SESSION['email_recuperacao'];

$erro = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $senha = trim($_POST['senha']);
    $confirmar = trim($_POST['confirmar']);

    if ($senha !== $confirmar) {

        $erro = "As senhas não coincidem.";

    } elseif (strlen($senha) < 6) {

        $erro = "A senha deve possuir pelo menos 6 caracteres.";

    } else {

        $senhaHash = password_hash(
            $senha,
            PASSWORD_BCRYPT
        );

        $stmt = mysqli_prepare(
            $conexao,
            "UPDATE usuarios
             SET senha = ?,
                 codigo_recuperacao = NULL,
                 codigo_expira = NULL
             WHERE email = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $senhaHash,
            $email
        );

        mysqli_stmt_execute($stmt);

        session_destroy();

        header("Location: login.php?senha=alterada");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nova Senha</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="card">

    <a href="index.php" class="logo">
        Origem<br>Brasil
    </a>

    <h2>Nova Senha</h2>
    <div id="forcaSenha" class="forca-senha">
    Força da senha: Fraca
</div>

    <p class="sub">
        Defina sua nova senha
    </p>

    <?php if (!empty($erro)): ?>
        <div style="
            color:#b91c1c;
            background:#fef2f2;
            border:1px solid #fecaca;
            padding:12px;
            border-radius:8px;
            margin-bottom:15px;
            text-align:center;
            font-size:13px;">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="field">
            <label>Nova senha</label>
            <input
                type="password"
                name="senha"
                required>
        </div>

        <div class="field">
            <label>Confirmar senha</label>
            <input
                type="password"
                name="confirmar"
                required>
        </div>

        <button class="btn-login" type="submit">
            Alterar Senha
        </button>

    </form>

</div>
<script>

const senha = document.querySelector('input[name="senha"]');
const forca = document.getElementById('forcaSenha');

senha.addEventListener('input', ()=>{

    let valor = senha.value;

    let pontos = 0;

    if(valor.length >= 8) pontos++;
    if(/[A-Z]/.test(valor)) pontos++;
    if(/[0-9]/.test(valor)) pontos++;
    if(/[!@#$%^&*(),.?":{}|<>]/.test(valor)) pontos++;

    if(pontos <= 1){
        forca.innerHTML = 'Força da senha: Fraca';
    }
    else if(pontos <= 3){
        forca.innerHTML = 'Força da senha: Média';
    }
    else{
        forca.innerHTML = 'Força da senha: Forte';
    }

});
</script>
</body>
</html>