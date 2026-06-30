<?php
$page_title  = 'Administradores';
$active_menu = 'admins';
$msg = ''; $msg_type = 'ok';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pa = $_POST['acao'] ?? '';
    if ($pa === 'criar') {
        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        if (strlen($senha) < 6) { $msg = 'Senha deve ter pelo menos 6 caracteres.'; $msg_type = 'err'; }
        else {
            $chk = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT id FROM usuarios WHERE email='".mysqli_real_escape_string($conexao,$email)."'"));
            if ($chk) { $msg = 'E-mail já cadastrado.'; $msg_type = 'err'; }
            else {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $s = mysqli_prepare($conexao,"INSERT INTO usuarios (nome,email,senha,tipo) VALUES (?,?,?,'admin')");
                mysqli_stmt_bind_param($s,'sss',$nome,$email,$hash);
                mysqli_stmt_execute($s);
                $msg = 'Administrador criado!';
            }
        }
    }
    if ($pa === 'revogar') {
        $uid = (int)$_POST['id'];
        if ($uid !== (int)$_SESSION['id']) {
            mysqli_query($conexao,"UPDATE usuarios SET tipo='cliente' WHERE id=$uid");
            $msg = 'Acesso admin revogado. Usuário tornou-se cliente.'; $msg_type = 'warn';
        } else { $msg = 'Você não pode revogar seu próprio acesso.'; $msg_type = 'err'; }
    }
}

$admins = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT * FROM usuarios WHERE tipo='admin' ORDER BY id ASC"
), MYSQLI_ASSOC);

include('layout.php');
?>
<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;">
    <div class="card">
        <div class="card-header"><h3>Administradores (<?php echo count($admins); ?>)</h3></div>
        <table class="adm-table">
            <thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Cadastro</th><th>Ação</th></tr></thead>
            <tbody>
            <?php foreach ($admins as $a): ?>
            <tr>
                <td style="font-size:11px;color:var(--text-3);">#<?php echo $a['id']; ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="avatar-circle" style="width:28px;height:28px;font-size:11px;"><?php echo mb_strtoupper(mb_substr($a['nome'],0,1)); ?></div>
                        <div>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($a['nome']); ?></div>
                            <?php if ($a['id']==(int)$_SESSION['id']): ?><div style="font-size:10px;color:var(--green);">← você</div><?php endif; ?>
                        </div>
                    </div>
                </td>
                <td style="font-size:12px;"><?php echo htmlspecialchars($a['email']); ?></td>
                <td style="font-size:11px;color:var(--text-3);"><?php echo date('d/m/Y', strtotime($a['criado_em'])); ?></td>
                <td>
                    <?php if ($a['id'] !== (int)$_SESSION['id']): ?>
                    <form method="POST" onsubmit="return confirm('Revogar acesso admin de <?php echo addslashes($a['nome']); ?>?')">
                        <input type="hidden" name="acao" value="revogar">
                        <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                        <button class="btn btn-red" style="padding:5px 12px;font-size:11px;">Revogar</button>
                    </form>
                    <?php else: ?><span style="font-size:11px;color:var(--text-3);">conta atual</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding:24px;align-self:start;">
        <h3 style="margin-bottom:20px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Novo Administrador</h3>
        <form method="POST">
            <input type="hidden" name="acao" value="criar">
            <div class="field" style="margin-bottom:14px;"><label>Nome completo</label><input type="text" name="nome" required></div>
            <div class="field" style="margin-bottom:14px;"><label>E-mail</label><input type="email" name="email" required></div>
            <div class="field" style="margin-bottom:20px;"><label>Senha inicial</label><input type="password" name="senha" required><span class="hint">Mínimo 6 caracteres</span></div>
            <button type="submit" class="btn btn-green btn-lg" style="width:100%;">Criar Admin</button>
        </form>
    </div>
</div>
</div></main></body></html>
