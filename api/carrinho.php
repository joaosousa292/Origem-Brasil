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
            "SELECT c.id, c.produto_id, c.quantidade,
                    p.nome, p.preco, p.imagem, p.estoque
             FROM carrinho c
             JOIN produtos p ON p.id = c.produto_id
             WHERE c.usuario_id = ?
             ORDER BY c.adicionado_em"
        );
        mysqli_stmt_bind_param($stmt, 'i', $usuario_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $itens = [];
        while ($row = mysqli_fetch_assoc($res)) $itens[] = $row;
        echo json_encode($itens);
        break;

    case 'adicionar':
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        $quantidade = (int)($_POST['quantidade'] ?? 1);
        if (!$produto_id || $quantidade < 1) {
            http_response_code(400);
            echo json_encode(['erro' => 'dados_invalidos']);
            exit;
        }

        $sp = mysqli_prepare($conexao, "SELECT id, estoque FROM produtos WHERE id = ?");
        mysqli_stmt_bind_param($sp, 'i', $produto_id);
        mysqli_stmt_execute($sp);
        $prod = mysqli_fetch_assoc(mysqli_stmt_get_result($sp));
        if (!$prod) { http_response_code(404); echo json_encode(['erro' => 'produto_nao_encontrado']); exit; }
        if ($prod['estoque'] <= 0) { echo json_encode(['erro' => 'sem_estoque']); exit; }

        $sc = mysqli_prepare($conexao,
            "SELECT id, quantidade FROM carrinho WHERE usuario_id = ? AND produto_id = ?"
        );
        mysqli_stmt_bind_param($sc, 'ii', $usuario_id, $produto_id);
        mysqli_stmt_execute($sc);
        $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($sc));

        if ($existente) {
            $nova_qty = min($existente['quantidade'] + $quantidade, $prod['estoque']);
            $su = mysqli_prepare($conexao, "UPDATE carrinho SET quantidade = ? WHERE id = ?");
            mysqli_stmt_bind_param($su, 'ii', $nova_qty, $existente['id']);
            mysqli_stmt_execute($su);
        } else {
            $si = mysqli_prepare($conexao,
                "INSERT INTO carrinho (usuario_id, produto_id, quantidade) VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param($si, 'iii', $usuario_id, $produto_id, $quantidade);
            mysqli_stmt_execute($si);
        }
        echo json_encode(['ok' => true]);
        break;

    case 'atualizar':
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        $quantidade = (int)($_POST['quantidade'] ?? 0);
        if (!$produto_id) { http_response_code(400); echo json_encode(['erro' => 'dados_invalidos']); exit; }

        if ($quantidade <= 0) {
            $sd = mysqli_prepare($conexao, "DELETE FROM carrinho WHERE usuario_id = ? AND produto_id = ?");
            mysqli_stmt_bind_param($sd, 'ii', $usuario_id, $produto_id);
            mysqli_stmt_execute($sd);
        } else {
            // Verifica estoque
            $se = mysqli_prepare($conexao, "SELECT estoque FROM produtos WHERE id = ?");
            mysqli_stmt_bind_param($se, 'i', $produto_id);
            mysqli_stmt_execute($se);
            $p = mysqli_fetch_assoc(mysqli_stmt_get_result($se));
            if ($p && $quantidade > $p['estoque']) {
                echo json_encode(['erro' => 'sem_estoque', 'max' => $p['estoque']]);
                exit;
            }
            $su = mysqli_prepare($conexao, "UPDATE carrinho SET quantidade = ? WHERE usuario_id = ? AND produto_id = ?");
            mysqli_stmt_bind_param($su, 'iii', $quantidade, $usuario_id, $produto_id);
            mysqli_stmt_execute($su);
        }
        echo json_encode(['ok' => true]);
        break;

    case 'remover':
        $produto_id = (int)($_POST['produto_id'] ?? 0);
        if (!$produto_id) { http_response_code(400); echo json_encode(['erro' => 'dados_invalidos']); exit; }
        $sd = mysqli_prepare($conexao, "DELETE FROM carrinho WHERE usuario_id = ? AND produto_id = ?");
        mysqli_stmt_bind_param($sd, 'ii', $usuario_id, $produto_id);
        mysqli_stmt_execute($sd);
        echo json_encode(['ok' => true]);
        break;

    case 'limpar':
        $sl = mysqli_prepare($conexao, "DELETE FROM carrinho WHERE usuario_id = ?");
        mysqli_stmt_bind_param($sl, 'i', $usuario_id);
        mysqli_stmt_execute($sl);
        echo json_encode(['ok' => true]);
        break;

    case 'contar':
        $sc = mysqli_prepare($conexao, "SELECT COALESCE(SUM(quantidade),0) as total FROM carrinho WHERE usuario_id = ?");
        mysqli_stmt_bind_param($sc, 'i', $usuario_id);
        mysqli_stmt_execute($sc);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($sc));
        echo json_encode(['total' => (int)$row['total']]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['erro' => 'acao_invalida']);
}
