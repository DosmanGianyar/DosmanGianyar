<script>
    (function() {
        const unreadApiUrl = "{{ route('chat.unread-count') }}";
        let lastKnownCount = null;

        async function checkGlobalUnreadChat() {
            try {
                const res = await fetch(unreadApiUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const count = parseInt(data.unread) || 0;

                // Update semua badge unread di halaman
                document.querySelectorAll('.chat-unread-badge').forEach(badge => {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.classList.remove('hidden');
                        if (lastKnownCount !== null && count > lastKnownCount) {
                            badge.classList.add('animate-bounce');
                            setTimeout(() => badge.classList.remove('animate-bounce'), 3000);
                        }
                    } else {
                        badge.classList.add('hidden');
                    }
                });

                lastKnownCount = count;
            } catch (e) {
                // Ignore silent network failure
            }
        }

        // Cek tiap 45 detik saat tab browser aktif
        setInterval(() => {
            if (!document.hidden) {
                checkGlobalUnreadChat();
            }
        }, 45000);
    })();
</script>
