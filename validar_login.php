<?php
include("conexao.php");

$email  = trim($_POST['email'] ?? '');
$senha  = $_POST['senha']  ?? '';
$voltar = $_POST['voltar'] ?? 'index.php';

if (!preg_match('/^[a-zA-Z0-9\/?=_\-\.]+$/', $voltar)) {
    $voltar = 'index.php';
}

if (empty($email) || empty($senha)) {
    header("Location: login.php?erro=campos&voltar=" . urlencode($voltar));
    exit;
}

$stmt = mysqli_prepare($conexao, "SELECT * FROM usuarios WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($usuario && password_verify($senha, $usuario['senha'])) {
    $_SESSION['id']   = $usuario['id'];
    $_SESSION['nome'] = $usuario['nome'];
    $_SESSION['tipo'] = $usuario['tipo'];
    header("Location: " . $voltar);
    exit;
} elseif ($usuario) {
    header("Location: login.php?erro=senha&voltar=" . urlencode($voltar));
    exit;
} else {
    header("Location: login.php?erro=usuario&voltar=" . urlencode($voltar));
    exit;
}
