import Swal from 'sweetalert2';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 4000,
    timerProgressBar: true,
    customClass: {
        popup: 'sppg-toast',
    },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

const titles = {
    success: 'Berhasil',
    error: 'Gagal',
    warning: 'Perhatian',
};

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[c]));
}

window.showAlert = {
    success(message, title) {
        Toast.fire({ icon: 'success', title: title || titles.success, text: message });
    },
    error(message, title) {
        Toast.fire({ icon: 'error', title: title || titles.error, text: message });
    },
    warning(message, title) {
        Toast.fire({ icon: 'warning', title: title || titles.warning, text: message });
    },
    confirm({ title = 'Konfirmasi', text = 'Yakin ingin melanjutkan?', confirmButtonText = 'Ya, lanjutkan', cancelButtonText = 'Batal', icon = 'warning', onConfirm = null }) {
        return Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText,
            reverseButtons: true,
            customClass: {
                confirmButton: 'sppg-confirm-btn',
                cancelButton: 'sppg-cancel-btn',
            },
        }).then((result) => {
            if (result.isConfirmed && typeof onConfirm === 'function') onConfirm();
            return result.isConfirmed;
        });
    },
};

window.confirmSubmit = function (form, text = 'Yakin ingin melanjutkan?', confirmButtonText = 'Ya, lanjutkan') {
    showAlert.confirm({ text, confirmButtonText, onConfirm: () => form.submit() });
    return false;
};

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('app-alerts');
    if (!el) return;

    let data;
    try {
        data = JSON.parse(el.textContent);
    } catch (e) {
        return;
    }

    if (data.type === 'validation' && Array.isArray(data.errors) && data.errors.length) {
        Swal.fire({
            icon: 'error',
            title: data.title || 'Validasi Gagal',
            html: '<ul class="sppg-error-list">' + data.errors.map((e) => `<li>${escapeHtml(e)}</li>`).join('') + '</ul>',
            confirmButtonText: 'Mengerti',
            customClass: {
                confirmButton: 'sppg-confirm-btn',
            },
        });
        return;
    }

    if (data.type === 'success') showAlert.success(data.message);
    else if (data.type === 'error') showAlert.error(data.message);
});
