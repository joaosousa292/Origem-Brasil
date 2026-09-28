<?php
$sessionPath = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'sessions';

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

if (session_status() === PHP_SESSION_NONE) {
    if (is_writable($sessionPath)) {
        ini_set('session.save_path', $sessionPath);
    }

    session_start();
}

$host    = "localhost";
$usuario = "root";
$senha   = "";
$banco   = "br";

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die(json_encode(['erro' => 'Falha na conexão com o banco de dados: ' . mysqli_connect_error()]));
}

mysqli_set_charset($conexao, "utf8mb4");
