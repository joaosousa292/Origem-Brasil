<?php
$page_title  = 'Produtores';
$active_menu = 'produtores';
$msg = ''; $msg_type = 'ok';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pa = $_POST['acao'] ?? '';
    if ($pa === 'salvar') {
        $pid  = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $hist = trim($_POST['historia'] ?? '');
        $faz  = trim($_POST['fazenda'] ?? '');
        $reg  = trim($_POST['regiao'] ?? '');
        $est  = trim($_POST['estado'] ?? '');
        $lat  = $_POST['latitude'] ? (float)$_POST['latitude'] : null;
        $lng  = $_POST['longitude'] ? (float)$_POST['longitude'] : null;
        $foto = trim($_POST['foto_atual'] ?? '');

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['jpg','jpeg','png','webp'])) {
                $dir = '../uploads/produtores/';
                if (!is_dir($dir)) mkdir($dir,0775,true);
                $novo = $dir . uniqid('prod_') . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $novo)) $foto = ltrim($novo,'../');
            }
        }

        if ($pid) {
            $s = mysqli_prepare($conexao,"UPDATE produtores SET nome=?,historia=?,fazenda=?,regiao=?,estado=?,latitude=?,longitude=?,foto=? WHERE id=?");
            mysqli_stmt_bind_param($s,'sssssddsi',$nome,$hist,$faz,$reg,$est,$lat,$lng,$foto,$pid);
            $msg = 'Produtor atualizado!';
        } else {
            $s = mysqli_prepare($conexao,"INSERT INTO produtores (nome,historia,fazenda,regiao,estado,latitude,longitude,foto) VALUES (?,?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($s,'sssssdds',$nome,$hist,$faz,$reg,$est,$lat,$lng,$foto);
            $msg = 'Produtor cadastrado!';
        }
        mysqli_stmt_execute($s);
    }
    if ($pa === 'excluir') {
        $pid = (int)$_POST['id'];
        mysqli_query($conexao,"DELETE FROM produtores WHERE id=$pid");
        $msg = 'Produtor excluído.'; $msg_type = 'warn';
    }
}

$acao = $_GET['acao'] ?? 'lista';
$prod_edit = [];
if ($acao === 'form' && isset($_GET['id'])) {
    $eid = (int)$_GET['id'];
    $prod_edit = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM produtores WHERE id=$eid")) ?? [];
}

$lista = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT pr.*, COUNT(p.id) AS total_produtos FROM produtores pr
     LEFT JOIN produtos p ON p.produtor_id=pr.id GROUP BY pr.id ORDER BY pr.id DESC"
), MYSQLI_ASSOC);

$topbar_action = ($acao==='lista')
    ? '<a href="produtores.php?acao=form" class="topbar-btn primary">+ Novo Produtor</a>'
    : '<a href="produtores.php" class="topbar-btn">← Voltar</a>';
include('layout.php');
?>

<?php if ($acao === 'lista'): ?>
<div class="card">
    <div class="card-header"><h3>Produtores <span style="color:var(--text-3);font-weight:400;">(<?php echo count($lista); ?>)</span></h3></div>
    <table class="adm-table">
        <thead><tr><th>Foto</th><th>Nome</th><th>Fazenda</th><th>Região</th><th>Estado</th><th>Produtos</th><th>Cadastro</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach ($lista as $p): ?>
        <tr>
            <td>
                <?php if ($p['foto']): ?>
                <img src="../<?php echo htmlspecialchars($p['foto']); ?>" class="img-thumb" style="border-radius:50%;" onerror="this.style.display='none'">
                <?php else: ?>
                <div class="avatar-circle"><?php echo mb_strtoupper(mb_substr($p['nome'],0,1)); ?></div>
                <?php endif; ?>
            </td>
            <td><strong><?php echo htmlspecialchars($p['nome']); ?></strong></td>
            <td><?php echo htmlspecialchars($p['fazenda']??'–'); ?></td>
            <td><?php echo htmlspecialchars($p['regiao']??'–'); ?></td>
            <td><?php echo htmlspecialchars($p['estado']??'–'); ?></td>
            <td><span class="badge" style="background:#f0f7f1;color:var(--green);"><?php echo $p['total_produtos']; ?> prod.</span></td>
            <td style="font-size:11px;color:var(--text-3);"><?php echo date('d/m/Y', strtotime($p['criado_em'])); ?></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="../produtor.php?id=<?php echo $p['id']; ?>" target="_blank" class="btn btn-blue" style="padding:5px 10px;font-size:11px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                    <a href="produtores.php?acao=form&id=<?php echo $p['id']; ?>" class="btn btn-gray" style="padding:5px 10px;font-size:11px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg></a>
                    <form method="POST" onsubmit="return confirm('Excluir produtor?')" style="display:inline;">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button class="btn btn-red" style="padding:5px 10px;font-size:11px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php elseif ($acao === 'form'):
$pe = $prod_edit; $editing = !empty($pe); ?>
<div class="card" style="padding:28px;">
    <h3 style="margin-bottom:24px;"><?php echo $editing ? '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg> Editar Produtor' : '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Novo Produtor'; ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="salvar">
        <?php if ($editing): ?><input type="hidden" name="id" value="<?php echo $pe['id']; ?>"><?php endif; ?>
        <input type="hidden" name="foto_atual" value="<?php echo htmlspecialchars($pe['foto']??''); ?>">
        <div class="form-grid" style="gap:18px;margin-bottom:20px;">
            <div class="field form-full"><label>Nome do Produtor *</label><input type="text" name="nome" value="<?php echo htmlspecialchars($pe['nome']??''); ?>" required></div>
            <div class="field"><label>Fazenda / Propriedade</label><input type="text" name="fazenda" value="<?php echo htmlspecialchars($pe['fazenda']??''); ?>"></div>
            <div class="field"><label>Região</label><input type="text" name="regiao" value="<?php echo htmlspecialchars($pe['regiao']??''); ?>" placeholder="Cerrado Mineiro"></div>
            <div class="field"><label>Estado (UF)</label><input type="text" name="estado" value="<?php echo htmlspecialchars($pe['estado']??''); ?>" placeholder="MG" maxlength="50"></div>
            <div class="field"><label>Latitude GPS</label><input type="number" name="latitude" step="any" value="<?php echo $pe['latitude']??''; ?>" placeholder="-18.5122"></div>
            <div class="field"><label>Longitude GPS</label><input type="number" name="longitude" step="any" value="<?php echo $pe['longitude']??''; ?>" placeholder="-46.5130"></div>
            <div class="field form-full"><label>História / Bio</label><textarea name="historia" style="min-height:120px;"><?php echo htmlspecialchars($pe['historia']??''); ?></textarea></div>
            <div class="field form-full">
                <label>Foto do Produtor</label>
                <?php if ($editing && !empty($pe['foto'])): ?>
                <img src="../<?php echo htmlspecialchars($pe['foto']); ?>" style="height:80px;width:80px;border-radius:50%;margin-bottom:8px;display:block;object-fit:cover;">
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*">
                <span class="hint">JPG, PNG ou WebP. Deixe vazio para manter a foto atual.</span>
            </div>
        </div>
        <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-green btn-lg"><?php echo $editing ? '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Salvar' : '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Cadastrar'; ?></button>
            <a href="produtores.php" class="btn btn-gray btn-lg">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

</div></main></body></html>
