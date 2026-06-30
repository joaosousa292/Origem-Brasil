<?php
header('Content-Type: application/json');
include("../conexao.php");

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'nao_logado']);
    exit;
}

$usuario_id = (int)$_SESSION['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'listar':
        $stmt = mysqli_prepare($conexao,
            "SELECT f.produto_id, p.nome, p.preco, p.imagem, p.descricao, p.regiao
             FROM favoritos f
             JOIN produtos p ON p.id = f.produto_id
             WHERE f.usuario_id = ?
             ORDER BY f.id DESC"
        );
        mysqli_stmt_bind_param($stmt, 'i', $usuario_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $favs = [];
        $ids  = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $favs[] = $row;
            $ids[]  = (int)$row['produto_id'];
        }
        echo json_encode(['favs' => $favs, 'ids' => $ids]);
        break;

    case 'toggle':
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        if (!$produto_id) { http_response_code(400); echo json_encode(['erro' => 'dados_invalidos']); exit; }

        // Verifica se já existe
        $sc = mysqli_prepare($conexao, "SELECT id FROM favoritos WHERE usuario_id = ? AND produto_id = ?");
        mysqli_stmt_bind_param($sc, 'ii', $usuario_id, $produto_id);
        mysqli_stmt_execute($sc);
        $existe = mysqli_fetch_assoc(mysqli_stmt_get_result($sc));

        if ($existe) {
            $sd = mysqli_prepare($conexao, "DELETE FROM favoritos WHERE usuario_id = ? AND produto_id = ?");
            mysqli_stmt_bind_param($sd, 'ii', $usuario_id, $produto_id);
            mysqli_stmt_execute($sd);
            echo json_encode(['ok' => true, 'ativo' => false]);
        } else {
            $si = mysqli_prepare($conexao, "INSERT INTO favoritos (usuario_id, produto_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($si, 'ii', $usuario_id, $produto_id);
            mysqli_stmt_execute($si);
            echo json_encode(['ok' => true, 'ativo' => true]);
        }
        break;

    case 'verificar':
        $produto_id = (int)($_GET['produto_id'] ?? 0);
        if (!$produto_id) { echo json_encode(['ativo' => false]); exit; }
        $sc = mysqli_prepare($conexao, "SELECT id FROM favoritos WHERE usuario_id = ? AND produto_id = ?");
        mysqli_stmt_bind_param($sc, 'ii', $usuario_id, $produto_id);
        mysqli_stmt_execute($sc);
        $r = mysqli_fetch_assoc(mysqli_stmt_get_result($sc));
        echo json_encode(['ativo' => (bool)$r]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['erro' => 'acao_invalida']);
}
