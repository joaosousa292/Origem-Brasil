<?php
include("../conexao.php");

$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);

    $stmt = mysqli_prepare(
        $conexao,
        "SELECT id, nome, email
         FROM usuarios
         WHERE email = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $email
    );

    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);

    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario) {

        $codigo = str_pad(
            rand(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $expira = date(
            'Y-m-d H:i:s',
            strtotime('+10 minutes')
        );

        $update = mysqli_prepare(
            $conexao,
            "UPDATE usuarios
             SET codigo_recuperacao = ?,
                 codigo_expira = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "ssi",
            $codigo,
            $expira,
            $usuario['id']
        );

        mysqli_stmt_execute($update);

        // TEMPORÁRIO PARA TESTES
        require_once 'enviar_codigo.php';

        if (enviarCodigo($email, $codigo)) {

            $_SESSION['email_recuperacao'] = $email;
        
            header("Location: verificar_codigo.php");
            exit;
        
        } else {
        
            $erro = "Erro ao enviar o e-mail.";
        
        }

    } else {

        $erro = "Nenhuma conta foi encontrada com este e-mail.";

    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha — Origem Brasil</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/esquece.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

<div class="card">

    <a href="../index.php" class="logo">
        Origem<br>Brasil
    </a>

    <h2>Recuperar Senha</h2>

    <p class="sub">
        Digite seu e-mail para receber as instruções
    </p>

    <?php if (!empty($msg)): ?>
        <div style="
            color:#166534;
            background:#f0fdf4;
            border:1px solid #bbf7d0;
            padding:12px;
            border-radius:8px;
            margin-bottom:15px;
            text-align:center;
            font-size:13px;
        ">
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>

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
            <?php echo htmlspecialchars($erro); ?>
        </div>
    <?php endif; ?>

    <form id="form-esquece" method="POST">

        <div class="field">
            <label for="email">
                E-mail
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="seu@email.com"
                required>
        </div>

        <button class="btn-login" type="submit">
            Enviar Instruções
        </button>

    </form>

    <div class="divider">
        ou
    </div>

    <p class="link-cadastro">
        <a href="../login.php">
            ← Voltar ao login
        </a>
    </p>

</div>

</body>
</html>
