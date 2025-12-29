/**
 * Real-time Notification System
 * Uses Server-Sent Events (SSE) for instant notifications
 */

class NotificationManager {
    constructor(options = {}) {
        this.apiUrl = options.apiUrl || '/api/notifications-stream.php';
        this.notificationApiUrl = options.notificationApiUrl || '/api/notifications.php';
        this.eventSource = null;
        this.retryCount = 0;
        this.maxRetries = 5;
        this.retryDelay = 3000;
        this.onNotification = options.onNotification || this.defaultNotificationHandler;
        this.onUnreadCount = options.onUnreadCount || this.defaultUnreadHandler;
        this.onUpcomingSessions = options.onUpcomingSessions || (() => {});
        this.onError = options.onError || console.error;
        this.initialized = false;
    }

    /**
     * Initialize the notification system
     */
    init() {
        if (this.initialized) return;

        // Check if SSE is supported
        if (!window.EventSource) {
            console.warn('SSE not supported, falling back to polling');
            this.startPolling();
            return;
        }

        this.connect();
        this.initialized = true;

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.disconnect();
            } else {
                this.connect();
            }
        });
    }

    /**
     * Connect to SSE stream
     */
    connect() {
        if (this.eventSource) {
            this.disconnect();
        }

        try {
            this.eventSource = new EventSource(this.apiUrl);

            this.eventSource.addEventListener('connected', (event) => {
                console.log('Connected to notification stream');
                this.retryCount = 0;
            });

            this.eventSource.addEventListener('notification', (event) => {
                const notification = JSON.parse(event.data);
                this.onNotification(notification);
            });

            this.eventSource.addEventListener('unread_count', (event) => {
                const data = JSON.parse(event.data);
                this.onUnreadCount(data.count);
            });

            this.eventSource.addEventListener('upcoming_sessions', (event) => {
                const data = JSON.parse(event.data);
                this.onUpcomingSessions(data.sessions);
            });

            this.eventSource.addEventListener('timeout', () => {
                console.log('Connection timeout, reconnecting...');
                this.reconnect();
            });

            this.eventSource.addEventListener('error', (event) => {
                if (event.target.readyState === EventSource.CLOSED) {
                    console.log('Connection closed, reconnecting...');
                    this.reconnect();
                }
            });

        } catch (error) {
            console.error('Failed to connect to notification stream:', error);
            this.reconnect();
        }
    }

    /**
     * Disconnect from SSE stream
     */
    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
            this.eventSource = null;
        }
    }

    /**
     * Reconnect with exponential backoff
     */
    reconnect() {
        this.disconnect();

        if (this.retryCount >= this.maxRetries) {
            console.warn('Max retries reached, falling back to polling');
            this.startPolling();
            return;
        }

        const delay = this.retryDelay * Math.pow(2, this.retryCount);
        this.retryCount++;

        console.log(`Reconnecting in ${delay}ms... (attempt ${this.retryCount})`);

        setTimeout(() => {
            this.connect();
        }, delay);
    }

    /**
     * Fallback polling mechanism
     */
    startPolling() {
        this.pollInterval = setInterval(() => {
            this.fetchNotifications();
        }, 30000); // Poll every 30 seconds

        // Initial fetch
        this.fetchNotifications();
    }

    /**
     * Fetch notifications via API
     */
    async fetchNotifications() {
        try {
            const response = await fetch(`${this.notificationApiUrl}?action=list&limit=5&unread_only=1`);
            const data = await response.json();

            if (data.success) {
                this.onUnreadCount(data.unread_count);
                data.notifications.forEach(n => this.onNotification(n, true));
            }
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch(this.notificationApiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'mark_as_read',
                    id: notificationId
                })
            });
            return await response.json();
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
            return { success: false };
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await fetch(this.notificationApiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_all_as_read' })
            });
            return await response.json();
        } catch (error) {
            console.error('Failed to mark all as read:', error);
            return { success: false };
        }
    }

    /**
     * Default notification handler - shows browser notification
     */
    defaultNotificationHandler(notification, isSilent = false) {
        // Update UI notification badge/list if elements exist
        this.updateNotificationUI(notification);

        // Show browser notification if permission granted and not silent
        if (!isSilent && Notification.permission === 'granted') {
            this.showBrowserNotification(notification);
        }

        // Show toast notification
        if (!isSilent && window.showToast) {
            showToast(notification.title, this.getToastType(notification.type));
        }
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(notification) {
        const browserNotification = new Notification(notification.title, {
            body: notification.message,
            icon: '/assets/images/logo.png',
            tag: `notification-${notification.id}`,
            requireInteraction: notification.type.includes('live_session')
        });

        browserNotification.onclick = () => {
            window.focus();
            if (notification.link) {
                window.location.href = notification.link;
            }
            browserNotification.close();
        };

        // Auto-close after 10 seconds
        setTimeout(() => browserNotification.close(), 10000);
    }

    /**
     * Update notification UI elements
     */
    updateNotificationUI(notification) {
        const notificationList = document.getElementById('notification-list');
        if (notificationList) {
            const html = this.createNotificationHTML(notification);
            notificationList.insertAdjacentHTML('afterbegin', html);

            // Limit to 10 notifications in list
            while (notificationList.children.length > 10) {
                notificationList.removeChild(notificationList.lastChild);
            }
        }
    }

    /**
     * Create notification HTML
     */
    createNotificationHTML(notification) {
        const colorClasses = {
            green: 'text-green-500',
            red: 'text-red-500',
            blue: 'text-blue-500',
            orange: 'text-orange-500',
            yellow: 'text-yellow-500',
            purple: 'text-purple-500'
        };

        const iconColor = colorClasses[notification.color] || 'text-gray-500';

        return `
            <a href="${notification.link || '#'}"
               class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 notification-item"
               data-notification-id="${notification.id}"
               onclick="notificationManager.markAsRead(${notification.id})">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas ${notification.icon || 'fa-bell'} ${iconColor}"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">${notification.title}</p>
                        <p class="text-sm text-gray-500 line-clamp-2">${notification.message}</p>
                        <p class="text-xs text-gray-400 mt-1">${notification.time || 'Just now'}</p>
                    </div>
                </div>
            </a>
        `;
    }

    /**
     * Default unread count handler
     */
    defaultUnreadHandler(count) {
        const badges = document.querySelectorAll('.notification-badge');
        badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
    }

    /**
     * Get toast type from notification type
     */
    getToastType(notificationType) {
        if (notificationType.includes('success') || notificationType.includes('completed')) return 'success';
        if (notificationType.includes('error') || notificationType.includes('cancelled')) return 'error';
        if (notificationType.includes('warning') || notificationType.includes('reminder')) return 'warning';
        return 'info';
    }

    /**
     * Request browser notification permission
     */
    static async requestPermission() {
        if (!('Notification' in window)) {
            console.warn('Browser notifications not supported');
            return false;
        }

        if (Notification.permission === 'granted') {
            return true;
        }

        if (Notification.permission !== 'denied') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }

        return false;
    }
}

// Create global instance
let notificationManager;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Only initialize if user is logged in (check for user-specific elements)
    const userMenu = document.querySelector('[data-user-menu]') ||
                     document.querySelector('.notification-badge');

    if (userMenu) {
        notificationManager = new NotificationManager();
        notificationManager.init();

        // Request browser notification permission
        NotificationManager.requestPermission();
    }
});

// Export for modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
