<section class="mt-4 mb-5">
    <!-- Jumbotron de Bienvenida -->
    <div class="p-5 mb-4 bg-white rounded-3 shadow-sm border-0 position-relative overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
        <div class="container-fluid py-5 position-relative" style="z-index: 2;">
            <h1 class="display-5 fw-bold text-dark">
                ¡Bienvenido, <span class="text-primary"><?= isset($nombreUsuario) && $nombreUsuario !== '' ? htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') : 'Usuario'; ?></span>!
            </h1>
            <p class="col-md-8 fs-4 text-muted">
                Has ingresado al Sistema de Gestión Empresarial. Desde aquí podrás administrar el capital humano, definir puestos de trabajo y gestionar el acceso al sistema de forma eficiente.
            </p>
            <hr class="my-4" style="width: 100px; border-top: 3px solid #0d6efd;">
            <p class="mb-0 text-secondary">
                <i class="fas fa-calendar-alt me-2"></i> Hoy es <?= date('d/m/Y'); ?>
            </p>
        </div>
        <!-- Decoración de fondo -->
        <i class="fas fa-laptop-code position-absolute" style="right: -50px; bottom: -50px; font-size: 300px; color: rgba(13, 110, 253, 0.03); transform: rotate(-15deg);"></i>
    </div>

    <div class="row g-4">
        <!-- Acceso a Empleados -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 transition-hover">
                <div class="card-body text-center p-4">
                    <div class="avatar-lg bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                    <h5 class="card-title fw-bold">Empleados</h5>
                    <p class="card-text text-muted small">Gestiona el personal, altas, bajas y genera cartas de recomendación.</p>
                    <a href="empleados" class="btn btn-outline-primary btn-sm rounded-pill px-4">Ir a Empleados</a>
                </div>
            </div>
        </div>

        <!-- Acceso a Puestos -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 transition-hover">
                <div class="card-body text-center p-4">
                    <div class="avatar-lg bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="fas fa-briefcase fa-2x"></i>
                    </div>
                    <h5 class="card-title fw-bold">Puestos</h5>
                    <p class="card-text text-muted small">Define y organiza los roles y cargos dentro de la estructura organizacional.</p>
                    <a href="puestos" class="btn btn-outline-info btn-sm rounded-pill px-4">Ir a Puestos</a>
                </div>
            </div>
        </div>

        <!-- Acceso a Usuarios (Solo Admin) -->
        <?php if (!empty($isAdmin)) : ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 transition-hover">
                    <div class="card-body text-center p-4">
                        <div class="avatar-lg bg-dark bg-opacity-10 text-dark rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                            <i class="fas fa-users-cog fa-2x"></i>
                        </div>
                        <h5 class="card-title fw-bold">Configuración</h5>
                        <p class="card-text text-muted small">Administra los usuarios del sistema y gestiona permisos de acceso.</p>
                        <a href="usuarios" class="btn btn-outline-dark btn-sm rounded-pill px-4">Ir a Usuarios</a>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 bg-light">
                    <div class="card-body d-flex align-items-center justify-content-center p-4">
                        <div class="text-center">
                            <i class="fas fa-lock text-muted mb-2 fa-2x"></i>
                            <p class="text-muted small mb-0">Módulos adicionales restringidos</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>