<?php
/**
 * View: Admin Users Index
 */

$users = $users ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-people me-2"></i>Usuários</h3>
    <div class="text-muted">
        Total: <?php echo count($users); ?> usuário(s)
    </div>
</div>

<?php if (!empty($users)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th style="width: 100px;">Role</th>
                            <th style="width: 150px;">Cadastro</th>
                            <th style="width: 150px;" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-fill me-1"></i>Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td class="text-end">
                                    <a href="<?php echo BASE_URL; ?>adm/usuarios/<?php echo $user['id']; ?>/editar" 
                                       class="btn btn-sm btn-outline-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="<?php echo BASE_URL; ?>adm/usuarios/<?php echo $user['id']; ?>/deletar" 
                                              style="display: inline;" onsubmit="return confirm('Tem certeza que deseja deletar este usuário?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Deletar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Você mesmo">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-people" style="font-size: 4rem; color: var(--text-gray);"></i>
            <h4 class="mt-3">Nenhum usuário encontrado</h4>
        </div>
    </div>
<?php endif; ?>
