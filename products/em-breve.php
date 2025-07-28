<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Em Breve | Batrip</title>
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
        <div id="cart-preview">
          <div class="d-flex align-items-center mb-3">
            <img src="img/fragmentado-costa.jpeg" alt="Camiseta Fragmentado Boxy" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
              <div class="fw-bold">Camiseta Fragmentos Boxy</div>
              <div class="text-muted small">1x R$ 149,99</div>
            </div>
            <button class="btn btn-sm btn-link text-danger ms-auto"><i class="fas fa-trash"></i></button>
          </div>
          <div class="d-flex align-items-center mb-3">
            <img src="img/spiderweb-oversized.jpeg" alt="Camiseta Spiderweb Oversized" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
              <div class="fw-bold">Camiseta Spiderweb Oversized</div>
              <div class="text-muted small">1x R$ 149,99</div>
            </div>
            <button class="btn btn-sm btn-link text-danger ms-auto"><i class="fas fa-trash"></i></button>
          </div>
          <hr>
          <div class="d-flex justify-content-between fw-bold">
            <span>Total:</span>
            <span>R$ 299,98</span>
          </div>
          <a href="carrinho.php" class="btn btn-custom w-100 mt-3">Finalizar Compra</a>
        </div>
      </div>
    </div>
    <div class="d-flex justify-content-center align-items-center" style="min-height:100vh;">
        <h1 class="hero-title text-center">em breve</h1>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>