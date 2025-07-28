<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Produto | Batrip</title>
    <link rel="icon" href="materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Arvo:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="materials/batrip png branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#lancamentos">Lançamentos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#conjuntos">Conjuntos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#artistas">Artistas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#personalizacao">Personalização</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sobre.php">Sobre</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="login.php" class="login-btn">
                        <i class="fas fa-user"></i> Login
                    </a>
                    <a href="register.php" class="login-btn">
                        <i class="fas fa-user"></i> Registrar
                    </a>
                    <a href="perfil.php" class="login-btn">
                        <i class="fas fa-user"></i> Perfil
                    </a>
                </div>
            </div>
            <!-- Botão do carrinho sempre visível e alinhado à direita -->
            <button class="btn btn-outline-light ms-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartSidebar" aria-controls="cartSidebar" style="z-index:1051;">
                <i class="fas fa-shopping-cart"></i>
                <span class="badge bg-danger" id="cart-count">2</span>
            </button>
        </div>
    </nav>
    <!-- Barra lateral do carrinho -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="cartSidebarLabel"><i class="fas fa-shopping-cart"></i> Carrinho</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
      </div>
      <div class="offcanvas-body">
        <!-- Prévia dos itens do carrinho -->
        <div id="cart-preview"></div>
      </div>
    </div>

    <!-- Produto Section -->
    <section class="section produto-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="product-image-store">
                        <img src="img/fragmentado-costa.jpeg" alt="Camiseta Fragmentado Boxy" class="img-fluid rounded product-img-store">
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="product-title mb-2">Camiseta Fragmentado Boxy</h2>
                    <p class="product-price mb-2">R$ 149,99</p>
                    <p class="product-desc">
                        Do Opium ao Streetwear, a Batrip explora textura e aspectos musicais em forma de moda.
                        A fusão do mundo punk e rock com as ruas criando uma passarela alternativa e agressiva
                        Batrip tem uma essência autêntica e exclusiva visando peças com acabamento de qualidade e visual sombrio. <br>
                        <br>
                        Tecida inteiramente com fios de algodão, oferece um toque agradável e conforto por permitir a troca de calor. Por passar por um sistema penteado, tem as fibras curtas e impurezas (como cascas) retiradas, tornando o tecido mais limpo e resistente macio e com um aspecto mais bonito A Penteada tem uma melhor aparência, é mais macia e ainda mais confortável. <br>
                        <br>
                        <b>Recomendações de Conservação</b>
                        <ul>
                            <li>Lavar à mão ou em ciclo delicado</li>
                            <li>Não usar alvejantes</li>
                            <li>Secar à sombra para preservar a tonalidade</li>
                            <li>Passar com ferro em temperatura baixa</li>
                        </ul>
                    </p>
                    <form id="product-form" autocomplete="off">
                        <div class="mb-3">
                            <label for="tamanho" class="form-label">Selecione o tamanho:</label>
                            <select class="form-select" id="tamanho" name="tamanho" required>
                                <option value="">Escolha...</option>
                                <option value="P">P</option>
                                <option value="M">M</option>
                                <option value="G">G</option>
                                <option value="GG">GG</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <table class="table table-bordered table-sm align-middle text-center product-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Tamanho</th>
                                        <th>Largura (cm)</th>
                                        <th>Altura (cm)</th>
                                        <th>Busto (cm)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>P</td>
                                        <td>52</td>
                                        <td>70</td>
                                        <td>98</td>
                                    </tr>
                                    <tr>
                                        <td>M</td>
                                        <td>54</td>
                                        <td>72</td>
                                        <td>104</td>
                                    </tr>
                                    <tr>
                                        <td>G</td>
                                        <td>56</td>
                                        <td>74</td>
                                        <td>110</td>
                                    </tr>
                                    <tr>
                                        <td>GG</td>
                                        <td>58</td>
                                        <td>76</td>
                                        <td>116</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Comprar</button>
                    </form>
                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        if (typeof updateCartPreview === 'function') updateCartPreview();
                        if (typeof updateCartCount === 'function') updateCartCount();
                        var form = document.getElementById('product-form');
                        if (form) {
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                var sizeSelect = document.getElementById('tamanho');
                                var size = sizeSelect && sizeSelect.value ? sizeSelect.value : 'M';
                                var title = document.querySelector('.product-title')?.textContent.trim() || document.title;
                                var priceText = document.querySelector('.product-price')?.textContent.replace(/[^\d,]/g, '').replace(',', '.');
                                var price = parseFloat(priceText);
                                var img = document.querySelector('.product-img-store')?.getAttribute('src') || '';
                                if (!size || !price || !title) {
                                    alert('Selecione o tamanho e verifique os dados do produto.');
                                    return;
                                }
                                if (typeof addToCart === 'function') {
                                    addToCart({ title, price, img, size });
                                    var btn = form.querySelector('button[type="submit"]');
                                    if (btn) {
                                        btn.disabled = true;
                                        var old = btn.innerHTML;
                                        btn.innerHTML = '<i class="fas fa-check"></i> Adicionado!';
                                        setTimeout(function(){ btn.innerHTML = old; btn.disabled = false; }, 1200);
                                    }
                                }
                            });
                        }
                    });
                    </script>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h3 class="footer-title">Batrip</h3>
                    <p style="color: var(--text-gray);">
                        Exclusividade • Sonoridade • Autenticidade<br>
                        A marca favorita do seu artista favorito.
                    </p>
                </div>
                <div class="col-md-4">
                    <h4 class="footer-title">Contato</h4>
                    <a href="mailto:contato@batrip.com" class="footer-link">
                        <i class="fas fa-envelope"></i> contato@batrip.com
                    </a>
                    <a href="tel:+5511123456789" class="footer-link">
                        <i class="fas fa-phone"></i> (11) 123456789
                    </a>
                    <p class="footer-link">
                        <i class="fas fa-map-marker-alt"></i> São Paulo, SP
                    </p>
                </div>
                <div class="col-md-4">
                    <h4 class="footer-title">Redes Sociais</h4>
                    <div>
                        <a href="https://www.instagram.com/batrip___/" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="https://x.com/pradasoueu" class="social-icon"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: var(--border-gray); margin: 2rem 0 1rem;">
            <div class="text-center">
                <p style="color: var(--text-gray); margin: 0;">
                    © 2025 BATRIP. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>