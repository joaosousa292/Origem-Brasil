<?php
include('conexao.php');

$nome  = 'Admin';
$email = 'admin@origem.com';
$senha = 'admin123';
$hash  = password_hash($senha, PASSWORD_DEFAULT);

$s = mysqli_prepare($conexao, "INSERT INTO usuarios (nome, email, senha, tipo, criado_em) VALUES (?, ?, ?, 'admin', NOW())");
mysqli_stmt_bind_param($s, 'sss', $nome, $email, $hash);
mysqli_stmt_execute($s);

echo "Admin criado! ID: " . mysqli_insert_id($conexao);
?>