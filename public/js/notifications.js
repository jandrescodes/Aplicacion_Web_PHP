/**
 * Sistema de Gestión Empresarial - Notificaciones Toast
 */

const showToast = (icon, message) => {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    Toast.fire({
        icon: icon,
        titleText: message
    });
};

// Verificar si hay mensajes flash al cargar el documento
$(document).ready(function() {
    const flashEl = document.getElementById('flash-data');
    if (flashEl) {
        const icon = flashEl.getAttribute('data-icon');
        const message = flashEl.getAttribute('data-message');
        if (icon && message) {
            showToast(icon, message);
        }
    }
});
