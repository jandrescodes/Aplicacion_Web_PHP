<section class="mt-3 mb-5">
    <form action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" class="needs-validation employee-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-success">
                    <i class="fas fa-id-card me-2"></i>Datos del Empleado
                </h5>
                <span class="badge bg-light text-dark border">ID: <?= htmlspecialchars($txtID, ENT_QUOTES, 'UTF-8'); ?></span>
                <input type="hidden" name="txtID" value="<?= htmlspecialchars($txtID, ENT_QUOTES, 'UTF-8'); ?>">
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
                    <!-- Nombres -->
                    <div class="col-md-6 mb-3">
                        <label for="primernombre" class="form-label fw-bold">Primer Nombre</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-user"></i></span>
                            <input type="text" value="<?= htmlspecialchars($primernombre, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="primernombre" id="primernombre" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="segundonombre" class="form-label fw-bold">Segundo Nombre</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-user-tag"></i></span>
                            <input type="text" value="<?= htmlspecialchars($segundonombre, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="segundonombre" id="segundonombre">
                        </div>
                    </div>

                    <!-- Apellidos -->
                    <div class="col-md-6 mb-3">
                        <label for="primerapellido" class="form-label fw-bold">Primer Apellido</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-id-card"></i></span>
                            <input type="text" value="<?= htmlspecialchars($primerapellido, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="primerapellido" id="primerapellido" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="segundoapellido" class="form-label fw-bold">Segundo Apellido</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-id-card-clip"></i></span>
                            <input type="text" value="<?= htmlspecialchars($segundoapellido, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="segundoapellido" id="segundoapellido" required>
                        </div>
                    </div>

                    <hr class="my-4 text-muted">

                    <!-- Documentos y Puesto -->
                    <div class="col-md-6 mb-4">
                        <label for="foto" class="form-label fw-bold">Foto del Empleado</label>
                        <div class="d-flex align-items-center mb-2">
                            <?php $fotoPath = (string)$foto; ?>
                            <?php if ($fotoPath !== '' && strpos($fotoPath, '/') === false) {
                                $fotoPath = 'storage/uploads/' . $fotoPath;
                            } ?>
                            <div class="me-3 position-relative">
                                <img width="80" height="80" src="<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8'); ?>" class="rounded-circle shadow-sm border object-fit-cover" alt="Foto actual de <?= htmlspecialchars(trim(preg_replace('/\s+/', ' ', $primernombre . " " . $primerapellido)), ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-success border border-light">
                                    <i class="fas fa-check small"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-light text-success"><i class="fas fa-camera"></i></span>
                                    <input type="file" class="form-control" name="foto" id="foto" accept="image/*" aria-describedby="foto-edit-help">
                                </div>
                                <div class="form-text small" id="foto-edit-help">Deje vacío para mantener la foto actual.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="CV" class="form-label fw-bold">Curriculum Vitae (PDF)</label>
                        <div class="mb-2">
                            <?php $cvPath = (string)$cv; ?>
                            <?php if ($cvPath !== '' && strpos($cvPath, '/') === false) {
                                $cvPath = 'storage/uploads/' . $cvPath;
                            } ?>
                            <a href="<?= htmlspecialchars($cvPath, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-outline-info btn-sm mb-2">
                                <i class="fas fa-file-pdf me-1"></i> Ver CV Actual
                            </a>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-light text-success"><i class="fas fa-file-upload"></i></span>
                                <input type="file" class="form-control" name="CV" id="CV" accept=".pdf" aria-describedby="cv-edit-help">
                            </div>
                            <div class="form-text small" id="cv-edit-help">Deje vacío para mantener el CV actual.</div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="idpuesto" class="form-label fw-bold">Puesto de Trabajo</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-briefcase"></i></span>
                            <select class="form-select" name="idpuesto" id="idpuesto" required>
                                <?php foreach ($lista_tbl_puestos as $puesto) : ?>
                                    <option <?= ($idpuesto == $puesto["ID"]) ? "selected" : ""; ?> value="<?= (int)$puesto["ID"]; ?>">
                                        <?= htmlspecialchars($puesto["Nombredelpuesto"], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="fechadeingreso" class="form-label fw-bold">Fecha de Ingreso</label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-light text-success"><i class="fas fa-calendar-check"></i></span>
                            <input type="date" value="<?= htmlspecialchars($fechadeingreso, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" name="fechadeingreso" id="fechadeingreso" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white py-3">
                <div class="row g-2">
                    <div class="col-12 col-sm-auto">
                        <button type="submit" class="btn btn-success shadow-sm w-100">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Empleado
                        </button>
                    </div>
                    <div class="col-12 col-sm-auto">
                        <a class="btn btn-light border w-100" href="empleados" role="button">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
