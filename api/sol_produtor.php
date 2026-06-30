<?php
include("../conexao.php");
ini_set('display_errors', '0');
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['erro'=>'Método inválido']); exit; }

// É preciso estar logado para se candidatar a produtor. A solicitação fica
// vinculada a esta conta — não se cria uma conta nova nem se pede senha aqui.
if (!isset($_SESSION['id'])) {
    echo json_encode(['erro'=>'Você precisa estar logado para se cadastrar como produtor.']); exit;
}
if (($_SESSION['tipo'] ?? '') === 'produtor') {
    echo json_encode(['erro'=>'Sua conta já é uma conta de produtor.']); exit;
}

// Confere se a migração mais recente já foi aplicada no banco
$chkMigracao = mysqli_query($conexao, "SHOW COLUMNS FROM solicitacoes_produtores LIKE 'usuario_id'");
if (!$chkMigracao || mysqli_num_rows($chkMigracao) === 0) {
    echo json_encode(['erro'=>'O banco de dados ainda não foi atualizado. Peça ao administrador para executar a migração mais recente.']); exit;
}

$usuario_id = (int)$_SESSION['id'];

// Já existe uma solicitação pendente desta conta?
$chkPend = mysqli_prepare($conexao, "SELECT id FROM solicitacoes_produtores WHERE usuario_id=? AND status='pendente'");
if (!$chkPend) { echo json_encode(['erro'=>'Erro de banco de dados: '.mysqli_error($conexao)]); exit; }
mysqli_stmt_bind_param($chkPend, 'i', $usuario_id);
mysqli_stmt_execute($chkPend);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($chkPend))) {
    echo json_encode(['erro'=>'Você já tem uma solicitação em análise.']); exit;
}

// Nome e e-mail vêm da própria conta logada — não do formulário — para
// garantir que, quando aprovado, o login de produtor seja exatamente esta
// mesma conta (mesmo e-mail e senha), e não uma conta nova.
$stmtU = mysqli_prepare($conexao, "SELECT nome, email FROM usuarios WHERE id=?");
mysqli_stmt_bind_param($stmtU, 'i', $usuario_id);
mysqli_stmt_execute($stmtU);
$usuarioLogado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtU));
if (!$usuarioLogado) { echo json_encode(['erro'=>'Sessão inválida. Faça login novamente.']); exit; }

$nome          = trim($_POST['nome'] ?? '') ?: $usuarioLogado['nome'];
$email         = $usuarioLogado['email'];
$telefone      = trim($_POST['telefone'] ?? '');
$cpf_cnpj      = trim($_POST['cpf_cnpj'] ?? '');
$fazenda       = trim($_POST['fazenda'] ?? '');
$estado        = trim($_POST['estado'] ?? '');
$regiao        = trim($_POST['regiao'] ?? '');
$municipio     = trim($_POST['municipio'] ?? '');
$especialidade = trim($_POST['especialidade'] ?? '');
$mensagem      = trim($_POST['mensagem'] ?? '');

if (!$nome || !$telefone || !$fazenda || !$estado || !$especialidade || !$mensagem) {
    echo json_encode(['erro'=>'Preencha todos os campos obrigatórios.']); exit;
}

// Upload da foto
$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
        echo json_encode(['erro'=>'Formato de imagem inválido. Use JPG ou PNG.']); exit;
    }
    if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
        echo json_encode(['erro'=>'Imagem muito grande. Máximo 5MB.']); exit;
    }
    $dir = '../uploads/solicitacoes_produtores/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $novo = $dir . uniqid('sol_prod_') . '.' . $ext;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $novo)) {
        $foto = ltrim($novo, '../');
    }
}

$s = mysqli_prepare($conexao,
    "INSERT INTO solicitacoes_produtores
     (usuario_id, nome, email, telefone, cpf_cnpj, fazenda, estado, regiao, municipio, especialidade, mensagem, foto, status, criado_em)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'pendente',NOW())"
);
if (!$s) { echo json_encode(['erro'=>'Erro de banco de dados: '.mysqli_error($conexao)]); exit; }
mysqli_stmt_bind_param($s,'isssssssssss',$usuario_id,$nome,$email,$telefone,$cpf_cnpj,$fazenda,$estado,$regiao,$municipio,$especialidade,$mensagem,$foto);

if (mysqli_stmt_execute($s)) {
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['erro'=>'Erro ao salvar: '.mysqli_error($conexao)]);
}
