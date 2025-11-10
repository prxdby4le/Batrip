<?php
/**
 * View: Profile/Edit
 * Formulário de edição de perfil
 */
?>

<section class="profile-edit-page" style="padding-top: 100px; padding-bottom: 40px;">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Editar Perfil</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo BASE_URL; ?>perfil/atualizar">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       placeholder="(11) 99999-9999">
                            </div>
                            
                            <div class="mb-3">
                                <label for="zipcode" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="zipcode" name="zipcode" 
                                       value="<?php echo htmlspecialchars($user['zipcode'] ?? ''); ?>" 
                                       placeholder="00000-000">
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Endereço</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" 
                                       placeholder="Rua, número">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="city" class="form-label">Cidade</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="state" class="form-label">Estado</label>
                                    <select class="form-select" id="state" name="state">
                                        <option value="">Selecione</option>
                                        <?php
                                        $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                        $selState = $user['state'] ?? '';
                                        foreach ($ufs as $uf) {
                                            $sel = ($selState === $uf) ? 'selected' : '';
                                            echo "<option value=\"{$uf}\" {$sel}>{$uf}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-custom">
                                    <i class="bi bi-check-circle me-2"></i>Salvar Alterações
                                </button>
                                <a href="<?php echo BASE_URL; ?>perfil" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

