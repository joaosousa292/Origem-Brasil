<?php
$page_title  = 'Solicitações — Novos Produtores';
$active_menu = 'sol_produtores';
$msg = ''; $msg_type = 'ok';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pa  = $_POST['acao'] ?? '';
    $sid = (int)$_POST['sol_id'];

    if ($pa === 'aprovar') {
        $sol = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM solicitacoes_produtores WHERE id=$sid"));
        if ($sol) {
            if (empty($sol['usuario_id'])) {
                // Solicitação antiga, enviada antes do produtor precisar estar logado.
                // Não tem conta vinculada — não dá pra aprovar automaticamente.
                $msg = 'Não foi possível aprovar: esta solicitação foi enviada antes da atualização do sistema e não está vinculada a nenhuma conta de login. Rejeite e peça para a pessoa enviar de novo, já logada com a conta dela.';
                $msg_type = 'warn';
            } else {
                $usuarioConta = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT id,tipo,email FROM usuarios WHERE id=" . (int)$sol['usuario_id']));
                if (!$usuarioConta) {
                    $msg = 'Não foi possível aprovar: a conta de usuário vinculada a esta solicitação não existe mais.';
                    $msg_type = 'warn';
                } else {
                    // Faz o "upgrade": a conta que já existia (cliente) passa a ser produtor.
                    // Mesmo e-mail, mesma senha — não se cria uma conta nova.
                    mysqli_query($conexao,"UPDATE usuarios SET tipo='produtor' WHERE id=" . (int)$sol['usuario_id']);

                    // Evita duplicar o perfil de produtor caso essa conta já tenha um
                    $jaTemPerfil = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT id FROM produtores WHERE usuario_id=" . (int)$sol['usuario_id']));
                    if ($jaTemPerfil) {
                        $novo_id = $jaTemPerfil['id'];
                    } else {
                        $s = mysqli_prepare($conexao,"INSERT INTO produtores (usuario_id,nome,historia,fazenda,regiao,estado,foto) VALUES (?,?,?,?,?,?,?)");
                        mysqli_stmt_bind_param($s,'issssss',$sol['usuario_id'],$sol['nome'],$sol['historia'],$sol['fazenda'],$sol['regiao'],$sol['estado'],$sol['foto']);
                        mysqli_stmt_execute($s);
                        $novo_id = mysqli_insert_id($conexao);
                    }
                    mysqli_query($conexao,"UPDATE solicitacoes_produtores SET status='aprovado', produtor_id=$novo_id, analisado_em=NOW() WHERE id=$sid");
                    $msg = "Produtor aprovado! A conta {$usuarioConta['email']} agora tem acesso de produtor — mesmo login de antes. (ID #$novo_id)";
                }
            }
        }
    }
    if ($pa === 'rejeitar') {
        $motivo = trim($_POST['motivo'] ?? '');
        $s = mysqli_prepare($conexao,"UPDATE solicitacoes_produtores SET status='rejeitado', motivo_rejeicao=?, analisado_em=NOW() WHERE id=?");
        mysqli_stmt_bind_param($s,'si',$motivo,$sid);
        mysqli_stmt_execute($s);
        $msg = 'Solicitação rejeitada.'; $msg_type = 'warn';
    }
}

$aba = $_GET['aba'] ?? 'pendente';
$lista = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT * FROM solicitacoes_produtores WHERE status='$aba' ORDER BY criado_em DESC"
), MYSQLI_ASSOC);

$n_pend = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtores WHERE status='pendente'"))['n'];
$n_aprov= (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtores WHERE status='aprovado'"))['n'];
$n_rej  = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtores WHERE status='rejeitado'"))['n'];

include('layout.php');
?>
<div class="tabs">
    <a href="sol_produtores.php?aba=pendente"  class="tab-btn <?php echo $aba==='pendente'?'active':''; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Pendentes (<?php echo $n_pend; ?>)</a>
    <a href="sol_produtores.php?aba=aprovado"  class="tab-btn <?php echo $aba==='aprovado'?'active':''; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg> Aprovados (<?php echo $n_aprov; ?>)</a>
    <a href="sol_produtores.php?aba=rejeitado" class="tab-btn <?php echo $aba==='rejeitado'?'active':''; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Rejeitados (<?php echo $n_rej; ?>)</a>
</div>

<?php if (empty($lista)): ?>
<div class="empty-state"><span class="icon"><?php echo $aba==='pendente'?'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>':'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>'; ?></span>
<p><?php echo $aba==='pendente'?'Nenhuma solicitação pendente.':'Nenhum registro aqui.'; ?></p></div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:16px;">
<?php foreach ($lista as $sol): ?>
<div class="card" style="overflow:visible;">
    <div style="display:grid;grid-template-columns:100px 1fr auto;gap:20px;padding:20px;align-items:start;">
        <!-- Foto -->
        <div>
            <?php if ($sol['foto']): ?>
            <img src="../<?php echo htmlspecialchars($sol['foto']); ?>" style="width:90px;height:90px;object-fit:cover;border-radius:12px;border:1px solid var(--border);" onerror="this.src='../imagens/cafe1.jpg'">
            <?php else: ?>
            <div style="width:90px;height:90px;border-radius:12px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:32px;border:1px solid var(--border);"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-dasharray="2 2"/></svg></div>
            <?php endif; ?>
        </div>
        <!-- Info -->
        <div>
            <div style="font-size:16px;font-weight:700;margin-bottom:4px;"><?php echo htmlspecialchars($sol['nome']); ?></div>
            <?php if (empty($sol['usuario_id']) && $aba === 'pendente'): ?>
            <div style="font-size:11px;color:#92400e;background:#fef3c7;padding:6px 10px;border-radius:8px;margin-bottom:10px;display:inline-flex;align-items:center;gap:6px;">
                <svg style="width:1em;height:1em;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Cadastro antigo, sem conta de login vinculada — não pode ser aprovado automaticamente
            </div>
            <?php endif; ?>
            <div style="font-size:12px;color:var(--text-3);margin-bottom:10px;">
                <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> <?php echo htmlspecialchars($sol['email']??''); ?> &nbsp;·&nbsp; <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg> <?php echo htmlspecialchars($sol['telefone']??''); ?>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                <span class="badge" style="background:#f0f7f1;color:var(--green);"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> <?php echo htmlspecialchars($sol['fazenda']??''); ?></span>
                <span class="badge" style="background:#eff6ff;color:#1e40af;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <?php echo htmlspecialchars($sol['regiao']??''); ?>, <?php echo htmlspecialchars($sol['estado']??''); ?></span>
                <span class="badge" style="background:#fef3c7;color:#92400e;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 10 10"/><path d="M8 5.07A10 10 0 0 1 22 12h-3"/><path d="M12 12a5 5 0 0 0 5-5"/><path d="M12 12a5 5 0 0 1-5-5"/></svg> <?php echo htmlspecialchars($sol['especialidade']??''); ?></span>
            </div>
            <div style="font-size:12px;color:var(--text-2);line-height:1.7;background:var(--bg);padding:10px 14px;border-radius:8px;">
                <strong>Mensagem:</strong><br>
                <?php echo nl2br(htmlspecialchars($sol['mensagem']??$sol['historia']??'–')); ?>
            </div>
            <?php if ($sol['analisado_em']): ?>
            <div style="font-size:11px;color:var(--text-3);margin-top:8px;">Analisado em: <?php echo date('d/m/Y H:i', strtotime($sol['analisado_em'])); ?></div>
            <?php endif; ?>
            <?php if ($sol['motivo_rejeicao']): ?>
            <div style="font-size:12px;color:#991b1b;margin-top:8px;background:#fef2f2;padding:8px 12px;border-radius:8px;">Motivo: <?php echo htmlspecialchars($sol['motivo_rejeicao']); ?></div>
            <?php endif; ?>
        </div>
        <!-- Ações -->
        <?php if ($aba === 'pendente'): ?>
        <div style="display:flex;flex-direction:column;gap:8px;min-width:160px;">
            <div style="font-size:11px;color:var(--text-3);margin-bottom:4px;">Recebido em<br><?php echo date('d/m/Y H:i', strtotime($sol['criado_em'])); ?></div>
            <form method="POST">
                <input type="hidden" name="acao" value="aprovar">
                <input type="hidden" name="sol_id" value="<?php echo $sol['id']; ?>">
                <button class="btn btn-green" style="width:100%;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg> Aprovar Produtor</button>
            </form>
            <button class="btn btn-red" style="width:100%;" onclick="document.getElementById('rej-<?php echo $sol['id']; ?>').style.display='block'"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Rejeitar</button>
            <div id="rej-<?php echo $sol['id']; ?>" style="display:none;margin-top:6px;">
                <form method="POST">
                    <input type="hidden" name="acao" value="rejeitar">
                    <input type="hidden" name="sol_id" value="<?php echo $sol['id']; ?>">
                    <textarea name="motivo" placeholder="Motivo da rejeição (opcional)…" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:8px;font-family:'Inter',sans-serif;font-size:12px;resize:none;min-height:60px;outline:none;" required></textarea>
                    <button type="submit" class="btn btn-red" style="width:100%;margin-top:6px;">Confirmar Rejeição</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div>
            <span class="badge badge-<?php echo $sol['status']; ?>"><?php echo ucfirst($sol['status']); ?></span>
            <?php if ($sol['status']==='aprovado' && $sol['produtor_id']): ?>
            <div style="margin-top:10px;"><a href="produtores.php?acao=form&id=<?php echo $sol['produtor_id']; ?>" class="btn btn-blue" style="font-size:11px;">Ver Produtor</a></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div></main></body></html>
