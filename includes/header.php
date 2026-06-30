<?php
include_once(__DIR__ . "/icons.php");
$paginaAtual = basename($_SERVER['PHP_SELF']);
$catAtual    = $_GET['categoria'] ?? '';
$emProdutos  = in_array($paginaAtual, ['produtos.php', 'cafes.php']);

// Categorias extras (criadas a partir de solicitações de produtores aprovadas)
$catsExtraMenu = [];
if (isset($conexao)) {
    $resCatsMenu = mysqli_query($conexao,
        "SELECT slug, label FROM categorias WHERE slug NOT IN ('cafe','mel','pimenta','farinha') ORDER BY label ASC"
    );
    if ($resCatsMenu) {
        while ($rowCatMenu = mysqli_fetch_assoc($resCatsMenu)) {
            $catsExtraMenu[] = $rowCatMenu;
        }
    }
}
?>
<header class="site-header">
<div class="header-inner">
    <div class="header-top">

        <div class="header-left">
            <button class="hbtn search-btn" id="btn-busca-toggle" aria-label="Buscar">
                <?php echo icon('search'); ?> <span>Buscar</span>
            </button>
            <div class="conta-wrap">
                
                <button class="hbtn" id="btn-conta" aria-label="Conta" style="display: flex; align-items: center; gap: 8px;">
                    
                    <?php 
                    // Se o usuário estiver logado e tiver uma foto salva na sessão
                    if (isset($_SESSION['id']) && !empty($_SESSION['usuario_foto'])): 
                    ?>
                        <img src="<?php echo htmlspecialchars(ltrim($_SESSION['usuario_foto'], '/')); ?>" alt="Perfil" class="header-avatar">
                    <?php else: ?>
                        <?php echo icon('user'); ?>
                    <?php endif; ?>
                    <span><?php echo isset($_SESSION['nome']) ? htmlspecialchars(explode(' ',$_SESSION['nome'])[0]) : 'Conta'; ?></span>
                    <?php echo icon('chevron-d'); ?>
                </button>
                
                <div class="conta-dropdown" id="conta-dropdown">
                    <?php if (isset($_SESSION['id'])): ?>
                        <a href="perfil.php"><?php echo icon('user'); ?> Meu Perfil</a>
                        <a href="pedidos.php"><?php echo icon('orders'); ?> Meus Pedidos</a>
                        <a href="favoritos.php"><?php echo icon('heart'); ?> Favoritos</a>
                        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo']==='produtor'): ?>
                        <div class="dd-sep"></div>
                        <a href="solicitar_produto.php" style="color:var(--green-2);font-weight:600;"><?php echo icon('box'); ?> Solicitar Produto</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo']==='admin'): ?>
                        <div class="dd-sep"></div>
                        <a href="adm/dashboard.php" style="color:var(--green-2);font-weight:600;"><?php echo icon('settings'); ?> Painel Admin</a>
                        <?php endif; ?>
                        <div class="dd-sep"></div>
                        <a href="logout.php" style="color:#ef4444;"><?php echo icon('logout'); ?> Sair</a>
                    <?php else: ?>
                        <a href="login.php"><?php echo icon('user'); ?> Entrar</a>
                        <a href="cadastro.php"><?php echo icon('plus'); ?> Criar Conta</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="header-center">
            <a href="index.php" class="logo-mark" aria-label="Origem Brasil">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
                </div>
                <div class="logo-text">
                    <strong>Origem Brasil</strong>
                    <span>Do campo à mesa</span>
                </div>
            </a>
        </div>

        <div class="header-right">
            <button class="hbtn" onclick="window.location.href='favoritos.php'" aria-label="Favoritos">
                <?php echo icon('heart'); ?> <span>Favoritos</span>
            </button>
            <button class="hbtn" id="open-cart" aria-label="Carrinho">
                <?php echo icon('cart'); ?> <span>Carrinho</span>
                <span class="cart-count" id="cart-count">0</span>
            </button>
        </div>
    </div>

    <div class="search-expand" id="search-bar-wrap">
        <div class="search-inner">
            <form class="search-form" action="busca.php" method="GET">
                <input type="text" name="q" placeholder="Buscar produtos, regiões, produtores…"
                       autocomplete="off" id="search-input">
                <button type="submit"><?php echo icon('search'); ?></button>
            </form>
        </div>
    </div>

    <nav class="site-nav" aria-label="Menu principal">

        <a href="index.php" <?php echo $paginaAtual==='index.php'?'class="nav-active"':''; ?>>
            <?php echo icon('home'); ?> <span>Início</span>
        </a>

        <div class="nav-mega-wrap" id="nav-mega-wrap">
            <button class="nav-mega-btn <?php echo $emProdutos?'nav-active':''; ?>" id="nav-produtos-btn"
                    onclick="toggleMegaMenu(event)" aria-haspopup="true" aria-expanded="false">
                <?php echo icon('box'); ?>
                <span>Produtos</span>
                <svg class="mega-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     style="width:12px;height:12px;margin-left:2px;transition:transform .2s;">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            <div class="mega-menu" id="mega-menu" role="menu">
                <div class="mega-menu-header">
                    <span>Navegue por categoria</span>
                </div>

                <a href="produtos.php?categoria=cafe" class="mega-item <?php echo $catAtual==='cafe'||$paginaAtual==='cafes.php'?'mega-active':''; ?>" role="menuitem">
                    <div class="mega-item-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>
                    </div>
                    <div class="mega-item-text">
                        <strong>Cafés Especiais</strong>
                        <span>Grãos selecionados do Brasil</span>
                    </div>
                    <?php echo icon('chevron-r'); ?>
                </a>

                <a href="produtos.php?categoria=mel" class="mega-item <?php echo $catAtual==='mel'?'mega-active':''; ?>" role="menuitem">
                    <div class="mega-item-icon" style="background:#fef9ec;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="1.8"><path d="M12 2C8.5 2 6 5 6 8c0 4 6 14 6 14s6-10 6-14c0-3-2.5-6-6-6z"/><circle cx="12" cy="8" r="2"/></svg>
                    </div>
                    <div class="mega-item-text">
                        <strong>Méis Artesanais</strong>
                        <span>Mel puro de abelhas nativas</span>
                    </div>
                    <span class="mega-soon">Em breve</span>
                </a>

                <a href="produtos.php?categoria=pimenta" class="mega-item <?php echo $catAtual==='pimenta'?'mega-active':''; ?>" role="menuitem">
                    <div class="mega-item-icon" style="background:#fef2f2;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.8"><path d="M12 2C8.5 2 6 5 6 8c0 4 6 14 6 14s6-10 6-14c0-3-2.5-6-6-6z"/><path d="M12 2c0 0 3-1 4 2"/></svg>
                    </div>
                    <div class="mega-item-text">
                        <strong>Pimentas Especiais</strong>
                        <span>Cultivo artesanal brasileiro</span>
                    </div>
                    <span class="mega-soon">Em breve</span>
                </a>

                <a href="produtos.php?categoria=farinha" class="mega-item <?php echo $catAtual==='farinha'?'mega-active':''; ?>" role="menuitem">
                    <div class="mega-item-icon" style="background:#fdf8f0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="1.8"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                    </div>
                    <div class="mega-item-text">
                        <strong>Farinhas Naturais</strong>
                        <span>Agricultura familiar orgânica</span>
                    </div>
                    <span class="mega-soon">Em breve</span>
                </a>

                <?php foreach ($catsExtraMenu as $extraCat): ?>
                <a href="produtos.php?categoria=<?php echo htmlspecialchars($extraCat['slug']); ?>" class="mega-item <?php echo $catAtual===$extraCat['slug']?'mega-active':''; ?>" role="menuitem">
                    <div class="mega-item-icon" style="background:var(--green-light);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="var(--green-2)" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    </div>
                    <div class="mega-item-text">
                        <strong><?php echo htmlspecialchars($extraCat['label']); ?></strong>
                        <span>Novidade da Origem Brasil</span>
                    </div>
                    <span class="mega-soon" style="background:var(--orange);color:#fff;">Novo</span>
                </a>
                <?php endforeach; ?>

                <div class="mega-menu-footer">
                    <a href="produtos.php">
                        Ver todos os produtos
                        <?php echo icon('chevron-r'); ?>
                    </a>
                </div>
            </div>
        </div>

        <a href="produtores.php" <?php echo $paginaAtual==='produtores.php'?'class="nav-active"':''; ?>>
            <?php echo icon('users'); ?> <span>Produtores</span>
        </a>

        <a href="sobre.php" <?php echo $paginaAtual==='sobre.php'?'class="nav-active"':''; ?>>
            <?php echo icon('info'); ?> <span>Nossa História</span>
        </a>

    </nav>
</div>
    <script src="https://cdn.userway.org/widget.js" data-account="SEU_CODIGO_AQUI"></script>
</header>

<script>
function toggleMegaMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('mega-menu');
    const btn  = document.getElementById('nav-produtos-btn');
    const arrow = btn.querySelector('.mega-arrow');
    const isOpen = menu.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen);
    arrow.style.transform = isOpen ? 'rotate(180deg)' : '';
}
document.addEventListener('click', () => {
    document.getElementById('mega-menu')?.classList.remove('open');
    const btn = document.getElementById('nav-produtos-btn');
    if (btn) btn.setAttribute('aria-expanded', 'false');
    const arrow = btn?.querySelector('.mega-arrow');
    if (arrow) arrow.style.transform = '';
});
document.getElementById('mega-menu')?.addEventListener('click', e => e.stopPropagation());
</script>
