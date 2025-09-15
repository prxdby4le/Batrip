<?php $pageTitle = 'Endereço de Entrega | Batrip'; ?>
<?php include '../../includes/head.php'; ?>
<body>
    <?php include '../../includes/nav.php'; ?>
    <?php include '../../includes/cart-sidebar.php'; ?>
    <div class="navbar-space"></div>
    <section class="section" style="min-height:60vh;">
        <div class="container">
            <h2 class="section-title mb-4"><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h2>
            <form class="row g-3" autocomplete="off">
                <div class="col-md-8">
                    <label for="cep" class="form-label">CEP</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="cep" name="cep" maxlength="9" required placeholder="00000-000">
                        <button class="btn btn-outline-secondary" type="button" id="buscar-cep">Buscar</button>
                    </div>
                </div>
                <div class="col-md-8">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" required>
                </div>
                <div class="col-md-4">
                    <label for="numero" class="form-label">Número</label>
                    <input type="text" class="form-control" id="numero" name="numero" required>
                </div>
                <div class="col-md-6">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" class="form-control" id="bairro" name="bairro" required>
                </div>
                <div class="col-md-6">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" class="form-control" id="cidade" name="cidade" required>
                </div>
                <div class="col-md-4">
                    <label for="uf" class="form-label">UF</label>
                    <input type="text" class="form-control" id="uf" name="uf" maxlength="2" required>
                </div>
                <div class="col-md-8">
                    <label for="complemento" class="form-label">Complemento</label>
                    <input type="text" class="form-control" id="complemento" name="complemento">
                </div>
                <div class="col-12">
                    <label for="comentario" class="form-label">Comentário para entrega</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="2" placeholder="Ex: Deixar na portaria, tocar campainha, etc."></textarea>
                </div>
                <div class="col-12">
                    <a href="checkout/frete.php" class="btn btn-custom w-100">Continuar para Frete</a>
                </div>
            </form>
        </div>
    </section>
    <?php include '../../includes/footer.php'; ?>
    <?php include '../../includes/scripts.php'; ?>
    <script>
    // Busca de CEP via ViaCEP
    (function(){
        const btn = document.getElementById('buscar-cep');
        if (!btn) return;
        btn.addEventListener('click', function(){
            var cep = document.getElementById('cep').value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch('https://viacep.com.br/ws/' + cep + '/json/')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco').value = data.logradouro || '';
                            document.getElementById('bairro').value = data.bairro || '';
                            document.getElementById('cidade').value = data.localidade || '';
                            document.getElementById('uf').value = data.uf || '';
                        } else {
                            alert('CEP não encontrado.');
                        }
                    })
                    .catch(() => alert('Falha ao consultar CEP.'));
            } else {
                alert('Digite um CEP válido.');
            }
        });
    })();
    </script>
</body>
</html>

