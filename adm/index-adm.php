<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração | Batrip</title>
    <link rel="icon" href="../materials/batrip symbol.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        .admin-section { margin-top: 90px; }
        .product-img-preview {
            width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;
        }
        .table td, .table th { vertical-align: middle; }
        /* Campos não editáveis mais escuros */
        .non-editable {
            background: #23272b !important;
            color: #bbb !important;
            border-color: #23272b !important;
            opacity: 1 !important;
        }
        .non-editable:disabled {
            background: #23272b !important;
            color: #bbb !important;
            border-color: #23272b !important;
            opacity: 1 !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg fixed-top bg-dark">
        <div class="container">
            <a class="navbar-brand text-white" href="#">
                <img src="../materials/batrip png branco.png" alt="Batrip Logo" style="height: 45px; width: auto; display: inline-block; vertical-align: middle; filter: drop-shadow(0 1px 2px rgba(255, 255, 255, 0.15)); transition: filter 0.2s, transform 0.2s;">
                <span class="badge bg-danger ms-2">Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAdmin">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAdmin">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php">Loja</a></li>
                </ul>
                <span class="navbar-text text-white">Painel de Administração</span>
            </div>
        </div>
    </nav>

    <section class="admin-section">
        <div class="container">
            <h2 class="mb-4">Gerenciar Produtos</h2>
            <!-- Formulário para adicionar produto -->
            <div class="card mb-4">
                <div class="card-header">Adicionar Produto</div>
                <div class="card-body">
                    <form id="addProductForm" class="row g-3">
                        <div class="col-md-4">
                            <label for="productName" class="form-label">Nome do Produto</label>
                            <input type="text" class="form-control" id="productName" required>
                        </div>
                        <div class="col-md-2">
                            <label for="productType" class="form-label">Tipo</label>
                            <select class="form-select" id="productType" required>
                                <option value="peça">Peça</option>
                                <option value="conjunto">Conjunto</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="productPrice" class="form-label">Preço (R$)</label>
                            <input type="number" class="form-control" id="productPrice" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label for="productImage" class="form-label">Imagem</label>
                            <input type="file" class="form-control" id="productImage" accept="image/*" required>
                        </div>
                        <div class="col-md-6">
                            <label for="productDescription" class="form-label">Descrição</label>
                            <textarea class="form-control" id="productDescription" rows="2" required></textarea>
                        </div>
                        <div class="col-md-3">
                            <label for="productFabric" class="form-label">Tecido</label>
                            <select class="form-select" id="productFabric" required>
                                <option value="algodao">Algodão</option>
                                <option value="poliester">Poliéster</option>
                                <option value="jeans">Jeans</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="productRecommendation" class="form-label">Recomendações de Uso</label>
                            <textarea class="form-control" id="productRecommendation" rows="2" required readonly></textarea>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-plus"></i> Adicionar</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Tabela de produtos -->
            <div class="card">
                <div class="card-header">Produtos Cadastrados</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" id="productsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Imagem</th>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Preço</th>
                                    <th>Descrição</th>
                                    <th>Tecido</th>
                                    <th>Recomendações</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Produtos serão inseridos aqui via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Pedidos Recebidos -->
    <section class="admin-section">
        <div class="container">
            <h2 class="mb-4 mt-5">Pedidos Recebidos</h2>
            <ul class="nav nav-tabs mb-3" id="ordersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="normal-orders-tab" data-bs-toggle="tab" data-bs-target="#normal-orders" type="button" role="tab">Pedidos Normais</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="custom-orders-tab" data-bs-toggle="tab" data-bs-target="#custom-orders" type="button" role="tab">Pedidos Personalizados</button>
                </li>
            </ul>
            <div class="tab-content" id="ordersTabContent">
                <!-- Pedidos Normais -->
                <div class="tab-pane fade show active" id="normal-orders" role="tabpanel">
                    <div class="card">
                        <div class="card-header">Pedidos Normais</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0" id="normalOrdersTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Valor Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Pedidos normais via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Pedidos Personalizados -->
                <div class="tab-pane fade" id="custom-orders" role="tabpanel">
                    <div class="card">
                        <div class="card-header">Pedidos Personalizados</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0" id="customOrdersTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Email</th>
                                            <th>Descrição</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Pedidos personalizados via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="script-adm.js"></script>
    <script>
    // Recomendações padrão para cada tecido
    const fabricRecommendations = {
        algodao: "Lavar à mão com água fria. Não usar alvejante. Passar em temperatura média. Secar à sombra para maior durabilidade.",
        poliester: "Lavar à máquina em ciclo delicado. Não usar ferro quente. Secar à sombra. Evitar altas temperaturas para não deformar.",
        jeans: "Lavar separadamente das outras peças. Não torcer. Secar pendurado à sombra. Evitar exposição prolongada ao sol para não desbotar."
    };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>