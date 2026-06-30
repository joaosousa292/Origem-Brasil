<?php
header('Content-Type: application/json');
include("../conexao.php");

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'nao_logado']);
    exit;
}

$usuario_id = (int)$_SESSION['id'];
$action = $_POST['action'] ?? '';

if ($action !== 'finalizar') {
    http_response_code(400);
    echo json_encode(['erro' => 'acao_invalida']);
    exit;
}

// ── 1. Busca carrinho ──────────────────────────────
$stmt = mysqli_prepare($conexao,
    "SELECT c.produto_id, c.quantidade, p.nome, p.preco, p.estoque
     FROM carrinho c
     JOIN produtos p ON p.id = c.produto_id
     WHERE c.usuario_id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $usuario_id);
mysqli_stmt_execute($stmt);
$itens = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

if (empty($itens)) {
    echo json_encode(['erro' => 'carrinho_vazio']);
    exit;
}

// ── 2. Validação de estoque ───────────────────────
foreach ($itens as $item) {
    if ($item['quantidade'] > $item['estoque']) {
        echo json_encode([
            'erro' => 'sem_estoque',
            'produto' => $item['nome']
        ]);
        exit;
    }
}

// ── 3. Calcula totais ─────────────────────────────
$frete    = (float)($_POST['frete']   ?? 0);
$desconto = (float)($_POST['desconto'] ?? 0);
$pagamento = htmlspecialchars($_POST['pagamento'] ?? 'cartao');

$subtotal = array_sum(array_map(fn($i) => $i['preco'] * $i['quantidade'], $itens));
$total    = max(0, $subtotal + $frete - $desconto);

// ── 4. Cria o pedido (transação) ──────────────────
mysqli_begin_transaction($conexao);

try {
    // Insere pedido
    $sp = mysqli_prepare($conexao,
        "INSERT INTO pedidos (usuario_id, total, frete, desconto, status, pagamento)
         VALUES (?, ?, ?, ?, 'pendente', ?)"
    );
    mysqli_stmt_bind_param($sp, 'iddds', $usuario_id, $total, $frete, $desconto, $pagamento);
    mysqli_stmt_execute($sp);
    $pedido_id = mysqli_insert_id($conexao);

    // Insere itens e decrementa estoque
    foreach ($itens as $item) {
        $si = mysqli_prepare($conexao,
            "INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco)
             VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($si, 'iiid', $pedido_id, $item['produto_id'], $item['quantidade'], $item['preco']);
        mysqli_stmt_execute($si);

        $se = mysqli_prepare($conexao,
            "UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?"
        );
        mysqli_stmt_bind_param($se, 'iii', $item['quantidade'], $item['produto_id'], $item['quantidade']);
        mysqli_stmt_execute($se);

        if (mysqli_stmt_affected_rows($se) === 0) {
            throw new Exception("Estoque insuficiente para: " . $item['nome']);
        }
    }

    // Limpa carrinho
    $sc = mysqli_prepare($conexao, "DELETE FROM carrinho WHERE usuario_id = ?");
    mysqli_stmt_bind_param($sc, 'i', $usuario_id);
    mysqli_stmt_execute($sc);

    mysqli_commit($conexao);

    echo json_encode([
        'ok'        => true,
        'pedido_id' => $pedido_id,
        'numero'    => 'ORB-' . str_pad($pedido_id, 6, '0', STR_PAD_LEFT),
        'total'     => $total
    ]);

} catch (Exception $e) {
    mysqli_rollback($conexao);
    echo json_encode(['erro' => $e->getMessage()]);
}
