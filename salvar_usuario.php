<?php
session_start();
include("conexao.php");

$nome   = htmlspecialchars(trim($_POST['nome']           ?? ''));
$email  = trim($_POST['email']          ?? '');
$senha  = $_POST['senha']               ?? '';
$conf   = $_POST['confirma_senha']      ?? '';

if (empty($nome) || empty($email) || empty($senha) || empty($conf)) {
    header("Location: cadastro.php?erro=campos"); exit;
}
if (strlen($senha) < 6) {
    header("Location: cadastro.php?erro=curta"); exit;
}
if ($senha !== $conf) {
    header("Location: cadastro.php?erro=senha"); exit;
}

// E-mail duplicado?
$chk = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($chk, "s", $email);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    header("Location: cadastro.php?erro=email"); exit;
}

$hash = password_hash($senha, PASSWORD_BCRYPT);
$ins  = mysqli_prepare($conexao, "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($ins, "sss", $nome, $email, $hash);

if (mysqli_stmt_execute($ins)) {
    $id = mysqli_insert_id($conexao);
    $_SESSION['id']   = $id;
    $_SESSION['nome'] = $nome;
    $_SESSION['tipo'] = 'cliente';
    header("Location: index.php");
} else {
    header("Location: cadastro.php?erro=campos");
}
exit;
