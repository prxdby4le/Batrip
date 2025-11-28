<?php
/**
 * View: Pages/Customization
 * Página de Personalização
 */
?>

<div class="navbar-space"></div>
<!-- Personalização -->
<section class="customization-page" style="padding-top: 20px; padding-bottom: 40px;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="mb-3">Personalização</h1>
            <p class="lead text-muted">
                Crie peças únicas com seu próprio estilo
            </p>
        </div>
        
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <i class="bi bi-palette" style="font-size: 3rem; color: var(--accent-blue);"></i>
                        <h3 class="mt-3 mb-3">Personalização Total</h3>
                        <p class="text-muted">
                            Oferecemos serviço de personalização completa para suas peças. 
                            Você pode adicionar:
                        </p>
                        <ul class="text-muted">
                            <li>Seu nome ou apelido</li>
                            <li>Números customizados</li>
                            <li>Logos e designs especiais</li>
                            <li>Estampas exclusivas</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <i class="bi bi-clock-history" style="font-size: 3rem; color: var(--accent-blue);"></i>
                        <h3 class="mt-3 mb-3">Como Funciona</h3>
                        <ol class="text-muted">
                            <li>Escolha o produto que deseja personalizar</li>
                            <li>Entre em contato conosco com suas ideias</li>
                            <li>Nossa equipe criará um mockup para aprovação</li>
                            <li>Finalize o pedido e aguarde a produção (10-15 dias úteis)</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Valores -->
        <div class="card mb-5">
            <div class="card-body p-4">
                <h3 class="mb-4">Valores e Prazos</h3>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h5><i class="bi bi-tag me-2"></i>Preços</h5>
                        <p class="text-muted mb-0">
                            A partir de <strong>R$ 50,00</strong> por personalização, 
                            variando conforme complexidade
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5><i class="bi bi-clock me-2"></i>Prazo</h5>
                        <p class="text-muted mb-0">
                            Produção em <strong>10 a 15 dias úteis</strong> após aprovação do mockup
                        </p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5><i class="bi bi-shield-check me-2"></i>Garantia</h5>
                        <p class="text-muted mb-0">
                            <strong>100% de satisfação</strong> garantida ou reembolso total
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Formulário de Contato -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <h3 class="mb-4 text-center">Solicite um Orçamento</h3>
                        
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefone (WhatsApp)</label>
                                <input type="tel" class="form-control" id="phone" placeholder="(11) 99999-9999">
                            </div>
                            
                            <div class="mb-3">
                                <label for="product" class="form-label">Produto Desejado</label>
                                <select class="form-select" id="product">
                                    <option value="">Selecione um produto</option>
                                    <option>Camiseta</option>
                                    <option>Moletom</option>
                                    <option>Calça</option>
                                    <option>Conjunto</option>
                                    <option>Outro</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="details" class="form-label">Descreva sua Ideia</label>
                                <textarea class="form-control" id="details" rows="5" 
                                          placeholder="Conte-nos o que você tem em mente..." required></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-custom btn-lg">
                                    <i class="bi bi-send me-2"></i>Solicitar Orçamento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informações Adicionais -->
        <div class="alert alert-info mt-4" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Dica:</strong> Entre em contato pelo WhatsApp 
            <a href="tel:+5511123456789" class="alert-link">(11) 12345-6789</a> 
            para um atendimento mais rápido!
        </div>
    </div>
</section>
