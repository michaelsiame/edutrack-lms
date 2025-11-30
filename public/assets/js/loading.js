/**
 * Loading Indicators and Spinners
 * Provides visual feedback during asynchronous operations
 */

class LoadingIndicator {
    constructor() {
        this.activeLoaders = new Set();
        this.init();
    }

    init() {
        // Create full-page loader overlay if it doesn't exist
        if (!document.getElementById('page-loader')) {
            const loader = document.createElement('div');
            loader.id = 'page-loader';
            loader.className = 'page-loader hidden';
            loader.innerHTML = `
                <div class="page-loader-backdrop"></div>
                <div class="page-loader-content">
                    <div class="spinner-large"></div>
                    <p class="page-loader-text">Loading...</p>
                </div>
            `;
            document.body.appendChild(loader);
        }
    }

    /**
     * Show full-page loading overlay
     * @param {string} message - Optional loading message
     */
    showPageLoader(message = 'Loading...') {
        const loader = document.getElementById('page-loader');
        if (loader) {
            const textElement = loader.querySelector('.page-loader-text');
            if (textElement) {
                textElement.textContent = message;
            }
            loader.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Hide full-page loading overlay
     */
    hidePageLoader() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    /**
     * Show loading state on a button
     * @param {HTMLElement} button - Button element
     * @param {string} loadingText - Text to show while loading
     */
    showButtonLoader(button, loadingText = 'Loading...') {
        if (!button) return;

        // Store original content
        button.dataset.originalContent = button.innerHTML;
        button.dataset.originalText = button.textContent;
        button.disabled = true;

        // Add loading spinner and text
        button.innerHTML = `
            <span class="inline-flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${loadingText}
            </span>
        `;

        this.activeLoaders.add(button);
    }

    /**
     * Hide loading state from a button
     * @param {HTMLElement} button - Button element
     */
    hideButtonLoader(button) {
        if (!button) return;

        const originalContent = button.dataset.originalContent;
        if (originalContent) {
            button.innerHTML = originalContent;
            delete button.dataset.originalContent;
            delete button.dataset.originalText;
        }
        button.disabled = false;

        this.activeLoaders.delete(button);
    }

    /**
     * Show loading spinner in a container
     * @param {HTMLElement} container - Container element
     * @param {string} size - Spinner size (small, medium, large)
     * @param {string} message - Optional message
     */
    showContainerLoader(container, size = 'medium', message = '') {
        if (!container) return;

        const sizeClasses = {
            small: 'spinner-small',
            medium: 'spinner-medium',
            large: 'spinner-large'
        };

        const spinnerClass = sizeClasses[size] || sizeClasses.medium;

        // Store original content
        container.dataset.originalContent = container.innerHTML;

        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8">
                <div class="${spinnerClass}"></div>
                ${message ? `<p class="text-gray-600 mt-4">${this.escapeHtml(message)}</p>` : ''}
            </div>
        `;

        this.activeLoaders.add(container);
    }

    /**
     * Hide loading spinner from a container
     * @param {HTMLElement} container - Container element
     */
    hideContainerLoader(container) {
        if (!container) return;

        const originalContent = container.dataset.originalContent;
        if (originalContent) {
            container.innerHTML = originalContent;
            delete container.dataset.originalContent;
        }

        this.activeLoaders.delete(container);
    }

    /**
     * Create skeleton loader for content
     * @param {HTMLElement} container - Container element
     * @param {string} type - Skeleton type (card, list, table, text)
     * @param {number} count - Number of skeleton items
     */
    showSkeletonLoader(container, type = 'card', count = 3) {
        if (!container) return;

        container.dataset.originalContent = container.innerHTML;

        const skeletons = {
            card: this.createCardSkeleton(),
            list: this.createListSkeleton(),
            table: this.createTableSkeleton(),
            text: this.createTextSkeleton()
        };

        const skeletonHTML = skeletons[type] || skeletons.card;
        container.innerHTML = Array(count).fill(skeletonHTML).join('');

        this.activeLoaders.add(container);
    }

    createCardSkeleton() {
        return `
            <div class="bg-white rounded-lg shadow p-6 mb-4 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-5/6 mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-4/6"></div>
            </div>
        `;
    }

    createListSkeleton() {
        return `
            <div class="flex items-center py-3 border-b border-gray-200 animate-pulse">
                <div class="h-10 w-10 bg-gray-200 rounded-full mr-3"></div>
                <div class="flex-1">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                </div>
            </div>
        `;
    }

    createTableSkeleton() {
        return `
            <tr class="animate-pulse">
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-full"></div></td>
                <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-20"></div></td>
            </tr>
        `;
    }

    createTextSkeleton() {
        return `
            <div class="animate-pulse mb-4">
                <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            </div>
        `;
    }

    /**
     * Show inline loading spinner
     * @param {HTMLElement} element - Element to show spinner in
     */
    showInlineLoader(element) {
        if (!element) return;

        element.dataset.originalContent = element.innerHTML;
        element.innerHTML = '<div class="spinner-small inline-block"></div>';

        this.activeLoaders.add(element);
    }

    /**
     * Clear all active loaders (emergency cleanup)
     */
    clearAllLoaders() {
        this.hidePageLoader();

        this.activeLoaders.forEach(element => {
            if (element.tagName === 'BUTTON') {
                this.hideButtonLoader(element);
            } else {
                this.hideContainerLoader(element);
            }
        });

        this.activeLoaders.clear();
    }

    /**
     * Utility: Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize global loading indicator
window.loading = new LoadingIndicator();

/**
 * Auto-enhance forms with loading states
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add loading state to forms on submit
    document.querySelectorAll('form[data-loading]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                const loadingText = form.dataset.loadingText || 'Processing...';
                window.loading.showButtonLoader(submitButton, loadingText);
            }
        });
    });

    // Add loading state to async buttons
    document.querySelectorAll('[data-async-action]').forEach(button => {
        button.addEventListener('click', function() {
            const loadingText = button.dataset.loadingText || 'Loading...';
            window.loading.showButtonLoader(button, loadingText);
        });
    });
});

/**
 * AJAX Helper with automatic loading states
 */
window.fetchWithLoading = async function(url, options = {}) {
    const {
        showPageLoader = false,
        pageLoaderMessage = 'Loading...',
        button = null,
        buttonText = 'Loading...',
        container = null,
        containerSize = 'medium',
        containerMessage = '',
        ...fetchOptions
    } = options;

    try {
        // Show appropriate loader
        if (showPageLoader) {
            window.loading.showPageLoader(pageLoaderMessage);
        } else if (button) {
            window.loading.showButtonLoader(button, buttonText);
        } else if (container) {
            window.loading.showContainerLoader(container, containerSize, containerMessage);
        }

        const response = await fetch(url, fetchOptions);

        return response;

    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    } finally {
        // Hide loaders
        if (showPageLoader) {
            window.loading.hidePageLoader();
        } else if (button) {
            window.loading.hideButtonLoader(button);
        } else if (container) {
            window.loading.hideContainerLoader(container);
        }
    }
};

/**
 * Navigation loading state
 */
window.addEventListener('beforeunload', function() {
    // Only show loader for form submissions or specific navigation
    const isFormSubmit = document.activeElement && document.activeElement.form;
    if (isFormSubmit) {
        window.loading.showPageLoader('Submitting...');
    }
});

// Clean up loaders on page errors
window.addEventListener('error', function() {
    setTimeout(() => {
        window.loading.clearAllLoaders();
    }, 500);
});
