<?php include("conexao.php");

$q = trim($_GET['q'] ?? '');

$sql = "SELECT pr.*, COUNT(p.id) AS total_produtos
        FROM produtores pr
        LEFT JOIN produtos p ON p.produtor_id = pr.id";
if ($q) {
    $sql .= " WHERE pr.nome LIKE ? OR pr.fazenda LIKE ? OR pr.regiao LIKE ? OR pr.estado LIKE ?";
}
$sql .= " GROUP BY pr.id ORDER BY pr.nome ASC";

$stmt = mysqli_prepare($conexao, $sql);
if ($q) {
    $like = "%$q%";
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
}
mysqli_stmt_execute($stmt);
$produtores = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$total = count($produtores);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos Produtores — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;900&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .prod-hero {
            position: relative;
            background: linear-gradient(160deg, rgba(13,31,16,.88) 0%, rgba(28,51,32,.85) 50%, rgba(42,61,26,.88) 100%),
                        url('imagens/bg-campo5.png') center/cover;
            padding: 72px 24px 56px;
            color: #fff;
            text-align: center;
        }
        .prod-hero .eyebrow { font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#e8c87a;margin-bottom:10px; }
        .prod-hero h1 { font-family:'Playfair Display',serif;font-size:36px;font-weight:700;margin-bottom:12px; }
        .prod-hero p { font-size:14px;opacity:.85;max-width:560px;margin:0 auto; }

        .prod-search-wrap { max-width:1100px;margin:0 auto;padding:32px 24px 0; }
        .prod-search { display:flex;gap:10px;max-width:420px;margin:0 auto 8px; }
        .prod-search input {
            flex:1;padding:12px 16px;border:1.5px solid var(--border);border-radius:30px;
            font-family:'Poppins',sans-serif;font-size:13px;outline:none;background:var(--cream);
        }
        .prod-search input:focus { border-color:var(--green-2);background:#fff; }
        .prod-search button {
            padding:12px 22px;border:none;border-radius:30px;background:var(--green-2);color:#fff;
            font-size:13px;font-weight:700;cursor:pointer;
        }
        .prod-total { text-align:center;font-size:13px;color:var(--text-3);margin-bottom:28px; }

        .prod-listing { max-width:1100px;margin:0 auto;padding:0 24px 90px; }
        .produtores-grid-full { display:grid;grid-template-columns:repeat(3,1fr);gap:20px; }
        .produtores-grid-full .produtor-foto-wrap { width:96px;height:96px;border:3px solid #fff;box-shadow:0 4px 14px rgba(0,0,0,.1); }
        .produtores-grid-full .produtor-avatar-fallback { width:96px;height:96px;font-size:32px; }

        .prod-empty { text-align:center;padding:80px 24px;color:var(--text-3); }
        .prod-empty svg { width:48px;height:48px;color:var(--text-4);margin-bottom:16px; }
        .prod-empty h3 { font-family:'Playfair Display',serif;font-size:20px;color:var(--text);margin-bottom:8px; }

        .cta-seja-produtor {
            max-width:1100px;margin:0 auto 90px;padding:0 24px;
        }
        .cta-seja-produtor .inner {
            background:linear-gradient(135deg,#1C3320,#2C4A2E);border-radius:24px;padding:40px 32px;
            text-align:center;color:#fff;
        }
        .cta-seja-produtor h3 { font-family:'Playfair Display',serif;font-size:22px;margin-bottom:10px; }
        .cta-seja-produtor p { font-size:13px;opacity:.85;margin-bottom:22px;max-width:480px;margin-left:auto;margin-right:auto; }
        .cta-seja-produtor a {
            display:inline-flex;align-items:center;gap:8px;background:var(--orange);color:#fff;
            padding:13px 28px;border-radius:30px;font-size:13px;font-weight:700;text-decoration:none;
        }
        @media(max-width:768px){ .produtores-grid-full{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php include("includes/header.php"); ?>

<div class="prod-hero">
    <div class="eyebrow">Do campo à sua mesa</div>
    <h1>Nossos Produtores</h1>
    <p>Conheça as famílias e cooperativas rurais por trás de cada produto da Origem Brasil — de norte a sul do país.</p>
</div>

<div class="prod-search-wrap">
    <form class="prod-search" method="get" action="produtores.php">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Buscar por nome, fazenda, região ou estado...">
        <button type="submit"><?php echo icon('search'); ?></button>
    </form>
    <div class="prod-total">
        <?php echo $total; ?> produtor<?php echo $total != 1 ? 'es' : ''; ?> parceiro<?php echo $total != 1 ? 's' : ''; ?> da Origem Brasil
    </div>
</div>

<div class="prod-listing">
    <?php if (empty($produtores)): ?>
    <div class="prod-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <h3>Nenhum produtor encontrado</h3>
        <p><?php echo $q ? 'Tente buscar por outro termo.' : 'Ainda não há produtores cadastrados.'; ?></p>
    </div>
    <?php else: ?>
    <div class="produtores-grid-full">
        <?php foreach ($produtores as $prod): ?>
        <a href="produtor.php?id=<?php echo $prod['id']; ?>" class="produtor-card">
            <div class="produtor-foto-wrap">
                <?php if (!empty($prod['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($prod['foto']); ?>"
                         alt="<?php echo htmlspecialchars($prod['nome']); ?>"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div class="produtor-avatar-fallback" style="display:none;"><?php echo mb_strtoupper(mb_substr($prod['nome'],0,1)); ?></div>
                <?php else: ?>
                    <div class="produtor-avatar-fallback"><?php echo mb_strtoupper(mb_substr($prod['nome'],0,1)); ?></div>
                <?php endif; ?>
            </div>
            <div class="produtor-card-body">
                <h4><?php echo htmlspecialchars($prod['nome']); ?></h4>
                <?php if ($prod['fazenda']): ?>
                <div class="fazenda">
                    <?php echo icon('box'); ?>
                    <?php echo htmlspecialchars($prod['fazenda']); ?>
                </div>
                <?php endif; ?>
                <div class="produtor-card-footer">
                    <?php if ($prod['regiao'] || $prod['estado']): ?>
                    <span class="regiao-chip">
                        <?php echo icon('pin'); ?>
                        <?php echo htmlspecialchars(trim(($prod['regiao'] ? $prod['regiao'].', ' : '') . $prod['estado'])); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($prod['total_produtos'] > 0): ?>
                    <span class="prod-count"><?php echo $prod['total_produtos']; ?> produto<?php echo $prod['total_produtos'] != 1 ? 's' : ''; ?></span>
                    <?php endif; ?>
                </div>
                <div class="ver-link">
                    Ver história <?php echo icon('chevron-r'); ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div class="cta-seja-produtor">
    <div class="inner">
        <h3>Você também é produtor rural?</h3>
        <p>Junte-se à Origem Brasil e leve seus produtos artesanais para consumidores em todo o país.</p>
        <a href="seja_produtor.php"><?php echo icon('plus'); ?> Seja um Produtor</a>
    </div>
</div>

<?php include("includes/footer.php"); ?>
</body>
</html>
