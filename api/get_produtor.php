<?php
include("../conexao.php");
header('Content-Type: application/json');
$id = (int)($_GET['id'] ?? 0);
$p = $id ? mysqli_fetch_assoc(mysqli_query($conexao,"SELECT id,nome,fazenda,estado FROM produtores WHERE id=$id")) : null;
echo json_encode($p ?: ['erro'=>'não encontrado']);
