<?php
include("../conexao.php");
// Garante que nenhum aviso/notice do PHP seja impresso e corrompa a resposta JSON
ini_set('display_errors', '0');
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['erro'=>'Método inválido']); exit; }

// Apenas produtores logados podem solicitar a publicação de um produto
if (!isset($_SESSION['id']) || ($_SESSION['tipo'] ?? '') !== 'produtor') {
    echo json_encode(['erro'=>'Acesso restrito a produtores. Faça login com uma conta de produtor.']); exit;
}

// Confere se a migração (migracao_produtor.sql) já foi executada no banco.
// Sem ela faltam a coluna produtores.usuario_id e a tabela categorias,
// e qualquer consulta abaixo falharia silenciosamente.
$chkMigracao = mysqli_query($conexao, "SHOW COLUMNS FROM produtores LIKE 'usuario_id'");
if (!$chkMigracao || mysqli_num_rows($chkMigracao) === 0) {
    echo json_encode(['erro'=>'O banco de dados ainda não foi atualizado. Peça ao administrador para executar o arquivo migracao_produtor.sql.']); exit;
}

// O produtor_id vem da sessão (e não do formulário), para evitar que alguém
// publique uma solicitação em nome de outro produtor.
$stmtPr = mysqli_prepare($conexao, "SELECT id FROM produtores WHERE usuario_id=?");
if (!$stmtPr) { echo json_encode(['erro'=>'Erro de banco de dados: '.mysqli_error($conexao)]); exit; }
mysqli_stmt_bind_param($stmtPr, 'i', $_SESSION['id']);
mysqli_stmt_execute($stmtPr);
$produtorLogado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtPr));
if (!$produtorLogado) {
    echo json_encode(['erro'=>'Seu cadastro de produtor não está vinculado a um perfil. Contate o suporte.']); exit;
}
$produtor_id = (int)$produtorLogado['id'];

$nome          = trim($_POST['nome'] ?? '');
$descricao     = trim($_POST['descricao'] ?? '');
$historia      = trim($_POST['historia'] ?? '');
$preco         = (float)str_replace(',','.',($_POST['preco'] ?? 0));
$categoria     = trim($_POST['categoria'] ?? '');
$nova_categoria_nome = trim($_POST['nova_categoria_nome'] ?? '');
$regiao        = trim($_POST['regiao'] ?? '');
$origem        = trim($_POST['origem'] ?? '');
$peso          = trim($_POST['peso'] ?? '');
$estoque_ini   = (int)($_POST['estoque_inicial'] ?? 0);

if (!$nome || !$descricao || !$preco || !$categoria) {
    echo json_encode(['erro'=>'Preencha todos os campos obrigatórios.']); exit;
}

if ($categoria === 'outra') {
    if (!$nova_categoria_nome) {
        echo json_encode(['erro'=>'Digite o nome da nova categoria que você está sugerindo.']); exit;
    }
} else {
    // Garante que a categoria escolhida realmente existe no site
    $chkCat = mysqli_prepare($conexao, "SELECT slug FROM categorias WHERE slug=?");
    if (!$chkCat) { echo json_encode(['erro'=>'Erro de banco de dados: '.mysqli_error($conexao)]); exit; }
    mysqli_stmt_bind_param($chkCat, 's', $categoria);
    mysqli_stmt_execute($chkCat);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($chkCat))) {
        echo json_encode(['erro'=>'Categoria inválida.']); exit;
    }
    $nova_categoria_nome = null;
}

$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext,['jpg','jpeg','png','webp'])) { echo json_encode(['erro'=>'Imagem inválida.']); exit; }
    if ($_FILES['foto']['size'] > 8*1024*1024) { echo json_encode(['erro'=>'Imagem muito grande. Máx 8MB.']); exit; }
    $dir = '../uploads/solicitacoes_produtos/';
    if (!is_dir($dir)) mkdir($dir,0775,true);
    $novo = $dir . uniqid('sol_prd_') . '.' . $ext;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $novo)) $foto = ltrim($novo,'../');
} else {
    echo json_encode(['erro'=>'Envie uma foto do produto.']); exit;
}

$s = mysqli_prepare($conexao,
    "INSERT INTO solicitacoes_produtos
     (produtor_id,nome,descricao,historia,preco,categoria,nova_categoria_nome,foto,regiao,origem,peso,estoque_inicial,status,criado_em)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'pendente',NOW())"
);
if (!$s) { echo json_encode(['erro'=>'Erro de banco de dados: '.mysqli_error($conexao)]); exit; }
mysqli_stmt_bind_param($s,'isssdssssssi',$produtor_id,$nome,$descricao,$historia,$preco,$categoria,$nova_categoria_nome,$foto,$regiao,$origem,$peso,$estoque_ini);

if (mysqli_stmt_execute($s)) {
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['erro'=>'Erro ao salvar: '.mysqli_error($conexao)]);
}
