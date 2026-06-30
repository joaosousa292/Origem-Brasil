<?php include("conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Origem Brasil — Do Campo à Mesa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* SEÇÃO QUEM SOMOS */
        .qs-section { background: var(--cream); padding: 80px 0; }
        .qs-inner { max-width:1320px;margin:0 auto;padding:0 40px;display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center; }
        .qs-text .eyebrow { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--green-2);margin-bottom:14px;display:flex;align-items:center;gap:6px; }
        .qs-text .eyebrow::after { content:'';flex:1;height:1px;background:var(--green-2);opacity:.3; }
        .qs-text h2 { font-family:var(--font-display);font-size:34px;font-weight:700;line-height:1.2;color:var(--text);margin-bottom:20px; }
        .qs-text h2 em { font-style:italic;color:var(--green-2); }
        .qs-text p { font-size:14.5px;line-height:1.8;color:var(--text-3);margin-bottom:14px; }
        .qs-stats { display:flex;gap:32px;margin-top:28px;padding-top:28px;border-top:1px solid var(--border); }
        .qs-stat strong { display:block;font-size:26px;font-weight:800;color:var(--green);line-height:1; }
        .qs-stat span { font-size:12px;color:var(--text-3);margin-top:4px;display:block; }
        .qs-visual { background:linear-gradient(135deg,var(--green) 0%,var(--green-2) 60%,#4a8a50 100%);border-radius:24px;height:380px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden; }
        .qs-visual::before { content:'';position:absolute;inset:0;background:url('imagens/cafe1.jpg') center/cover;opacity:.18; }
        .qs-visual svg { width:96px;height:96px;stroke:#fff;fill:none;stroke-width:1;opacity:.8;position:relative;z-index:1; }

        /* CTA STRIP */
        .cta-strip { background:var(--green);padding:56px 40px;text-align:center;color:#fff; }
        .cta-strip h2 { font-family:var(--font-display);font-size:30px;font-weight:700;margin-bottom:12px; }
        .cta-strip p { font-size:15px;opacity:.75;margin-bottom:28px; }
        .cta-btns { display:flex;gap:14px;justify-content:center;flex-wrap:wrap; }
        .cta-btn-primary { background:#fff;color:var(--green);padding:14px 32px;border-radius:var(--radius);font-weight:700;font-size:14px;transition:transform .2s,box-shadow .2s; }
        .cta-btn-primary:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2); }
        .cta-btn-secondary { background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.4);padding:14px 32px;border-radius:var(--radius);font-weight:600;font-size:14px;transition:border-color .2s,background .2s; }
        .cta-btn-secondary:hover { border-color:#fff;background:rgba(255,255,255,.08); }

        @media(max-width:768px){
            .qs-inner{grid-template-columns:1fr;gap:32px;padding:0 18px;}
            .qs-visual{display:none;}
            .qs-stats{gap:20px;}
            .cta-strip{padding:40px 18px;}
        }
    </style>
</head>
<body data-logado="<?php echo isset($_SESSION['id'])?'1':'0'; ?>">

<?php include("includes/header.php"); ?>

<!-- CARROSSEL -->
<div class="carousel-wrap">
    <div class="carousel-track" id="carousel-track">
        <!-- Slide 1: sem imagem externa, usa gradiente + texto -->
        <div class="carousel-slide" style="background:linear-gradient(135deg,rgba(13,38,16,.80),rgba(28,51,32,.82)),url('imagens/bg-campo4.png') center/cover;">
            <div style="display:flex;align-items:center;justify-content:center;height:340px;gap:48px;padding:0 80px;flex-wrap:wrap;">
                <div style="color:#fff;max-width:560px;">
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.55);margin-bottom:14px;">Do campo à sua mesa</p>
                    <h2 style="font-family:'Playfair Display',serif;font-size:40px;font-weight:700;line-height:1.2;margin-bottom:16px;">Produtos com <em style="font-style:italic;color:#a3d9ab;">origem rastreável</em></h2>
                    <p style="font-size:15px;opacity:.75;line-height:1.7;margin-bottom:28px;">Conectamos pequenos produtores rurais a quem valoriza alimentos artesanais com história e transparência.</p>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;">
                        <a href="produtos.php" style="display:inline-flex;align-items:center;gap:8px;background:#D4622A;color:#fff;padding:13px 28px;border-radius:12px;font-weight:700;font-size:14px;">Ver Produtos <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="9 18 15 12 9 6"/></svg></a>
                        <a href="sobre.php" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);padding:13px 28px;border-radius:12px;font-weight:600;font-size:14px;">Nossa história</a>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;flex-shrink:0;">
                    <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:20px 16px;text-align:center;color:#fff;">
                        <div style="font-size:28px;font-weight:800;color:#a3d9ab;">50+</div>
                        <div style="font-size:11px;opacity:.7;margin-top:4px;">Produtores</div>
                    </div>
                    <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:20px 16px;text-align:center;color:#fff;">
                        <div style="font-size:28px;font-weight:800;color:#f5d08a;">200+</div>
                        <div style="font-size:11px;opacity:.7;margin-top:4px;">Produtos</div>
                    </div>
                    <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:20px 16px;text-align:center;color:#fff;">
                        <div style="font-size:28px;font-weight:800;color:#e8834e;">12</div>
                        <div style="font-size:11px;opacity:.7;margin-top:4px;">Estados</div>
                    </div>
                    <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:16px;padding:20px 16px;text-align:center;color:#fff;">
                        <div style="font-size:28px;font-weight:800;color:#fff;">100%</div>
                        <div style="font-size:11px;opacity:.7;margin-top:4px;">Natural</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="carousel-slide" style="background:linear-gradient(135deg,rgba(26,58,28,.80),rgba(45,90,48,.82)),url('imagens/bg-campo2.png') center/cover;">
            <div style="display:flex;align-items:center;justify-content:center;height:340px;gap:48px;padding:0 80px;flex-wrap:wrap;">
                <div style="color:#fff;max-width:480px;">
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.6);margin-bottom:14px;">Novidade</p>
                    <h2 style="font-family:'Playfair Display',serif;font-size:36px;font-weight:700;line-height:1.2;margin-bottom:16px;">Cafés Especiais do <em style="font-style:italic;color:#a3d9ab;">Cerrado Mineiro</em></h2>
                    <p style="font-size:15px;opacity:.75;line-height:1.7;margin-bottom:28px;">Grãos selecionados, torra artesanal e rastreabilidade total da fazenda à sua xícara.</p>
                    <a href="produtos.php?categoria=cafe" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1a3a1c;padding:13px 28px;border-radius:12px;font-weight:700;font-size:14px;">Ver Cafés <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="9 18 15 12 9 6"/></svg></a>
                </div>
                <!-- Mosaico de imagens reais de café que existem no projeto -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;width:220px;height:220px;flex-shrink:0;">
                    <img src="imagens/cafe/cafe-acaia.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.parentElement.style.display='none'">
                    <img src="imagens/cafe/cafe-bourbon-amarelo.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.style.display='none'">
                    <img src="imagens/cafe/cafe-cerrado-mineiro.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.style.display='none'">
                    <img src="imagens/cafe/cafe-mundo-novo.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
        <div class="carousel-slide" style="background:linear-gradient(135deg,rgba(58,31,10,.82),rgba(107,58,24,.84)),url('imagens/bg-campo1.png') center/cover;">
            <div style="display:flex;align-items:center;justify-content:center;height:340px;gap:48px;padding:0 80px;flex-wrap:wrap;">
                <div style="color:#fff;max-width:480px;">
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.6);margin-bottom:14px;">Mais Vendido</p>
                    <h2 style="font-family:'Playfair Display',serif;font-size:36px;font-weight:700;line-height:1.2;margin-bottom:16px;">Méis Artesanais <em style="font-style:italic;color:#f5d08a;">de Abelhas Nativas</em></h2>
                    <p style="font-size:15px;opacity:.75;line-height:1.7;margin-bottom:28px;">Colhidos por apicultores familiares. Sem aquecimento, sem conservantes, 100% puro.</p>
                    <a href="produtos.php?categoria=mel" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#5a3a1a;padding:13px 28px;border-radius:12px;font-weight:700;font-size:14px;">Ver Méis <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:16px;height:16px;"><polyline points="9 18 15 12 9 6"/></svg></a>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;width:220px;height:220px;flex-shrink:0;">
                    <img src="imagens/mel/mel-jatai.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.style.display='none'">
                    <img src="imagens/mel/mel-urucu.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.style.display='none'">
                    <img src="imagens/mel/mel-silvestre.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.style.display='none'">
                    <img src="imagens/mel/mel-guaraipo.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </div>
    <button class="carousel-btn carousel-prev" id="carousel-prev" aria-label="Anterior">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button class="carousel-btn carousel-next" id="carousel-next" aria-label="Próximo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <div class="carousel-dots">
        <button class="carousel-dot active"></button>
        <button class="carousel-dot"></button>
        <button class="carousel-dot"></button>
    </div>
</div>

<!-- FEATURES BAR -->
<div class="features-bar">
    <div class="features-inner">
        <div class="feature-item">
            <div class="feature-icon"><?php echo icon('truck'); ?></div>
            <div class="feature-text"><strong>Frete Grátis</strong><span>Compras acima de R$ 150</span></div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><?php echo icon('qr'); ?></div>
            <div class="feature-text"><strong>Origem Rastreável</strong><span>QR Code da fazenda</span></div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><?php echo icon('leaf'); ?></div>
            <div class="feature-text"><strong>100% Natural</strong><span>Sem conservantes</span></div>
        </div>
        <div class="feature-item">
            <div class="feature-icon"><?php echo icon('shield'); ?></div>
            <div class="feature-text"><strong>Compra Segura</strong><span>Pagamento criptografado</span></div>
        </div>
    </div>
</div>

<?php
// CAFÉS DESTAQUE
$stmt = mysqli_prepare($conexao,
    "SELECT p.*, COALESCE(ROUND(AVG(a.nota),1),0) as media_nota, COUNT(a.id) as total_av
     FROM produtos p LEFT JOIN avaliacoes a ON a.produto_id=p.id
     WHERE p.categoria='cafe' AND p.destaque=1
     GROUP BY p.id LIMIT 4"
);
mysqli_stmt_execute($stmt);
$cafes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// MÉIS DESTAQUE (substituindo cereais que não tem imagens)
$stmt2 = mysqli_prepare($conexao,
    "SELECT p.*, COALESCE(ROUND(AVG(a.nota),1),0) as media_nota, COUNT(a.id) as total_av
     FROM produtos p LEFT JOIN avaliacoes a ON a.produto_id=p.id
     WHERE p.categoria='mel' AND p.destaque=1
     GROUP BY p.id LIMIT 4"
);
mysqli_stmt_execute($stmt2);
$meis = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);

// PRODUTORES com contagem de produtos
$stmt3 = mysqli_prepare($conexao,
    "SELECT pr.*, COUNT(p.id) as total_produtos
     FROM produtores pr
     LEFT JOIN produtos p ON p.produtor_id = pr.id
     GROUP BY pr.id
     ORDER BY pr.id LIMIT 3"
);
mysqli_stmt_execute($stmt3);
$produtores = mysqli_fetch_all(mysqli_stmt_get_result($stmt3), MYSQLI_ASSOC);

function cardProduto($p) { ?>
<div class="product-card" data-produto-id="<?php echo $p['id']; ?>">
    <div class="img-wrap">
        <a href="produto.php?id=<?php echo $p['id']; ?>">
            <img src="<?php echo htmlspecialchars($p['imagem']); ?>"
                 alt="<?php echo htmlspecialchars($p['nome']); ?>"
                 loading="lazy" onerror="this.src='imagens/cafe1.jpg'">
        </a>
        <?php if ($p['destaque']): ?><span class="badge-destaque">Destaque</span><?php endif; ?>
        <?php if ($p['estoque']==0): ?><span class="badge-sem-estoque">Esgotado</span><?php endif; ?>
    </div>
    <div class="product-info">
        <?php if ($p['regiao']): ?>
        <div class="product-origin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?php echo htmlspecialchars($p['regiao']); ?>
        </div>
        <?php endif; ?>
        <div class="product-name"><a href="produto.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></a></div>
        <div class="product-desc"><?php echo htmlspecialchars($p['descricao']); ?></div>
        <?php if (!empty($p['media_nota']) && $p['media_nota'] > 0): ?>
        <div class="product-stars">
            <?php for ($s=1;$s<=5;$s++): ?><span class="star <?php echo $s<=round($p['media_nota'])?'':'empty'; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span><?php endfor; ?>
            <span class="star-count">(<?php echo $p['media_nota']; ?>)</span>
        </div>
        <?php endif; ?>
        <div class="product-footer">
            <div class="product-price">
                <span class="price-val">R$ <?php echo number_format($p['preco'],2,',','.'); ?></span>
                <?php if ($p['peso']): ?><span class="price-unit"><?php echo htmlspecialchars($p['peso']); ?></span><?php endif; ?>
            </div>
            <div class="product-actions">
                <button class="fav-btn" data-id="<?php echo $p['id']; ?>"
                        onclick="toggleFav(<?php echo $p['id']; ?>,this)" aria-label="Favoritar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
                <?php if ($p['estoque']>0): ?>
                <button class="add-btn" data-id="<?php echo $p['id']; ?>"
                        onclick="addToCart(<?php echo $p['id']; ?>,'<?php echo addslashes($p['nome']); ?>',<?php echo $p['preco']; ?>,'<?php echo addslashes($p['imagem']); ?>')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Adicionar
                </button>
                <?php else: ?>
                <button class="add-btn" disabled>Esgotado</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<!-- CAFÉS DESTAQUE -->
<?php if (!empty($cafes)): ?>
<section class="section-wrap">
    <div class="section-head">
        <h2>Cafés em <em>Destaque</em></h2>
        <a href="produtos.php?categoria=cafe">Ver todos <?php echo icon('chevron-r'); ?></a>
    </div>
    <div class="products-grid">
        <?php foreach ($cafes as $p) cardProduto($p); ?>
    </div>
</section>
<?php endif; ?>

<!-- QUEM SOMOS -->
<section class="qs-section">
    <div class="qs-inner">
        <div class="qs-text">
            <div class="eyebrow">Nossa Missão</div>
            <h2>Origem com <em>Propósito</em></h2>
            <p>A Origem Brasil conecta pequenos produtores rurais a consumidores que valorizam ingredientes artesanais com ciência, cuidado e rastreabilidade total.</p>
            <p>Cada produto tem uma história — e nós a contamos com orgulho, do campo à sua mesa.</p>
            <div class="qs-stats">
                <div class="qs-stat"><strong>50+</strong><span>Produtores</span></div>
                <div class="qs-stat"><strong>12</strong><span>Estados</span></div>
                <div class="qs-stat"><strong>200+</strong><span>Produtos</span></div>
            </div>
        </div>
        <div class="qs-visual">
            <svg viewBox="0 0 24 24"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
        </div>
    </div>
</section>

<!-- MÉIS DESTAQUE -->
<?php if (!empty($meis)): ?>
<section class="section-wrap">
    <div class="section-head">
        <h2>Méis <em>Artesanais</em></h2>
        <a href="produtos.php?categoria=mel">Ver todos <?php echo icon('chevron-r'); ?></a>
    </div>
    <div class="products-grid">
        <?php foreach ($meis as $p) cardProduto($p); ?>
    </div>
</section>
<?php endif; ?>

<!-- PRODUTORES -->
<?php if (!empty($produtores)): ?>
<section class="section-wrap" style="padding-top:0;">
    <div class="section-head">
        <h2>Nossos <em>Produtores</em></h2>
        <a href="produtores.php">Conheça todos <?php echo icon('chevron-r'); ?></a>
    </div>
    <div class="produtores-grid">
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
                <div class="fazenda">
                    <?php echo icon('box'); ?>
                    <?php echo htmlspecialchars($prod['fazenda']); ?>
                </div>
                <div class="produtor-card-footer">
                    <span class="regiao-chip">
                        <?php echo icon('pin'); ?>
                        <?php echo htmlspecialchars($prod['regiao']); ?>, <?php echo htmlspecialchars($prod['estado']); ?>
                    </span>
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
</section>
<?php endif; ?>

<!-- CTA -->
<div class="cta-strip">
    <h2>Descubra o Brasil em Cada Produto</h2>
    <p>Cafés especiais, méis artesanais, pimentas e muito mais — tudo com rastreabilidade total.</p>
    <div class="cta-btns">
        <a href="produtos.php?categoria=cafe" class="cta-btn-primary">Explorar Cafés</a>
        <a href="produtos.php?categoria=mel" class="cta-btn-secondary">Ver Méis</a>
    </div>
</div>

<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
<style>
    .produtor-foto-wrap {
    width: 140px;
    height: 140px;
    margin: 0 auto;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #fff;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
}

.produtor-foto-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.produtor-avatar-fallback {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    font-weight: 700;
}
</style>
</body>
</html>