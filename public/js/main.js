/**
 * Sistema de Gestión Empresarial - Scripts Principales
 */

$(document).ready(function () {
    // --- ANIMACIONES ORIGINALES DEL LOGIN ---
    if ($(".login-form").length > 0 || $("#tarjeta").length > 0) {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!prefersReducedMotion) {
            // Animación continua: las figuras se desplazan y se re-encadenan.
            // Se pausa mientras la pestaña está oculta para no gastar CPU/batería en segundo plano.
            let currentAnim = null;
            let pendingRestart = false;

            function randomValues() {
                currentAnim = anime({
                    targets: '.square, .circle, .triangle',
                    translateX: function () {
                        return anime.random(-window.innerWidth / 2, window.innerWidth / 2);
                    },
                    translateY: function () {
                        return anime.random(-window.innerHeight / 2, window.innerHeight / 2);
                    },
                    rotate: function () {
                        return anime.random(0, 360);
                    },
                    scale: function () {
                        return anime.random(.2, 1.5);
                    },
                    duration: 1000,
                    easing: 'easeInOutQuad',
                    complete: function () {
                        if (document.hidden) {
                            pendingRestart = true;
                        } else {
                            randomValues();
                        }
                    },
                });
            }
            randomValues();

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    if (currentAnim) {
                        currentAnim.pause();
                    }
                } else if (pendingRestart) {
                    pendingRestart = false;
                    randomValues();
                } else if (currentAnim) {
                    currentAnim.play();
                }
            });
        } else {
            // Posicionamiento inicial estático para quienes prefieren poco movimiento
            anime.set('.square, .circle, .triangle', {
                translateX: function () { return anime.random(-window.innerWidth / 3, window.innerWidth / 3); },
                translateY: function () { return anime.random(-window.innerHeight / 3, window.innerHeight / 3); },
                rotate: function () { return anime.random(0, 360); },
                scale: function () { return anime.random(.5, 1); }
            });
        }
    }

    // --- TOGGLE DE VISIBILIDAD DE CONTRASEÑA (LOGIN) ---
    var $togglePassword = $('#togglePassword');
    if ($togglePassword.length > 0) {
        $togglePassword.on('click', function () {
            var $password = $('#password');
            var $icon = $togglePassword.find('i');
            var isHidden = $password.attr('type') === 'password';

            $password.attr('type', isHidden ? 'text' : 'password');
            $icon.toggleClass('bi-eye-fill', !isHidden).toggleClass('bi-eye-slash-fill', isHidden);
            $togglePassword.attr('aria-pressed', isHidden ? 'true' : 'false');
            $togglePassword.attr('aria-label', isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    }

    // --- INICIALIZACIÓN DE DATATABLES ---
    var $table = $("#tabla_id");
    if ($table.length > 0) {
        $table.css('visibility', 'hidden');
        var module = $table.data('module');
        var moduleConfigs = {
            users: {
                title: 'Reporte de Usuarios - Sistema de Gestión Hospitalaria',
                filename: 'reporte_usuarios_' + new Date().toISOString().slice(0, 10),
                subtitle: 'Usuarios registrados en el sistema',
                exportColumns: [0, 1, 2],
                sInfo: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ usuarios',
                sInfoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 usuarios',
                sInfoFiltered: '(filtrado de un total de _MAX_ usuarios)'
            },
            employees: {
                title: 'Reporte de Empleados - Sistema de Gestión Hospitalaria',
                filename: 'reporte_empleados_' + new Date().toISOString().slice(0, 10),
                subtitle: 'Empleados registrados en el sistema',
                exportColumns: [0, 1, 4, 5],
                sInfo: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ empleados',
                sInfoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 empleados',
                sInfoFiltered: '(filtrado de un total de _MAX_ empleados)'
            },
            positions: {
                title: 'Reporte de Puestos - Sistema de Gestión Hospitalaria',
                filename: 'reporte_puestos_' + new Date().toISOString().slice(0, 10),
                subtitle: 'Puestos registrados en el sistema',
                exportColumns: [0, 1],
                sInfo: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ puestos',
                sInfoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 puestos',
                sInfoFiltered: '(filtrado de un total de _MAX_ puestos)'
            },
            audit: {
                title: 'Reporte de Auditoría - Sistema de Gestión Hospitalaria',
                filename: 'reporte_auditoria_' + new Date().toISOString().slice(0, 10),
                subtitle: 'Registro de acciones del sistema',
                exportColumns: [0, 1, 2, 3, 4, 5],
                sInfo: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ entradas',
                sInfoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 entradas',
                sInfoFiltered: '(filtrado de un total de _MAX_ entradas)'
            }
        };

        var cfg = moduleConfigs[module] || {
            title: 'Reporte - Sistema de Gestión Hospitalaria',
            filename: 'reporte_' + new Date().toISOString().slice(0, 10),
            subtitle: '',
            exportColumns: ':visible',
            sInfo: 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
            sInfoEmpty: 'Mostrando registros del 0 al 0 de un total de 0 registros',
            sInfoFiltered: '(filtrado de un total de _MAX_ registros)'
        };

        var dt = $table.DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            buttons: [
                {
                    extend: 'collection',
                    text: '<i class="fas fa-file-export me-1"></i> Reportes',
                    orientation: 'landscape',
                    buttons: [
                        {
                            text: '<i class="fas fa-copy me-1"></i> Copiar',
                            extend: 'copy',
                            exportOptions: { columns: cfg.exportColumns }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                            title: cfg.title,
                            filename: cfg.filename,
                            pageSize: 'LEGAL',
                            exportOptions: { columns: cfg.exportColumns },
                            customize: function (doc) {
                                doc.defaultStyle.fontSize = 10;
                                doc.styles.tableHeader.fontSize = 11;
                                doc.styles.tableHeader.fillColor = '#4b545c';
                                doc.styles.tableHeader.color = '#ffffff';
                                doc.content.splice(0, 1, {
                                    text: cfg.title.toUpperCase(),
                                    style: { fontSize: 16, alignment: 'center', bold: true, margin: [0, 10, 0, 10] }
                                });
                                doc.content.splice(1, 0, {
                                    text: cfg.subtitle,
                                    style: { fontSize: 11, alignment: 'center', italics: true, margin: [0, 0, 0, 10] }
                                });
                                doc.content.splice(2, 0, {
                                    text: 'Generado el: ' + new Date().toLocaleString('es-BO'),
                                    style: { fontSize: 9, alignment: 'right', margin: [0, 0, 0, 10] }
                                });
                                doc.footer = function (currentPage, pageCount) {
                                    return {
                                        columns: [
                                            { text: 'Sistema de Gestión Hospitalaria', alignment: 'left', fontSize: 8 },
                                            { text: 'Página ' + currentPage + ' de ' + pageCount, alignment: 'center', fontSize: 8 },
                                            { text: 'Confidencial', alignment: 'right', fontSize: 8 }
                                        ],
                                        margin: [40, 0]
                                    };
                                };
                            }
                        },
                        {
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-1"></i> Excel',
                            title: cfg.title,
                            filename: cfg.filename,
                            messageTop: cfg.subtitle,
                            messageBottom: 'Documento generado el ' + new Date().toLocaleDateString('es-BO'),
                            exportOptions: { columns: cfg.exportColumns }
                        },
                        {
                            extend: 'csv',
                            text: '<i class="fas fa-file-csv me-1"></i> CSV',
                            filename: cfg.filename,
                            exportOptions: { columns: cfg.exportColumns }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-1"></i> Imprimir',
                            title: cfg.title,
                            messageTop: 'Reporte generado el ' + new Date().toLocaleDateString('es-BO'),
                            exportOptions: { columns: cfg.exportColumns },
                            customize: function (win) {
                                $(win.document.body).find('table').addClass('table-striped').css('font-size', '12px');
                            }
                        }
                    ]
                },
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-columns me-1"></i> Columnas'
                }
            ],
            language: {
                sProcessing: 'Procesando...',
                sLengthMenu: 'Mostrar _MENU_ registros',
                sZeroRecords: 'No se encontraron resultados',
                sEmptyTable: 'Ningún dato disponible en esta tabla',
                sInfo: cfg.sInfo,
                sInfoEmpty: cfg.sInfoEmpty,
                sInfoFiltered: cfg.sInfoFiltered,
                sSearch: 'Buscar:',
                sInfoThousands: ',',
                sLoadingRecords: 'Cargando...',
                oPaginate: {
                    sFirst: 'Primero',
                    sLast: 'Último',
                    sNext: 'Siguiente',
                    sPrevious: 'Anterior'
                },
                oAria: {
                    sSortAscending: ': Activar para ordenar de manera ascendente',
                    sSortDescending: ': Activar para ordenar de manera descendente'
                }
            },
            initComplete: function () {
                $(this.api().table().node()).css('visibility', 'visible');
            }
        });

        dt.buttons().container().addClass('mt-2').appendTo('#tabla_id_wrapper .col-md-6:eq(0)');
    }

    // --- VALIDACIÓN DE FORMULARIOS BOOTSTRAP (needs-validation) ---
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // --- TOOLTIPS DE BOOTSTRAP ---
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

/**
 * Función para confirmar el borrado de un registro
 */
function borrar(formId) {
    Swal.fire({
        title: '¿Está seguro de borrar el registro?',
        text: '¡Una vez borrado no se puede recuperar!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, elimínelo',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            }
        }
    });
}
