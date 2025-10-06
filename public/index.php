<?php
$pageTitle = 'Batrip - not all bats are dead!';
include '../includes/head.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Padroniza basePath
$basePath = $basePath ?? '/';

// Buscar produtos ativos do banco de dados
$homeProducts = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE active = 1 ORDER BY created_at DESC LIMIT 6');
    $stmt->execute();
    $homeProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar produtos: " . $e->getMessage());
    // Fallback com produtos estáticos se houver erro no banco
    $homeProducts = [
        [
            'id' => 1,
            'title' => 'Camiseta Fragmentado Oversized',
            'price' => 149.90,
            'image' => 'fragmentado-frente.jpeg',
            'description' => 'Camiseta oversized com design exclusivo Fragmentado'
        ],
        [
            'id' => 2,
            'title' => 'Camiseta Fragmentado Boxy',
            'price' => 149.90,
            'image' => 'fragmentado-costa.jpeg',
            'description' => 'Camiseta boxy com estampa Fragmentado'
        ],
        [
            'id' => 3,
            'title' => 'Camiseta Spiderweb Oversized',
            'price' => 149.90,
            'image' => 'spiderweb-oversized.jpeg',
            'description' => 'Camiseta oversized branca com design Spiderweb'
        ]
    ];
}
?>
<body>
    <?php include '../includes/nav.php'; ?>
    <?php include '../includes/cart-sidebar.php'; ?>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">batrip</h1>
            <p class="hero-subtitle">Exclusividade • Sonoridade • Autenticidade</p>
        </div>
    </section>

    <!-- Lançamentos -->
    <section id="lancamentos" class="section">
        <div class="container">
            <h2 class="section-title">Lançamentos</h2>
            <div class="row">
                <?php if (!empty($homeProducts)): ?>
                    <?php foreach ($homeProducts as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="product-card">
                                <a href="produto.php?id=<?= (int)$product['id'] ?>" class="product-image-store d-block">
                                    <img src="product-image.php?id=<?= (int)$product['id'] ?>" 
                                         alt="<?= htmlspecialchars($product['title']) ?>" 
                                         class="product-img-store"
                                         onerror="this.src='assets/img/placeholder.svg'">
                                </a>
                                <div class="p-3">
                                    <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                                    <?php if (!empty($product['description'])): ?>
                                        <p class="text-muted mb-2"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                                    <?php endif; ?>
                                    <p class="product-price">R$ <?= number_format((float)$product['price'], 2, ',', '.') ?></p>
                                    <div class="d-flex gap-2">
                                        <a href="produto.php?id=<?= (int)$product['id'] ?>" class="btn btn-custom flex-fill">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </a>
                                        <button type="button" class="btn btn-outline-light" 
                                                onclick="addToCart(<?= (int)$product['id'] ?>, '<?= htmlspecialchars($product['title']) ?>', <?= (float)$product['price'] ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h4>Nenhum produto encontrado</h4>
                            <p>Em breve novos lançamentos!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Conjuntos -->
    <section id="conjuntos" class="section">
        <div class="container">
            <h2 class="section-title">Conjuntos</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= $basePath ?>assets/img/fragmentado-frente.jpeg" alt="Camiseta Fragmentado Oversized" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
                        </div>
                        <h3 class="product-title">Drop Fragmentado</h3>
                        <p style="color: var(--text-gray); margin-bottom: 1rem;">Oversized + Boxy</p>
                        <p class="product-price">R$ 270,00</p>
                        <a href="<?= $basePath ?>produtos/conjunto-fragmentado.php" class="btn btn-custom">Ver Conjunto</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="product-card">
                        <div class="product-image">
                            <!-- <img src="../assets/img/fragmentado-frente.jpeg" alt="Camiseta Fragmentado Oversized" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;"> -->
                        </div>
                        <h3 class="product-title">Em breve</h3>
                        <p style="color: var(--text-gray); margin-bottom: 1rem;">??? + ??? + ???</p>
                        <p class="product-price">R$ ???,??</p>
                        <a href="<?= $basePath ?>produtos/em-breve.php" class="btn btn-custom">Ver Conjunto</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Artistas Parceiros -->
    <section id="artistas" class="section">
        <div class="container">
            <h2 class="section-title">No fone e na peita</h2>
            <div id="artistasCarousel" class="carousel slide" data-bs-ride="carousel" role="region" aria-roledescription="carousel" aria-label="Artistas parceiros">
                <!-- Indicadores -->
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#artistasCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#artistasCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#artistasCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <!-- Itens do Carrossel -->
                <div class="carousel-inner">
                    <!-- Primeiro grupo -->
                    <div class="carousel-item active">
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/chard-la-plaga.jpg" alt="Chard la Plaga">
                                    </div>
                                    <h3 class="artist-name">Chard la Plaga</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/link-do-zap.jpg" alt="Link do Zap">
                                    </div>
                                    <h3 class="artist-name">Link do Zap</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/ugovhb.jpg" alt="Ugovhb">
                                    </div>
                                    <h3 class="artist-name">Ugovhb</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Segundo grupo -->
                    <div class="carousel-item">
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/ef.jpg" alt="EF">
                                    </div>
                                    <h3 class="artist-name">EF</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/pradasoueu.jpg" alt="pradasoueu">
                                    </div>
                                    <h3 class="artist-name">pradasoueu</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/prxdby4le.jpg" alt="prxdby4le">
                                    </div>
                                    <h3 class="artist-name">prxdby4le</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Terceiro grupo -->
                    <div class="carousel-item">
                        <div class="row justify-content-center">
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/thejoia.jpg" alt="TheJoia">
                                    </div>
                                    <h3 class="artist-name">TheJoia</h3>
                                    <p class="artist-genre">Cantora e produtora</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/mugi.png" alt="Mugi">
                                    </div>
                                    <h3 class="artist-name">Mugi</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $basePath ?>assets/img/yung-loof.jpg" alt="Yung Loof">
                                    </div>
                                    <h3 class="artist-name">Yung Loof</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#artistasCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#artistasCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
            </div>
        </div>
    </section>
    <section class="section pt-5 pb-5" id="sobre">
        <div class="container">
            <div class="row align-items-center flex-column-reverse flex-md-row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h1 class="section-title mb-4">Sobre a Batrip</h1>
                    <p class="lead" style="color:var(--text-gray); font-size:1.2rem;">
                        "A batrip surgiu da minha necessidade de vestir algo diferente. Eu procurava  roupas que me agradassem e nunca tava satisfeito, entao chegou uma hora que eu cansei de esperar alguem ler minha mente e fui <strong>eu mesmo</strong> fazer as roupas que eu queria usar!
                        Quando eu mostrei pra uns amigos, inclusive o Lucca, todo mundo disse que queria uma também, entao eu decidi juntar o util ao agradavel e <strong>criei a marca</strong>. Bglh deu <strong>sold out</strong> no primeiro drop e eu fiquei muito animado.
                        Meus planos pro futuro são simples, nem penso no dinheiro: eu quero dar vida aos designs de conjunto que eu tenho, to com a máquina de costura e trampando pra tornar isso realidade o quanto antes, pq é outra fita que a rapazeada que viu enche o saco pra eu soltar, e com razao, pq modestia a parte, os design tão mto lindo! <strong>Meu foco na marca é unir moda e musica.</strong>"
                    </p>
                </div>
                <div class="col-md-6 d-flex justify-content-center">
                    <img src="assets/img/pradasoueu.jpg" alt="Sobre a Batrip" class="img-fluid rounded shadow" style="max-height:340px; object-fit:cover; width:100%; max-width:400px;">
                </div>
            </div>
        </div>
    </section>
    <section class="section pt-0 pb-5">
        <div class="container">
            <h2 class="section-title mb-4">Referências</h2>
            <div class="row g-3 gallery-batrip">
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/3.png" class="img-fluid rounded gallery-img" alt="Trocadilho do nome"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/1.png" class="img-fluid rounded gallery-img" alt="Ref da logo icon da batrip"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/2.png" class="img-fluid rounded gallery-img" alt="Ref da logo oficial da batrip"></div>
                </div>
            </div>
        </div>
    </section>
    <section class="section pt-0 pb-5">
        <div class="container">
            <h2 class="section-title mb-4">Idealizador</h2>
            <div class="row align-items-center justify-content-center">
                <div class="col-md-4 d-flex justify-content-center mb-4 mb-md-0">
                    <div class="prada-card text-center p-4 rounded shadow">
                        <div class="prada-avatar mx-auto mb-3">
                            <img src="assets/img/pradasoueu.jpg" alt="Prada" class="img-fluid rounded-circle" style="width:140px; height:140px; object-fit:cover; border:4px solid var(--accent-red);">
                        </div>
                        <h3 class="artist-name mb-1">Prada</h3>
                        <p class="artist-genre mb-2">Fundador, Artista &amp; Diretor Criativo</p>
                        <p style="color:var(--text-gray); font-size:1.05rem;">
                            "A Batrip é mais do que roupa, é sobre criar pontes entre arte, música e atitude. Cada peça carrega um pouco da nossa história e da energia de quem faz parte desse movimento."
                        </p>
                        <div class="mt-2">
                            <a href="https://x.com/pradasoueu" class="social-icon" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="https://www.instagram.com/batrip___/" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <!-- Sobre -->
    <section id="sobre" class="section bg-dark text-light py-5">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title mb-3">Sobre a Batrip</h2>
                    <p class="lead">A Batrip é uma marca independente focada em exclusividade, sonoridade e autenticidade. Nossas peças são criadas em colaboração com artistas parceiros e pensadas para quem busca estilo e identidade própria.</p>
                </div>
            </div>
        </div>
    </section>
    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
    
    <script>
    // Funcionalidade do carrinho
    function addToCart(productId, title, price, size = 'M') {
        const data = {
            action: 'add',
            id: productId,
            title: title,
            price: price,
            size: size,
            qty: 1
        };
        
        fetch('cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Atualizar contador do carrinho
                updateCartCount(result.cart_count);
                
                // Mostrar feedback visual
                showAlert('Produto adicionado ao carrinho!', 'success');
                
                // Opcional: abrir sidebar do carrinho
                // const cartSidebar = new bootstrap.Offcanvas(document.getElementById('cartSidebar'));
                // cartSidebar.show();
            } else {
                showAlert(result.message || 'Erro ao adicionar produto', 'danger');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showAlert('Erro ao adicionar produto', 'danger');
        });
    }
    
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('#cart-count, #sidebar-cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }
    
    function showAlert(message, type = 'info') {
        // Remover alertas existentes
        const existingAlert = document.querySelector('.temp-alert');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} temp-alert position-fixed`;
        alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
        
        const icons = {
            success: 'fas fa-check-circle',
            warning: 'fas fa-exclamation-triangle',
            danger: 'fas fa-times-circle',
            info: 'fas fa-info-circle'
        };
        
        alertDiv.innerHTML = `
            <i class="${icons[type] || icons.info} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-2" onclick="this.parentElement.remove()"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remover após 4 segundos
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alertDiv.remove(), 300);
            }
        }, 4000);
    }
    
    // Mensagem de logout se existir
    <?php if (isset($_SESSION['logout_success'])): ?>
        showAlert('<?= htmlspecialchars($_SESSION['logout_success']) ?>', 'success');
        <?php unset($_SESSION['logout_success']); ?>
    <?php endif; ?>
    </script>
    
    <style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    .temp-alert {
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    </style>
</body>
</html>

