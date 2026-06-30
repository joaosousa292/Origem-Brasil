<?php
// Redireciona /cafes.php para /produtos.php?categoria=cafe
// mantendo compatibilidade com links antigos
$qs = $_SERVER['QUERY_STRING'] ? '&' . $_SERVER['QUERY_STRING'] : '';
header("Location: produtos.php?categoria=cafe" . $qs, true, 301);
exit;
