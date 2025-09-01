<?php
// Integração inicial: sem funções de carrinho, apenas layout estático
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="cartSidebarLabel"><i class="fas fa-shopping-cart"></i> Carrinho
      <span class="badge bg-secondary ms-2" title="Itens no carrinho">0</span>
    </h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body">
  <div class="text-center text-muted">Seu carrinho está vazio.</div>  
  <div class="col-12">
                    <a href="checkout/carrinho.php" class="btn btn-custom w-100">Ver carrinho</a>
                </div>
</div>
  </div>

<?php // Previa do carrinho renderizada acima ?>
