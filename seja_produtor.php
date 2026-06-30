<?php
include("conexao.php");

// É preciso estar logado com uma conta normal para se candidatar a produtor.
// Quando aprovado, essa MESMA conta passa a ser do tipo "produtor" — não se
// cria uma conta nova nem se troca o e-mail.
if (!isset($_SESSION['id'])) {
    header("Location: login.php?voltar=seja_produtor.php");
    exit;
}

// Confere se a migração mais recente já foi aplicada no banco
$chkMig = mysqli_query($conexao, "SHOW COLUMNS FROM solicitacoes_produtores LIKE 'usuario_id'");
$migracaoOk = $chkMig && mysqli_num_rows($chkMig) > 0;

$statusAtual = null; // null = pode se candidatar normalmente
if (!$migracaoOk) {
    $statusAtual = 'migracao_pendente';
} elseif (($_SESSION['tipo'] ?? '') === 'produtor') {
    $statusAtual = 'ja_produtor';
} else {
    $chkPend = mysqli_prepare($conexao, "SELECT id FROM solicitacoes_produtores WHERE usuario_id=? AND status='pendente'");
    mysqli_stmt_bind_param($chkPend, 'i', $_SESSION['id']);
    mysqli_stmt_execute($chkPend);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($chkPend))) {
        $statusAtual = 'pendente';
    }
}

$stmtU = mysqli_prepare($conexao, "SELECT nome, email FROM usuarios WHERE id=?");
mysqli_stmt_bind_param($stmtU, 'i', $_SESSION['id']);
mysqli_stmt_execute($stmtU);
$usuarioLogado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtU)) ?: ['nome'=>'','email'=>''];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Seja um Produtor Parceiro — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .sp-hero {
            background: linear-gradient(135deg,rgba(13,38,16,.84) 0%,rgba(28,51,32,.82) 50%,rgba(44,74,46,.84) 100%),
                        url('imagens/bg-campo3.png') center/cover;
            padding: 80px 40px 100px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .sp-hero::before {
            content:'';position:absolute;inset:0;
            background: radial-gradient(ellipse 60% 50% at 80% 80%, rgba(212,98,42,.18) 0%, transparent 70%);
            pointer-events:none;
        }
        .sp-hero-inner { position:relative;z-index:1;max-width:700px;margin:0 auto; }
        .sp-hero .eyebrow {
            display:inline-flex;align-items:center;gap:8px;
            background:rgba(163,217,171,.15);border:1px solid rgba(163,217,171,.3);
            color:#a3d9ab;font-size:12px;font-weight:700;text-transform:uppercase;
            letter-spacing:.1em;padding:6px 18px;border-radius:20px;margin-bottom:24px;
        }
        .sp-hero h1 { font-family:'Playfair Display',serif;font-size:44px;font-weight:700;line-height:1.2;margin-bottom:20px; }
        .sp-hero h1 em { font-style:italic;color:#a3d9ab; }
        .sp-hero p { font-size:16px;opacity:.75;line-height:1.8;max-width:560px;margin:0 auto 32px; }
        .sp-stats {
            display:flex;gap:40px;justify-content:center;flex-wrap:wrap;
            padding-top:32px;border-top:1px solid rgba(255,255,255,.1);
        }
        .sp-stat strong { display:block;font-size:28px;font-weight:800;color:#a3d9ab; }
        .sp-stat span   { font-size:13px;opacity:.6;margin-top:4px;display:block; }

        .sp-como { background:#FDFAF5;padding:72px 40px; }
        .sp-como-inner { max-width:1000px;margin:0 auto; }
        .sp-como h2 { font-family:'Playfair Display',serif;font-size:32px;font-weight:700;text-align:center;margin-bottom:8px;color:#1C1208; }
        .sp-como .sub { text-align:center;font-size:15px;color:#7A6248;margin-bottom:48px; }
        .steps { display:grid;grid-template-columns:repeat(4,1fr);gap:24px; }
        .step {
            text-align:center;padding:28px 20px;
            background:#fff;border:1px solid #E8DCC8;border-radius:16px;
            position:relative;
        }
        .step-num {
            width:40px;height:40px;border-radius:50%;
            background:linear-gradient(135deg,#2C4A2E,#3D6B40);
            color:#fff;font-size:16px;font-weight:800;
            display:flex;align-items:center;justify-content:center;margin:0 auto 16px;
        }
        .step h3 { font-size:15px;font-weight:700;color:#1C1208;margin-bottom:8px; }
        .step p  { font-size:13px;color:#7A6248;line-height:1.6; }
        .step-arrow {
            position:absolute;right:-14px;top:50%;transform:translateY(-50%);
            font-size:20px;color:#D4622A;z-index:1;
        }
        .step:last-child .step-arrow { display:none; }

        .sp-form-wrap { max-width:760px;margin:0 auto;padding:64px 40px; }
        .sp-form-title { text-align:center;margin-bottom:40px; }
        .sp-form-title h2 { font-family:'Playfair Display',serif;font-size:30px;font-weight:700;color:#1C1208;margin-bottom:8px; }
        .sp-form-title p  { font-size:14px;color:#7A6248; }

        .form-card {
            background:#fff;border:1px solid #E8DCC8;border-radius:20px;padding:36px;
            box-shadow:0 4px 24px rgba(0,0,0,.06);
        }
        .section-label {
            font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;
            color:#2C4A2E;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #E8DCC8;
        }
        .fgrid { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px; }
        .fgrid-1 { grid-column:1/-1; }
        .field { display:flex;flex-direction:column;gap:5px; }
        .field label { font-size:11px;font-weight:600;color:#7A6248;text-transform:uppercase;letter-spacing:.05em; }
        .field input,.field select,.field textarea {
            padding:11px 14px;border:1.5px solid #E8DCC8;border-radius:10px;
            font-family:'Inter',sans-serif;font-size:14px;outline:none;background:#FDFAF5;color:#1C1208;
            transition:border-color .15s,background .15s;
        }
        .field input:focus,.field select:focus,.field textarea:focus { border-color:#2C4A2E;background:#fff; }
        .field textarea { resize:vertical;min-height:100px; }
        .field .hint { font-size:11px;color:#9CA3AF; }

        .photo-upload{
    border:2px dashed #E8DCC8;
    border-radius:12px;
    padding:28px;
    background:#FDFAF5;
    cursor:pointer;

    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:8px;

    overflow:hidden;
}

.photo-upload:hover{
    border-color:#2C4A2E;
    background:#f0f7f1;
}

.photo-upload input{
    display:none;
}

.photo-upload .icon{
    font-size:32px;
    display:block;
}

.photo-upload .txt{
    font-size:15px;
    color:#7A6248;
    font-weight:600;
}

.photo-upload .sub{
    font-size:12px;
    color:#9CA3AF;
}

        .btn-enviar {
            width:100%;padding:17px;background:linear-gradient(135deg,#2C4A2E,#3D6B40);
            color:#fff;border:none;border-radius:14px;font-family:'Inter',sans-serif;
            font-size:16px;font-weight:700;cursor:pointer;margin-top:8px;
            transition:transform .2s,box-shadow .2s;
        }
        .btn-enviar:hover { transform:translateY(-2px);box-shadow:0 8px 24px rgba(44,74,46,.3); }

        .success-box {
            display:none;text-align:center;padding:40px 24px;
            background:#f0fdf4;border:1px solid #bbf7d0;border-radius:16px;margin-bottom:20px;
        }
        .success-box .icon { font-size:48px;margin-bottom:12px;display:block; }
        .success-box h3 { font-size:20px;font-weight:700;color:#166534;margin-bottom:8px; }
        .success-box p  { font-size:14px;color:#166534;opacity:.8; }

        .sp-beneficios { background:#1C3320;padding:64px 40px;color:#fff; }
        .sp-beneficios-inner { max-width:1000px;margin:0 auto; }
        .sp-beneficios h2 { font-family:'Playfair Display',serif;font-size:30px;text-align:center;margin-bottom:40px; }
        .beneficios-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:20px; }
        .beneficio {
            background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
            border-radius:14px;padding:24px;
        }
        .beneficio .icon { font-size:28px;margin-bottom:12px;display:block; }
        .beneficio h3 { font-size:15px;font-weight:700;color:#a3d9ab;margin-bottom:8px; }
        .beneficio p  { font-size:13px;color:rgba(255,255,255,.65);line-height:1.7; }

        .status-box {
            max-width:600px;margin:0 auto;text-align:center;padding:48px 32px;
            background:#fff;border:1px solid #E8DCC8;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.06);
        }
        .status-box .icon { color:#D4622A;margin-bottom:16px; }
        .status-box h3 { font-family:'Playfair Display',serif;font-size:22px;color:#1C1208;margin-bottom:10px; }
        .status-box p { font-size:14px;color:#7A6248;line-height:1.7;margin-bottom:20px; }
        .status-box a {
            display:inline-flex;align-items:center;gap:8px;background:#2C4A2E;color:#fff;
            padding:12px 26px;border-radius:30px;font-size:13px;font-weight:700;text-decoration:none;
        }
        .email-locked { background:#F0EBE0 !important;color:#7A6248 !important;cursor:not-allowed; }

        @media(max-width:768px){
            .sp-hero{padding:48px 18px 64px;}
            .sp-hero h1{font-size:28px;}
            .steps{grid-template-columns:1fr 1fr;}
            .step-arrow{display:none;}
            .fgrid{grid-template-columns:1fr;}
            .sp-form-wrap{padding:40px 18px;}
            .form-card{padding:20px;}
            .beneficios-grid{grid-template-columns:1fr;}
            .sp-como{padding:48px 18px;}
        }
    </style>
</head>
<body>
<?php include("includes/header.php"); ?>

<!-- HERO -->
<section class="sp-hero">
    <div class="sp-hero-inner">
        <div class="eyebrow"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 10 10"/><path d="M8 5.07A10 10 0 0 1 22 12h-3"/><path d="M12 12a5 5 0 0 0 5-5"/><path d="M12 12a5 5 0 0 1-5-5"/></svg> Seja um Parceiro</div>
        <h1>Faça Parte da Nossa <em>História</em></h1>
        <p>Conectamos produtores rurais apaixonados a consumidores que valorizam alimentos com origem rastreável, história e propósito. Se você produz algo especial, queremos te ouvir.</p>
        <a href="#formulario" style="display:inline-flex;align-items:center;gap:8px;background:#D4622A;color:#fff;padding:15px 32px;border-radius:14px;font-size:15px;font-weight:700;transition:transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
            Quero me cadastrar ↓
        </a>
        <div class="sp-stats">
            <div class="sp-stat"><strong>50+</strong><span>Produtores ativos</span></div>
            <div class="sp-stat"><strong>12</strong><span>Estados parceiros</span></div>
            <div class="sp-stat"><strong>200+</strong><span>Produtos vendidos</span></div>
            <div class="sp-stat"><strong>100%</strong><span>Comércio justo</span></div>
        </div>
    </div>
</section>

<!-- COMO FUNCIONA -->
<section class="sp-como">
    <div class="sp-como-inner">
        <h2>Como Funciona</h2>
        <p class="sub">Processo simples, transparente e pensado para valorizar seu trabalho</p>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <h3>Você se cadastra</h3>
                <p>Preencha o formulário abaixo com informações da sua propriedade e produtos.</p>
                <div class="step-arrow">→</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <h3>Análise da equipe</h3>
                <p>Nossa equipe analisa seu cadastro em até 5 dias úteis e entra em contato.</p>
                <div class="step-arrow">→</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <h3>Aprovação</h3>
                <p>Se aprovado, você recebe acesso para solicitar a publicação de seus produtos.</p>
                <div class="step-arrow">→</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <h3>Venda online</h3>
                <p>Seus produtos ficam visíveis na loja com sua história e rastreabilidade completa.</p>
            </div>
        </div>
    </div>
</section>

<!-- BENEFÍCIOS -->
<section class="sp-beneficios">
    <div class="sp-beneficios-inner">
        <h2>Por que ser parceiro Origem Brasil?</h2>
        <div class="beneficios-grid">
            <div class="beneficio"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span><h3>Preço Justo</h3><p>Você define o preço do seu produto. Sem intermediários que desvalorizem seu trabalho artesanal.</p></div>
            <div class="beneficio"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span><h3>Sua História Contada</h3><p>Cada produto tem uma página com a história do produtor, a fazenda e o processo de produção.</p></div>
            <div class="beneficio"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg></span><h3>QR de Rastreabilidade</h3><p>Os consumidores escaneiam e veem exatamente de onde vem o produto — isso aumenta as vendas.</p></div>
            <div class="beneficio"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></span><h3>Alcance Nacional</h3><p>Venda para todo o Brasil sem precisar sair da sua propriedade ou investir em logística própria.</p></div>
            <div class="beneficio"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M17 11h4v6h-4"/><path d="M7 11H3v6h4"/><path d="M7 11l2-4h6l2 4"/><path d="M7 11v6l2 2h6l2-2v-6"/></svg></span><h3>Suporte Dedicado</h3><p>Equipe disponível para tirar dúvidas sobre cadastro de produtos, pedidos e pagamentos.</p></div>
            <div class="beneficio"><span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 10 10"/><path d="M8 5.07A10 10 0 0 1 22 12h-3"/><path d="M12 12a5 5 0 0 0 5-5"/><path d="M12 12a5 5 0 0 1-5-5"/></svg></span><h3>Missão Sustentável</h3><p>Fazemos parte de um movimento de valorização da agricultura familiar e do campo brasileiro.</p></div>
        </div>
    </div>
</section>

<!-- FORMULÁRIO -->
<div class="sp-form-wrap" id="formulario">

    <?php if ($statusAtual === 'migracao_pendente'): ?>

    <div class="status-box">
        <div class="icon"><svg style="width:44px;height:44px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
        <h3>Banco de dados desatualizado</h3>
        <p>Esta área depende de uma atualização no banco de dados que ainda não foi aplicada. Peça ao administrador para executar o arquivo de migração mais recente no phpMyAdmin.</p>
        <a href="index.php"><?php echo icon('home'); ?> Voltar à loja</a>
    </div>

    <?php elseif ($statusAtual === 'ja_produtor'): ?>

    <div class="status-box">
        <div class="icon" style="color:#16a34a;"><svg style="width:44px;height:44px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg></div>
        <h3>Sua conta já é uma conta de produtor</h3>
        <p>Você já pode solicitar a publicação de novos produtos diretamente.</p>
        <a href="solicitar_produto.php"><?php echo icon('box'); ?> Solicitar Produto</a>
    </div>

    <?php elseif ($statusAtual === 'pendente'): ?>

    <div class="status-box">
        <div class="icon"><svg style="width:44px;height:44px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg></div>
        <h3>Sua solicitação já está em análise</h3>
        <p>Recebemos seu cadastro e nossa equipe está avaliando. Você será avisado pelo e-mail <strong><?php echo htmlspecialchars($usuarioLogado['email']); ?></strong> assim que houver uma resposta.</p>
        <a href="index.php"><?php echo icon('home'); ?> Voltar à loja</a>
    </div>

    <?php else: ?>

    <div class="sp-form-title">
        <h2>Formulário de Cadastro</h2>
        <p>Preencha com cuidado — quanto mais detalhes, mais rápida a análise</p>
    </div>

    <div id="success-box" class="success-box">
        <span class="icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg></span>
        <h3>Solicitação enviada com sucesso!</h3>
        <p>Nossa equipe analisará seu cadastro em até 5 dias úteis e entrará em contato pelo e-mail informado. Obrigado pelo interesse!</p>
    </div>

    <form class="form-card" id="form-produtor" enctype="multipart/form-data" onsubmit="enviarFormulario(event)">

        <div class="section-label"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg> Dados Pessoais</div>
        <div class="fgrid" style="margin-bottom:24px;">
            <div class="field"><label>Nome completo *</label><input type="text" name="nome" required value="<?php echo htmlspecialchars($usuarioLogado['nome']); ?>" placeholder="João da Silva Santos"></div>
            <div class="field"><label>E-mail da sua conta</label><input type="email" value="<?php echo htmlspecialchars($usuarioLogado['email']); ?>" class="email-locked" readonly></div>
            <div class="field"><label>Telefone / WhatsApp *</label><input type="tel" name="telefone" required placeholder="(47) 99999-9999"></div>
            <div class="field"><label>CPF ou CNPJ</label><input type="text" name="cpf_cnpj" placeholder="Opcional"></div>
            <div class="field fgrid-1"><span class="hint">Sua solicitação fica vinculada à conta com a qual você está logado agora. Se for aprovada, essa mesma conta (e-mail e senha) passa a ter acesso de produtor — você não precisa criar um login novo.</span></div>
        </div>

        <div class="section-label"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Sua Propriedade</div>
        <div class="fgrid" style="margin-bottom:24px;">
            <div class="field"><label>Nome da Fazenda / Sítio *</label><input type="text" name="fazenda" required placeholder="Fazenda Boa Esperança"></div>
            <div class="field"><label>Estado (UF) *</label>
                <select name="estado" required>
                    <option value="">Selecione…</option>
                    <?php foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf): ?>
                    <option value="<?php echo $uf; ?>"><?php echo $uf; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label>Região / Microrregião</label><input type="text" name="regiao" placeholder="Cerrado Mineiro, Mata Atlântica…"></div>
            <div class="field"><label>Município</label><input type="text" name="municipio" placeholder="Patos de Minas, MG"></div>
            <div class="field fgrid-1"><label>Especialidade / Produtos que produz *</label>
                <input type="text" name="especialidade" required placeholder="Café especial, mel de abelhas nativas, pimenta…">
            </div>
        </div>

        <div class="section-label"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg> Foto do Produtor</div>
        <div style="margin-bottom:24px;">
            <label class="photo-upload" for="foto-input">
                <input type="file" id="foto-input" name="foto" accept="image/*" onchange="previewFoto(this)">
                <span class="icon" id="foto-icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></span>
                <div class="txt">Clique para enviar sua foto</div>
                <div class="sub">JPG ou PNG · Máximo 5MB · Foto do produtor ou da propriedade</div>
            </label>
            <img id="foto-preview" src="" style="display:none;width:120px;height:120px;border-radius:50%;object-fit:cover;margin:12px auto 0;display:none;border:3px solid #E8DCC8;">
        </div>

        <div class="section-label"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Sua História</div>
        <div style="margin-bottom:28px;">
            <div class="field">
                <label>Conte sua história e por que quer ser parceiro *</label>
                <textarea name="mensagem" required placeholder="Fale sobre sua propriedade, sua trajetória, como produz, por que quer vender na Origem Brasil, o que torna seu produto especial…" style="min-height:130px;"></textarea>
            </div>
        </div>

        <button type="submit" class="btn-enviar"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 10 10"/><path d="M8 5.07A10 10 0 0 1 22 12h-3"/><path d="M12 12a5 5 0 0 0 5-5"/><path d="M12 12a5 5 0 0 1-5-5"/></svg> Enviar Solicitação de Cadastro</button>
        <p style="text-align:center;font-size:12px;color:#9CA3AF;margin-top:12px;">Após o envio, nossa equipe entrará em contato em até 5 dias úteis.</p>
    </form>

    <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('foto-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('foto-icon').textContent = '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

async function enviarFormulario(e) {
    e.preventDefault();

    const btn = document.querySelector('.btn-enviar');
    btn.textContent = 'Enviando…'; btn.disabled = true;

    const fd = new FormData(document.getElementById('form-produtor'));
    const res = await fetch('api/sol_produtor.php', { method:'POST', body:fd });
    const data = await res.json();

    if (data.ok) {
        document.getElementById('form-produtor').style.display = 'none';
        const sb = document.getElementById('success-box');
        sb.style.display = 'block';
        sb.scrollIntoView({ behavior:'smooth' });
    } else {
        alert(data.erro || 'Erro ao enviar. Tente novamente.');
        btn.textContent = '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M12 22V12"/><path d="M5 12H2a10 10 0 0 0 10 10"/><path d="M8 5.07A10 10 0 0 1 22 12h-3"/><path d="M12 12a5 5 0 0 0 5-5"/><path d="M12 12a5 5 0 0 1-5-5"/></svg> Enviar Solicitação de Cadastro'; btn.disabled = false;
    }
}
</script>
</body></html>
