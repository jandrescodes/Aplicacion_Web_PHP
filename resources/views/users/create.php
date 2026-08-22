<section class="mt-3 mb-5">
    <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" class="needs-validation admin-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">
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
                    <div class="col-md-6 mb-3">
                        <label for="usuario" class="form-label fw-bold">Nombre del usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-primary"></i></span>
                            <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Ej: Juan Perez" required>
                        </div>
                        <div class="form-text">El nombre que usará para iniciar sesión.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                            <input type="email" class="form-control" name="correo" id="correo" placeholder="ejemplo@correo.com" required>
                        </div>
                        <div class="form-text">Nunca compartiremos su correo con terceros.</div>
                    </div>

                    <div class="col-md-12 mb-4">
                        <label for="password" class="form-label fw-bold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Mínimo 8 caracteres" minlength="8" required>
                        </div>
                        <div class="form-text">Use una combinación de letras y números para mayor seguridad.</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white py-3">
                <div class="row g-2">
                    <div class="col-12 col-sm-auto g-2">
                        <button type="submit" class="btn btn-primary shadow-sm w-100">
                            <i class="fas fa-save"></i> Guardar Usuario
                        </button>
                    </div>
                    <div class="col-12 col-sm-auto">
                        <a class="btn btn-light border w-100" href="usuarios" role="button">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
