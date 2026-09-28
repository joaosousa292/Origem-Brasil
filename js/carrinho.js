// ===================================================
// ORIGEM BRASIL — Carrinho + Favoritos + Toast
// ===================================================
const API = 'api/carrinho.php';

function fmtBRL(v) {
    return parseFloat(v).toLocaleString('pt-BR', { minimumFractionDigits:2, maximumFractionDigits:2 });
}
function estaLogado() { return document.body.dataset.logado === '1'; }
function pedirLogin() {
    const v = encodeURIComponent(window.location.pathname + window.location.search);
    window.location.href = 'login.php?voltar=' + v;
}

// ── TOAST ─────────────────────────────────────────
const TOAST_ICONS = {
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
    error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
};
function showToast(msg, tipo='success', dur=3200) {
    const c = document.getElementById('toast-container');
    if (!c) return;
    const t = document.createElement('div');
    t.className = `toast ${tipo}`;
    t.innerHTML = `${TOAST_ICONS[tipo]||''} ${msg}`;
    c.appendChild(t);
    setTimeout(() => {
        t.style.animation = 'toastOut .3s ease forwards';
        setTimeout(() => t.remove(), 300);
    }, dur);
}

// ── API ───────────────────────────────────────────
async function apiPost(action, params={}) {
    try {
        const fd = new FormData();
        fd.append('action', action);
        for (const [k,v] of Object.entries(params)) fd.append(k, v);
        const res = await fetch(API, { method:'POST', body:fd });
        if (res.status === 401) { pedirLogin(); return null; }
        return await res.json();
    } catch(e) { console.error(e); return null; }
}
async function apiGet(action) {
    try {
        const res = await fetch(`${API}?action=${action}`);
        if (res.status === 401) { pedirLogin(); return null; }
        return await res.json();
    } catch(e) { return null; }
}

// ── CARRINHO ──────────────────────────────────────
async function addToCart(produto_id, nome, preco, imagem) {
    if (!estaLogado()) { pedirLogin(); return; }
    const btns = document.querySelectorAll(`.add-btn[data-id="${produto_id}"]`);
    btns.forEach(b => { b.disabled=true; b.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;animation:spin .7s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg>'; });

    const data = await apiPost('adicionar', { produto_id, quantidade:1 });

    btns.forEach(b => { b.disabled=false; b.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Adicionar'; });

    if (!data) return;
    if (data.erro === 'sem_estoque') { showToast('Estoque indisponível', 'warning'); return; }
    if (data.erro) { showToast('Erro ao adicionar', 'error'); return; }

    showToast(`${nome} adicionado ao carrinho`, 'success');
    await renderCart();
    document.getElementById('cart-sidebar')?.classList.add('active');
    document.getElementById('cart-overlay')?.classList.add('active');
}

async function removeFromCart(produto_id) {
    await apiPost('remover', { produto_id });
    await renderCart();
    showToast('Item removido', 'info');
}

async function changeQty(produto_id, delta, qty_atual) {
    const nova = qty_atual + delta;
    if (nova <= 0) { await removeFromCart(produto_id); return; }
    const data = await apiPost('atualizar', { produto_id, quantidade:nova });
    if (data?.erro === 'sem_estoque') { showToast('Quantidade máxima disponível atingida', 'warning'); }
    await renderCart();
}

async function renderCart() {
    const container = document.getElementById('cart-items');
    const totalEl   = document.getElementById('cart-total');
    if (!container) return;

    if (!estaLogado()) {
        container.innerHTML = `<div class="cart-empty">
            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:28px;height:28px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
            <p>Faça login para ver seu carrinho</p>
            <a href="login.php">Entrar agora</a>
        </div>`;
        if (totalEl) totalEl.innerHTML = 'Total <span>R$ 0,00</span>';
        const tv0 = document.getElementById('cart-total-val'); if(tv0) tv0.textContent='R$ 0,00';
        const cl0 = document.getElementById('cart-count-label'); if(cl0) cl0.textContent='0 itens';
        updateCartCount(0); return;
    }

    // Mostra loading somente na primeira abertura. Em alteracoes de quantidade,
    // manter a lista atual evita piscadas e saltos visuais no HUD.
    if (!container.dataset.loaded) {
        container.innerHTML = `<div style="padding:16px;display:flex;flex-direction:column;gap:16px;">
            ${[1,2].map(()=>`<div style="display:flex;gap:12px;">
                <div class="skeleton" style="width:54px;height:54px;border-radius:10px;flex-shrink:0;"></div>
                <div style="flex:1;"><div class="skeleton" style="height:12px;width:75%;margin-bottom:8px;"></div><div class="skeleton" style="height:10px;width:45%;"></div></div>
            </div>`).join('')}
        </div>`;
    }

    const itens = await apiGet('listar');
    if (!itens) return;

    if (itens.length === 0) {
        container.dataset.loaded = '1';
        container.innerHTML = `<div class="cart-empty">
            <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:28px;height:28px"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
            <p>Seu carrinho está vazio</p>
            <a href="cafes.php">Ver Produtos</a>
        </div>`;
        if (totalEl) totalEl.innerHTML = 'Total <span>R$ 0,00</span>';
        const tv1 = document.getElementById('cart-total-val'); if(tv1) tv1.textContent='R$ 0,00';
        const cl1 = document.getElementById('cart-count-label'); if(cl1) cl1.textContent='0 itens';
        updateCartCount(0); return;
    }

    let total = 0;
    container.dataset.loaded = '1';
    container.innerHTML = itens.map(item => {
        const sub = item.preco * item.quantidade;
        total += sub;
        const img = item.imagem || 'imagens/cafe1.jpg';
        return `<div class="cart-item">
            <img src="${img}" alt="${item.nome}" class="cart-item-img" onerror="this.src='imagens/cafe/cafe1.jpg'">
            <div class="cart-item-info">
                <div class="cart-item-name">${item.nome}</div>
                <div class="cart-item-price">R$ ${fmtBRL(item.preco)} × ${item.quantidade}</div>
                <div class="cart-controls">
                    <button class="qty-btn" onclick="changeQty(${item.produto_id},-1,${item.quantidade})">−</button>
                    <span class="qty-num">${item.quantidade}</span>
                    <button class="qty-btn" onclick="changeQty(${item.produto_id},1,${item.quantidade})">+</button>
                    <button class="remove-btn" onclick="removeFromCart(${item.produto_id})" title="Remover">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');

    const qty = itens.reduce((s,i) => s + parseInt(i.quantidade), 0);
    if (totalEl) totalEl.innerHTML = `Total <span>R$ ${fmtBRL(total)}</span>`;
    const tvMain = document.getElementById('cart-total-val'); if(tvMain) tvMain.textContent=`R$ ${fmtBRL(total)}`;
    const clMain = document.getElementById('cart-count-label'); if(clMain) clMain.textContent=`${qty} ${qty===1?'item':'itens'}`;
    updateCartCount(qty);
}

function updateCartCount(n) {
    document.querySelectorAll('#cart-count, .cart-count').forEach(el => el.textContent = n || '0');
}
async function loadCartCount() {
    if (!estaLogado()) { updateCartCount(0); return; }
    const itens = await apiGet('listar');
    if (!itens) return;
    updateCartCount(itens.reduce((s,i) => s + parseInt(i.quantidade), 0));
}

// ── FAVORITOS ─────────────────────────────────────
async function toggleFav(produto_id, btn) {
    if (!estaLogado()) { pedirLogin(); return; }
    const res = await fetch('api/favoritos.php?action=toggle', {
        method:'POST', body: new URLSearchParams({ produto_id })
    });
    const data = await res.json();
    if (data.ativo) {
        btn.classList.add('active');
        showToast('Adicionado aos favoritos', 'success');
    } else {
        btn.classList.remove('active');
        showToast('Removido dos favoritos', 'info');
    }
}
async function loadFavs() {
    if (!estaLogado()) return;
    try {
        const res  = await fetch('api/favoritos.php?action=listar');
        const data = await res.json();
        const ids  = (data.ids || []).map(Number);
        document.querySelectorAll('.fav-btn[data-id]').forEach(btn => {
            btn.classList.toggle('active', ids.includes(parseInt(btn.dataset.id)));
        });
    } catch(e) {}
}

function closeCart() {
    document.getElementById('cart-sidebar')?.classList.remove('active');
    document.getElementById('cart-overlay')?.classList.remove('active');
}

// ── EVENTOS ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Carrinho
    document.querySelectorAll('[data-cart-toggle], #open-cart').forEach(btn => btn.addEventListener('click', async () => {
        document.getElementById('cart-sidebar')?.classList.add('active');
        document.getElementById('cart-overlay')?.classList.add('active');
        await renderCart();
    }));
    document.getElementById('close-cart')?.addEventListener('click', closeCart);
    document.getElementById('cart-overlay')?.addEventListener('click', closeCart);

    // Dropdown conta
    const btnConta = document.getElementById('btn-conta');
    const dropdown = document.getElementById('conta-dropdown');
    if (btnConta && dropdown) {
        btnConta.addEventListener('click', e => { e.stopPropagation(); dropdown.classList.toggle('open'); });
        document.addEventListener('click', () => dropdown.classList.remove('open'));
    }

    // Busca toggle
    document.getElementById('btn-busca-toggle')?.addEventListener('click', () => {
        const w = document.getElementById('search-bar-wrap');
        w?.classList.toggle('open');
        if (w?.classList.contains('open')) document.getElementById('search-input')?.focus();
    });

    // Carrossel
    initCarousel();

    loadCartCount();
    loadFavs();
});

// ── CARROSSEL ─────────────────────────────────────
function initCarousel() {
    const track = document.getElementById('carousel-track');
    if (!track) return;
    const slides = track.querySelectorAll('.carousel-slide');
    const dots   = document.querySelectorAll('.carousel-dot');
    let current  = 0;
    let timer    = null;

    function goTo(idx) {
        current = (idx + slides.length) % slides.length;
        track.style.transform = `translateX(-${current * 100}%)`;
        dots.forEach((d,i) => d.classList.toggle('active', i === current));
    }
    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }
    function startAuto() { timer = setInterval(next, 5000); }
    function stopAuto()  { clearInterval(timer); }

    document.getElementById('carousel-prev')?.addEventListener('click', () => { stopAuto(); prev(); startAuto(); });
    document.getElementById('carousel-next')?.addEventListener('click', () => { stopAuto(); next(); startAuto(); });
    dots.forEach((d,i) => d.addEventListener('click', () => { stopAuto(); goTo(i); startAuto(); }));

    // Touch/swipe
    let startX = 0;
    track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; stopAuto(); });
    track.addEventListener('touchend',   e => {
        const diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) diff > 0 ? next() : prev();
        startAuto();
    });

    goTo(0);
    startAuto();
}

// Spin animation para loading do botão
const spinStyle = document.createElement('style');
spinStyle.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(spinStyle);
