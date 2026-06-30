<?php
$page_title  = 'Categorias';
$active_menu = 'categorias';
include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

$cats = mysqli_fetch_all(mysqli_query($conexao,
    "SELECT c.slug AS categoria,
            COALESCE(p.total,0) AS total,
            COALESCE(p.estoque_total,0) AS estoque_total,
            COALESCE(p.preco_medio,0) AS preco_medio,
            COALESCE(p.em_destaque,0) AS em_destaque,
            COALESCE(p.sem_estoque,0) AS sem_estoque,
            c.label AS label_db
     FROM categorias c
     LEFT JOIN (
        SELECT categoria, COUNT(*) AS total, COALESCE(SUM(estoque),0) AS estoque_total,
               COALESCE(AVG(preco),0) AS preco_medio,
               SUM(CASE WHEN destaque=1 THEN 1 ELSE 0 END) AS em_destaque,
               SUM(CASE WHEN estoque=0 THEN 1 ELSE 0 END) AS sem_estoque
        FROM produtos GROUP BY categoria
     ) p ON p.categoria = c.slug
     ORDER BY total DESC"
), MYSQLI_ASSOC);

$cat_info = [
    'cafe'    => ['label'=>'Cafés Especiais',   'emoji'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>', 'cor'=>'#6B3A2A'],
    'mel'     => ['label'=>'Méis Artesanais',   'emoji'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M8 2h8l1 4H7L8 2z"/><path d="M6 6c-1 1-2 3-2 5 0 5 4 9 8 9s8-4 8-9c0-2-1-4-2-5"/></svg>', 'cor'=>'#D4622A'],
    'pimenta' => ['label'=>'Pimentas Especiais','emoji'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 2c0 0 3 1 3 5s-3 5-3 5"/><path d="M9 7c0 0-4 1-5 6s3 9 6 9 5-3 5-9c0-4-3-6-6-6z"/></svg>', 'cor'=>'#EF4444'],
    'farinha' => ['label'=>'Farinhas Naturais', 'emoji'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22v-9"/><path d="M15.17 3c.51.98.83 2.09.83 3.25C16 9.47 14.21 12 12 12s-4-2.53-4-5.75C8 5.09 8.49 3.98 9 3"/><path d="M6.08 8c.27.97.92 1.82 1.92 2.37"/><path d="M17.92 8c-.27.97-.92 1.82-1.92 2.37"/></svg>', 'cor'=>'#D97706'],
    'cereais' => ['label'=>'Cereais e Grãos',   'emoji'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/></svg>', 'cor'=>'#10B981'],
];

include('layout.php');
?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
<?php foreach ($cats as $c):
    $info = $cat_info[$c['categoria']] ?? ['label'=>$c['label_db'] ?: ucfirst($c['categoria']),'emoji'=>'<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>','cor'=>'#9CA3AF'];
?>
<div class="card" style="padding:24px;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
        <div style="width:44px;height:44px;border-radius:12px;background:<?php echo $info['cor']; ?>20;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
            <?php echo $info['emoji']; ?>
        </div>
        <div>
            <div style="font-size:15px;font-weight:700;"><?php echo $info['label']; ?></div>
            <div style="font-size:11px;color:var(--text-3);font-family:monospace;"><?php echo $c['categoria']; ?></div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
        <div style="background:var(--bg);border-radius:8px;padding:10px;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:<?php echo $info['cor']; ?>;"><?php echo $c['total']; ?></div>
            <div style="font-size:10px;color:var(--text-3);margin-top:2px;">produtos</div>
        </div>
        <div style="background:var(--bg);border-radius:8px;padding:10px;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:var(--green);"><?php echo $c['estoque_total']; ?></div>
            <div style="font-size:10px;color:var(--text-3);margin-top:2px;">em estoque</div>
        </div>
        <div style="background:var(--bg);border-radius:8px;padding:10px;text-align:center;">
            <div style="font-size:14px;font-weight:700;color:var(--text);">R$ <?php echo number_format($c['preco_medio'],2,',','.'); ?></div>
            <div style="font-size:10px;color:var(--text-3);margin-top:2px;">preço médio</div>
        </div>
        <div style="background:var(--bg);border-radius:8px;padding:10px;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:<?php echo $c['sem_estoque']>0?'#EF4444':'#166534'; ?>;"><?php echo $c['sem_estoque']; ?></div>
            <div style="font-size:10px;color:var(--text-3);margin-top:2px;">sem estoque</div>
        </div>
    </div>
    <a href="produtos.php?filtro=&q=&categoria=<?php echo $c['categoria']; ?>" class="btn btn-gray" style="width:100%;justify-content:center;">Ver Produtos →</a>
</div>
<?php endforeach; ?>
</div>
</div></main></body></html>
