<?php
$page_title  = 'Configurações';
$active_menu = 'configuracoes';
$msg = ''; $msg_type = 'ok';
include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pa = $_POST['acao'] ?? '';
    if ($pa === 'minha_conta') {
        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $uid   = (int)$_SESSION['id'];
        $chk = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT id FROM usuarios WHERE email='".mysqli_real_escape_string($conexao,$email)."' AND id!=$uid"));
        if ($chk) { $msg = 'E-mail já em uso.'; $msg_type = 'err'; }
        else {
            $s = mysqli_prepare($conexao,"UPDATE usuarios SET nome=?,email=? WHERE id=?");
            mysqli_stmt_bind_param($s,'ssi',$nome,$email,$uid);
            mysqli_stmt_execute($s);
            $_SESSION['nome'] = $nome;
            $msg = 'Dados atualizados!';
        }
    }
    if ($pa === 'senha') {
        $uid   = (int)$_SESSION['id'];
        $atual = $_POST['senha_atual'] ?? '';
        $nova  = $_POST['nova_senha'] ?? '';
        $conf  = $_POST['confirmar'] ?? '';
        $user = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT senha FROM usuarios WHERE id=$uid"));
        if (!password_verify($atual, $user['senha'])) { $msg = 'Senha atual incorreta.'; $msg_type = 'err'; }
        elseif ($nova !== $conf) { $msg = 'As senhas não conferem.'; $msg_type = 'err'; }
        elseif (strlen($nova) < 6) { $msg = 'Senha deve ter pelo menos 6 caracteres.'; $msg_type = 'err'; }
        else {
            $hash = password_hash($nova, PASSWORD_DEFAULT);
            $s = mysqli_prepare($conexao,"UPDATE usuarios SET senha=? WHERE id=?");
            mysqli_stmt_bind_param($s,'si',$hash,$uid);
            mysqli_stmt_execute($s);
            $msg = 'Senha alterada com sucesso!';
        }
    }
}
$me = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM usuarios WHERE id=".(int)$_SESSION['id']));
include('layout.php');
?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:900px;">
    <div class="card" style="padding:24px;">
        <h3 style="margin-bottom:20px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Minha Conta</h3>
        <form method="POST">
            <input type="hidden" name="acao" value="minha_conta">
            <div class="field" style="margin-bottom:14px;"><label>Nome</label><input type="text" name="nome" value="<?php echo htmlspecialchars($me['nome']); ?>" required></div>
            <div class="field" style="margin-bottom:20px;"><label>E-mail</label><input type="email" name="email" value="<?php echo htmlspecialchars($me['email']); ?>" required></div>
            <button type="submit" class="btn btn-green"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Salvar</button>
        </form>
    </div>
    <div class="card" style="padding:24px;">
        <h3 style="margin-bottom:20px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Alterar Senha</h3>
        <form method="POST">
            <input type="hidden" name="acao" value="senha">
            <div class="field" style="margin-bottom:14px;"><label>Senha atual</label><input type="password" name="senha_atual" required></div>
            <div class="field" style="margin-bottom:14px;"><label>Nova senha</label><input type="password" name="nova_senha" required></div>
            <div class="field" style="margin-bottom:20px;"><label>Confirmar nova senha</label><input type="password" name="confirmar" required></div>
            <button type="submit" class="btn btn-green"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Alterar Senha</button>
        </form>
    </div>
</div>
</div></main></body></html>
