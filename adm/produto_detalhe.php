<?php
$page_title  = 'Detalhe do Produto';
$active_menu = 'produtos';
include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: produtos.php"); exit; }

$p = mysqli_fetch_assoc(mysqli_query($conexao,
    "SELECT p.*, pr.nome AS produtor_nome, pr.foto AS produtor_foto, pr.fazenda,
     COALESCE(ROUND(AVG(a.nota),1),0) AS media, COUNT(DISTINCT a.id) AS n_av,
     COALESCE(SUM(pi.quantidade),0) AS total_vendido,
     COALESCE(SUM(pi.quantidade*pi.preco),0) AS receita_total
     FROM produtos p
     LEFT JOIN produtores pr ON pr.id=p.produtor_id
     LEFT JOIN avaliacoes a ON a.produto_id=p.id
     LEFT JOIN pedido_itens pi ON pi.produto_id=p.id
     LEFT JOIN pedidos pe ON pe.id=pi.pedido_id AND pe.status NOT IN('cancelado')
     WHERE p.id=$id GROUP BY p.id"
));
if (!$p) { header("Location: produtos.php"); exit; }

$avaliacoes = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT a.*, u.nome AS usuario_nome FROM avaliacoes a
     JOIN usuarios u ON u.id=a.usuario_id WHERE a.produto_id=$id ORDER BY a.criado_em DESC"
), MYSQLI_ASSOC);

$topbar_action = '<a href="produtos.php?acao=form&id='.$id.'" class="topbar-btn primary"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg> Editar</a>
<a href="produtos.php" class="topbar-btn">← Voltar</a>';
include('layout.php');
?>
<div style="display:grid;grid-template-columns:280px 1fr;gap:24px;">
    <!-- Imagem + métricas -->
    <div>
        <div class="card" style="padding:20px;text-align:center;margin-bottom:16px;">
            <img src="../<?php echo htmlspecialchars($p['imagem']); ?>"
                 style="width:100%;max-height:240px;object-fit:cover;border-radius:12px;border:1px solid var(--border);"
                 onerror="this.src='../imagens/cafe1.jpg'">
            <div style="margin-top:14px;">
                <?php if ($p['destaque']): ?><span class="badge badge-pago" style="margin-bottom:8px;display:inline-block;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:#f59e0b;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> Destaque</span><?php endif; ?>
                <div style="font-size:22px;font-weight:800;color:var(--green);">R$ <?php echo number_format($p['preco'],2,',','.'); ?></div>
                <div style="font-size:12px;color:var(--text-3);"><?php echo htmlspecialchars($p['peso']??''); ?></div>
            </div>
        </div>
        <div class="card" style="padding:0;overflow:hidden;">
            <?php
            $metrics = [
                ['label'=>'Estoque','val'=>$p['estoque'],'color'=>$p['estoque']==0?'#EF4444':($p['estoque']<=5?'#D97706':'#166534')],
                ['label'=>'Total Vendido','val'=>$p['total_vendido'].' un.','color'=>'var(--green)'],
                ['label'=>'Receita Gerada','val'=>'R$ '.number_format($p['receita_total'],2,',','.'),'color'=>'var(--green)'],
                ['label'=>'Avaliação Média','val'=>$p['n_av']>0?'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:#f59e0b;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> '.$p['media'].' ('.$p['n_av'].')':'Sem avaliações','color'=>'#D97706'],
            ];
            foreach ($metrics as $m): ?>
            <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;color:var(--text-3);"><?php echo $m['label']; ?></span>
                <span style="font-size:13px;font-weight:700;color:<?php echo $m['color']; ?>;"><?php echo $m['val']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Detalhes -->
    <div>
        <div class="card" style="padding:24px;margin-bottom:16px;">
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                <span class="badge" style="background:#f0f7f1;color:var(--green);"><?php echo ucfirst($p['categoria']); ?></span>
                <span class="badge" style="background:#eff6ff;color:#1e40af;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <?php echo htmlspecialchars($p['regiao']??''); ?></span>
                <span class="badge" style="background:#f3f4f6;color:#374151;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 10 10"/><path d="M8 5.07A10 10 0 0 1 22 12h-3"/><path d="M12 12a5 5 0 0 0 5-5"/><path d="M12 12a5 5 0 0 1-5-5"/></svg> <?php echo htmlspecialchars($p['origem']??''); ?></span>
            </div>
            <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;"><?php echo htmlspecialchars($p['nome']); ?></h2>

            <?php if ($p['produtor_nome']): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--bg);border-radius:10px;margin-bottom:16px;">
                <?php if ($p['produtor_foto']): ?>
                <img src="../<?php echo htmlspecialchars($p['produtor_foto']); ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" onerror="this.style.display='none'">
                <?php else: ?>
                <div class="avatar-circle" style="width:36px;height:36px;"><?php echo mb_strtoupper(mb_substr($p['produtor_nome'],0,1)); ?></div>
                <?php endif; ?>
                <div>
                    <div style="font-size:12px;color:var(--text-3);">Produtor</div>
                    <div style="font-size:13px;font-weight:600;"><?php echo htmlspecialchars($p['produtor_nome']); ?></div>
                    <div style="font-size:11px;color:var(--text-3);"><?php echo htmlspecialchars($p['fazenda']??''); ?></div>
                </div>
                <a href="produtores.php?acao=form&id=<?php echo $p['produtor_id']; ?>" class="btn btn-gray" style="margin-left:auto;padding:5px 10px;font-size:11px;">Ver</a>
            </div>
            <?php endif; ?>

            <div style="margin-bottom:14px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-3);margin-bottom:6px;">Descrição</div>
                <p style="font-size:13px;line-height:1.7;color:var(--text-2);"><?php echo nl2br(htmlspecialchars($p['descricao']??'')); ?></p>
            </div>
            <?php if ($p['historia']): ?>
            <div>
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-3);margin-bottom:6px;">História</div>
                <p style="font-size:13px;line-height:1.7;color:var(--text-2);"><?php echo nl2br(htmlspecialchars($p['historia'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Avaliações -->
        <div class="card">
            <div class="card-header"><h3><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:#f59e0b;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> Avaliações (<?php echo count($avaliacoes); ?>)</h3></div>
            <?php if (empty($avaliacoes)): ?>
            <div class="empty-state"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:#f59e0b;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span><p>Nenhuma avaliação ainda</p></div>
            <?php else: ?>
            <table class="adm-table">
                <thead><tr><th>Usuário</th><th>Nota</th><th>Comentário</th><th>Data</th></tr></thead>
                <tbody>
                <?php foreach ($avaliacoes as $av): ?>
                <tr>
                    <td style="font-weight:500;"><?php echo htmlspecialchars($av['usuario_nome']); ?></td>
                    <td><span style="color:#F59E0B;font-weight:700;"><?php echo str_repeat('<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',$av['nota']); ?></span><span style="color:#D1D5DB;"><?php echo str_repeat('<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',5-$av['nota']); ?></span></td>
                    <td style="font-size:12px;color:var(--text-2);"><?php echo htmlspecialchars($av['comentario']??'–'); ?></td>
                    <td style="font-size:11px;color:var(--text-3);"><?php echo date('d/m/Y', strtotime($av['criado_em'])); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</div></main></body></html>
