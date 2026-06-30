<?php
$page_title  = 'Solicitações — Novos Produtos';
$active_menu = 'sol_produtos';
$msg = ''; $msg_type = 'ok';

include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pa  = $_POST['acao'] ?? '';
    $sid = (int)$_POST['sol_id'];

    if ($pa === 'aprovar') {
        $sol = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM solicitacoes_produtos WHERE id=$sid"));
        if ($sol) {
            $categoria_final = $sol['categoria'];

            // Se o produtor sugeriu uma categoria nova, cria (ou reaproveita) o slug
            // na tabela "categorias" e usa esse slug no produto publicado.
            if ($sol['categoria'] === 'outra' && !empty($sol['nova_categoria_nome'])) {
                $nomeCat = trim($sol['nova_categoria_nome']);
                $slug = strtolower($nomeCat);
                $slug = preg_replace('/[áàâãä]/u','a',$slug);
                $slug = preg_replace('/[éèêë]/u','e',$slug);
                $slug = preg_replace('/[íìîï]/u','i',$slug);
                $slug = preg_replace('/[óòôõö]/u','o',$slug);
                $slug = preg_replace('/[úùûü]/u','u',$slug);
                $slug = preg_replace('/[ç]/u','c',$slug);
                $slug = preg_replace('/[^a-z0-9]+/','_',$slug);
                $slug = trim($slug,'_');
                if ($slug === '') $slug = 'categoria_' . time();

                $existe = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT slug FROM categorias WHERE slug='" . mysqli_real_escape_string($conexao,$slug) . "'"));
                if (!$existe) {
                    $insCat = mysqli_prepare($conexao,"INSERT INTO categorias (slug,label) VALUES (?,?)");
                    mysqli_stmt_bind_param($insCat,'ss',$slug,$nomeCat);
                    mysqli_stmt_execute($insCat);
                }
                $categoria_final = $slug;
            }

            $s = mysqli_prepare($conexao,"INSERT INTO produtos (nome,descricao,historia,preco,categoria,imagem,estoque,produtor_id,regiao,origem,peso,destaque) VALUES (?,?,?,?,?,?,?,?,?,?,?,0)");
            $estoque_inicial_int = intval($sol['estoque_inicial']);
            mysqli_stmt_bind_param($s,'sssdssiisss',$sol['nome'],$sol['descricao'],$sol['historia'],$sol['preco'],$categoria_final,$sol['foto'],$estoque_inicial_int,$sol['produtor_id'],$sol['regiao'],$sol['origem'],$sol['peso']);
            mysqli_stmt_execute($s);
            $novo_id = mysqli_insert_id($conexao);
            mysqli_query($conexao,"UPDATE solicitacoes_produtos SET status='aprovado', produto_id=$novo_id, analisado_em=NOW() WHERE id=$sid");
            $msg = "Produto aprovado e publicado! (ID #$novo_id)";
            if ($categoria_final !== $sol['categoria']) {
                $msg .= " Nova categoria \"" . htmlspecialchars($sol['nova_categoria_nome']) . "\" criada no site.";
            }
        }
    }
    if ($pa === 'rejeitar') {
        $motivo = trim($_POST['motivo'] ?? '');
        $s = mysqli_prepare($conexao,"UPDATE solicitacoes_produtos SET status='rejeitado', motivo_rejeicao=?, analisado_em=NOW() WHERE id=?");
        mysqli_stmt_bind_param($s,'si',$motivo,$sid);
        mysqli_stmt_execute($s);
        $msg = 'Solicitação rejeitada.'; $msg_type = 'warn';
    }
}

$aba = $_GET['aba'] ?? 'pendente';
$lista = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT sp.*, pr.nome AS produtor_nome
     FROM solicitacoes_produtos sp
     LEFT JOIN produtores pr ON pr.id=sp.produtor_id
     WHERE sp.status='$aba' ORDER BY sp.criado_em DESC"
), MYSQLI_ASSOC);

$n_pend = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtos WHERE status='pendente'"))['n'];
$n_aprov= (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtos WHERE status='aprovado'"))['n'];
$n_rej  = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtos WHERE status='rejeitado'"))['n'];

include('layout.php');
?>
<div class="tabs">
    <a href="sol_produtos.php?aba=pendente"  class="tab-btn <?php echo $aba==='pendente'?'active':''; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Pendentes (<?php echo $n_pend; ?>)</a>
    <a href="sol_produtos.php?aba=aprovado"  class="tab-btn <?php echo $aba==='aprovado'?'active':''; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg> Aprovados (<?php echo $n_aprov; ?>)</a>
    <a href="sol_produtos.php?aba=rejeitado" class="tab-btn <?php echo $aba==='rejeitado'?'active':''; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Rejeitados (<?php echo $n_rej; ?>)</a>
</div>

<?php if (empty($lista)): ?>
<div class="empty-state"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span><p><?php echo $aba==='pendente'?'Nenhuma solicitação pendente.':'Nenhum registro.'; ?></p></div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:16px;">
<?php foreach ($lista as $sol): ?>
<div class="card">
    <div style="display:grid;grid-template-columns:120px 1fr auto;gap:20px;padding:20px;align-items:start;">
        <!-- Imagem produto -->
        <div>
            <?php if ($sol['foto']): ?>
            <img src="../<?php echo htmlspecialchars($sol['foto']); ?>" style="width:110px;height:110px;object-fit:cover;border-radius:12px;border:1px solid var(--border);" onerror="this.src='../imagens/cafe1.jpg'">
            <?php else: ?>
            <div style="width:110px;height:110px;border-radius:12px;background:var(--bg);display:flex;align-items:center;justify-content:center;font-size:40px;border:1px solid var(--border);"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
            <?php endif; ?>
        </div>
        <!-- Info -->
        <div>
            <div style="font-size:16px;font-weight:700;margin-bottom:6px;"><?php echo htmlspecialchars($sol['nome']); ?></div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                <?php if ($sol['categoria'] === 'outra'): ?>
                <span class="badge" style="background:#fff7ed;color:#c2410c;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Nova categoria sugerida: <?php echo htmlspecialchars($sol['nova_categoria_nome'] ?? ''); ?></span>
                <?php else: ?>
                <span class="badge" style="background:#f0f7f1;color:var(--green);"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg> <?php echo ucfirst($sol['categoria']??'–'); ?></span>
                <?php endif; ?>
                <span class="badge" style="background:#fff3ed;color:var(--orange);"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> R$ <?php echo number_format($sol['preco'],2,',','.'); ?></span>
                <span class="badge" style="background:#eff6ff;color:#1e40af;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <?php echo htmlspecialchars($sol['regiao']??''); ?></span>
                <?php if ($sol['peso']): ?>
                <span class="badge" style="background:#f3f4f6;color:#374151;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><line x1="12" y1="3" x2="12" y2="21"/><path d="M18 6H4l4 6H4"/><path d="M4 6l4 6H4"/><path d="M20 6l-4 6h4"/><line x1="5" y1="21" x2="19" y2="21"/></svg> <?php echo htmlspecialchars($sol['peso']); ?></span>
                <?php endif; ?>
            </div>
            <div style="font-size:13px;color:var(--text-2);margin-bottom:8px;line-height:1.6;"><?php echo htmlspecialchars($sol['descricao']??''); ?></div>
            <?php if ($sol['historia']): ?>
            <details>
                <summary style="font-size:12px;color:var(--green);cursor:pointer;font-weight:600;">Ver história completa</summary>
                <div style="font-size:12px;color:var(--text-2);margin-top:8px;line-height:1.7;background:var(--bg);padding:10px 14px;border-radius:8px;"><?php echo nl2br(htmlspecialchars($sol['historia'])); ?></div>
            </details>
            <?php endif; ?>
            <div style="margin-top:10px;font-size:12px;color:var(--text-3);">
                <svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-dasharray="2 2"/></svg> Produtor: <strong><?php echo htmlspecialchars($sol['produtor_nome']??'–'); ?></strong>
                &nbsp;·&nbsp; Estoque inicial: <strong><?php echo $sol['estoque_inicial']??0; ?> un.</strong>
                &nbsp;·&nbsp; Origem: <?php echo htmlspecialchars($sol['origem']??''); ?>
            </div>
            <?php if ($sol['motivo_rejeicao']): ?>
            <div style="font-size:12px;color:#991b1b;margin-top:8px;background:#fef2f2;padding:8px 12px;border-radius:8px;">Motivo rejeição: <?php echo htmlspecialchars($sol['motivo_rejeicao']); ?></div>
            <?php endif; ?>
        </div>
        <!-- Ações -->
        <?php if ($aba === 'pendente'): ?>
        <div style="display:flex;flex-direction:column;gap:8px;min-width:160px;">
            <div style="font-size:11px;color:var(--text-3);">Recebido em<br><?php echo date('d/m/Y H:i', strtotime($sol['criado_em'])); ?></div>
            <form method="POST">
                <input type="hidden" name="acao" value="aprovar">
                <input type="hidden" name="sol_id" value="<?php echo $sol['id']; ?>">
                <button class="btn btn-green" style="width:100%;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg> Aprovar & Publicar</button>
            </form>
            <button class="btn btn-red" style="width:100%;" onclick="document.getElementById('rej-<?php echo $sol['id']; ?>').style.display='block'"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Rejeitar</button>
            <div id="rej-<?php echo $sol['id']; ?>" style="display:none;margin-top:6px;">
                <form method="POST">
                    <input type="hidden" name="acao" value="rejeitar">
                    <input type="hidden" name="sol_id" value="<?php echo $sol['id']; ?>">
                    <textarea name="motivo" placeholder="Motivo (opcional)…" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:8px;font-family:'Inter',sans-serif;font-size:12px;resize:none;min-height:60px;outline:none;"></textarea>
                    <button type="submit" class="btn btn-red" style="width:100%;margin-top:6px;">Confirmar</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div>
            <span class="badge badge-<?php echo $sol['status']; ?>"><?php echo ucfirst($sol['status']); ?></span>
            <?php if ($sol['status']==='aprovado' && $sol['produto_id']): ?>
            <div style="margin-top:10px;"><a href="../produto.php?id=<?php echo $sol['produto_id']; ?>" target="_blank" class="btn btn-blue" style="font-size:11px;">Ver Produto</a></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

</div></main></body></html>
