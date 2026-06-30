<?php include("conexao.php");
if (!isset($_SESSION['id'])) { header("Location: login.php?voltar=favoritos.php"); exit; }
$uid = (int)$_SESSION['id'];
$stmt = mysqli_prepare($conexao,
    "SELECT p.*, f.id as fav_id FROM favoritos f
     JOIN produtos p ON p.id = f.produto_id
     WHERE f.usuario_id = ? ORDER BY f.id DESC"
);
mysqli_stmt_bind_param($stmt,'i',$uid);
mysqli_stmt_execute($stmt);
$favs = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #FDFAF5; color: #1C1208; }

        .checkout-header { background:#fff; border-bottom:1px solid #E8DCC8; padding:18px 40px; display:flex; align-items:center; gap:16px; }
        .checkout-header .logo { font-size:1.2rem; font-weight:700; color:#2C4A2E; text-decoration:none; }
        .checkout-header .sep { color:#E8DCC8; font-size:20px; }
        .checkout-header .page-name { font-size:14px; color:#7A6248; }

        .steps-bar { background:#fff; border-bottom:1px solid #E8DCC8; padding:14px 40px; display:flex; align-items:center; }
        .step { display:flex; align-items:center; gap:8px; font-size:13px; color:#9CA3AF; }
        .step.active { color:#2C4A2E; font-weight:600; }
        .step-num { width:24px; height:24px; border-radius:50%; background:#E8DCC8; color:#7A6248; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; }
        .step.active .step-num { background:#2C4A2E; color:white; }
        .step-line { flex:1; height:1px; background:#E8DCC8; margin:0 12px; max-width:60px; }

        .checkout-layout { max-width:1100px; margin:0 auto; padding:40px 24px 80px; display:grid; grid-template-columns:1fr 380px; gap:32px; }
        .section-card { background:#fff; border:1px solid #E8DCC8; border-radius:16px; padding:28px; margin-bottom:20px; }
        .section-title { font-size:15px; font-weight:600; color:#1C1208; margin-bottom:20px; display:flex; align-items:center; gap:8px; }

        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
        .form-row.full { grid-template-columns:1fr; }
        .form-row.trio { grid-template-columns:2fr 1fr 1fr; }
        .field label { display:block; font-size:12px; font-weight:500; color:#7A6248; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
        .field input, .field select {
            width:100%; padding:11px 14px; border:1px solid #E8DCC8; border-radius:10px;
            font-family:'Poppins',sans-serif; font-size:13px; color:#1C1208;
            background:#FDFAF5; outline:none; transition:border-color .2s;
        }
        .field input:focus, .field select:focus { border-color:#2C4A2E; background:#fff; }

        .delivery-options { display:flex; flex-direction:column; gap:10px; }
        .delivery-opt { display:flex; align-items:center; gap:14px; padding:14px 16px; border:1.5px solid #E8DCC8; border-radius:12px; cursor:pointer; transition:all .2s; }
        .delivery-opt:hover, .delivery-opt.selected { border-color:#2C4A2E; background:#f0f7f1; }
        .delivery-opt input[type="radio"] { accent-color:#2C4A2E; width:16px; height:16px; }
        .delivery-opt .info { flex:1; }
        .delivery-opt .info strong { display:block; font-size:13px; font-weight:600; color:#1C1208; }
        .delivery-opt .info span { font-size:12px; color:#7A6248; }
        .delivery-opt .price { font-size:13px; font-weight:600; color:#2C4A2E; }

        .payment-tabs { display:flex; gap:8px; margin-bottom:20px; }
        .pay-tab { flex:1; padding:10px; border:1.5px solid #E8DCC8; border-radius:10px; background:#FDFAF5; font-family:'Poppins',sans-serif; font-size:12px; font-weight:500; cursor:pointer; color:#7A6248; transition:all .2s; text-align:center; }
        .pay-tab.active { border-color:#2C4A2E; background:#2C4A2E; color:white; }
        .pay-panel { display:none; }
        .pay-panel.active { display:block; }
        .pix-info { text-align:center; padding:24px; background:#f0f7f1; border-radius:12px; }
        .pix-info p { font-size:13px; color:#7A6248; line-height:1.6; }
        .boleto-info { text-align:center; padding:24px; background:#fef9ec; border-radius:12px; }
        .boleto-info p { font-size:13px; color:#7A6248; }

        .order-summary { position:sticky; top:20px; }
        .summary-card { background:#fff; border:1px solid #E8DCC8; border-radius:16px; padding:24px; }
        .summary-title { font-size:15px; font-weight:600; margin-bottom:20px; }
        .summary-item { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; gap:12px; }
        .item-info .item-name { font-size:13px; font-weight:500; line-height:1.35; }
        .item-info .item-qty  { font-size:12px; color:#7A6248; }
        .item-price { font-size:13px; font-weight:600; color:#2C4A2E; white-space:nowrap; }
        .divider-line { height:1px; background:#E8DCC8; margin:16px 0; }
        .summary-row { display:flex; justify-content:space-between; font-size:13px; color:#7A6248; margin-bottom:10px; }
        .summary-row.total { font-size:16px; font-weight:700; color:#1C1208; margin-top:14px; }
        .summary-row.total span:last-child { color:#2C4A2E; }

        .coupon-row { display:flex; gap:8px; margin-bottom:16px; }
        .coupon-row input { flex:1; padding:10px 14px; border:1px solid #E8DCC8; border-radius:10px; font-family:'Poppins',sans-serif; font-size:13px; outline:none; background:#FDFAF5; }
        .coupon-row input:focus { border-color:#2C4A2E; }
        .coupon-row button { padding:10px 16px; border:1px solid #2C4A2E; background:transparent; color:#2C4A2E; border-radius:10px; cursor:pointer; font-family:'Poppins',sans-serif; font-size:12px; font-weight:600; transition:all .2s; }
        .coupon-row button:hover { background:#2C4A2E; color:white; }

        .btn-finalizar { width:100%; padding:15px; background:#2C4A2E; color:white; border:none; border-radius:12px; font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; cursor:pointer; transition:background .2s; margin-top:8px; }
        .btn-finalizar:hover { background:#3D6B40; }
        .btn-finalizar:disabled { background:#9CA3AF; cursor:not-allowed; }

        .security-badges { display:flex; justify-content:center; gap:16px; margin-top:14px; font-size:11px; color:#9CA3AF; }
        .security-badges span { display:flex; align-items:center; gap:4px; }

        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; }
        .modal-overlay.active { display:flex; }
        .modal { background:white; border-radius:20px; padding:48px 40px; text-align:center; max-width:420px; width:90%; animation:popIn .4s cubic-bezier(0.34,1.56,0.64,1); }
        @keyframes popIn { from{opacity:0;transform:scale(0.8)} to{opacity:1;transform:scale(1)} }
        .modal .success-icon { font-size:64px; display:block; margin-bottom:16px; }
        .modal h2 { font-size:1.5rem; color:#1C1208; margin-bottom:8px; }
        .modal p { color:#7A6248; font-size:14px; line-height:1.6; margin-bottom:24px; }
        .modal .order-num { background:#f0f7f1; border-radius:10px; padding:12px; font-weight:700; color:#2C4A2E; font-size:16px; margin-bottom:24px; }
        .modal .btn-voltar { display:inline-block; padding:12px 28px; background:#2C4A2E; color:white; border-radius:10px; text-decoration:none; font-weight:600; }

        @media (max-width:768px) { .checkout-layout { grid-template-columns:1fr; } .form-row { grid-template-columns:1fr; } }
    </style>
</head>
<body data-logado="<?php echo isset($_SESSION['id']) ? '1' : '0'; ?>">

    <header class="checkout-header">
        <a href="index.php" class="logo">Origem Brasil</a>
        <span class="sep">›</span>
        <span class="page-name">Finalizar Compra</span>
    </header>

    <div class="steps-bar">
        <div class="step active">
            <div class="step-num">1</div>
            <span>Endereço</span>
        </div>
        <div class="step-line"></div>
        <div class="step active">
            <div class="step-num">2</div>
            <span>Entrega</span>
        </div>
        <div class="step-line"></div>
        <div class="step active">
            <div class="step-num">3</div>
            <span>Pagamento</span>
        </div>
    </div>

    <div class="checkout-layout">
        <!-- COLUNA ESQUERDA -->
        <div>
            <!-- ENDEREÇO -->
            <div class="section-card">
                <div class="section-title"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Endereço de Entrega</div>
                <div class="form-row full">
                    <div class="field">
                        <label>Nome Completo</label>
                        <input type="text" id="nome" placeholder="Seu nome completo"
                               value="<?php echo isset($_SESSION['nome']) ? htmlspecialchars($_SESSION['nome']) : ''; ?>">
                    </div>
                </div>
                <div class="form-row trio">
                    <div class="field">
                        <label>CEP</label>
                        <input type="text" id="cep" placeholder="00000-000" maxlength="9" oninput="mascararCEP(this)" required>
                    </div>
                    <div class="field">
                        <label>Número</label>
                        <input type="text" id="numero" placeholder="123">
                    </div>
                    <div class="field">
                        <label>Compl.</label>
                        <input type="text" id="complemento" placeholder="Apto">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Rua</label>
                        <input type="text" id="rua" placeholder="Rua, Avenida…">
                    </div>
                    <div class="field">
                        <label>Bairro</label>
                        <input type="text" id="bairro" placeholder="Bairro">
                    </div>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label>Cidade</label>
                        <input type="text" id="cidade" placeholder="Sua cidade">
                    </div>
                    <div class="field">
                        <label>Estado</label>
                        <select id="estado">
                            <option value="">UF</option>
                            <?php
                            $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                            foreach ($ufs as $uf) echo "<option value=\"{$uf}\">{$uf}</option>";
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ENTREGA -->
            <div class="section-card">
                <div class="section-title"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Opções de Entrega</div>
                <div class="delivery-options">
                    <label class="delivery-opt selected" onclick="selectDelivery(this, 0)">
                        <input type="radio" name="frete" value="0" checked>
                        <div class="info">
                            <strong>PAC — Correios</strong>
                            <span>Entrega em 7-12 dias úteis</span>
                        </div>
                        <span class="price">Grátis</span>
                    </label>
                    <label class="delivery-opt" onclick="selectDelivery(this, 19.90)">
                        <input type="radio" name="frete" value="19.90">
                        <div class="info">
                            <strong>SEDEX</strong>
                            <span>Entrega em 2-4 dias úteis</span>
                        </div>
                        <span class="price">R$ 19,90</span>
                    </label>
                    <label class="delivery-opt" onclick="selectDelivery(this, 34.90)">
                        <input type="radio" name="frete" value="34.90">
                        <div class="info">
                            <strong>SEDEX 10</strong>
                            <span>Entrega até às 10h do próximo dia</span>
                        </div>
                        <span class="price">R$ 34,90</span>
                    </label>
                </div>
            </div>

            <!-- PAGAMENTO -->
            <div class="section-card">
                <div class="section-title"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Forma de Pagamento</div>
                <div class="payment-tabs">
                    <button class="pay-tab active" onclick="switchPay('cartao', this)"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Cartão</button>
                    <button class="pay-tab" onclick="switchPay('pix', this)"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg> PIX</button>
                    <button class="pay-tab" onclick="switchPay('boleto', this)"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> Boleto</button>
                </div>
                <input type="hidden" id="pagamento-tipo" value="cartao">

                <div class="pay-panel active" id="pan-cartao">
                    <div class="form-row full"><div class="field">
                        <label>Número do Cartão</label>
                        <input type="text" id="num-cartao" placeholder="0000 0000 0000 0000" maxlength="19" oninput="fmtCartao(this)">
                    </div></div>
                    <div class="form-row full"><div class="field">
                        <label>Nome no Cartão</label>
                        <input type="text" id="nome-cartao" placeholder="NOME SOBRENOME">
                    </div></div>
                    <div class="form-row">
                        <div class="field">
                            <label>Validade</label>
                            <input type="text" id="validade" placeholder="MM/AA" maxlength="5">
                        </div>
                        <div class="field">
                            <label>CVV</label>
                            <input type="text" id="cvv" placeholder="123" maxlength="4">
                        </div>
                    </div>
                    <div class="form-row full"><div class="field">
                        <label>Parcelas</label>
                        <select id="parcelas">
                            <option>1x sem juros</option>
                            <option>2x sem juros</option>
                            <option>3x sem juros</option>
                            <option>6x sem juros</option>
                            <option>12x com juros</option>
                        </select>
                    </div></div>
                </div>

                <div class="pay-panel" id="pan-pix">
                    <div class="pix-info">
                        <span style="font-size:48px;display:block;margin-bottom:8px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg></span>
                        <p>Após confirmar o pedido, você receberá o <strong>QR Code PIX</strong>.<br>O pedido é confirmado em até <strong>5 minutos</strong>.</p>
                    </div>
                </div>

                <div class="pay-panel" id="pan-boleto">
                    <div class="boleto-info">
                        <span style="font-size:48px;display:block;margin-bottom:8px;"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></span>
                        <p>O boleto será gerado após a confirmação. Prazo: <strong>3 dias úteis</strong>.<br>A entrega inicia após a compensação.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESUMO -->
        <div class="order-summary">
            <div class="summary-card">
                <div class="summary-title"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Resumo do Pedido</div>
                <div id="summary-items">
                    <div style="text-align:center;color:#9CA3AF;padding:20px">Carregando…</div>
                </div>
                <div class="divider-line"></div>
                <div class="coupon-row">
                    <input type="text" id="coupon-input" placeholder="Cupom de desconto">
                    <button onclick="aplicarCupom()">Aplicar</button>
                </div>
                <div class="summary-row"><span>Subtotal</span><span id="sum-subtotal">R$ 0,00</span></div>
                <div class="summary-row"><span>Frete</span><span id="sum-frete">Grátis</span></div>
                <div class="summary-row"><span>Desconto</span><span id="sum-desconto" style="color:#16a34a">-R$ 0,00</span></div>
                <div class="divider-line"></div>
                <div class="summary-row total"><span>Total</span><span id="sum-total">R$ 0,00</span></div>
                <button class="btn-finalizar" id="btn-finalizar" onclick="finalizarPedido()" disabled>
                    Finalizar Pedido
                </button>
                <div class="security-badges">
                    <span><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Seguro</span>
                    <span><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg> Garantia</span>
                    <span><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Rastreado</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal sucesso -->
    <div class="modal-overlay" id="success-modal">
        <div class="modal">
            <span class="success-icon"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></span>
            <h2>Pedido Realizado!</h2>
            <p>Seu pedido foi confirmado com sucesso. Você pode acompanhá-lo em <strong>Meus Pedidos</strong>.</p>
            <div class="order-num" id="order-num"></div>
            <a href="pedidos.php" class="btn-voltar" style="margin-right:10px">Ver Pedido</a>
            <a href="index.php" class="btn-voltar" style="background:#7A6248;">Início</a>
        </div>
    </div>

    <script src="js/carrinho.js"></script>
    <script>
    let freteValor    = 0;
    let descontoValor = 0;
    let itensCarrinho = [];

    async function renderSummary() {
        const container = document.getElementById('summary-items');
        if (!container) return;

        if (!estaLogado()) {
            container.innerHTML = `<div style="text-align:center;padding:30px">
                <p style="color:#7A6248;margin-bottom:16px">Faça login para finalizar.</p>
                <a href="login.php?voltar=checkout.php" style="display:inline-block;padding:10px 24px;background:#2C4A2E;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Entrar</a>
            </div>`;
            document.getElementById('btn-finalizar').disabled = true;
            return;
        }

        container.innerHTML = '<div style="text-align:center;padding:20px;color:#9CA3AF">Carregando…</div>';
        const itens = await apiGet('listar');
        if (!itens) return;
        itensCarrinho = itens;

        if (itens.length === 0) {
            container.innerHTML = `<div style="text-align:center;padding:30px">
                <span style="font-size:40px"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;stroke:currentColor;fill:none;stroke-width:2;" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></span>
                <p style="color:#7A6248;margin-top:12px">Seu carrinho está vazio.</p>
                <a href="index.php" style="display:inline-block;margin-top:12px;padding:10px 24px;background:#2C4A2E;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Ver Produtos</a>
            </div>`;
            document.getElementById('btn-finalizar').disabled = true;
            updateTotals(0);
            return;
        }

        let subtotal = 0;
        container.innerHTML = itens.map(item => {
            const sub = item.preco * item.quantidade;
            subtotal += sub;
            return `<div class="summary-item">
                <div class="item-info">
                    <div class="item-name">${item.nome}</div>
                    <div class="item-qty">Qtd: ${item.quantidade}</div>
                </div>
                <div class="item-price">R$ ${fmtBRL(sub)}</div>
            </div>`;
        }).join('');

        updateTotals(subtotal);
        document.getElementById('btn-finalizar').disabled = false;
    }

    function updateTotals(subtotal) {
        const total = Math.max(0, subtotal + freteValor - descontoValor);
        document.getElementById('sum-subtotal').textContent = 'R$ ' + fmtBRL(subtotal);
        document.getElementById('sum-frete').textContent    = freteValor === 0 ? 'Grátis' : 'R$ ' + fmtBRL(freteValor);
        document.getElementById('sum-desconto').textContent = '-R$ ' + fmtBRL(descontoValor);
        document.getElementById('sum-total').textContent    = 'R$ ' + fmtBRL(total);
    }

    function selectDelivery(el, valor) {
        document.querySelectorAll('.delivery-opt').forEach(d => d.classList.remove('selected'));
        el.classList.add('selected');
        freteValor = parseFloat(valor);
        const sub = itensCarrinho.reduce((s,i) => s + i.preco * i.quantidade, 0);
        updateTotals(sub);
    }

    function switchPay(tipo, btn) {
        document.querySelectorAll('.pay-tab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.pay-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('pan-' + tipo).classList.add('active');
        document.getElementById('pagamento-tipo').value = tipo;
    }

    function fmtCartao(input) {
        let v = input.value.replace(/\D/g,'').substring(0,16);
        input.value = v.replace(/(.{4})/g,'$1 ').trim();
    }

    function mascararCEP(input) {
        let v = input.value.replace(/\D/g,'').substring(0,8);
        if (v.length > 5) v = v.slice(0,5) + '-' + v.slice(5);
        input.value = v;
    }

    async function aplicarCupom() {
        const code = document.getElementById('coupon-input').value.trim().toUpperCase();
        const sub  = itensCarrinho.reduce((s,i) => s + i.preco * i.quantidade, 0);
        if (code === 'ORIGEM10') {
            descontoValor = sub * 0.10;
            showToast('Cupom aplicado! 10% de desconto.', 'success');
        } else if (code === 'FRETEGRATIS') {
            freteValor = 0;
            showToast('Frete grátis aplicado!', 'success');
        } else {
            showToast('Cupom inválido.', 'error');
            return;
        }
        updateTotals(sub);
    }

    async function finalizarPedido() {
        if (!estaLogado()) { pedirLogin(); return; }
        if (itensCarrinho.length === 0) { showToast('Seu carrinho está vazio!', 'warning'); return; }

        const nome = document.getElementById('nome').value.trim();
        if (!nome) { showToast('Preencha seu nome.', 'warning'); return; }

        const btn = document.getElementById('btn-finalizar');
        btn.disabled = true;
        btn.textContent = 'Processando…';

        const fd = new FormData();
        fd.append('action', 'finalizar');
        fd.append('frete',    freteValor);
        fd.append('desconto', descontoValor);
        fd.append('pagamento', document.getElementById('pagamento-tipo').value);

        const res = await fetch('api/checkout.php', { method: 'POST', body: fd });
        const data = await res.json();

        btn.disabled = false;
        btn.textContent = 'Finalizar Pedido';

        if (data.ok) {
            document.getElementById('order-num').textContent = 'Pedido #' + data.numero;
            document.getElementById('success-modal').classList.add('active');
        } else if (data.erro === 'sem_estoque') {
            showToast(`Estoque insuficiente: ${data.produto}`, 'error');
        } else if (data.erro === 'carrinho_vazio') {
            showToast('Seu carrinho está vazio!', 'warning');
        } else {
            showToast(data.erro || 'Erro ao finalizar pedido.', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', renderSummary);
    </script>
</body>
</html>
