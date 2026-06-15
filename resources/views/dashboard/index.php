<section class="mb-5">

    <!-- Encabezado de bienvenida -->
    <div class="p-4 mb-4 bg-white rounded-3 shadow-sm border-0 position-relative overflow-hidden mt-4">
        <div class="position-relative" style="z-index: 2;">
            <h1 class="fw-bold text-dark mb-1 fs-3">
                <i class="fas fa-gauge-high me-2 text-primary"></i>Dashboard
            </h1>
            <p class="text-muted mb-0">
                Bienvenido, <strong><?= htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></strong>
                &mdash; <i class="fas fa-calendar-alt me-1"></i><?= date('d/m/Y'); ?>
            </p>
        </div>
        <i class="fas fa-gauge-high position-absolute" style="right:-30px;bottom:-40px;font-size:220px;color:rgba(13,110,253,0.04);transform:rotate(-10deg);"></i>
    </div>

    <!-- Cards de totales -->
    <div class="row g-4 mb-4">

        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:60px;height:60px;">
                        <i class="fas fa-user-tie fa-xl"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1"><?= $total_empleados; ?></div>
                        <div class="text-muted small mt-1">Empleados</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <a href="empleados" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-right me-1"></i> Ver módulo
                    </a>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:60px;height:60px;">
                        <i class="fas fa-briefcase fa-xl"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1"><?= $total_puestos; ?></div>
                        <div class="text-muted small mt-1">Puestos</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <a href="puestos" class="btn btn-outline-info btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-right me-1"></i> Ver módulo
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($isAdmin)) : ?>
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3 p-4">
                    <div class="rounded-circle bg-dark bg-opacity-10 text-dark d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:60px;height:60px;">
                        <i class="fas fa-users-cog fa-xl"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold lh-1"><?= $total_usuarios; ?></div>
                        <div class="text-muted small mt-1">Usuarios</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                    <a href="usuarios" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-right me-1"></i> Ver módulo
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Distribución de empleados por puesto -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-chart-bar me-2 text-primary"></i>Empleados por puesto
            </h5>
        </div>
        <div class="card-body px-4 pb-4">
            <?php if (empty($distribucion_por_puesto)) : ?>
                <p class="text-muted mb-0">Sin datos registrados.</p>
            <?php else : ?>
                <?php $max = max(1, max(array_column($distribucion_por_puesto, 'total'))); ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($distribucion_por_puesto as $item) : ?>
                        <?php $pct = round((int)$item['total'] / $max * 100); ?>
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-semibold"><?= htmlspecialchars($item['puesto'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="small text-muted"><?= (int)$item['total']; ?> <?= (int)$item['total'] === 1 ? 'empleado' : 'empleados'; ?></span>
                            </div>
                            <div class="progress" style="height:10px;" role="progressbar"
                                 aria-valuenow="<?= $pct; ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-primary" style="width:<?= $pct; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</section>
