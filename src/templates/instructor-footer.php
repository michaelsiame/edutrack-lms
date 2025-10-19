</main>
    </div>
</div>

<script>
// Reusable functions for instructor panel
function confirmDelete(message = 'Are you sure you want to delete this?') {
    return confirm(message);
}

function showToast(message, type = 'success') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} shadow-lg z-50`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

</body>
</html>