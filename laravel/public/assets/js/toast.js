/**
 * Toast Notification System for EduTrack LMS
 * Usage: showToast(message, type, duration)
 * Types: 'success', 'error', 'warning', 'info'
 */

// Toast container
let toastContainer = null;

function initToastContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none';
        document.body.appendChild(toastContainer);
    }
}

function showToast(message, type = 'success', duration = 3000) {
    initToastContainer();
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `${colors[type]} text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 min-w-[300px] max-w-md pointer-events-auto transform transition-all duration-300 translate-x-full opacity-0`;
    toast.innerHTML = `
        <i class="fas ${icons[type]} text-xl"></i>
        <span class="font-medium flex-1">${message}</span>
        <button onclick="this.parentElement.remove()" class="opacity-75 hover:opacity-100 transition">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Animate in
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    });
    
    // Auto dismiss
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Auto-convert flash messages to toasts
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('[data-flash-message]');
    flashMessages.forEach(flash => {
        const message = flash.dataset.message;
        const type = flash.dataset.type || 'success';
        if (message) {
            showToast(message, type, 5000);
            flash.remove();
        }
    });
});

// Export for global use
window.showToast = showToast;
