/**
 * Toast Notification System
 * Modern, non-intrusive notifications for EduTrack LMS
 */

class ToastNotification {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toast-container')) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'fixed top-4 right-4 z-50 space-y-3';
            this.container.setAttribute('aria-live', 'polite');
            this.container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toast-container');
        }
    }

    show(message, type = 'info', duration = 5000) {
        const toast = this.createToast(message, type);
        this.container.appendChild(toast);

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('toast-show');
        }, 10);

        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }

        return toast;
    }

    createToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} transform translate-x-full transition-all duration-300 ease-out`;
        toast.setAttribute('role', 'alert');

        const config = this.getTypeConfig(type);

        toast.innerHTML = `
            <div class="flex items-start gap-3 p-4 bg-white rounded-lg shadow-lg border-l-4 ${config.borderColor} min-w-[320px] max-w-md">
                <div class="flex-shrink-0">
                    <div class="${config.iconBg} rounded-full p-2">
                        <i class="${config.icon} ${config.iconColor}"></i>
                    </div>
                </div>
                <div class="flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900">${config.title}</p>
                    <p class="text-sm text-gray-600 mt-1">${this.escapeHtml(message)}</p>
                </div>
                <button type="button" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors" onclick="window.toast.remove(this.closest('.toast'))">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        return toast;
    }

    getTypeConfig(type) {
        const configs = {
            success: {
                title: 'Success',
                icon: 'fas fa-check-circle',
                iconColor: 'text-green-600',
                iconBg: 'bg-green-100',
                borderColor: 'border-green-500'
            },
            error: {
                title: 'Error',
                icon: 'fas fa-exclamation-circle',
                iconColor: 'text-red-600',
                iconBg: 'bg-red-100',
                borderColor: 'border-red-500'
            },
            warning: {
                title: 'Warning',
                icon: 'fas fa-exclamation-triangle',
                iconColor: 'text-yellow-600',
                iconBg: 'bg-yellow-100',
                borderColor: 'border-yellow-500'
            },
            info: {
                title: 'Info',
                icon: 'fas fa-info-circle',
                iconColor: 'text-blue-600',
                iconBg: 'bg-blue-100',
                borderColor: 'border-blue-500'
            }
        };

        return configs[type] || configs.info;
    }

    remove(toast) {
        toast.classList.remove('toast-show');
        toast.classList.add('translate-x-full', 'opacity-0');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    removeAll() {
        const toasts = this.container.querySelectorAll('.toast');
        toasts.forEach(toast => this.remove(toast));
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Convenience methods
    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }

    // Loading toast (doesn't auto-dismiss)
    loading(message = 'Loading...') {
        const toast = this.createLoadingToast(message);
        this.container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('toast-show');
        }, 10);

        return toast;
    }

    createLoadingToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast transform translate-x-full transition-all duration-300 ease-out';
        toast.setAttribute('role', 'status');

        toast.innerHTML = `
            <div class="flex items-center gap-3 p-4 bg-white rounded-lg shadow-lg border-l-4 border-blue-500 min-w-[320px] max-w-md">
                <div class="flex-shrink-0">
                    <div class="animate-spin">
                        <i class="fas fa-spinner text-blue-600"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">${this.escapeHtml(message)}</p>
                </div>
            </div>
        `;

        return toast;
    }
}

// Initialize global toast instance
window.toast = new ToastNotification();

// Auto-show PHP flash messages as toasts
document.addEventListener('DOMContentLoaded', function() {
    // Check for flash messages in data attributes
    const flashMessages = document.querySelectorAll('[data-flash-message]');
    flashMessages.forEach(function(element) {
        const message = element.getAttribute('data-flash-message');
        const type = element.getAttribute('data-flash-type') || 'info';

        window.toast.show(message, type);

        // Hide the original flash message element
        element.style.display = 'none';
    });
});
