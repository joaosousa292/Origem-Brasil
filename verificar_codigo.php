<?php

session_start();
include("conexao.php");

if (!isset($_SESSION['email_recuperacao'])) {
    header("Location: esquece.php");
    exit;
}

$email = $_SESSION['email_recuperacao'];

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo = trim($_POST['codigo']);

    $stmt = mysqli_prepare(
        $conexao,
        "SELECT id,
                codigo_expira
         FROM usuarios
         WHERE email = ?
         AND codigo_recuperacao = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $email,
        $codigo
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario) {

        if (strtotime($usuario['codigo_expira']) < time()) {

            $erro = "Código expirado.";

        } else {

            $_SESSION['codigo_validado'] = true;

            header("Location: nova_senha.php");
            exit;

        }

    } else {

        $erro = "Código inválido.";

    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Verificar Código</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/login.css">
</head>

<body>

<div class="card">

    <a href="index.php" class="logo">
        Origem<br>Brasil
    </a>

    <h2>Verificação</h2>

    <p class="sub">
        Digite o código enviado para seu e-mail
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
            font-size:13px;
        ">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

    <label>Código de Verificação</label>

<div class="codigo-boxes">
    <input type="text" maxlength="1" class="codigo-input">
    <input type="text" maxlength="1" class="codigo-input">
    <input type="text" maxlength="1" class="codigo-input">
    <input type="text" maxlength="1" class="codigo-input">
    <input type="text" maxlength="1" class="codigo-input">
    <input type="text" maxlength="1" class="codigo-input">
</div>

<input type="hidden" name="codigo" id="codigoCompleto">

<p class="codigo-info">
    O código expira em 10 minutos.
</p>

        <button class="btn-login" type="submit">
            Verificar Código
        </button>

    </form>

</div>
<script>
const inputs = document.querySelectorAll('.codigo-input');
const hidden = document.getElementById('codigoCompleto');

inputs.forEach((input,index)=>{

    input.addEventListener('input',()=>{

        if(input.value.length === 1){

            if(index < inputs.length-1){
                inputs[index+1].focus();
            }

        }

        atualizarCodigo();

    });

    input.addEventListener('keydown',(e)=>{

        if(e.key === 'Backspace' && !input.value && index > 0){
            inputs[index-1].focus();
        }

    });

});

function atualizarCodigo(){

    let codigo = '';

    inputs.forEach(input=>{
        codigo += input.value;
    });

    hidden.value = codigo;

}
</script>
</body>
</html>