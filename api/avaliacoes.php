<?php
header('Content-Type: application/json');
include("../conexao.php");

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'listar':
        $produto_id = (int)($_GET['produto_id'] ?? 0);
        if (!$produto_id) { echo json_encode([]); exit; }
        $stmt = mysqli_prepare($conexao,
            "SELECT a.id, a.nota, a.comentario, a.criado_em, u.nome
             FROM avaliacoes a
             JOIN usuarios u ON u.id = a.usuario_id
             WHERE a.produto_id = ?
             ORDER BY a.criado_em DESC"
        );
        mysqli_stmt_bind_param($stmt, 'i', $produto_id);
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        echo json_encode($rows);
        break;

    case 'media':
        $produto_id = (int)($_GET['produto_id'] ?? 0);
        $stmt = mysqli_prepare($conexao,
            "SELECT ROUND(AVG(nota),1) as media, COUNT(*) as total FROM avaliacoes WHERE produto_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $produto_id);
        mysqli_stmt_execute($stmt);
        echo json_encode(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)));
        break;

    case 'salvar':
        if (!isset($_SESSION['id'])) { http_response_code(401); echo json_encode(['erro' => 'nao_logado']); exit; }
        $produto_id  = (int)($_POST['produto_id'] ?? 0);
        $nota        = (int)($_POST['nota']        ?? 0);
        $comentario  = htmlspecialchars(trim($_POST['comentario'] ?? ''));
        $usuario_id  = (int)$_SESSION['id'];

        if (!$produto_id || $nota < 1 || $nota > 5) {
            echo json_encode(['erro' => 'dados_invalidos']); exit;
        }

        // Verifica compra — opcionalmente só quem comprou avalia
        $stmt = mysqli_prepare($conexao,
            "INSERT INTO avaliacoes (usuario_id, produto_id, nota, comentario) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE nota = VALUES(nota), comentario = VALUES(comentario)"
        );
        mysqli_stmt_bind_param($stmt, 'iiis', $usuario_id, $produto_id, $nota, $comentario);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['erro' => 'Erro ao salvar avaliação.']);
        }
        break;

    default:
        echo json_encode(['erro' => 'acao_invalida']);
}
