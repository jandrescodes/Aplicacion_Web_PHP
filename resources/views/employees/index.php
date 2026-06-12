<section class="mt-3 mb-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center border-bottom gap-3">
            <h5 class="card-title mb-0">
                <i class="fas fa-list-ul me-2 text-primary"></i>Lista de Registros
            </h5>
            <div class="card-tools">
                <a class="btn btn-primary btn-sm shadow-sm" href="empleados-crear" role="button">
                    <i class="fas fa-user-plus me-1"></i> Agregar Registro
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered" id="tabla_id" data-module="employees" style="visibility: hidden;">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="text-center" style="width: 50px;">#</th>
                        <th scope="col">Nombre Completo</th>
                        <th scope="col">Foto</th>
                        <th scope="col">CV</th>
                        <th scope="col">Puesto</th>
                        <th scope="col">Fecha Ingreso</th>
                        <th scope="col" class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    foreach ($lista_tbl_empleados as $registro) : ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $counter++; ?></td>
                            <td class="fw-medium">
                                <?= htmlspecialchars($registro["Primernombre"] . " " . $registro["Segundonombre"] . " " . $registro["Primerapellido"] . " " . $registro["Segundoapellido"], ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($registro["Foto"])) : ?>
                                    <?php $fotoPath = (string)$registro["Foto"]; ?>
                                    <?php if ($fotoPath !== '' && strpos($fotoPath, '/') === false) {
                                        $fotoPath = 'storage/uploads/' . $fotoPath;
                                    } ?>
                                    <img width="45" src="<?= htmlspecialchars($fotoPath, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded-circle shadow-sm border" alt="Foto" style="height: 45px; object-fit: cover;">
                                <?php else : ?>
                                    <div class="avatar-sm mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center border shadow-sm" style="width: 45px; height: 45px;">
                                        <i class="fas fa-user text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($registro["CV"])) : ?>
                                    <?php $cvPath = (string)$registro["CV"]; ?>
                                    <?php if ($cvPath !== '' && strpos($cvPath, '/') === false) {
                                        $cvPath = 'storage/uploads/' . $cvPath;
                                    } ?>
                                    <a href="<?= htmlspecialchars($cvPath, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-outline-info btn-sm" title="Ver CV">
                                        <i class="fas fa-file-pdf me-1"></i> Ver CV
                                    </a>
                                <?php else : ?>
                                    <span class="badge bg-light text-dark border">Sin CV</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                    <?= htmlspecialchars($registro["puesto"], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> <?= htmlspecialchars($registro["Fecha"], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm" role="group">
                                    <a class="btn btn-outline-info btn-sm"
                                        href="empleados-carta-recomendacion?txtID=<?= urlencode($registro['ID']); ?>"
                                        data-bs-toggle="tooltip"
                                        title="Carta de Recomendación"
                                        target="_blank">
                                        <i class="fas fa-file-signature"></i>
                                    </a>
                                    <a class="btn btn-outline-success btn-sm"
                                        href="empleados-editar?txtID=<?= urlencode($registro['ID']); ?>"
                                        data-bs-toggle="tooltip"
                                        title="Editar Registro">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="eliminarEmpleado(<?= (int)$registro['ID']; ?>)"
                                        data-bs-toggle="tooltip"
                                        title="Eliminar Registro">
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

<!-- Script específico para Empleados -->
<script src="<?= $public_base; ?>js/employees.js"></script>