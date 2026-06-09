<?php
$usuario   = htmlspecialchars((string)($Nombreusuario ?? ''), ENT_QUOTES, 'UTF-8');
$correo    = htmlspecialchars((string)($Correo ?? ''), ENT_QUOTES, 'UTF-8');
$inicial   = strtoupper(mb_substr((string)($Nombreusuario ?? '?'), 0, 1));
$esAdmin   = !empty($isAdmin);
$csrfToken = \Core\Security::getCsrfToken();
?>

<div class="row mt-3 g-4">

  <!-- Columna lateral: tarjeta de perfil -->
  <div class="col-md-4 col-lg-3">
    <div class="card shadow-sm text-center">
      <div class="card-body py-4">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center bg-primary text-white fw-bold mb-3"
          style="width:80px;height:80px;font-size:2rem;">
          <?= $inicial; ?>
        </div>
        <h5 class="card-title mb-1 fw-bold"><?= $usuario; ?></h5>
        <p class="text-muted small mb-3"><?= $correo ?: '—'; ?></p>
        <?php if ($esAdmin): ?>
          <span class="badge bg-danger px-3 py-2">
            <i class="fas fa-shield-alt me-1"></i>Administrador
          </span>
        <?php else: ?>
          <span class="badge bg-secondary px-3 py-2">
            <i class="fas fa-user me-1"></i>Usuario
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Columna principal: formularios separados -->
  <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">

    <!-- Formulario 1: Datos de cuenta -->
    <form action="<?= htmlspecialchars($public_base, ENT_QUOTES, 'UTF-8'); ?>perfil-datos"
      method="POST" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
      <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
          <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-id-card me-2"></i>Datos de cuenta
          </h5>
        </div>
        <div class="card-body p-4">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="usuario" class="form-label fw-bold">Nombre de usuario</label>
              <div class="input-group shadow-sm">
                <span class="input-group-text bg-light text-primary">
                  <i class="fas fa-user"></i>
                </span>
                <input type="text" class="form-control" id="usuario" name="usuario"
                  value="<?= $usuario; ?>" required maxlength="100">
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="correo" class="form-label fw-bold">Correo electrónico</label>
              <div class="input-group shadow-sm">
                <span class="input-group-text bg-light text-primary">
                  <i class="fas fa-envelope"></i>
                </span>
                <input type="email" class="form-control" id="correo" name="correo"
                  value="<?= $correo; ?>" required maxlength="150">
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-white py-3">
          <button type="submit" class="btn btn-primary shadow-sm">
            <i class="fas fa-save me-1"></i>Guardar datos
          </button>
        </div>
      </div>
    </form>

    <!-- Formulario 2: Cambio de contraseña -->
    <form action="<?= htmlspecialchars($public_base, ENT_QUOTES, 'UTF-8'); ?>perfil-contrasena"
      method="POST" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
          <h5 class="mb-0 fw-bold text-primary">
            <i class="fas fa-key me-2"></i>Cambiar contraseña
          </h5>
        </div>
        <div class="card-body p-4">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="currentPassword" class="form-label fw-bold">Contraseña actual</label>
              <div class="input-group shadow-sm">
                <span class="input-group-text bg-light text-primary">
                  <i class="fas fa-lock"></i>
                </span>
                <input type="password" class="form-control" id="currentPassword"
                  name="currentPassword" autocomplete="current-password">
              </div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="newPassword" class="form-label fw-bold">Nueva contraseña</label>
              <div class="input-group shadow-sm">
                <span class="input-group-text bg-light text-primary">
                  <i class="fas fa-lock-open"></i>
                </span>
                <input type="password" class="form-control" id="newPassword"
                  name="newPassword" autocomplete="new-password" minlength="8">
              </div>
              <div class="form-text">Mínimo 8 caracteres.</div>
            </div>
            <div class="col-md-4 mb-3">
              <label for="confirmNewPassword" class="form-label fw-bold">Confirmar contraseña</label>
              <div class="input-group shadow-sm">
                <span class="input-group-text bg-light text-primary">
                  <i class="fas fa-lock-open"></i>
                </span>
                <input type="password" class="form-control" id="confirmNewPassword"
                  name="confirmNewPassword" autocomplete="new-password">
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer bg-white py-3">
          <button type="submit" class="btn btn-warning shadow-sm">
            <i class="fas fa-key me-1"></i>Cambiar contraseña
          </button>
        </div>
      </div>
    </form>

  </div>
</div>