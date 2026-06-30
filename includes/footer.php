<?php include_once(__DIR__ . "/icons.php"); ?>

<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <span class="logo-foot">Origem Brasil</span>
            <p>Conectamos pequenos produtores rurais a consumidores que valorizam qualidade, rastreabilidade e autenticidade. Do campo à mesa.</p>
        </div>
        <div class="footer-col">
            <h4>Loja</h4>
            <a href="produtos.php?categoria=cafe"><?php echo icon('chevron-r'); ?> Cafés Especiais</a>
            <a href="produtos.php?categoria=mel"><?php echo icon('chevron-r'); ?> Méis Artesanais</a>
            <a href="produtos.php?categoria=pimenta"><?php echo icon('chevron-r'); ?> Pimentas</a>
            <a href="produtos.php?categoria=farinha"><?php echo icon('chevron-r'); ?> Farinhas</a>
            <a href="busca.php"><?php echo icon('chevron-r'); ?> Buscar Produtos</a>
        </div>
        <div class="footer-col">
            <h4>Conta</h4>
            <a href="<?php echo isset($_SESSION['id'])?'perfil.php':'login.php'; ?>"><?php echo icon('chevron-r'); ?> <?php echo isset($_SESSION['id'])?'Meu Perfil':'Entrar'; ?></a>
            <a href="pedidos.php"><?php echo icon('chevron-r'); ?> Meus Pedidos</a>
            <a href="favoritos.php"><?php echo icon('chevron-r'); ?> Favoritos</a>
        </div>
        <div class="footer-col">
            <h4>Parceiros</h4>
            <a href="produtores.php"><?php echo icon('chevron-r'); ?> Nossos Produtores</a>
            <a href="seja_produtor.php"><?php echo icon('chevron-r'); ?> Seja um Produtor</a>
            <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo']==='produtor'): ?>
            <a href="solicitar_produto.php"><?php echo icon('chevron-r'); ?> Solicitar Produto</a>
            <?php endif; ?>
            <a href="sobre.php"><?php echo icon('chevron-r'); ?> Nossa História</a>
            <a href="https://wa.me/5547996280485?text=Olá!%20Gostaria%20de%20saber%20mais%20sobre%20os%20produtos%20da%20Origem%20Brasil."><?php echo icon('chevron-r'); ?> Contato</a>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© <?php echo date('Y'); ?> Origem Brasil. Todos os direitos reservados.</span>
        <div class="footer-seals">
            <span><?php echo icon('shield'); ?> Compra Segura</span>
            <span><?php echo icon('truck'); ?> Entrega Rastreada</span>
            <span><?php echo icon('leaf'); ?> 100% Natural</span>
        </div>
    </div>
</footer>
