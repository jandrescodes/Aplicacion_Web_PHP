<section class="mb-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-primary">
                <i class="fas fa-list me-2"></i>Listado de Usuarios
            </h5>
            <a class="btn btn-primary btn-sm" href="usuarios-crear" role="button">
                <i class="fas fa-plus-circle me-1"></i>Agregar Usuario
            </a>
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered" id="tabla_id" data-module="users" style="visibility: hidden;">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="text-center" style="width: 80px;">#</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    foreach ($lista_tbl_usuarios as $registro) :
                    ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?= $counter++; ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-user small"></i>
                                    </div>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($registro['Nombreusuario'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted">
                                    <i class="fas fa-envelope me-2 small"></i><?= htmlspecialchars($registro['Correo'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm" role="group">
                                    <a class="btn btn-outline-success btn-sm"
                                        href="usuarios-editar?txtID=<?= urlencode($registro['ID']); ?>"
                                        data-bs-toggle="tooltip"
                                        title="Editar Usuario">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="eliminarUsuario(<?= (int)$registro['ID']; ?>)"
                                        data-bs-toggle="tooltip"
                                        title="Eliminar Usuario">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="<?= $public_base; ?>js/users.js"></script>