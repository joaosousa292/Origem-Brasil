<?php include("conexao.php");
if (!isset($_SESSION['id'])) { header("Location: login.php?voltar=checkout.php"); exit; }
$uid  = (int)$_SESSION['id'];
$user = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT nome,email,telefone FROM usuarios WHERE id=$uid"));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Finalizar Compra — Origem Brasil</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --soil:#1A1208; --green:#2C4A2E; --green2:#3D6B40;
  --clay:#C4531F; --clay-bg:#FBF0EB;
  --cream:#F5F2ED; --white:#fff;
  --border:#E5DED4; --border2:#D6CCC0;
  --ink:#1C1208; --ink2:#5C5046; --dust:#9E9080;
  --r:12px; --shadow:0 2px 8px rgba(26,18,8,.07),0 8px 28px rgba(26,18,8,.07);
}
body{font-family:'Inter',sans-serif;background:var(--cream);color:var(--ink);min-height:100vh;font-size:14px;}
a{text-decoration:none;color:inherit;}

/* ── HEADER ── */
.ck-header{
  background:var(--soil);padding:0 40px;height:58px;
  display:flex;align-items:center;justify-content:space-between;
}
.ck-logo{font-family:'Playfair Display',serif;font-size:18px;color:#fff;font-weight:700;}
.ck-logo span{color:var(--clay);font-size:11px;font-weight:400;font-family:'Inter',sans-serif;margin-left:10px;letter-spacing:.08em;text-transform:uppercase;}
.ck-secure{display:flex;align-items:center;gap:6px;font-size:11px;color:rgba(255,255,255,.45);}
.ck-secure svg{width:13px;height:13px;stroke:#4ade80;fill:none;stroke-width:2.5;}

/* ── BREADCRUMB ── */
.ck-trail{background:var(--white);border-bottom:1px solid var(--border);padding:12px 40px;}
.ck-trail a{font-size:12px;color:var(--dust);} .ck-trail a:hover{color:var(--green);}
.ck-trail span{font-size:12px;color:var(--dust);margin:0 6px;}

/* ── LAYOUT ── */
.ck-body{max-width:1120px;margin:0 auto;padding:36px 24px 80px;display:grid;grid-template-columns:1fr 368px;gap:28px;align-items:start;}

/* ── SECTION CARD ── */
.sc{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px;margin-bottom:16px;box-shadow:var(--shadow);}
.sc-title{display:flex;align-items:center;gap:12px;margin-bottom:24px;}
.sc-num{
  width:28px;height:28px;border-radius:50%;background:var(--green);
  color:#fff;font-size:12px;font-weight:700;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.sc-title h2{font-family:'Playfair Display',serif;font-size:17px;font-weight:600;color:var(--ink);}

/* ── FORMS ── */
.fg{display:grid;gap:14px;margin-bottom:14px;}
.fg-2{grid-template-columns:1fr 1fr;}
.fg-3{grid-template-columns:2fr 1fr 1fr;}
.fg-cep{grid-template-columns:180px 1fr 1fr;}
.f{display:flex;flex-direction:column;gap:5px;}
.f label{font-size:10px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--dust);}
.f label .req{color:var(--clay);margin-left:2px;}
.f input,.f select{
  padding:11px 14px;border:1.5px solid var(--border);border-radius:9px;
  font-family:'Inter',sans-serif;font-size:13px;color:var(--ink);background:var(--cream);
  outline:none;transition:border-color .18s,background .18s;
}
.f input:focus,.f select:focus{border-color:var(--green);background:var(--white);}
.f input.err{border-color:var(--clay);background:#fff7f5;}
.f .err-msg{font-size:11px;color:var(--clay);display:none;}
.f input.err ~ .err-msg,.f select.err ~ .err-msg{display:block;}

/* ── FRETE ── */
.frete-opts{display:flex;flex-direction:column;gap:10px;}
.frete-opt{
  display:flex;align-items:center;gap:14px;padding:14px 16px;
  border:1.5px solid var(--border);border-radius:10px;cursor:pointer;transition:all .18s;
}
.frete-opt:hover{border-color:var(--green2);background:#f6faf6;}
.frete-opt.sel{border-color:var(--green);background:#f0f7f0;}
.frete-opt input[type="radio"]{accent-color:var(--green);width:16px;height:16px;flex-shrink:0;}
.frete-opt .fi{flex:1;}
.frete-opt .fi strong{display:block;font-size:13px;font-weight:600;}
.frete-opt .fi span{font-size:12px;color:var(--dust);}
.frete-opt .fp{font-size:13px;font-weight:700;color:var(--green);}
.frete-opt.sel .fp{color:var(--green);}

/* ── PAGAMENTO TABS ── */
.pay-tabs{display:flex;gap:8px;margin-bottom:20px;}
.pay-tab{
  flex:1;padding:11px 10px;border:1.5px solid var(--border);border-radius:10px;
  background:var(--cream);font-family:'Inter',sans-serif;
  font-size:12px;font-weight:600;cursor:pointer;color:var(--dust);
  transition:all .18s;display:flex;align-items:center;justify-content:center;gap:7px;
}
.pay-tab svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;}
.pay-tab:hover{border-color:var(--green2);color:var(--ink);}
.pay-tab.on{border-color:var(--green);background:var(--green);color:#fff;}
.pay-panel{display:none;}
.pay-panel.on{display:block;}

/* cartão */
.card-visual{
  background:linear-gradient(135deg,var(--soil) 0%,#2C4A2E 100%);
  border-radius:14px;padding:22px 24px;margin-bottom:20px;
  position:relative;overflow:hidden;
}
.card-visual::before{
  content:'';position:absolute;top:-40px;right:-40px;
  width:160px;height:160px;border-radius:50%;
  background:rgba(255,255,255,.04);
}
.card-visual .cv-num{font-size:17px;letter-spacing:.22em;color:rgba(255,255,255,.9);margin:16px 0 20px;font-family:'Inter',sans-serif;font-weight:300;}
.card-visual .cv-row{display:flex;justify-content:space-between;align-items:flex-end;}
.card-visual .cv-label{font-size:9px;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.4);margin-bottom:3px;}
.card-visual .cv-val{font-size:13px;color:rgba(255,255,255,.85);font-weight:500;}
.card-chip{width:36px;height:28px;background:linear-gradient(135deg,#d4a843,#f0c86a);border-radius:5px;margin-bottom:4px;}
.card-brand{position:absolute;bottom:20px;right:24px;font-size:11px;color:rgba(255,255,255,.3);font-weight:700;letter-spacing:.04em;}

/* PIX */
.pix-wrap{display:flex;gap:28px;align-items:flex-start;}
.pix-qr-area{
  display:flex;flex-direction:column;align-items:center;gap:12px;
  background:var(--cream);border:1px solid var(--border);border-radius:12px;padding:20px;flex-shrink:0;
}
.pix-qr-area img{width:160px;height:160px;border-radius:6px;display:block;}
.pix-qr-area .pix-timer{font-size:11px;color:var(--clay);font-weight:600;}
.pix-info-col{flex:1;}
.pix-info-col h4{font-family:'Playfair Display',serif;font-size:15px;color:var(--ink);margin-bottom:10px;}
.pix-info-col p{font-size:12px;color:var(--ink2);line-height:1.7;margin-bottom:14px;}
.pix-key-box{
  background:var(--cream);border:1.5px dashed var(--border2);border-radius:9px;
  padding:11px 14px;display:flex;align-items:center;justify-content:space-between;gap:10px;
}
.pix-key-box span{font-size:12px;font-weight:600;color:var(--ink);font-family:'Inter',sans-serif;}
.pix-copy{
  background:var(--green);color:#fff;border:none;border-radius:7px;
  padding:7px 14px;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;
  transition:background .15s;
}
.pix-copy:hover{background:var(--green2);}
.pix-steps{margin-top:16px;display:flex;flex-direction:column;gap:8px;}
.pix-step{display:flex;align-items:flex-start;gap:10px;font-size:12px;color:var(--ink2);}
.pix-step-n{
  width:20px;height:20px;border-radius:50%;background:var(--green);
  color:#fff;font-size:10px;font-weight:700;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}

/* BOLETO */
.boleto-wrap{text-align:center;padding:8px 0;}
.boleto-bar{
  display:flex;gap:1px;justify-content:center;margin-bottom:16px;
  height:48px;overflow:hidden;border-radius:6px;background:#fff;padding:8px 16px;border:1px solid var(--border);
}
.boleto-bar div{width:2px;background:var(--soil);border-radius:1px;}
.boleto-bar div:nth-child(3n){width:4px;}
.boleto-bar div:nth-child(5n){width:1px;background:var(--border);}
.boleto-info-text{font-size:12px;color:var(--ink2);line-height:1.7;}

/* ── RESUMO LATERAL ── */
.sum-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:24px;box-shadow:var(--shadow);position:sticky;top:24px;}
.sum-title{font-family:'Playfair Display',serif;font-size:17px;font-weight:600;color:var(--ink);margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--border);}
#sum-items{margin-bottom:16px;}
.si{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;}
.si-img{width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--border);flex-shrink:0;background:var(--cream);}
.si-body{flex:1;min-width:0;}
.si-name{font-size:13px;font-weight:600;color:var(--ink);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.si-prod{font-size:11px;color:var(--dust);margin-top:1px;}
.si-qty{font-size:11px;color:var(--dust);margin-top:2px;}
.si-price{font-size:13px;font-weight:700;color:var(--green);white-space:nowrap;flex-shrink:0;}

.div-line{height:1px;background:var(--border);margin:16px 0;}
.coupon-row{display:flex;gap:8px;margin-bottom:16px;}
.coupon-row input{
  flex:1;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;
  font-family:'Inter',sans-serif;font-size:13px;outline:none;background:var(--cream);
}
.coupon-row input:focus{border-color:var(--green);background:var(--white);}
.coupon-row button{
  padding:10px 14px;border:1.5px solid var(--green);background:transparent;
  color:var(--green);border-radius:9px;cursor:pointer;font-size:12px;font-weight:700;
  transition:all .18s;white-space:nowrap;
}
.coupon-row button:hover{background:var(--green);color:#fff;}

.sum-row{display:flex;justify-content:space-between;font-size:13px;color:var(--dust);margin-bottom:9px;}
.sum-row.tot{font-size:16px;font-weight:700;color:var(--ink);margin-top:6px;}
.sum-row.tot span:last-child{color:var(--green);font-size:19px;}
.sum-row .saving{color:#16a34a;font-weight:600;}

.btn-fin{
  width:100%;padding:15px;margin-top:16px;
  background:var(--green);color:#fff;border:none;border-radius:10px;
  font-family:'Inter',sans-serif;font-size:14px;font-weight:700;
  cursor:pointer;transition:background .18s;
  display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-fin svg{width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2.5;}
.btn-fin:hover{background:var(--green2);}
.btn-fin:disabled{background:#b5bdb5;cursor:not-allowed;}
.sec-badges{display:flex;justify-content:center;gap:14px;margin-top:14px;}
.sec-badge{display:flex;align-items:center;gap:5px;font-size:10px;color:var(--dust);}
.sec-badge svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;}

/* ── MODAL SUCESSO ── */
.overlay{display:none;position:fixed;inset:0;background:rgba(26,18,8,.55);z-index:2000;align-items:center;justify-content:center;backdrop-filter:blur(3px);}
.overlay.on{display:flex;}
.modal{
  background:var(--white);border-radius:20px;padding:48px 40px;text-align:center;
  max-width:420px;width:90%;animation:pop .4s cubic-bezier(.34,1.56,.64,1);
}
@keyframes pop{from{opacity:0;transform:scale(.85)}to{opacity:1;transform:scale(1)}}
.modal-icon{
  width:72px;height:72px;border-radius:50%;
  background:linear-gradient(135deg,var(--green),var(--green2));
  display:flex;align-items:center;justify-content:center;margin:0 auto 20px;
}
.modal-icon svg{width:36px;height:36px;stroke:#fff;fill:none;stroke-width:2.5;}
.modal h2{font-family:'Playfair Display',serif;font-size:24px;color:var(--ink);margin-bottom:8px;}
.modal p{color:var(--ink2);font-size:13px;line-height:1.7;margin-bottom:20px;}
.modal .onum{background:var(--cream);border-radius:9px;padding:12px;font-weight:700;color:var(--green);font-size:15px;margin-bottom:24px;border:1px solid var(--border);}
.modal-btns{display:flex;gap:10px;justify-content:center;}
.modal-btns a{padding:11px 24px;border-radius:10px;font-size:13px;font-weight:700;transition:all .18s;}
.modal-btns .mb-p{background:var(--green);color:#fff;} .modal-btns .mb-p:hover{background:var(--green2);}
.modal-btns .mb-s{background:var(--cream);color:var(--ink2);border:1px solid var(--border);} .modal-btns .mb-s:hover{background:var(--border);}

/* ── EMPTY / RESPONSIVE ── */
.empty-ck{text-align:center;padding:48px 20px;color:var(--dust);}
.empty-ck svg{width:48px;height:48px;stroke:var(--border2);fill:none;stroke-width:1.5;margin-bottom:14px;}
@media(max-width:860px){.ck-body{grid-template-columns:1fr;}.fg-2,.fg-3,.fg-cep{grid-template-columns:1fr;}.pix-wrap{flex-direction:column;align-items:center;}}
</style>
</head>
<body>

<header class="ck-header">
  <a href="index.php" class="ck-logo">Origem Brasil <span>Checkout</span></a>
  <div class="ck-secure">
    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Compra 100% segura e criptografada
  </div>
</header>

<div class="ck-trail">
  <a href="index.php">Início</a><span>›</span>
  <a href="produtos.php">Produtos</a><span>›</span>
  <span style="color:var(--ink);font-weight:500;">Finalizar Compra</span>
</div>

<div class="ck-body">

  <!-- ── COLUNA ESQUERDA ── -->
  <div>

    <!-- 1. DADOS PESSOAIS -->
    <div class="sc">
      <div class="sc-title">
        <div class="sc-num">1</div>
        <h2>Dados Pessoais</h2>
      </div>
      <div class="fg fg-2">
        <div class="f">
          <label>Nome Completo <span class="req">*</span></label>
          <input id="nome" type="text" placeholder="Seu nome completo"
                 value="<?php echo htmlspecialchars($user['nome'] ?? ''); ?>" required>
          <span class="err-msg">Campo obrigatório</span>
        </div>
        <div class="f">
          <label>CPF <span class="req">*</span></label>
          <input id="cpf" type="text" placeholder="000.000.000-00" maxlength="14" oninput="maskCPF(this)" required>
          <span class="err-msg">CPF inválido</span>
        </div>
      </div>
      <div class="fg fg-2">
        <div class="f">
          <label>E-mail <span class="req">*</span></label>
          <input id="email" type="email" placeholder="seu@email.com"
                 value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
          <span class="err-msg">E-mail inválido</span>
        </div>
        <div class="f">
          <label>Telefone / WhatsApp <span class="req">*</span></label>
          <input id="telefone" type="text" placeholder="(00) 00000-0000" maxlength="15"
                 value="<?php echo htmlspecialchars($user['telefone'] ?? ''); ?>" oninput="maskPhone(this)" required>
          <span class="err-msg">Campo obrigatório</span>
        </div>
      </div>
    </div>

    <!-- 2. ENDEREÇO -->
    <div class="sc">
      <div class="sc-title">
        <div class="sc-num">2</div>
        <h2>Endereço de Entrega</h2>
      </div>
      <div class="fg fg-cep" style="margin-bottom:14px;">
        <div class="f">
          <label>CEP <span class="req">*</span></label>
          <input id="cep" type="text" placeholder="00000-000" maxlength="9" oninput="maskCEP(this);buscaCEP(this)" required>
          <span class="err-msg">CEP inválido</span>
        </div>
        <div class="f">
          <label>Número <span class="req">*</span></label>
          <input id="numero" type="text" placeholder="123" required>
          <span class="err-msg">Campo obrigatório</span>
        </div>
        <div class="f">
          <label>Complemento</label>
          <input id="complemento" type="text" placeholder="Apto, sala…">
        </div>
      </div>
      <div class="fg fg-2">
        <div class="f">
          <label>Rua / Avenida <span class="req">*</span></label>
          <input id="rua" type="text" placeholder="Preenchido pelo CEP" required>
          <span class="err-msg">Campo obrigatório</span>
        </div>
        <div class="f">
          <label>Bairro <span class="req">*</span></label>
          <input id="bairro" type="text" placeholder="Preenchido pelo CEP" required>
          <span class="err-msg">Campo obrigatório</span>
        </div>
      </div>
      <div class="fg fg-2">
        <div class="f">
          <label>Cidade <span class="req">*</span></label>
          <input id="cidade" type="text" placeholder="Sua cidade" required>
          <span class="err-msg">Campo obrigatório</span>
        </div>
        <div class="f">
          <label>Estado <span class="req">*</span></label>
          <select id="estado" required>
            <option value="">Selecione</option>
            <?php foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
              echo "<option value=\"$uf\">$uf</option>"; ?>
          </select>
          <span class="err-msg">Selecione o estado</span>
        </div>
      </div>
    </div>

    <!-- 3. FRETE -->
    <div class="sc">
      <div class="sc-title">
        <div class="sc-num">3</div>
        <h2>Opções de Entrega</h2>
      </div>
      <div class="frete-opts">
        <label class="frete-opt sel" onclick="selFrete(this,0)">
          <input type="radio" name="frete" value="0" checked>
          <div class="fi"><strong>PAC — Correios</strong><span>7 a 12 dias úteis</span></div>
          <span class="fp">Grátis</span>
        </label>
        <label class="frete-opt" onclick="selFrete(this,19.90)">
          <input type="radio" name="frete" value="19.90">
          <div class="fi"><strong>SEDEX</strong><span>2 a 4 dias úteis</span></div>
          <span class="fp">R$ 19,90</span>
        </label>
        <label class="frete-opt" onclick="selFrete(this,34.90)">
          <input type="radio" name="frete" value="34.90">
          <div class="fi"><strong>SEDEX 10</strong><span>Até às 10h do próximo dia útil</span></div>
          <span class="fp">R$ 34,90</span>
        </label>
      </div>
    </div>

    <!-- 4. PAGAMENTO -->
    <div class="sc">
      <div class="sc-title">
        <div class="sc-num">4</div>
        <h2>Forma de Pagamento</h2>
      </div>
      <div class="pay-tabs">
        <button class="pay-tab on" onclick="switchPay('cartao',this)">
          <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg> Cartão
        </button>
        <button class="pay-tab" onclick="switchPay('pix',this)">
          <svg viewBox="0 0 24 24"><path d="M5 8h14M5 12h9M5 16h5"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg> PIX
        </button>
        <button class="pay-tab" onclick="switchPay('boleto',this)">
          <svg viewBox="0 0 24 24"><rect x="3" y="3" width="4" height="18"/><rect x="9" y="3" width="2" height="18"/><rect x="13" y="3" width="4" height="18"/><rect x="19" y="3" width="2" height="18"/></svg> Boleto
        </button>
      </div>
      <input type="hidden" id="pay-tipo" value="cartao">

      <!-- CARTÃO -->
      <div class="pay-panel on" id="pan-cartao">
        <div class="card-visual">
          <div class="card-chip"></div>
          <div class="cv-num" id="cv-num">0000  0000  0000  0000</div>
          <div class="cv-row">
            <div><div class="cv-label">Titular</div><div class="cv-val" id="cv-name">NOME NO CARTÃO</div></div>
            <div><div class="cv-label">Validade</div><div class="cv-val" id="cv-exp">MM/AA</div></div>
          </div>
          <div class="card-brand">VISA</div>
        </div>
        <div class="fg fg-2">
          <div class="f" style="grid-column:1/-1">
            <label>Número do Cartão <span class="req">*</span></label>
            <input id="num-cartao" type="text" placeholder="0000 0000 0000 0000" maxlength="19"
                   oninput="fmtCartao(this)">
          </div>
        </div>
        <div class="fg fg-2">
          <div class="f" style="grid-column:1/-1">
            <label>Nome no Cartão <span class="req">*</span></label>
            <input id="nome-cartao" type="text" placeholder="NOME IMPRESSO NO CARTÃO"
                   oninput="document.getElementById('cv-name').textContent=this.value.toUpperCase()||'NOME NO CARTÃO'"
                  >
          </div>
        </div>
        <div class="fg fg-2">
          <div class="f">
            <label>Validade <span class="req">*</span></label>
            <input id="validade" type="text" placeholder="MM/AA" maxlength="5"
                   oninput="fmtExp(this);document.getElementById('cv-exp').textContent=this.value||'MM/AA'"
                  >
          </div>
          <div class="f">
            <label>CVV <span class="req">*</span></label>
            <input id="cvv" type="text" placeholder="•••" maxlength="4">
          </div>
        </div>
        <div class="fg" style="margin-top:6px;">
          <div class="f">
            <label>Parcelas</label>
            <select id="parcelas">
              <option>1× sem juros</option>
              <option>2× sem juros</option>
              <option>3× sem juros</option>
              <option>6× sem juros</option>
              <option>12× com juros (1,99% a.m.)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- PIX -->
      <div class="pay-panel" id="pan-pix">
        <div class="pix-wrap">
          <div class="pix-qr-area">
            <img id="pix-qr" src="" alt="QR Code PIX">
            <div class="pix-timer" id="pix-timer">⏱ Válido por 30:00</div>
          </div>
          <div class="pix-info-col">
            <h4>Pague com PIX</h4>
            <p>Escaneie o QR Code com o app do seu banco ou copie a chave abaixo. O pagamento é confirmado em <strong>até 5 minutos</strong>.</p>
            <div class="pix-key-box">
              <span id="pix-key-display">pix@origembrasil.com.br</span>
              <button class="pix-copy" onclick="copyPix()">Copiar chave</button>
            </div>
            <div class="pix-steps">
              <div class="pix-step"><div class="pix-step-n">1</div><span>Abra o app do seu banco e acesse a área <strong>PIX</strong></span></div>
              <div class="pix-step"><div class="pix-step-n">2</div><span>Escolha <strong>Pagar com QR Code</strong> ou use a chave</span></div>
              <div class="pix-step"><div class="pix-step-n">3</div><span>Confirme o valor e finalize o pagamento</span></div>
            </div>
          </div>
        </div>
      </div>

      <!-- BOLETO -->
      <div class="pay-panel" id="pan-boleto">
        <div class="boleto-wrap">
          <div class="boleto-bar" id="boleto-bar"></div>
          <p class="boleto-info-text">
            O boleto bancário será gerado após a confirmação do pedido.<br>
            Prazo para pagamento: <strong>3 dias úteis</strong>.<br>
            A entrega começa após a <strong>compensação bancária</strong> (1-2 dias úteis).
          </p>
        </div>
      </div>
    </div>

  </div><!-- /col esquerda -->

  <!-- ── RESUMO ── -->
  <div>
    <div class="sum-card">
      <div class="sum-title">Resumo do Pedido</div>
      <div id="sum-items"><div style="text-align:center;padding:24px;color:var(--dust)">Carregando…</div></div>
      <div class="div-line"></div>
      <div class="coupon-row">
        <input id="coupon-input" type="text" placeholder="Cupom de desconto">
        <button onclick="aplicarCupom()">Aplicar</button>
      </div>
      <div class="sum-row"><span>Subtotal</span><span id="sum-sub">R$ 0,00</span></div>
      <div class="sum-row"><span>Frete</span><span id="sum-frt">Grátis</span></div>
      <div class="sum-row" id="sum-desc-row" style="display:none">
        <span>Desconto</span><span id="sum-desc" class="saving">-R$ 0,00</span>
      </div>
      <div class="div-line"></div>
      <div class="sum-row tot"><span>Total</span><span id="sum-tot">R$ 0,00</span></div>
      <button class="btn-fin" id="btn-fin" onclick="finalizar()" disabled>
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Confirmar Pedido
      </button>
      <div class="sec-badges">
        <div class="sec-badge"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> SSL 256-bit</div>
        <div class="sec-badge"><svg viewBox="0 0 24 24"><polyline points="9 12 11 14 15 10"/><circle cx="12" cy="12" r="10"/></svg> Garantia</div>
        <div class="sec-badge"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg> Rastreável</div>
      </div>
    </div>
  </div>

</div><!-- /ck-body -->

<!-- MODAL SUCESSO -->
<div class="overlay" id="success-overlay">
  <div class="modal">
    <div class="modal-icon">
      <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h2>Pedido Confirmado!</h2>
    <p>Recebemos seu pedido com sucesso. Você pode acompanhar o status em <strong>Meus Pedidos</strong>.</p>
    <div class="onum" id="onum"></div>
    <div class="modal-btns">
      <a href="pedidos.php" class="mb-p">Ver meu pedido</a>
      <a href="index.php" class="mb-s">Continuar comprando</a>
    </div>
  </div>
</div>

<script src="js/carrinho.js"></script>
<script>
/* ─── STATE ─── */
let freteVal    = 0;
let descontoVal = 0;
let itens       = [];
let pixTimerInt = null;
const PIX_KEY   = 'pix@origembrasil.com.br';

/* ─── CART ─── */
async function renderSummary() {
  const box = document.getElementById('sum-items');
  const data = await apiGet('listar');
  if (!data) return;
  itens = data;

  if (!itens.length) {
    box.innerHTML = `<div class="empty-ck">
      <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <p>Seu carrinho está vazio.</p>
    </div>`;
    document.getElementById('btn-fin').disabled = true;
    updateTotals(0); return;
  }

  let sub = 0;
  box.innerHTML = itens.map(it => {
    const s = it.preco * it.quantidade;
    sub += s;
    const img = it.imagem ? it.imagem : 'imagens/placeholder.jpg';
    return `<div class="si">
      <img class="si-img" src="${img}" alt="${it.nome}" onerror="this.src='https://placehold.co/48x48/f5f2ed/9e9080?text=?'">
      <div class="si-body">
        <div class="si-name">${it.nome}</div>
        <div class="si-prod">por ${it.produtor_nome || '—'}</div>
        <div class="si-qty">Qtd: ${it.quantidade}</div>
      </div>
      <div class="si-price">R$ ${fmtBRL(s)}</div>
    </div>`;
  }).join('');

  updateTotals(sub);
  document.getElementById('btn-fin').disabled = false;
  updatePixQR(sub);
}

function updateTotals(sub) {
  const tot = Math.max(0, sub + freteVal - descontoVal);
  document.getElementById('sum-sub').textContent = 'R$ ' + fmtBRL(sub);
  document.getElementById('sum-frt').textContent  = freteVal ? 'R$ ' + fmtBRL(freteVal) : 'Grátis';
  document.getElementById('sum-tot').textContent  = 'R$ ' + fmtBRL(tot);
  const dr = document.getElementById('sum-desc-row');
  if (descontoVal > 0) { dr.style.display='flex'; document.getElementById('sum-desc').textContent='-R$ '+fmtBRL(descontoVal); }
  else dr.style.display='none';
  updatePixQR(sub);
}

/* ─── FRETE ─── */
function selFrete(el, val) {
  document.querySelectorAll('.frete-opt').forEach(o => o.classList.remove('sel'));
  el.classList.add('sel');
  freteVal = parseFloat(val);
  const sub = itens.reduce((s,i) => s + i.preco*i.quantidade, 0);
  updateTotals(sub);
}

/* ─── PIX ─── */
function updatePixQR(subtotal) {
  const tot = Math.max(0, subtotal + freteVal - descontoVal);
  const payload = `00020126580014BR.GOV.BCB.PIX0136${PIX_KEY}52040000530398654${String(tot.toFixed(2)).length.toString().padStart(2,'0')}${tot.toFixed(2)}5802BR5913Origem Brasil6008Joinville62070503***63041234`;
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(payload)}&margin=8`;
  document.getElementById('pix-qr').src = qrUrl;
}

function startPixTimer() {
  clearInterval(pixTimerInt);
  let secs = 30 * 60;
  const el = document.getElementById('pix-timer');
  pixTimerInt = setInterval(() => {
    secs--;
    const m = String(Math.floor(secs/60)).padStart(2,'0');
    const s = String(secs%60).padStart(2,'0');
    el.textContent = `⏱ Válido por ${m}:${s}`;
    if (secs <= 0) { clearInterval(pixTimerInt); el.textContent = 'QR Code expirado — recarregue a página'; }
  }, 1000);
}

function copyPix() {
  navigator.clipboard.writeText(PIX_KEY).then(() => {
    const btn = document.querySelector('.pix-copy');
    btn.textContent = '✓ Copiado!';
    btn.style.background = '#16a34a';
    setTimeout(() => { btn.textContent = 'Copiar chave'; btn.style.background = ''; }, 2000);
  });
}

/* ─── BOLETO BARS ─── */
function buildBoleto() {
  const bar = document.getElementById('boleto-bar');
  bar.innerHTML = '';
  const pattern = [3,1,5,2,1,4,2,3,1,6,1,2,5,1,3,2,4,1,2,3,1,5,2,1,4,2,3,1,6,2,3,1,4,1,2];
  pattern.forEach((_, i) => {
    const d = document.createElement('div');
    const w = (i%3===0)?'3px':(i%5===0)?'1px':'2px';
    d.style.cssText = `width:${w};background:${i%4===3?'var(--border)':'var(--soil)'};border-radius:1px;`;
    bar.appendChild(d);
  });
}

/* ─── PAY SWITCH ─── */
function switchPay(tipo, btn) {
  document.querySelectorAll('.pay-tab').forEach(b => b.classList.remove('on'));
  document.querySelectorAll('.pay-panel').forEach(p => p.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('pan-'+tipo).classList.add('on');
  document.getElementById('pay-tipo').value = tipo;
  if (tipo === 'pix') startPixTimer();
  else clearInterval(pixTimerInt);
}

/* ─── MASKS ─── */
function maskCPF(inp) {
  let v = inp.value.replace(/\D/g,'').substring(0,11);
  if (v.length > 9) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6,9)+'-'+v.slice(9);
  else if (v.length > 6) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6);
  else if (v.length > 3) v = v.slice(0,3)+'.'+v.slice(3);
  inp.value = v;
}
function maskPhone(inp) {
  let v = inp.value.replace(/\D/g,'').substring(0,11);
  if (v.length > 10) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
  else if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,6)+'-'+v.slice(6);
  else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
  inp.value = v;
}
function maskCEP(inp) {
  let v = inp.value.replace(/\D/g,'').substring(0,8);
  if (v.length > 5) v = v.slice(0,5)+'-'+v.slice(5);
  inp.value = v;
}
function fmtCartao(inp) {
  let v = inp.value.replace(/\D/g,'').substring(0,16);
  document.getElementById('cv-num').textContent = (v || '0000000000000000').replace(/(.{4})/g,'$1  ').trim() || '0000  0000  0000  0000';
  inp.value = v.replace(/(.{4})/g,'$1 ').trim();
}
function fmtExp(inp) {
  let v = inp.value.replace(/\D/g,'').substring(0,4);
  if (v.length > 2) v = v.slice(0,2)+'/'+v.slice(2);
  inp.value = v;
}

/* ─── CEP AUTOFILL ─── */
let cepTimer;
function buscaCEP(inp) {
  clearTimeout(cepTimer);
  const cep = inp.value.replace(/\D/g,'');
  if (cep.length !== 8) return;
  cepTimer = setTimeout(async () => {
    try {
      const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
      const d = await r.json();
      if (d.erro) { inp.classList.add('err'); return; }
      inp.classList.remove('err');
      document.getElementById('rua').value    = d.logradouro || '';
      document.getElementById('bairro').value = d.bairro     || '';
      document.getElementById('cidade').value = d.localidade || '';
      const sel = document.getElementById('estado');
      [...sel.options].forEach(o => { if (o.value === d.uf) o.selected = true; });
      document.getElementById('numero').focus();
    } catch(e) {}
  }, 500);
}

/* ─── CUPOM ─── */
function aplicarCupom() {
  const code = document.getElementById('coupon-input').value.trim().toUpperCase();
  const sub  = itens.reduce((s,i) => s + i.preco*i.quantidade, 0);
  if (code === 'ORIGEM10') {
    descontoVal = sub * 0.10;
    showToast('Cupom aplicado! 10% de desconto ✓','success');
  } else if (code === 'FRETEGRATIS') {
    freteVal = 0;
    document.querySelector('.frete-opt.sel .fp').textContent = 'Grátis';
    showToast('Frete grátis aplicado! ✓','success');
  } else { showToast('Cupom inválido.','error'); return; }
  updateTotals(sub);
}

/* ─── VALIDAÇÃO ─── */
function req(id, testFn) {
  const el = document.getElementById(id);
  const ok = testFn ? testFn(el.value) : el.value.trim() !== '';
  el.classList.toggle('err', !ok);
  return ok;
}
function validCPF(v) {
  v = v.replace(/\D/g,'');
  if (v.length !== 11 || /^(\d)\1+$/.test(v)) return false;
  let s = 0;
  for (let i=0;i<9;i++) s += +v[i]*(10-i);
  let r = (s*10)%11; if (r===10||r===11) r=0; if (r!==+v[9]) return false;
  s=0; for (let i=0;i<10;i++) s += +v[i]*(11-i);
  r=(s*10)%11; if (r===10||r===11) r=0; return r===+v[10];
}
function validExp(v) {
  const m = /^(\d{2})\/(\d{2})$/.exec(v.trim());
  if (!m) return false;

  const mes = parseInt(m[1], 10);
  const ano = 2000 + parseInt(m[2], 10);
  if (mes < 1 || mes > 12) return false;

  const agora = new Date();
  const anoAtual = agora.getFullYear();
  const mesAtual = agora.getMonth() + 1;

  return ano > anoAtual || (ano === anoAtual && mes >= mesAtual);
}

/* ─── FINALIZAR ─── */
async function finalizar() {
  const tipo = document.getElementById('pay-tipo').value;
  let ok = true;
  ok = req('nome')       && ok;
  ok = req('cpf', validCPF) && ok;
  ok = req('email', v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) && ok;
  ok = req('telefone', v => v.replace(/\D/g,'').length >= 10) && ok;
  ok = req('cep',  v => v.replace(/\D/g,'').length === 8) && ok;
  ok = req('numero')  && ok;
  ok = req('rua')     && ok;
  ok = req('bairro')  && ok;
  ok = req('cidade')  && ok;
  ok = req('estado')  && ok;
  if (tipo === 'cartao') {
    ok = req('num-cartao', v => v.replace(/\D/g,'').length === 16) && ok;
    ok = req('nome-cartao') && ok;
    ok = req('validade', validExp) && ok;
    ok = req('cvv', v => v.replace(/\D/g,'').length >= 3) && ok;
  }
  if (!ok) { showToast('Preencha todos os campos obrigatórios.','error'); return; }
  if (!itens.length) { showToast('Seu carrinho está vazio!','warning'); return; }

  const btn = document.getElementById('btn-fin');
  btn.disabled = true; btn.textContent = 'Processando…';

  const fd = new FormData();
  fd.append('action','finalizar'); fd.append('frete',freteVal);
  fd.append('desconto',descontoVal); fd.append('pagamento',tipo);
  const res  = await fetch('api/checkout.php',{method:'POST',body:fd});
  const data = await res.json();
  btn.disabled = false;
  btn.innerHTML = '<svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2.5;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Confirmar Pedido';

  if (data.ok) {
    document.getElementById('onum').textContent = '🎉 Pedido #'+data.numero;
    document.getElementById('success-overlay').classList.add('on');
    clearInterval(pixTimerInt);
  } else if (data.erro === 'sem_estoque') {
    showToast('Estoque insuficiente: '+data.produto,'error');
  } else { showToast(data.erro||'Erro ao finalizar pedido.','error'); }
}

/* ─── INIT ─── */
document.addEventListener('DOMContentLoaded', () => {
  renderSummary();
  buildBoleto();
  // pré-carrega QR com chave pix (sem valor até carregar)
  document.getElementById('pix-qr').src =
    `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(PIX_KEY)}&margin=8`;
});
</script>
</body>
</html>
