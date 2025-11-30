/**
 * Global Search Functionality
 * Live search with instant results
 */

class GlobalSearch {
    constructor() {
        this.searchInput = null;
        this.searchResults = null;
        this.searchTimeout = null;
        this.currentQuery = '';
        this.isOpen = false;
        this.selectedIndex = -1;
        this.results = [];

        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.searchInput = document.getElementById('globalSearch');
        this.searchResults = document.getElementById('searchResults');

        if (!this.searchInput || !this.searchResults) {
            return; // Search not enabled on this page
        }

        // Bind event listeners
        this.searchInput.addEventListener('input', (e) => this.handleInput(e));
        this.searchInput.addEventListener('focus', () => this.handleFocus());
        this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Close results when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.searchResults.contains(e.target)) {
                this.closeResults();
            }
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeResults();
                this.searchInput.blur();
            }
        });
    }

    handleInput(e) {
        const query = e.target.value.trim();

        // Clear previous timeout
        clearTimeout(this.searchTimeout);

        // If query is too short, hide results
        if (query.length < 2) {
            this.closeResults();
            return;
        }

        // Show loading state
        this.showLoading();

        // Debounce search
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    }

    handleFocus() {
        // Show previous results if available
        if (this.results.length > 0 && this.currentQuery.length >= 2) {
            this.openResults();
        }
    }

    handleKeydown(e) {
        if (!this.isOpen || this.results.length === 0) {
            return;
        }

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
                this.updateSelection();
                break;

            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection();
                break;

            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0) {
                    const selectedResult = this.results[this.selectedIndex];
                    window.location.href = selectedResult.url;
                }
                break;

            case 'Escape':
                this.closeResults();
                break;
        }
    }

    async performSearch(query) {
        this.currentQuery = query;

        try {
            const response = await fetch(`/api/search.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.success) {
                this.results = data.results;
                this.renderResults();
            } else {
                this.showError(data.message || 'Search failed');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Failed to perform search');
        }
    }

    showLoading() {
        this.searchResults.innerHTML = `
            <div class="px-4 py-8 text-center">
                <div class="spinner-small inline-block mb-2"></div>
                <p class="text-sm text-gray-600">Searching...</p>
            </div>
        `;
        this.openResults();
    }

    showError(message) {
        this.searchResults.innerHTML = `
            <div class="px-4 py-4 text-center">
                <i class="fas fa-exclamation-circle text-red-500 text-2xl mb-2"></i>
                <p class="text-sm text-gray-600">${this.escapeHtml(message)}</p>
            </div>
        `;
        this.openResults();
    }

    renderResults() {
        if (this.results.length === 0) {
            this.searchResults.innerHTML = `
                <div class="px-4 py-8 text-center">
                    <i class="fas fa-search text-gray-300 text-4xl mb-2"></i>
                    <p class="text-sm text-gray-600">No results found for "${this.escapeHtml(this.currentQuery)}"</p>
                    <p class="text-xs text-gray-500 mt-1">Try different keywords</p>
                </div>
            `;
            this.openResults();
            return;
        }

        // Group results by type
        const grouped = this.groupResultsByType();

        let html = '<div class="divide-y divide-gray-100">';

        for (const [type, items] of Object.entries(grouped)) {
            if (items.length === 0) continue;

            html += `
                <div class="px-4 py-2 bg-gray-50">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        ${this.getTypeLabel(type)}
                    </h3>
                </div>
            `;

            items.forEach((result, index) => {
                const globalIndex = this.results.indexOf(result);
                const isSelected = globalIndex === this.selectedIndex;

                html += `
                    <a href="${result.url}"
                       class="flex items-center px-4 py-3 hover:bg-blue-50 transition cursor-pointer ${isSelected ? 'bg-blue-50' : ''}"
                       data-result-index="${globalIndex}">
                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-gray-100 rounded-lg">
                            <i class="fas ${result.icon} text-gray-600"></i>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                ${this.highlightQuery(result.title)}
                            </p>
                            ${result.description ? `
                                <p class="text-xs text-gray-500 truncate">
                                    ${this.escapeHtml(result.description)}
                                </p>
                            ` : ''}
                        </div>
                        ${result.badge ? `
                            <div class="ml-2 flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    ${this.escapeHtml(result.badge)}
                                </span>
                            </div>
                        ` : ''}
                        <div class="ml-2 flex-shrink-0">
                            <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                        </div>
                    </a>
                `;
            });
        }

        html += '</div>';

        // Add footer with count
        html += `
            <div class="px-4 py-2 bg-gray-50 border-t border-gray-200">
                <p class="text-xs text-gray-500 text-center">
                    ${this.results.length} result${this.results.length !== 1 ? 's' : ''} found
                </p>
            </div>
        `;

        this.searchResults.innerHTML = html;
        this.openResults();
        this.selectedIndex = -1;
    }

    groupResultsByType() {
        const grouped = {
            'my-course': [],
            'lesson': [],
            'course': [],
            'student': [],
            'instructor': []
        };

        this.results.forEach(result => {
            if (grouped[result.type]) {
                grouped[result.type].push(result);
            } else {
                grouped[result.type] = [result];
            }
        });

        return grouped;
    }

    getTypeLabel(type) {
        const labels = {
            'my-course': 'My Courses',
            'lesson': 'Lessons',
            'course': 'All Courses',
            'student': 'Students',
            'instructor': 'Instructors'
        };
        return labels[type] || type;
    }

    highlightQuery(text) {
        if (!this.currentQuery) return this.escapeHtml(text);

        const escaped = this.escapeHtml(text);
        const regex = new RegExp(`(${this.escapeRegex(this.currentQuery)})`, 'gi');

        return escaped.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
    }

    updateSelection() {
        // Remove previous selection
        this.searchResults.querySelectorAll('a').forEach((el, index) => {
            if (index === this.selectedIndex) {
                el.classList.add('bg-blue-50');
                el.scrollIntoView({ block: 'nearest' });
            } else {
                el.classList.remove('bg-blue-50');
            }
        });
    }

    openResults() {
        this.searchResults.classList.remove('hidden');
        this.isOpen = true;
    }

    closeResults() {
        this.searchResults.classList.add('hidden');
        this.isOpen = false;
        this.selectedIndex = -1;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    escapeRegex(text) {
        return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
}

// Initialize global search
window.globalSearch = new GlobalSearch();

// Keyboard shortcut: Ctrl/Cmd + K to focus search
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('globalSearch');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
});
