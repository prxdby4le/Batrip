<?php
$pageTitle = 'Batrip - not all bats are dead!';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/icon-helper.php';

// Base href para links e assets (definido em includes/head.php)
$baseHref = $baseHref ?? '/';

// Buscar produtos ativos do banco de dados
$homeProducts = [];
$homeSets = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE active = 1 ORDER BY created_at DESC LIMIT 6');
    $stmt->execute();
    $homeProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar produtos: " . $e->getMessage());
    $homeProducts = [];
}

// Buscar conjuntos ativos do banco de dados
try {
    $stmt = $pdo->prepare('SELECT id, title, price, image, description FROM sets WHERE active = 1 ORDER BY created_at DESC LIMIT 4');
    $stmt->execute();
    $homeSets = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar conjuntos: " . $e->getMessage());
    $homeSets = [];
}
// Galeria: buscar imagens dos produtos listados
$imagesByProduct = [];
if (!empty($homeProducts)) {
    $ids = array_map(fn($p) => (int)($p['id'] ?? 0), $homeProducts);
    $ids = array_values(array_filter($ids));
    if (!empty($ids)) {
        try {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT product_id, url FROM product_images WHERE product_id IN ($in) ORDER BY is_primary DESC, position ASC, id ASC");
            $stmt->execute($ids);
            $counters = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pid = (int)$row['product_id'];
                $idx = $counters[$pid] ?? 0;
                $imagesByProduct[$pid][] = $baseHref . 'product-image.php?id=' . $pid . '&idx=' . $idx;
                $counters[$pid] = $idx + 1;
            }
        } catch (Throwable $e) {
            $imagesByProduct = [];
        }
    }
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
                                                                <div class="product-card-gallery">
                                                                    <a href="<?= $baseHref ?>produto.php?id=<?= (int)$product['id'] ?>" class="product-image-store d-block position-relative">
                                                                        <?php 
                                                                            $pid = (int)$product['id'];
                                                                            $imgs = $imagesByProduct[$pid] ?? [];
                                                                            if (empty($imgs)) { $imgs = [ $baseHref . 'product-image.php?id=' . $pid ]; }
                                                                            $mediums = [];
                                                                            foreach ($imgs as $u) { $mediums[] = $u . (strpos($u,'?')!==false ? '&' : '?') . 'size=medium'; }
                                                                            $firstMedium = $mediums[0] ?? ($baseHref . 'product-image.php?id=' . (int)$product['id']);
                                                                            $imgCount = max(1, count($mediums));
                                                                        ?>
                                                                                                                                                <img id="pc-main-<?= (int)$product['id'] ?>" src="<?= htmlspecialchars($firstMedium) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-img-store" data-images='<?= htmlspecialchars(json_encode($mediums), ENT_QUOTES, "UTF-8") ?>' data-index="0" data-pid="<?= (int)$product['id'] ?>" data-count="<?= (int)$imgCount ?>" onerror="this.src='<?= $baseHref ?>assets/img/placeholder.svg'">
                                                                        <button type="button" class="btn btn-sm btn-outline-light pc-nav pc-prev" data-target="pc-main-<?= (int)$product['id'] ?>" aria-label="Anterior">&#8249;</button>
                                                                        <button type="button" class="btn btn-sm btn-outline-light pc-nav pc-next" data-target="pc-main-<?= (int)$product['id'] ?>" aria-label="Próxima">&#8250;</button>
                                                                                                                                                <?php if ($imgCount > 1): ?>
                                                                                                                                                    <div class="pc-dots" data-target="pc-main-<?= (int)$product['id'] ?>">
                                                                                                                                                        <?php for ($di=0; $di<$imgCount; $di++): ?>
                                                                                                                                                            <button type="button" class="pc-dot<?= $di===0 ? ' active' : '' ?>" data-idx="<?= (int)$di ?>" aria-label="Imagem <?= (int)$di+1 ?>"></button>
                                                                                                                                                        <?php endfor; ?>
                                                                                                                                                    </div>
                                                                                                                                                    <span class="pc-counter" data-target="pc-main-<?= (int)$product['id'] ?>">1/<?= (int)$imgCount ?></span>
                                                                                                                                                <?php endif; ?>
                                                                    </a>
                                                                </div>
                                <div class="p-3">
                                    <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
                                    <?php if (!empty($product['description'])): ?>
                                        <p class="text-muted mb-2"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
                                    <?php endif; ?>
                                    <p class="product-price">R$ <?= number_format((float)$product['price'], 2, ',', '.') ?></p>
                                    <div class="d-flex gap-2">
                                        <a href="<?= $baseHref ?>produto.php?id=<?= (int)$product['id'] ?>" class="btn btn-custom flex-fill">
                                            <?= icon('eye', 'icon me-1') ?>Ver
                                        </a>
                                        <button type="button" class="btn btn-outline-light" 
                                                onclick="addToCart(<?= (int)$product['id'] ?>, '<?= htmlspecialchars($product['title']) ?>', <?= (float)$product['price'] ?>)">
                                            <?= icon('cart-plus', 'icon') ?>
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
                                <?= icon('box-open', 'icon-5x') ?>
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
                <?php if (!empty($homeSets)): ?>
                    <?php foreach ($homeSets as $set): ?>
                        <div class="col-md-6 mb-4">
                            <div class="product-card h-100 d-flex flex-column">
                                <a href="<?= $baseHref ?>produtos/conjunto.php?id=<?= (int)$set['id'] ?>" class="product-image d-block" style="height:260px;">
                                    <?php
                                    $setImg = !empty($set['image']) ? ($baseHref . 'set-image.php?id=' . (int)$set['id'] . '&size=medium') : ($baseHref . 'assets/img/placeholder-conjunto.png');
                                    ?>
                                    <img src="<?= htmlspecialchars($setImg) ?>" alt="<?= htmlspecialchars($set['title']) ?>" class="img-fluid rounded" style="object-fit:cover; width:100%; height:100%;">
                                </a>
                                <div class="p-3 flex-fill d-flex flex-column">
                                    <h3 class="product-title mb-1"><?= htmlspecialchars($set['title']) ?></h3>
                                    <?php if (!empty($set['description'])): ?>
                                        <p class="text-muted mb-2"><?= htmlspecialchars(substr($set['description'], 0, 90)) ?><?= strlen($set['description']) > 90 ? '...' : '' ?></p>
                                    <?php endif; ?>
                                    <p class="product-price mt-auto">R$ <?= number_format((float)$set['price'], 2, ',', '.') ?></p>
                                    <a href="<?= $baseHref ?>produtos/conjunto.php?id=<?= (int)$set['id'] ?>" class="btn btn-custom">Ver Conjunto</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-4">
                        <p class="mb-0">Nenhum conjunto disponível no momento.</p>
                    </div>
                <?php endif; ?>
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
                                        <img src="<?= $baseHref ?>assets/img/chard-la-plaga.jpg" alt="Chard la Plaga">
                                    </div>
                                    <h3 class="artist-name">Chard la Plaga</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $baseHref ?>assets/img/link-do-zap.jpg" alt="Link do Zap">
                                    </div>
                                    <h3 class="artist-name">Link do Zap</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $baseHref ?>assets/img/ugovhb.jpg" alt="Ugovhb">
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
                                        <img src="<?= $baseHref ?>assets/img/ef.jpg" alt="EF">
                                    </div>
                                    <h3 class="artist-name">EF</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $baseHref ?>assets/img/pradasoueu.jpg" alt="pradasoueu">
                                    </div>
                                    <h3 class="artist-name">pradasoueu</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $baseHref ?>assets/img/prxdby4le.jpg" alt="prxdby4le">
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
                                        <img src="<?= $baseHref ?>assets/img/thejoia.jpg" alt="TheJoia">
                                    </div>
                                    <h3 class="artist-name">TheJoia</h3>
                                    <p class="artist-genre">Cantora e produtora</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $baseHref ?>assets/img/mugi.png" alt="Mugi">
                                    </div>
                                    <h3 class="artist-name">Mugi</h3>
                                    <p class="artist-genre">Cantor e produtor</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="artist-card">
                                    <div class="artist-avatar">
                                        <img src="<?= $baseHref ?>assets/img/yung-loof.jpg" alt="Yung Loof">
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
                    <img src="<?= $baseHref ?>assets/img/pradasoueu.jpg" alt="Sobre a Batrip" class="img-fluid rounded shadow" style="max-height:340px; object-fit:cover; width:100%; max-width:400px;">
                </div>
            </div>
        </div>
    </section>
    <section class="section pt-0 pb-5">
        <div class="container">
            <h2 class="section-title mb-4">Referências</h2>
            <div class="row g-3 gallery-batrip">
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="<?= $baseHref ?>assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/3.png" class="img-fluid rounded gallery-img" alt="Trocadilho do nome"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="<?= $baseHref ?>assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/1.png" class="img-fluid rounded gallery-img" alt="Ref da logo icon da batrip"></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="gallery-img-wrap"><img src="<?= $baseHref ?>assets/materials/forma um morcego, um coração e uma folha de diamba, fazendo referência aos trocadilhos do nome/2.png" class="img-fluid rounded gallery-img" alt="Ref da logo oficial da batrip"></div>
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
                            <img src="<?= $baseHref ?>assets/img/pradasoueu.jpg" alt="Prada" class="img-fluid rounded-circle" style="width:140px; height:140px; object-fit:cover; border:4px solid var(--accent-red);">
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

    <?php include '../includes/footer.php'; ?>
    <?php include '../includes/scripts.php'; ?>
    
    <script>
    // Config baseHref e funcionalidade do carrinho
    const baseHref = (window.BATRIP_CONFIG && window.BATRIP_CONFIG.baseHref) || '<?= addslashes($baseHref) ?>';
    
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
        
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || (window.CSRF_TOKEN || '');
        fetch(baseHref + 'cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
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
    // Nenhuma ação adicional para conjuntos na home: direcionamos para a página do conjunto para escolher tamanhos.
    
    function showAlert(message, type = 'info') {
        // Remover alertas existentes
        const existingAlert = document.querySelector('.temp-alert');
        if (existingAlert) existingAlert.remove();
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} temp-alert position-fixed`;
        alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
        
        const iconSvgs = {
            success: <?= json_encode(icon("check-circle", "icon")) ?>,
            warning: <?= json_encode(icon("exclamation-triangle", "icon")) ?>,
            danger: <?= json_encode(icon("times-circle", "icon")) ?>,
            info: <?= json_encode(icon("info-circle", "icon")) ?>
        };
        
        alertDiv.innerHTML = `
            ${iconSvgs[type] || iconSvgs.info}
            <span class="ms-2">${message}</span>
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
        showAlert(<?= json_encode((string)$_SESSION['logout_success']) ?>, 'success');
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
        <script>
            // Carousel nos cards (home) apenas com botões prev/next
            (function(){
                function updateCardUI(img){
                    if (!img) return;
                    const card = img.closest('.product-card');
                    const count = parseInt(img.getAttribute('data-count') || '0', 10);
                    const idx = parseInt(img.getAttribute('data-index') || '0', 10);
                    // counter
                    const counter = card ? card.querySelector('.pc-counter[data-target="'+img.id+'"]') : null;
                    if (counter && count > 1) {
                        counter.textContent = String((idx+1)) + '/' + String(count);
                    }
                    // dots
                    const dotsWrap = card ? card.querySelector('.pc-dots[data-target="'+img.id+'"]') : null;
                    if (dotsWrap) {
                        dotsWrap.querySelectorAll('.pc-dot').forEach((d,i)=>{
                            if (i === idx) d.classList.add('active'); else d.classList.remove('active');
                        });
                    }
                }

                // init: set counters/dots
                document.querySelectorAll('img[id^="pc-main-"]').forEach(img => updateCardUI(img));

                        document.querySelectorAll('.pc-nav').forEach(nav => {
                    nav.addEventListener('click', e => {
                        e.preventDefault(); e.stopPropagation();
                        const targetId = nav.getAttribute('data-target');
                        const img = document.getElementById(targetId);
                        if (!img) return;
                                let idx = parseInt(img.getAttribute('data-index') || '0', 10);
                                if (isNaN(idx)) idx = 0;
                                const count = parseInt(img.getAttribute('data-count') || '0', 10);
                                const pid = img.getAttribute('data-pid');
                                if (count > 1 && pid) {
                                    idx = nav.classList.contains('pc-prev') ? (idx - 1 + count) % count : (idx + 1) % count;
                                    img.setAttribute('data-index', String(idx));
                                    img.src = baseHref + 'product-image.php?id=' + encodeURIComponent(pid) + '&idx=' + idx + '&size=medium';
                            updateCardUI(img);
                                    return;
                                }
                                // fallback para data-images JSON
                                let arr = [];
                                try { arr = JSON.parse(img.getAttribute('data-images') || '[]'); } catch(e) { arr = []; }
                                if (!arr.length) return;
                                idx = nav.classList.contains('pc-prev') ? (idx - 1 + arr.length) % arr.length : (idx + 1) % arr.length;
                                img.setAttribute('data-index', String(idx));
                                img.src = arr[idx];
                        updateCardUI(img);
                    });
                });

                // click nos dots
                document.querySelectorAll('.pc-dots').forEach(wrap => {
                    const targetId = wrap.getAttribute('data-target');
                    const img = document.getElementById(targetId);
                    if (!img) return;
                    const pid = img.getAttribute('data-pid');
                    const count = parseInt(img.getAttribute('data-count') || '0', 10);
                    wrap.querySelectorAll('.pc-dot').forEach(dot => {
                        dot.addEventListener('click', e => {
                            const idx = parseInt(dot.getAttribute('data-idx') || '0', 10) || 0;
                            img.setAttribute('data-index', String(idx));
                            if (pid && count > 0) {
                                img.src = baseHref + 'product-image.php?id=' + encodeURIComponent(pid) + '&idx=' + idx + '&size=medium';
                            } else {
                                // fallback JSON
                                let arr = [];
                                try { arr = JSON.parse(img.getAttribute('data-images') || '[]'); } catch(e) { arr = []; }
                                if (arr.length) img.src = arr[idx % arr.length];
                            }
                            updateCardUI(img);
                        });
                    });
                });
            })();
        </script>
        <style>
            /* Navegação do carousel nos cards */
        .pc-nav { position:absolute; top:50%; transform: translateY(-50%); opacity:.85; }
        .pc-prev { left:.5rem; }
        .pc-next { right:.5rem; }
            .pc-counter { position:absolute; top:.5rem; right:.5rem; background:rgba(0,0,0,.55); border:1px solid rgba(255,255,255,.2); padding:.15rem .4rem; border-radius:6px; font-size:.8rem; }
            .pc-dots { position:absolute; bottom:.5rem; left:50%; transform:translateX(-50%); display:flex; gap:.35rem; }
            .pc-dot { width:8px; height:8px; border-radius:50%; border:1px solid rgba(255,255,255,.6); background:rgba(255,255,255,.2); padding:0; }
            .pc-dot.active { background:rgba(255,255,255,.9); }
    
        </style>
</body>
</html>

