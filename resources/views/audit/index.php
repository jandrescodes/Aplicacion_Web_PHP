<section class="mb-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-primary">
                <i class="fas fa-list me-2"></i>Registro de Auditoría
            </h5>
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered" id="tabla_id" data-module="audit" style="visibility: hidden;">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 80px;">#</th>
                        <th>Usuario (ID)</th>
                        <th>Acción</th>
                        <th>Entidad</th>
                        <th>ID Entidad</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry) : ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?= (int)$entry['id']; ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($entry['user_id'] !== null) : ?>
                                    <span class="badge bg-secondary"><?= (int)$entry['user_id']; ?></span>
                                <?php else : ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $actionMap = [
                                    'create' => ['bg-success', 'fas fa-plus-circle', 'Crear'],
                                    'update' => ['bg-warning text-dark', 'fas fa-pen', 'Editar'],
                                    'delete' => ['bg-danger', 'fas fa-trash-alt', 'Eliminar'],
                                ];
                                $a = $actionMap[$entry['action']] ?? ['bg-secondary', 'fas fa-circle', $entry['action']];
                                ?>
                                <span class="badge <?= $a[0]; ?>">
                                    <i class="<?= $a[1]; ?> me-1"></i><?= $a[2]; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $entityMap = [
                                    'employee' => ['fas fa-user-tie', 'Empleado'],
                                    'position' => ['fas fa-briefcase', 'Puesto'],
                                    'user'     => ['fas fa-users-cog', 'Usuario'],
                                ];
                                $e = $entityMap[$entry['entity']] ?? ['fas fa-circle', $entry['entity']];
                                ?>
                                <i class="<?= $e[0]; ?> me-1 text-muted"></i><?= $e[1]; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($entry['entity_id'] !== null) : ?>
                                    <code><?= (int)$entry['entity_id']; ?></code>
                                <?php else : ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($entry['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>