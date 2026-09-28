<?php
include("conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.php?voltar=adm/dashboard.php"); exit;
}
header("Location: adm/dashboard.php"); exit;
