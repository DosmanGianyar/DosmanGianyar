<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const iconMap = { success: 'success', danger: 'error', warning: 'warning', info: 'info' };

    function showSwal(notification) {
        const duration = (notification.duration && notification.duration !== 'persistent')
            ? notification.duration
            : 4000;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: iconMap[notification.status] ?? 'info',
            title: notification.title ?? '',
            html: notification.body ? `<span style="font-size:.85em">${notification.body}</span>` : undefined,
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });
    }

    // Override Alpine notificationComponent after Alpine boots
    // (Filament registers the original in alpine:init, we replace it in alpine:initialized)
    document.addEventListener('alpine:initialized', function () {
        if (typeof Alpine === 'undefined' || !Alpine.data) return;

        Alpine.data('notificationComponent', function ({ notification }) {
            return {
                isShown: false,

                init() {
                    showSwal(notification);

                    // Immediately tell Livewire the notification was closed
                    // so it removes it from the collection
                    setTimeout(() => {
                        window.dispatchEvent(
                            new CustomEvent('notificationClosed', {
                                detail: { id: notification.id },
                            })
                        );
                    }, 0);
                },

                close() {
                    window.dispatchEvent(
                        new CustomEvent('notificationClosed', {
                            detail: { id: notification.id },
                        })
                    );
                },

                destroy() {},
            };
        });
    });
})();
</script>
