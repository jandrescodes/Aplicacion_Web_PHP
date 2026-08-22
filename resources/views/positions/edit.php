<section class="mt-3 mb-5">
    <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="needs-validation admin-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-success">
                    <i class="fas fa-id-card me-2"></i>Datos del Puesto
                </h5>
                <span class="badge bg-light text-dark border">ID: <?= htmlspecialchars((string)$txtID, ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="hidden" name="txtID" value="<?= htmlspecialchars((string)$txtID, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="card-body p-4">
                <?php if (isset($mensaje) && $mensaje !== null && $mensaje !== '') : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="nombredelpuesto" class="form-label fw-bold">Nombre del Puesto</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-light text-success"><i class="fas fa-briefcase"></i></span>
                        <input type="text" value="<?= htmlspecialchars((string)$nombredelpuesto, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="nombredelpuesto" id="nombredelpuesto" required>
                    </div>
                    <div class="form-text mt-2">Modifique el nombre del cargo o puesto laboral.</div>
                </div>
            </div>
            <div class="card-footer bg-white py-3">
                <div class="row g-2">
                    <div class="col-12 col-sm-auto">
                        <button type="submit" class="btn btn-success shadow-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Puesto
                        </button>
                    </div>
                    <div class="col-12 col-sm-auto">
                        <a class="btn btn-light border w-100" href="puestos" role="button">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
