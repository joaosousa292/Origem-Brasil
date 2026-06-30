<?php
$page_title  = 'Clientes';
$active_menu = 'clientes';
$msg = ''; $msg_type = 'ok';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

// POST — editar usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p_acao = $_POST['acao'] ?? '';
    if ($p_acao === 'editar') {
        $uid    = (int)$_POST['id'];
        $nome   = trim($_POST['nome'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $tel    = trim($_POST['telefone'] ?? '');
        $tipo   = in_array($_POST['tipo'],['cliente','admin']) ? $_POST['tipo'] : 'cliente';
        // Verificar se email já existe em outro user
        $chk = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT id FROM usuarios WHERE email='".mysqli_real_escape_string($conexao,$email)."' AND id!=$uid"));
        if ($chk) { $msg = 'E-mail já cadastrado em outro usuário.'; $msg_type = 'err'; }
        else {
            $s = mysqli_prepare($conexao,"UPDATE usuarios SET nome=?,email=?,telefone=?,tipo=? WHERE id=?");
            mysqli_stmt_bind_param($s,'ssssi',$nome,$email,$tel,$tipo,$uid);
            mysqli_stmt_execute($s);
            $msg = 'Usuário atualizado!';
        }
    }
    if ($p_acao === 'resetar_senha') {
        $uid  = (int)$_POST['id'];
        $nova = trim($_POST['nova_senha'] ?? '');
        if (strlen($nova) >= 6) {
            $hash = password_hash($nova, PASSWORD_DEFAULT);
            $s = mysqli_prepare($conexao,"UPDATE usuarios SET senha=? WHERE id=?");
            mysqli_stmt_bind_param($s,'si',$hash,$uid);
            mysqli_stmt_execute($s);
            $msg = 'Senha redefinida!';
        } else { $msg = 'Senha deve ter pelo menos 6 caracteres.'; $msg_type = 'err'; }
    }
    if ($p_acao === 'excluir') {
        $uid = (int)$_POST['id'];
        if ($uid !== (int)$_SESSION['id']) {
            mysqli_query($conexao,"DELETE FROM usuarios WHERE id=$uid");
            $msg = 'Usuário excluído.'; $msg_type = 'warn';
        } else { $msg = 'Você não pode excluir sua própria conta.'; $msg_type = 'err'; }
    }
}

$busca = trim($_GET['q'] ?? '');
$tipo  = $_GET['tipo'] ?? '';
$where = "WHERE u.tipo='cliente'";
if ($busca) $where .= " AND (u.nome LIKE '%".mysqli_real_escape_string($conexao,$busca)."%' OR u.email LIKE '%".mysqli_real_escape_string($conexao,$busca)."%')";

$clientes = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT u.*, COUNT(DISTINCT p.id) AS total_pedidos,
     COALESCE(SUM(p.total),0) AS total_gasto,
     MAX(p.data_pedido) AS ultimo_pedido
     FROM usuarios u
     LEFT JOIN pedidos p ON p.usuario_id=u.id
     $where GROUP BY u.id ORDER BY u.id DESC"
), MYSQLI_ASSOC);

// Edição
$user_edit = null;
if (isset($_GET['editar'])) {
    $eid = (int)$_GET['editar'];
    $user_edit = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM usuarios WHERE id=$eid"));
}

include('layout.php');
?>

<?php if ($user_edit): ?>
<div style="margin-bottom:16px;">
    <a href="clientes.php" class="btn btn-gray">← Voltar</a>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <!-- Dados -->
    <div class="card" style="padding:24px;">
        <h3 style="margin-bottom:20px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg> Editar Usuário #<?php echo $user_edit['id']; ?></h3>
        <form method="POST">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" value="<?php echo $user_edit['id']; ?>">
            <div class="form-grid" style="gap:16px;margin-bottom:20px;">
                <div class="field form-full"><label>Nome completo</label><input type="text" name="nome" value="<?php echo htmlspecialchars($user_edit['nome']); ?>" required></div>
                <div class="field form-full"><label>E-mail</label><input type="email" name="email" value="<?php echo htmlspecialchars($user_edit['email']); ?>" required></div>
                <div class="field"><label>Telefone</label><input type="text" name="telefone" value="<?php echo htmlspecialchars($user_edit['telefone']??''); ?>" placeholder="(47) 99999-9999"></div>
                <div class="field"><label>Tipo de conta</label>
                    <select name="tipo">
                        <option value="cliente" <?php echo $user_edit['tipo']==='cliente'?'selected':''; ?>>Cliente</option>
                        <option value="admin"   <?php echo $user_edit['tipo']==='admin'?'selected':''; ?>>Administrador</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-green"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Salvar Alterações</button>
        </form>
    </div>
    <!-- Senha -->
    <div class="card" style="padding:24px;">
        <h3 style="margin-bottom:20px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Redefinir Senha</h3>
        <form method="POST">
            <input type="hidden" name="acao" value="resetar_senha">
            <input type="hidden" name="id" value="<?php echo $user_edit['id']; ?>">
            <div class="field" style="margin-bottom:16px;"><label>Nova senha</label><input type="password" name="nova_senha" placeholder="Mínimo 6 caracteres" required></div>
            <button type="submit" class="btn btn-orange"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg> Redefinir Senha</button>
        </form>
        <hr style="margin:20px 0;border:none;border-top:1px solid var(--border);">
        <h4 style="margin-bottom:12px;font-size:13px;color:var(--text-3);">Zona de Perigo</h4>
        <form method="POST" onsubmit="return confirm('Excluir este usuário permanentemente?')">
            <input type="hidden" name="acao" value="excluir">
            <input type="hidden" name="id" value="<?php echo $user_edit['id']; ?>">
            <button type="submit" class="btn btn-red"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg> Excluir Usuário</button>
        </form>
    </div>
</div>

<?php else: ?>
<form method="GET" style="display:flex;gap:10px;margin-bottom:20px;">
    <input type="text" name="q" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Buscar por nome ou e-mail…" style="padding:8px 14px;border:1px solid var(--border);border-radius:9px;font-size:13px;outline:none;background:var(--card);min-width:260px;">
    <button class="btn btn-gray">Buscar</button>
</form>

<div class="card">
    <div class="card-header">
        <h3>Clientes <span style="color:var(--text-3);font-weight:400;">(<?php echo count($clientes); ?>)</span></h3>
    </div>
    <table class="adm-table">
        <thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Pedidos</th><th>Total Gasto</th><th>Último Pedido</th><th>Cadastro</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($clientes as $u): ?>
        <tr>
            <td><span style="font-size:11px;color:var(--text-3);">#<?php echo $u['id']; ?></span></td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="avatar-circle" style="width:28px;height:28px;font-size:11px;"><?php echo mb_strtoupper(mb_substr($u['nome'],0,1)); ?></div>
                    <span style="font-weight:500;"><?php echo htmlspecialchars($u['nome']); ?></span>
                </div>
            </td>
            <td style="font-size:12px;"><?php echo htmlspecialchars($u['email']); ?></td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo htmlspecialchars($u['telefone']??'–'); ?></td>
            <td style="font-weight:600;"><?php echo $u['total_pedidos']; ?></td>
            <td style="font-weight:600;color:var(--green);">R$ <?php echo number_format($u['total_gasto'],2,',','.'); ?></td>
            <td style="font-size:12px;color:var(--text-3);"><?php echo $u['ultimo_pedido'] ? date('d/m/Y', strtotime($u['ultimo_pedido'])) : '–'; ?></td>
            <td style="font-size:11px;color:var(--text-3);"><?php echo date('d/m/Y', strtotime($u['criado_em'])); ?></td>
            <td><a href="clientes.php?editar=<?php echo $u['id']; ?>" class="btn btn-gray" style="padding:5px 10px;font-size:11px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg> Editar</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (empty($clientes)): ?><div class="empty-state"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><p>Nenhum cliente encontrado</p></div><?php endif; ?>
</div>
<?php endif; ?>

</div></main></body></html>
