<?php include("conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossa História — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;900&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* ── SOBRE — RÚSTICO & CHAMATIVO ─────────────────── */

        .sobre-hero {
            position: relative;
            background: linear-gradient(160deg, rgba(13,31,16,.88) 0%, rgba(28,51,32,.85) 50%, rgba(42,61,26,.88) 100%),
                        url('imagens/bg-campo5.png') center/cover;
            padding: 100px 48px 120px;
            color: #fff;
            overflow: hidden;
            text-align: center;
        }
        .sobre-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 80%, rgba(212,98,42,.18) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 80% 20%, rgba(44,74,46,.4) 0%, transparent 70%);
            pointer-events: none;
        }
        /* Textura de grain sutil */
        .sobre-hero::after {
            content: '';
            position: absolute; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.03'/%3E%3C/svg%3E");
            pointer-events: none;
        }
        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
            border-radius: 30px; padding: 6px 18px;
            font-size: 11px; font-weight: 600; letter-spacing: .12em;
            text-transform: uppercase; color: rgba(255,255,255,.75);
            margin-bottom: 28px;
        }
        .hero-eyebrow svg { width: 14px; height: 14px; stroke: #D4622A; fill: none; stroke-width: 2; }
        .sobre-hero h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(40px, 6vw, 72px);
            font-weight: 900;
            line-height: 1.08;
            margin-bottom: 24px;
            letter-spacing: -.02em;
        }
        .sobre-hero h1 em {
            font-style: italic;
            background: linear-gradient(135deg, #D4622A, #e8834e);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .sobre-hero p {
            font-size: 16px; line-height: 1.8;
            color: rgba(255,255,255,.72);
            max-width: 560px; margin: 0 auto 40px;
        }
        .hero-stats {
            display: inline-flex; gap: 0;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 16px; overflow: hidden;
            position: relative; z-index: 1;
        }
        .hero-stat {
            padding: 20px 36px; text-align: center;
            border-right: 1px solid rgba(255,255,255,.1);
        }
        .hero-stat:last-child { border-right: none; }
        .hero-stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 32px; font-weight: 900;
            color: #fff; line-height: 1;
            display: block; margin-bottom: 4px;
        }
        .hero-stat-num span { color: #D4622A; }
        .hero-stat-label {
            font-size: 11px; font-weight: 500;
            color: rgba(255,255,255,.5); letter-spacing: .06em;
            text-transform: uppercase;
        }

        /* Linha divisória orgânica */
        .hero-divider {
            position: absolute; bottom: -1px; left: 0; right: 0;
        }

        /* ── CORPO ──────────────────────────────────────── */
        .sobre-body {
            max-width: 960px; margin: 0 auto;
            padding: 80px 48px 100px;
        }

        .section-label {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .14em; color: #D4622A;
            margin-bottom: 14px;
        }
        .section-label::before {
            content: ''; display: block;
            width: 24px; height: 2px; background: #D4622A; border-radius: 2px;
        }

        .sobre-section { margin-bottom: 80px; }
        .sobre-section h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(26px, 3.5vw, 38px);
            font-weight: 700; color: #1C1208;
            line-height: 1.2; margin-bottom: 20px;
        }
        .sobre-section h2 strong { color: #2C4A2E; }
        .sobre-section p {
            font-size: 15px; line-height: 1.9;
            color: #4a3728; margin-bottom: 16px;
        }

        /* Destaque em bloco */
        .sobre-pullquote {
            margin: 32px 0;
            padding: 28px 32px;
            background: linear-gradient(135deg, #f5f0e8, #fdfaf5);
            border-left: 4px solid #D4622A;
            border-radius: 0 14px 14px 0;
        }
        .sobre-pullquote p {
            font-family: 'Playfair Display', serif;
            font-size: 19px; font-style: italic;
            color: #1C1208; line-height: 1.6; margin: 0;
        }
        .sobre-pullquote cite {
            display: block; margin-top: 12px;
            font-size: 12px; font-weight: 600; color: #7A6248;
            text-transform: uppercase; letter-spacing: .08em;
            font-style: normal;
        }

        /* ── VALORES ──────────────────────────────────── */
        .valores-showcase {
            margin-top: 40px;
            position: relative;
        }
        .valores-row-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1.5px solid #E8DCC8;
            border-radius: 24px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 32px rgba(28,51,32,.07);
            margin-bottom: 0;
        }
        .valor-feat {
            padding: 36px 32px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            border-right: 1.5px solid #E8DCC8;
            border-bottom: 1.5px solid #E8DCC8;
            transition: background .2s;
            position: relative;
            overflow: hidden;
        }
        .valor-feat:nth-child(2n) { border-right: none; }
        .valor-feat:nth-child(n+5) { border-bottom: none; }
        .valor-feat::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(44,74,46,.03) 0%, transparent 60%);
            opacity: 0;
            transition: opacity .25s;
        }
        .valor-feat:hover::after { opacity: 1; }
        .valor-feat:hover { background: #fdfcf9; }
        .valor-num {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            font-weight: 900;
            color: #E8DCC8;
            line-height: 1;
            flex-shrink: 0;
            width: 48px;
            transition: color .25s;
        }
        .valor-feat:hover .valor-num { color: #c8e0cc; }
        .valor-feat-content {}
        .valor-feat-icon-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .valor-feat-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #1C3320, #2C4A2E);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .valor-feat-icon svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 1.8; }
        .valor-feat h4 {
            font-size: 15px; font-weight: 700;
            color: #1C1208;
        }
        .valor-feat p {
            font-size: 13px; color: #7A6248;
            line-height: 1.7; margin: 0;
        }
        .valor-strip {
            margin-top: 16px;
            background: linear-gradient(135deg, #1C3320, #2C4A2E);
            border-radius: 16px;
            padding: 28px 32px;
            display: flex;
            align-items: center;
            gap: 24px;
            overflow: hidden;
            position: relative;
        }
        .valor-strip::before {
            content: '';
            position: absolute;
            right: -20px; top: -20px;
            width: 140px; height: 140px;
            background: radial-gradient(circle, rgba(212,98,42,.25) 0%, transparent 70%);
        }
        .valor-strip-icon {
            width: 52px; height: 52px; flex-shrink: 0;
            background: rgba(255,255,255,.1);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255,255,255,.15);
        }
        .valor-strip-icon svg { width: 24px; height: 24px; stroke: #a3d9ab; fill: none; stroke-width: 1.8; }
        .valor-strip-text { flex: 1; color: #fff; position: relative; z-index: 1; }
        .valor-strip-text h4 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .valor-strip-text p  { font-size: 13px; opacity: .75; line-height: 1.6; margin: 0; }

        /* ── TIMELINE ──────────────────────────────────── */
        .timeline {
            position: relative; margin-top: 24px;
        }
        .timeline::before {
            content: '';
            position: absolute; left: 20px; top: 8px; bottom: 8px;
            width: 2px;
            background: linear-gradient(180deg, #2C4A2E, #D4622A, #E8DCC8);
            border-radius: 2px;
        }
        .tl-item {
            display: flex; gap: 28px;
            margin-bottom: 40px; padding-left: 4px;
            align-items: flex-start;
        }
        .tl-dot {
            flex-shrink: 0;
            width: 42px; height: 42px;
            background: #fff; border: 2.5px solid #2C4A2E;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            z-index: 1; position: relative;
            box-shadow: 0 0 0 5px #fff;
        }
        .tl-dot svg { width: 18px; height: 18px; stroke: #2C4A2E; fill: none; stroke-width: 2; }
        .tl-item:nth-child(3) .tl-dot { border-color: #D4622A; }
        .tl-item:nth-child(3) .tl-dot svg { stroke: #D4622A; }
        .tl-item:last-child .tl-dot { background: #2C4A2E; }
        .tl-item:last-child .tl-dot svg { stroke: #fff; }
        .tl-content { flex: 1; padding-top: 6px; }
        .tl-year {
            font-size: 11px; font-weight: 800;
            color: #D4622A; text-transform: uppercase;
            letter-spacing: .1em; margin-bottom: 4px;
        }
        .tl-content h4 {
            font-family: 'Playfair Display', serif;
            font-size: 17px; font-weight: 700;
            color: #1C1208; margin-bottom: 6px;
        }
        .tl-content p {
            font-size: 13.5px; color: #6b5242;
            line-height: 1.7; margin: 0;
        }

        /* ── CTA FINAL ──────────────────────────────────── */
        .sobre-cta {
            background: linear-gradient(135deg, #1C3320, #2C4A2E);
            border-radius: 24px;
            padding: 56px 48px;
            text-align: center; color: #fff;
            position: relative; overflow: hidden;
        }
        .sobre-cta::before {
            content: '';
            position: absolute; top: -40px; right: -40px;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(212,98,42,.25) 0%, transparent 70%);
        }
        .sobre-cta h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px; font-weight: 700;
            margin-bottom: 16px; line-height: 1.2;
        }
        .sobre-cta p {
            font-size: 15px; color: rgba(255,255,255,.7);
            max-width: 480px; margin: 0 auto 32px; line-height: 1.7;
        }
        .cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .cta-btn-primary {
            padding: 14px 32px;
            background: #D4622A; color: #fff;
            border-radius: 12px; font-weight: 700; font-size: 14px;
            text-decoration: none; transition: all .2s;
        }
        .cta-btn-primary:hover { background: #c0521f; transform: translateY(-2px); }
        .cta-btn-outline {
            padding: 14px 32px;
            background: rgba(255,255,255,.08);
            border: 1.5px solid rgba(255,255,255,.25);
            color: #fff; border-radius: 12px;
            font-weight: 600; font-size: 14px;
            text-decoration: none; transition: all .2s;
        }
        .cta-btn-outline:hover { background: rgba(255,255,255,.14); transform: translateY(-2px); }

        @media(max-width:768px){
            .sobre-hero { padding: 64px 20px 80px; }
            .hero-stats  { flex-direction: column; }
            .hero-stat   { border-right: none; border-bottom: 1px solid rgba(255,255,255,.1); }
            .sobre-body  { padding: 40px 20px 60px; }
            .valores-row-main { grid-template-columns: 1fr; }
            .valor-feat { border-right: none !important; }
            .valor-feat:nth-child(n+5) { border-bottom: 1.5px solid #E8DCC8 !important; }
            .valor-feat:last-child { border-bottom: none !important; }
            .sobre-cta { padding: 40px 24px; }
            .tl-item { gap: 16px; }
        }
        @media(max-width:600px){
            .valor-strip { flex-direction: column; gap: 12px; padding: 20px; }
        }
    </style>
</head>
<body data-logado="<?php echo isset($_SESSION['id'])?'1':'0'; ?>">
<?php include("includes/header.php"); ?>

<!-- HERO -->
<div class="sobre-hero">
    <div style="position:relative;z-index:1;">
        <div class="hero-eyebrow">
            <svg viewBox="0 0 24 24"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/></svg>
            Feito no Brasil, do campo à sua mesa
        </div>
        <h1>Do campo à sua <em>mesa</em>,<br>com alma e propósito</h1>
        <p>Conectamos os melhores produtores artesanais do Brasil com quem valoriza a origem, a história e o sabor de verdade por trás de cada produto.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-num">50<span>+</span></span>
                <span class="hero-stat-label">Produtores</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-num">12</span>
                <span class="hero-stat-label">Estados</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-num">200<span>+</span></span>
                <span class="hero-stat-label">Produtos</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-num">5</span>
                <span class="hero-stat-label">Anos</span>
            </div>
        </div>
    </div>
    <svg class="hero-divider" viewBox="0 0 1440 48" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <path d="M0 48 C360 0 1080 0 1440 48 L1440 48 L0 48Z" fill="#FDFAF5"/>
    </svg>
</div>

<!-- CORPO -->
<div class="sobre-body" style="background:#FDFAF5;">

    <!-- QUEM SOMOS -->
    <div class="sobre-section">
        <div class="section-label">Nossa essência</div>
        <h2>Uma ideia simples que <strong>mudou tudo</strong></h2>
        <p>A Origem Brasil nasceu da convicção de que existe um abismo entre quem produz com excelência e quem quer consumir com consciência. Nossa missão é fechar essa distância.</p>
        <p>Somos uma plataforma de e-commerce especializada em produtos artesanais de origem rastreável. Cada item do nosso catálogo tem uma história — e nós a contamos com orgulho, do pequeno produtor familiar ao consumidor urbano que busca autenticidade.</p>

        <div class="sobre-pullquote">
            <p>"Compramos de quem planta com cuidado. Vendemos para quem come com consciência."</p>
            <cite>— Fundadores da Origem Brasil</cite>
        </div>

        <p>Nossa equipe percorre o Brasil em busca de pequenos produtores extraordinários: cafés especiais do Cerrado Mineiro, granolas do Planalto Central, cereais orgânicos do Sul. Produtos com alma, com propósito, com sabor que você não esquece.</p>
    </div>

    <!-- VALORES -->
    <div class="sobre-section">
        <div class="section-label">O que nos guia</div>
        <h2>Valores que não<br><strong>negociamos</strong></h2>
        <div class="valores-showcase">
            <div class="valores-row-main">
                <div class="valor-feat">
                    <div class="valor-num">01</div>
                    <div class="valor-feat-content">
                        <div class="valor-feat-icon-row">
                            <div class="valor-feat-icon">
                                <svg viewBox="0 0 24 24"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                            </div>
                            <h4>Sustentabilidade</h4>
                        </div>
                        <p>Apoiamos práticas agrícolas que respeitam o meio ambiente e as comunidades locais em todo o Brasil.</p>
                    </div>
                </div>
                <div class="valor-feat">
                    <div class="valor-num">02</div>
                    <div class="valor-feat-content">
                        <div class="valor-feat-icon-row">
                            <div class="valor-feat-icon">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <h4>Rastreabilidade</h4>
                        </div>
                        <p>Todo produto tem origem identificada. Você sabe exatamente de onde veio o que está consumindo.</p>
                    </div>
                </div>
                <div class="valor-feat">
                    <div class="valor-num">03</div>
                    <div class="valor-feat-content">
                        <div class="valor-feat-icon-row">
                            <div class="valor-feat-icon">
                                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <h4>Comércio Justo</h4>
                        </div>
                        <p>Pagamos preços justos e garantimos condições dignas de trabalho e produção para cada parceiro.</p>
                    </div>
                </div>
                <div class="valor-feat">
                    <div class="valor-num">04</div>
                    <div class="valor-feat-content">
                        <div class="valor-feat-icon-row">
                            <div class="valor-feat-icon">
                                <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            </div>
                            <h4>Qualidade</h4>
                        </div>
                        <p>Cada produto passa por rigoroso processo de curadoria antes de ser disponibilizado no catálogo.</p>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
                <div class="valor-strip">
                    <div class="valor-strip-icon">
                        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div class="valor-strip-text">
                        <h4>Brasil Primeiro</h4>
                        <p>Valorizamos ingredientes, territórios e sabores tipicamente brasileiros.</p>
                    </div>
                </div>
                <div class="valor-strip" style="background:linear-gradient(135deg,#7A3020,#D4622A);">
                    <div class="valor-strip-icon" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);">
                        <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <div class="valor-strip-text">
                        <h4>Propósito</h4>
                        <p>Somos um movimento por uma alimentação mais consciente e conectada.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TIMELINE -->
    <div class="sobre-section">
        <div class="section-label">Nossa trajetória</div>
        <h2>Cinco anos construindo<br><strong>pontes no campo</strong></h2>
        <div class="timeline">
            <div class="tl-item">
                <div class="tl-dot">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="tl-content">
                    <div class="tl-year">2019</div>
                    <h4>A ideia nasce</h4>
                    <p>Após visitar uma cooperativa de café no Cerrado Mineiro, os fundadores percebem o abismo entre produtores incríveis e consumidores que gostariam de conhecê-los.</p>
                </div>
            </div>
            <div class="tl-item">
                <div class="tl-dot">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div class="tl-content">
                    <div class="tl-year">2020</div>
                    <h4>Primeiros parceiros</h4>
                    <p>Firmamos parcerias com os primeiros três produtores e lançamos um catálogo piloto com 12 produtos. A resposta foi além do esperado.</p>
                </div>
            </div>
            <div class="tl-item">
                <div class="tl-dot">
                    <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <div class="tl-content">
                    <div class="tl-year">2021</div>
                    <h4>Sistema de rastreabilidade QR</h4>
                    <p>Desenvolvemos nosso sistema proprietário com QR Code, permitindo ao consumidor conhecer toda a cadeia produtiva do produto.</p>
                </div>
            </div>
            <div class="tl-item">
                <div class="tl-dot">
                    <svg viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div class="tl-content">
                    <div class="tl-year">2022</div>
                    <h4>50 produtores parceiros</h4>
                    <p>Chegamos à marca de 50 produtores em 12 estados brasileiros, com mais de 200 produtos no catálogo.</p>
                </div>
            </div>
            <div class="tl-item">
                <div class="tl-dot">
                    <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="tl-content">
                    <div class="tl-year">2024</div>
                    <h4>Plataforma nova geração</h4>
                    <p>Relançamos com novo design, experiência de compra aprimorada e storytelling ainda mais rico para cada produto e produtor.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="sobre-cta">
        <h2>Pronto para descobrir o Brasil<br>em cada produto?</h2>
        <p>Navegue pelo nosso catálogo e encontre produtos com história, sabor e propósito direto de quem produz com paixão.</p>
        <div class="cta-btns">
            <a href="produtos.php" class="cta-btn-primary">Ver Catálogo</a>
            <a href="seja_produtor.php" class="cta-btn-outline">Seja um Produtor</a>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
</body></html>
