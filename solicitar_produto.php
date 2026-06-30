<?php
include("conexao.php");

// Esta página é exclusiva para usuários logados com conta de PRODUTOR.
if (!isset($_SESSION['id'])) {
    header("Location: login.php?voltar=solicitar_produto.php");
    exit;
}

// Confere se a migração (migracao_produtor.sql) já foi executada no banco.
$chkMigracao = mysqli_query($conexao, "SHOW COLUMNS FROM produtores LIKE 'usuario_id'");
$migracaoPendente = !$chkMigracao || mysqli_num_rows($chkMigracao) === 0;

$restrito = ($_SESSION['tipo'] ?? '') !== 'produtor';
$produtor = null;
$categorias = [];

if (!$restrito && !$migracaoPendente) {
    $stmt = mysqli_prepare($conexao, "SELECT * FROM produtores WHERE usuario_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $produtor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    // Categorias já existentes no site — o produtor escolhe uma delas ou pede uma nova
    $categorias = mysqli_fetch_all(mysqli_query($conexao,
        "SELECT slug, label FROM categorias ORDER BY label ASC"
    ), MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Solicitar Publicação de Produto — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .page-wrap { max-width:760px;margin:0 auto;padding:48px 24px 80px; }
        .page-header { text-align:center;margin-bottom:40px; }
        .page-header h1 { font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:var(--text);margin-bottom:8px; }
        .page-header p  { font-size:14px;color:var(--text-3);line-height:1.7; }
        .produtor-badge {
            display:flex;align-items:center;gap:12px;
            background:var(--green-light);border:1px solid #c8e0cc;border-radius:12px;
            padding:14px 18px;margin-bottom:32px;
        }
        .produtor-badge img { width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border); }
        .produtor-badge .initial { width:48px;height:48px;border-radius:50%;background:var(--green-2);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;flex-shrink:0; }
        .produtor-badge strong { display:block;font-size:14px;font-weight:700;color:var(--text); }
        .produtor-badge span { font-size:12px;color:var(--text-3); }
        .form-card { background:#fff;border:1px solid var(--border);border-radius:20px;padding:32px;box-shadow:var(--shadow); }
        .sec-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--green-2);margin-bottom:14px;padding-bottom:8px;border-bottom:2px solid var(--border); }
        .fgrid { display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px; }
        .fgrid-1 { grid-column:1/-1; }
        .field { display:flex;flex-direction:column;gap:4px; }
        .field label { font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em; }
        .field input,.field select,.field textarea { padding:10px 13px;border:1.5px solid var(--border);border-radius:10px;font-family:'Inter',sans-serif;font-size:13px;outline:none;background:var(--cream);color:var(--text);transition:border-color .15s; }
        .field input:focus,.field select:focus,.field textarea:focus { border-color:var(--green-2);background:#fff; }
        .field textarea { resize:vertical;min-height:90px; }
        .field .hint { font-size:11px;color:var(--text-4); }
        .photo-upload { border:2px dashed var(--border);border-radius:12px;padding:24px;text-align:center;background:var(--cream);cursor:pointer;transition:all .2s;margin-bottom:22px; }
        .photo-upload:hover { border-color:var(--green-2);background:var(--green-light); }
        .photo-upload input { display:none; }
        .btn-send { width:100%;padding:16px;background:linear-gradient(135deg,#1C3320,#2C4A2E);color:#fff;border:none;border-radius:12px;font-family:'Inter',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:transform .2s;display:flex;align-items:center;justify-content:center;gap:8px; }
        .btn-send:hover { transform:translateY(-1px); }
        .btn-send:disabled { opacity:.7;cursor:default;transform:none; }
        .success-box { display:none;text-align:center;padding:40px;background:var(--green-light);border:1px solid #bbf7d0;border-radius:16px;margin-bottom:20px; }
        .success-box h3 { font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:#166534;margin-bottom:8px; }
        .success-box p { font-size:14px;color:#166534; }
        .nova-cat-wrap { display:none;margin-top:10px; }
        .nova-cat-wrap.show { display:block; }
        .restrito-box, .aviso-box {
            max-width:600px;margin:80px auto;padding:48px 36px;text-align:center;
            background:#fff;border:1px solid var(--border);border-radius:20px;box-shadow:var(--shadow);
        }
        .restrito-box .icon, .aviso-box .icon { color:var(--orange);margin-bottom:16px; }
        .restrito-box h2, .aviso-box h2 { font-family:'Playfair Display',serif;font-size:24px;color:var(--text);margin-bottom:12px; }
        .restrito-box p, .aviso-box p { font-size:14px;color:var(--text-3);line-height:1.7;margin-bottom:24px; }
        .restrito-box .botoes { display:flex;gap:12px;justify-content:center;flex-wrap:wrap; }
        .btn-pill { display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:30px;font-size:13px;font-weight:700;text-decoration:none;transition:transform .2s; }
        .btn-pill:hover { transform:translateY(-1px); }
        .btn-pill.green { background:var(--green-2);color:#fff; }
        .btn-pill.outline { background:transparent;color:var(--green-2);border:1.5px solid var(--green-2); }
        @media(max-width:640px){ .fgrid{grid-template-columns:1fr;} .page-wrap{padding:24px 16px 60px;} .form-card{padding:20px;} }
    </style>
</head>
<body>
<?php include("includes/header.php"); ?>

<?php if ($migracaoPendente): ?>

<div class="aviso-box">
    <div class="icon"><svg style="width:48px;height:48px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
    <h2>Banco de dados desatualizado</h2>
    <p>Esta área depende de uma atualização no banco de dados que ainda não foi aplicada. Peça ao administrador para executar o arquivo <strong>migracao_produtor.sql</strong> (na raiz do projeto) no phpMyAdmin.</p>
    <a href="index.php" class="btn-pill outline"><?php echo icon('home'); ?> Voltar à loja</a>
</div>

<?php elseif ($restrito): ?>

<div class="restrito-box">
    <div class="icon"><svg style="width:48px;height:48px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
    <h2>Área exclusiva para Produtores</h2>
    <p>A solicitação de publicação de produtos é reservada a contas de produtor aprovadas. Sua conta atual é de cliente. Se você é um produtor rural e quer vender na Origem Brasil, cadastre-se no formulário "Seja um Produtor".</p>
    <div class="botoes">
        <a href="seja_produtor.php" class="btn-pill green"><?php echo icon('plus'); ?> Seja um Produtor</a>
        <a href="index.php" class="btn-pill outline"><?php echo icon('home'); ?> Voltar à loja</a>
    </div>
</div>

<?php elseif (!$produtor): ?>

<div class="aviso-box">
    <div class="icon"><svg style="width:48px;height:48px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
    <h2>Cadastro de produtor não encontrado</h2>
    <p>Sua conta tem acesso de produtor, mas não encontramos um perfil de produtor vinculado a ela. Fale com nossa equipe para resolver isso.</p>
    <a href="https://wa.me/5547996280485?text=Olá!%20Minha%20conta%20de%20produtor%20não%20está%20vinculada%20a%20um%20perfil." class="btn-pill green" target="_blank"><?php echo icon('user'); ?> Falar com o Suporte</a>
</div>

<?php else: ?>

<div class="page-wrap">
    <div class="page-header">
        <h1><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Solicitar Publicação de Produto</h1>
        <p>Preencha os detalhes do produto que deseja anunciar na Origem Brasil.<br>Nossa equipe analisará e publicará em até 3 dias úteis.</p>
    </div>

    <div class="produtor-badge">
        <?php if (!empty($produtor['foto'])): ?>
        <img src="<?php echo htmlspecialchars($produtor['foto']); ?>" onerror="this.style.display='none'">
        <?php else: ?>
        <div class="initial"><?php echo mb_strtoupper(mb_substr($produtor['nome'],0,1)); ?></div>
        <?php endif; ?>
        <div>
            <strong><?php echo icon('user'); ?> <?php echo htmlspecialchars($produtor['nome']); ?></strong>
            <span><?php echo htmlspecialchars($produtor['fazenda'] ?? ''); ?><?php echo $produtor['fazenda'] && $produtor['estado'] ? ' — ' : ''; ?><?php echo htmlspecialchars($produtor['estado'] ?? ''); ?></span>
        </div>
    </div>

    <div id="success-box" class="success-box">
        <div style="margin-bottom:12px;"><svg style="width:48px;height:48px;color:#16a34a;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
        <h3>Solicitação enviada!</h3>
        <p id="success-msg">Nossa equipe analisará e entrará em contato em até 3 dias úteis.</p>
    </div>

    <form class="form-card" id="form-produto" enctype="multipart/form-data" onsubmit="enviar(event)">

        <div class="sec-label"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg> Informações do Produto</div>
        <div class="fgrid">
            <div class="field fgrid-1"><label>Nome do Produto *</label><input type="text" name="nome" required placeholder="Ex: Mel de Jataí Silvestre"></div>
            <div class="field fgrid-1">
                <label>Categoria *</label>
                <select name="categoria" id="categoria-select" required onchange="alternarNovaCategoria()">
                    <option value="">Selecione…</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['slug']); ?>"><?php echo htmlspecialchars($cat['label']); ?></option>
                    <?php endforeach; ?>
                    <option value="outra">+ Outra Categoria</option>
                </select>
                <div class="nova-cat-wrap" id="nova-cat-wrap">
                    <input type="text" name="nova_categoria_nome" id="nova-categoria-nome" placeholder="Digite o nome da nova categoria. Ex: Castanhas">
                    <span class="hint">Sua sugestão de categoria será enviada para a análise do administrador junto com o produto.</span>
                </div>
            </div>
            <div class="field"><label>Preço Sugerido (R$) *</label><input type="number" name="preco" step="0.01" min="0" required placeholder="49.90"></div>
            <div class="field"><label>Peso / Quantidade</label><input type="text" name="peso" placeholder="Ex: 300g, 1kg, 500ml"></div>
            <div class="field"><label>Região de Origem</label><input type="text" name="regiao" placeholder="Cerrado Mineiro, Mata Atlântica…"></div>
            <div class="field"><label>Município / Estado</label><input type="text" name="origem" placeholder="Patos de Minas, MG"></div>
            <div class="field"><label>Estoque Inicial (un.)</label><input type="number" name="estoque_inicial" min="0" value="0"></div>
            <div class="field fgrid-1"><label>Descrição Curta *</label><textarea name="descricao" required placeholder="Descreva o produto em 1-2 frases…"></textarea></div>
            <div class="field fgrid-1"><label>História / Processo de Produção</label><textarea name="historia" style="min-height:120px;" placeholder="Como é produzido, de onde vem a matéria-prima, o que torna especial…"></textarea></div>
        </div>

        <div class="sec-label"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg> Foto do Produto *</div>
        <label class="photo-upload" for="foto-prod">
            <input type="file" id="foto-prod" name="foto" accept="image/*" required onchange="prevFoto(this)">
            <div style="font-size:32px;margin-bottom:8px;" id="foto-icon-prod"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></div>
            <div style="font-size:13px;color:var(--text-3);font-weight:500;">Clique para enviar a foto do produto</div>
            <div style="font-size:11px;color:var(--text-4);margin-top:4px;">JPG ou PNG · Boa iluminação, fundo limpo · Máx 8MB</div>
        </label>
        <img id="foto-prev-prod" style="width:160px;height:160px;object-fit:cover;border-radius:12px;border:2px solid var(--border);margin:0 auto 24px;display:none;">

        <button type="submit" class="btn-send"><svg style="width:1em;height:1em;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg> Enviar Solicitação</button>
        <p style="text-align:center;font-size:12px;color:var(--text-4);margin-top:10px;">Após aprovação, o produto será publicado automaticamente na loja.</p>
    </form>
</div>

<?php endif; ?>

<?php include("includes/footer.php"); ?>
<script>
function alternarNovaCategoria() {
    const sel = document.getElementById('categoria-select');
    const wrap = document.getElementById('nova-cat-wrap');
    const input = document.getElementById('nova-categoria-nome');
    if (sel.value === 'outra') {
        wrap.classList.add('show');
        input.required = true;
    } else {
        wrap.classList.remove('show');
        input.required = false;
        input.value = '';
    }
}

function prevFoto(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => {
            const p = document.getElementById('foto-prev-prod');
            p.src = e.target.result; p.style.display='block';
            document.getElementById('foto-icon-prod').innerHTML = '<svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>';
        };
        r.readAsDataURL(input.files[0]);
    }
}

async function enviar(e) {
    e.preventDefault();
    const sel = document.getElementById('categoria-select');
    if (sel.value === 'outra' && !document.getElementById('nova-categoria-nome').value.trim()) {
        alert('Digite o nome da nova categoria que você está sugerindo.');
        return;
    }
    const btn = document.querySelector('.btn-send');
    const original = btn.innerHTML;
    btn.innerHTML = 'Enviando…'; btn.disabled = true;
    const fd = new FormData(document.getElementById('form-produto'));
    try {
        const res = await fetch('api/sol_produto.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.ok) {
            document.getElementById('form-produto').style.display='none';
            if (sel.value === 'outra') {
                document.getElementById('success-msg').textContent =
                    'Recebemos seu produto e a sugestão de nova categoria. Nossa equipe vai analisar ambos e entrar em contato em até 3 dias úteis.';
            }
            const sb = document.getElementById('success-box');
            sb.style.display='block';
            sb.scrollIntoView({behavior:'smooth'});
        } else {
            alert(data.erro || 'Erro ao enviar.');
            btn.innerHTML = original; btn.disabled = false;
        }
    } catch (err) {
        alert('Erro de conexão. Tente novamente.');
        btn.innerHTML = original; btn.disabled = false;
    }
}
</script>
</body></html>
