        </main>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-20 right-4 z-50 space-y-3 max-w-sm w-full pointer-events-none"></div>

<!-- Common Admin Scripts -->
<script>
(function() {
    'use strict';

    // Toast notification function with improved styling
    window.showToast = function(message, type = 'success', duration = 5000) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');

        const configs = {
            success: { icon: 'fa-check-circle', bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-600', iconColor: 'text-green-500' },
            error: { icon: 'fa-exclamation-circle', bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-600', iconColor: 'text-red-500' },
            warning: { icon: 'fa-exclamation-triangle', bg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-700', iconColor: 'text-yellow-500' },
            info: { icon: 'fa-info-circle', bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-600', iconColor: 'text-blue-500' }
        };

        const config = configs[type] || configs.info;

        toast.className = `pointer-events-auto flex items-start gap-3 p-4 ${config.bg} border ${config.border} rounded-xl shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
        toast.innerHTML = `
            <i class="fas ${config.icon} ${config.iconColor} text-xl flex-shrink-0 mt-0.5"></i>
            <div class="flex-1 min-w-0">
                <p class="${config.text} text-sm font-medium">${message}</p>
            </div>
            <button onclick="dismissToast(this.parentElement)" class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-white/50 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        `;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        });

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => dismissToast(toast), duration);
        }

        return toast;
    };

    // Dismiss toast with animation
    window.dismissToast = function(toast) {
        if (!toast || toast.classList.contains('dismissing')) return;
        toast.classList.add('dismissing', 'translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    };

    // Confirm delete function with custom modal option
    window.confirmDelete = function(message = 'Are you sure you want to delete this item?') {
        return confirm(message);
    };

    // Form loading state helper
    window.setFormLoading = function(form, loading = true) {
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;

        if (loading) {
            button.disabled = true;
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml || button.innerHTML;
        }
    };

    // Auto-dismiss flash messages
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('[data-auto-dismiss]');
        alerts.forEach(alert => {
            const delay = parseInt(alert.dataset.autoDismiss) || 5000;
            setTimeout(() => {
                alert.style.transition = 'opacity 300ms, transform 300ms';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, delay);
        });

        // Add loading state to forms on submit
        document.querySelectorAll('form[data-loading]').forEach(form => {
            form.addEventListener('submit', function() {
                setFormLoading(this, true);
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Add keyboard navigation for dropdowns
        document.querySelectorAll('[x-data]').forEach(el => {
            el.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const closeBtn = el.querySelector('[\\@click\\.away]');
                    if (closeBtn) closeBtn.click();
                }
            });
        });
    });

    // Debounce helper for search inputs
    window.debounce = function(func, wait = 300) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    // Format number with commas
    window.formatNumber = function(num) {
        return new Intl.NumberFormat().format(num);
    };

    // Copy to clipboard helper
    window.copyToClipboard = function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copied to clipboard!', 'success', 2000);
            }).catch(() => {
                showToast('Failed to copy', 'error', 2000);
            });
        }
    };
})();
</script>

</body>
</html>