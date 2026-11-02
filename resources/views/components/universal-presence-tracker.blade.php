{{-- Universal Live Presence & Activity Signal Tracker (Darul Ifta Domain-Wide Telemetry) --}}
<script>
(function() {
    let lastPresencePing = 0;
    function sendPresenceHeartbeat() {
        if (document.hidden) return;
        const now = Date.now();
        if (now - lastPresencePing < 20000) return; // Minimum 20s interval throttle
        lastPresencePing = now;

        const payload = JSON.stringify({
            page: window.location.pathname + window.location.search,
            title: document.title || ''
        });

        const targetUrl = '{{ route("user.heartbeat") }}';

        if (navigator.sendBeacon) {
            try {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(targetUrl, blob);
                return;
            } catch (e) {}
        }

        fetch(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: payload
        }).catch(function() {});
    }

    // 1. Initial heartbeat when DOM is ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        sendPresenceHeartbeat();
    } else {
        document.addEventListener('DOMContentLoaded', sendPresenceHeartbeat);
    }

    // 2. Recurring heartbeat every 35 seconds while active
    setInterval(sendPresenceHeartbeat, 35000);

    // 3. Trigger immediate ping when switching tabs back to this page
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            sendPresenceHeartbeat();
        }
    });
})();
</script>

