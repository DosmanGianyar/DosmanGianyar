import './bootstrap';
import Swal from 'sweetalert2';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

window.Swal = Swal;

// ── Toast helper (used from layouts) ──────────────────────────────────────────
window.swalToast = (icon, title) => Swal.fire({
    toast: true,
    position: 'top-end',
    icon,
    title,
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    customClass: { popup: 'swal-toast-popup' },
});

// ── Global data-confirm handler ────────────────────────────────────────────────
// Add data-confirm="Pesan…" to any <form> to get a SweetAlert confirm before submit.
document.addEventListener('submit', function (e) {
    const msg = e.target.dataset.confirm;
    if (!msg) return;

    e.preventDefault();
    const form = e.target;

    Swal.fire({
        title: 'Konfirmasi',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then(result => {
        if (result.isConfirmed) {
            delete form.dataset.confirm; // prevent re-trigger
            form.submit();
        }
    });
});

// ── Global swalAlert helper (replaces native alert()) ──────────────────────────
window.swalAlert = (text, icon = 'warning', title = 'Perhatian') =>
    Swal.fire({ icon, title, text, confirmButtonColor: '#2563eb' });
