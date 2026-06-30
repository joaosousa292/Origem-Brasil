<?php include_once(__DIR__ . "/icons.php"); ?>
<div id="cart-overlay"></div>
<aside id="cart-sidebar" role="dialog" aria-label="Carrinho">
    <!-- Header escuro rústico -->
    <div class="cart-header">
        <div class="cart-header-left">
            <div class="cart-header-icon">
                <?php echo icon('cart'); ?>
            </div>
            <div>
                <div class="cart-header-title">Seu Carrinho</div>
                <div class="cart-header-sub" id="cart-count-label">0 itens</div>
            </div>
        </div>
        <button id="close-cart" aria-label="Fechar">
            <?php echo icon('x'); ?>
        </button>
    </div>

    <!-- Itens -->
    <div id="cart-items"></div>

    <!-- Footer -->
    <div class="cart-footer">
        <div class="cart-subtotal-row">
            <span class="cart-subtotal-label">Subtotal</span>
            <div id="cart-total-val">R$ 0,00</div>
        </div>
        <div class="cart-shipping-note">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Frete calculado no checkout
        </div>
        <button class="checkout-btn" onclick="window.location.href='checkout.php'">
            Finalizar Compra
            <?php echo icon('chevron-r'); ?>
        </button>
        <a href="produtos.php" class="cart-continue">ou continuar comprando</a>
    </div>
</aside>
<div id="toast-container" aria-live="polite"></div>
