<section class="mt-3 mb-5">
    <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" class="needs-validation admin-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-success">
                    <i class="fas fa-id-card me-2"></i>Datos del Usuario
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if (isset($mensaje) && $mensaje !== null && $mensaje !== '') : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>


                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="txtID" class="form-label fw-bold">ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-hashtag text-muted"></i></span>
                            <input type="text" value="<?= htmlspecialchars((string)$txtID, ENT_QUOTES, 'UTF-8'); ?>" class="form-control bg-light" readonly name="txtID" id="txtID">
                        </div>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label for="usuario" class="form-label fw-bold">Nombre del usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-success"></i></span>
                            <input type="text" value="<?= htmlspecialchars((string)$usuario, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="usuario" id="usuario" placeholder="Ej: Juan Perez" required>
                        </div>
                    </div>

                    <div class="col-md-5 mb-3">
                        <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-success"></i></span>
                            <input type="email" value="<?= htmlspecialchars((string)$correo, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="correo" id="correo" placeholder="ejemplo@correo.com" required>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label for="password" class="form-label fw-bold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock text-success"></i></span>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Nueva contraseña" minlength="8">
                        </div>
                        <div class="form-text text-info">
                            <i class="fas fa-info-circle me-1"></i> Déjelo vacío para conservar la contraseña actual.
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white py-3">
                <div class="row g-2">
                    <div class="col-12 col-sm-auto mb-2 mb-sm-0">
                        <button type="submit" class="btn btn-success text-white shadow-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Usuario
                        </button>
                    </div>
                    <div class="col-12 col-sm-auto">
                        <a class="btn btn-light border w-100" href="usuarios" role="button">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
