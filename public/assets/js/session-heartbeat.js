/**
 * Session Heartbeat - keeps Laravel session alive while user is active on the page.
 * Prevents 419 CSRF errors caused by session expiry during long study sessions.
 */
(function () {
    'use strict';

    const HEARTBEAT_INTERVAL = 5 * 60 * 1000; // 5 minutes
    let lastActivity = Date.now();

    function isUserActive() {
        return Date.now() - lastActivity < HEARTBEAT_INTERVAL;
    }

    function sendHeartbeat() {
        if (!isUserActive()) return;

        fetch('/session/heartbeat', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).catch(() => {
            // Silently fail — heartbeat is non-critical
        });
    }

    // Track user activity
    ['mousedown', 'keydown', 'touchstart', 'scroll', 'mousemove'].forEach(function (event) {
        document.addEventListener(event, function () {
            lastActivity = Date.now();
        }, { passive: true });
    });

    // Send heartbeat every 5 minutes
    setInterval(sendHeartbeat, HEARTBEAT_INTERVAL);
})();
