<?php
function renderCard($p) { ?>
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
        <?php if (!empty($p['regiao'])): ?>
        <div class="product-origin">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?php echo htmlspecialchars($p['regiao']); ?>
        </div>
        <?php endif; ?>
        <div class="product-name"><a href="produto.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></a></div>
        <div class="product-desc"><?php echo htmlspecialchars($p['descricao']); ?></div>
        <?php if (!empty($p['media_nota']) && $p['media_nota']>0): ?>
        <div class="product-stars">
            <?php for ($s=1;$s<=5;$s++): ?><span class="star <?php echo $s<=round($p['media_nota'])?'':'empty'; ?>"><svg style="width:1em;height:1em;vertical-align:middle;display:inline-block;fill:currentColor;" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span><?php endfor; ?>
            <span class="star-count">(<?php echo $p['media_nota']; ?>)</span>
        </div>
        <?php endif; ?>
        <div class="product-footer">
            <div class="product-price">
                <span class="price-val">R$&nbsp;<?php echo number_format($p['preco'],2,',','.'); ?></span>
                <?php if (!empty($p['peso'])): ?><span class="price-unit"><?php echo htmlspecialchars($p['peso']); ?></span><?php endif; ?>
            </div>
            <div class="product-actions">
                <button class="fav-btn" data-id="<?php echo $p['id']; ?>"
                        onclick="toggleFav(<?php echo $p['id']; ?>,this)" aria-label="Favoritar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </button>
                <?php if ($p['estoque']>0): ?>
                <button class="add-btn" data-id="<?php echo $p['id']; ?>"
                        onclick="addToCart(<?php echo $p['id']; ?>,'<?php echo addslashes($p['nome']); ?>',<?php echo $p['preco']; ?>,'<?php echo addslashes($p['imagem']); ?>')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
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
