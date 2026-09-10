/**
 * Sistema de Gestión Empresarial - Lógica de Empleados
 */

/**
 * Confirma y ejecuta la eliminación de un empleado vía AJAX
 * @param {HTMLElement} trigger - Botón que disparó la confirmación (para restaurar foco)
 * @param {number} id - ID del empleado a eliminar
 */
function eliminarEmpleado(trigger, id) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    Swal.fire({
        title: '¿Está seguro de borrar el registro?',
        text: '¡Una vez borrado no se puede recuperar!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, elimínelo',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        didClose: () => trigger?.focus()
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar cargando
            Swal.fire({
                title: 'Procesando...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'empleados-eliminar',
                type: 'POST',
                data: {
                    txtID: id,
                    csrf_token: csrfToken
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (response) {
                    if (response.success) {
                        showToast('success', response.message);
                        // Recargar la página después de un breve delay para que se vea el toast
                        setTimeout(() => {
                            location.reload();
                        }, 5500);
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function () {
                    showToast('error', 'No se pudo eliminar el empleado. Intente nuevamente.');
                }
            });
        }
    });
}
