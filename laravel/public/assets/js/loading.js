/**
 * Loading States and Skeleton Screens for EduTrack LMS
 */

// Show loading spinner on button
function showButtonLoading(button, text = 'Loading...') {
    button.dataset.originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${text}`;
    button.classList.add('opacity-75', 'cursor-not-allowed');
}

// Hide loading spinner on button
function hideButtonLoading(button) {
    button.disabled = false;
    button.innerHTML = button.dataset.originalText || 'Submit';
    button.classList.remove('opacity-75', 'cursor-not-allowed');
}

// Show skeleton loading for cards
function showCardSkeleton(container, count = 3) {
    const skeletonHTML = Array(count).fill(0).map(() => `
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden animate-pulse">
            <div class="h-48 bg-gray-200"></div>
            <div class="p-4 space-y-3">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-full"></div>
                <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                <div class="flex justify-between pt-2">
                    <div class="h-8 bg-gray-200 rounded w-24"></div>
                    <div class="h-8 bg-gray-200 rounded w-20"></div>
                </div>
            </div>
        </div>
    `).join('');
    
    container.dataset.originalContent = container.innerHTML;
    container.innerHTML = skeletonHTML;
}

// Hide skeleton loading
function hideCardSkeleton(container) {
    if (container.dataset.originalContent) {
        container.innerHTML = container.dataset.originalContent;
    }
}

// Show table skeleton
function showTableSkeleton(container, rows = 5, cols = 4) {
    const rowHTML = Array(cols).fill(0).map(() => 
        `<td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-full animate-pulse"></div></td>`
    ).join('');
    
    const skeletonHTML = `
        <tbody class="divide-y divide-gray-200">
            ${Array(rows).fill(0).map(() => `<tr>${rowHTML}</tr>`).join('')}
        </tbody>
    `;
    
    const tbody = container.querySelector('tbody');
    if (tbody) {
        tbody.dataset.originalContent = tbody.innerHTML;
        tbody.innerHTML = skeletonHTML;
    }
}

// Hide table skeleton
function hideTableSkeleton(container) {
    const tbody = container.querySelector('tbody');
    if (tbody && tbody.dataset.originalContent) {
        tbody.innerHTML = tbody.dataset.originalContent;
    }
}

// Page loading overlay
function showPageLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'page-loading-overlay';
    overlay.className = 'fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center';
    overlay.innerHTML = `
        <div class="text-center">
            <div class="w-16 h-16 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Loading...</p>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hidePageLoading() {
    const overlay = document.getElementById('page-loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// Auto-apply to forms
document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to all forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.dataset.noLoading) {
                showButtonLoading(submitBtn);
            }
        });
    });
    
    // Add loading state to links with data-loading attribute
    document.querySelectorAll('a[data-loading]').forEach(link => {
        link.addEventListener('click', function() {
            showPageLoading();
        });
    });
});

// Export functions
window.showButtonLoading = showButtonLoading;
window.hideButtonLoading = hideButtonLoading;
window.showCardSkeleton = showCardSkeleton;
window.hideCardSkeleton = hideCardSkeleton;
window.showTableSkeleton = showTableSkeleton;
window.hideTableSkeleton = hideTableSkeleton;
window.showPageLoading = showPageLoading;
window.hidePageLoading = hidePageLoading;
