</main>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- Common Admin Scripts -->
<script>
// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const icons = {
        success: 'fa-check-circle text-green-500',
        error: 'fa-exclamation-circle text-red-500',
        warning: 'fa-exclamation-triangle text-yellow-500',
        info: 'fa-info-circle text-blue-500'
    };
    
    toast.className = 'flex items-center p-4 bg-white rounded-lg shadow-lg transform transition-all duration-300 translate-x-full';
    toast.innerHTML = `
        <i class="fas ${icons[type]} text-xl mr-3"></i>
        <span class="text-gray-800">${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.getElementById('toast-container').appendChild(toast);
    
    setTimeout(() => toast.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Confirm delete function
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Auto-dismiss alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('[data-auto-dismiss]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}, 100);
</script>

</body>
</html>