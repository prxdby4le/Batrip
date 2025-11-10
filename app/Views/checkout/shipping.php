<?php
/**
 * View: Checkout/Shipping (Frete)
 * Tela para calcular e selecionar o frete
 */

$zipcode = $input['zipcode'] ?? '';
$address = $input['address'] ?? '';
$city    = $input['city'] ?? '';
$state   = $input['state'] ?? '';
$weight  = $input['weight'] ?? '';
$length  = $input['length'] ?? '';
$width   = $input['width'] ?? '';
$height  = $input['height'] ?? '';
?>

<section class="checkout-page" style="padding-top: 100px; padding-bottom: 40px;">
    <div class="container">
        <h1 class="mb-4">Calcular Frete</h1>

        <div class="row">
            <div class="col-lg-7">
                <form method="POST" action="<?php echo BASE_URL; ?>frete/calcular">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-geo-alt me-2"></i>Endereço de Entrega
                            </h5>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="zipcode" class="form-label">CEP</label>
                                    <input type="text" class="form-control" id="zipcode" name="zipcode"
                                           value="<?php echo htmlspecialchars($zipcode); ?>" placeholder="00000-000" required>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="address" class="form-label">Endereço</label>
                                    <input type="text" class="form-control" id="address" name="address"
                                           value="<?php echo htmlspecialchars($address); ?>" placeholder="Rua, número">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="city" class="form-label">Cidade</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">Estado</label>
                                    <select class="form-select" id="state" name="state">
                                        <option value="">Selecione</option>
                                        <?php
                                        $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                        foreach ($ufs as $uf) {
                                            $sel = ($state === $uf) ? 'selected' : '';
                                            echo "<option value=\"{$uf}\" {$sel}>{$uf}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <h5 class="card-title mb-3 mt-2">
                                <i class="bi bi-box-seam me-2"></i>Dimensões e Peso
                            </h5>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="weight" class="form-label">Peso (kg)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="weight" name="weight" value="<?php echo htmlspecialchars($weight); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="length" class="form-label">Comprimento (cm)</label>
                                    <input type="number" step="0.1" min="0" class="form-control" id="length" name="length" value="<?php echo htmlspecialchars($length); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="width" class="form-label">Largura (cm)</label>
                                    <input type="number" step="0.1" min="0" class="form-control" id="width" name="width" value="<?php echo htmlspecialchars($width); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="height" class="form-label">Altura (cm)</label>
                                    <input type="number" step="0.1" min="0" class="form-control" id="height" name="height" value="<?php echo htmlspecialchars($height); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-custom btn-lg">
                            <i class="bi bi-truck me-2"></i>Calcular Frete
                        </button>
                    </div>
                </form>

                <?php if (!empty($quotes)): ?>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="bi bi-cash-coin me-2"></i>Opções de frete</h5>

                            <form method="POST" action="<?php echo BASE_URL; ?>frete/selecionar">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                <?php foreach ($quotes as $code => $q): ?>
                                    <div class="form-check mb-3 p-3" style="background: rgba(26,26,26,0.5); border-radius: 10px;">
                                        <input class="form-check-input" type="radio" name="method" id="ship_<?php echo $code; ?>" value="<?php echo $code; ?>" <?php echo ($selected && ($selected['method'] ?? '') === $code) ? 'checked' : ''; ?> required>
                                        <label class="form-check-label w-100" for="ship_<?php echo $code; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($q['label']); ?></strong>
                                                    <div class="small text-muted">Prazo: <?php echo htmlspecialchars($q['eta_days']); ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <span class="fs-5" style="color: var(--accent-red);">R$ <?php echo number_format($q['cost'], 2, ',', '.'); ?></span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i> Usar este frete
                                    </button>
                                    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>checkout">
                                        Voltar ao checkout
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Dicas</h5>
                        <ul class="mb-0">
                            <li>O CEP define a região de entrega.</li>
                            <li>Informe o peso total e, se possível, dimensões do pacote.</li>
                            <li>Após selecionar o frete, ele aparecerá no resumo do checkout.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
