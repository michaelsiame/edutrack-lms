/**
 * Course Page JavaScript
 * Interactive features for course pages
 */

// Tab System
function showTab(tabName) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('active', 'border-primary-600', 'text-primary-600');
        link.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    const contentElement = document.getElementById('content-' + tabName);
    if (contentElement) {
        contentElement.classList.remove('hidden');
    }
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    if (activeTab) {
        activeTab.classList.add('active', 'border-primary-600', 'text-primary-600');
        activeTab.classList.remove('border-transparent', 'text-gray-500');
    }
}

// Module Accordion Toggle
function toggleModule(index) {
    const content = document.getElementById('module-' + index);
    const button = content.previousElementSibling;
    const icon = button.querySelector('.module-icon');
    
    if (content && icon) {
        content.classList.toggle('hidden');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
    }
}

// Copy to Clipboard
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showNotification('Link copied to clipboard!', 'success');
        }, function() {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

// Fallback copy method for older browsers
function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Link copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy link', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Show Notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    // Set colors based on type
    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-gray-900'
    };
    
    notification.className += ` ${colors[type] || colors.info}`;
    notification.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Filter Courses
function filterCourses(filterType, filterValue) {
    const url = new URL(window.location.href);
    
    if (filterValue) {
        url.searchParams.set(filterType, filterValue);
    } else {
        url.searchParams.delete(filterType);
    }
    
    // Reset to page 1 when filtering
    url.searchParams.set('page', '1');
    
    window.location.href = url.toString();
}

// Search Form Enhancement
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="search"]');
        
        // Clear button
        if (searchInput && searchInput.value) {
            const clearBtn = document.createElement('button');
            clearBtn.type = 'button';
            clearBtn.className = 'absolute right-12 top-3 text-gray-400 hover:text-gray-600';
            clearBtn.innerHTML = '<i class="fas fa-times"></i>';
            clearBtn.onclick = function() {
                searchInput.value = '';
                searchInput.focus();
            };
            searchInput.parentElement.appendChild(clearBtn);
        }
    }
});

// Lazy Load Images
function lazyLoadImages() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading if supported
if ('IntersectionObserver' in window) {
    document.addEventListener('DOMContentLoaded', lazyLoadImages);
}

// Video Player Controls
function initializeVideoPlayer(videoElement) {
    if (!videoElement) return;
    
    const playPauseBtn = document.querySelector('.play-pause-btn');
    const progressBar = document.querySelector('.video-progress');
    const volumeSlider = document.querySelector('.volume-slider');
    const fullscreenBtn = document.querySelector('.fullscreen-btn');
    
    if (playPauseBtn) {
        playPauseBtn.addEventListener('click', function() {
            if (videoElement.paused) {
                videoElement.play();
                playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                videoElement.pause();
                playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        });
    }
    
    if (progressBar) {
        videoElement.addEventListener('timeupdate', function() {
            const progress = (videoElement.currentTime / videoElement.duration) * 100;
            progressBar.style.width = progress + '%';
        });
    }
    
    if (volumeSlider) {
        volumeSlider.addEventListener('input', function() {
            videoElement.volume = this.value / 100;
        });
    }
    
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            if (videoElement.requestFullscreen) {
                videoElement.requestFullscreen();
            } else if (videoElement.webkitRequestFullscreen) {
                videoElement.webkitRequestFullscreen();
            } else if (videoElement.msRequestFullscreen) {
                videoElement.msRequestFullscreen();
            }
        });
    }
}

// Rating Stars Interaction
function initializeRatingStars() {
    const ratingContainers = document.querySelectorAll('.rating-input');
    
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const input = container.querySelector('input[type="hidden"]');
        
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = index + 1;
                input.value = rating;
                
                // Update visual state
                stars.forEach((s, i) => {
                    if (i < rating) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                        s.classList.add('text-yellow-500');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                        s.classList.remove('text-yellow-500');
                    }
                });
            });
            
            // Hover effect
            star.addEventListener('mouseenter', function() {
                stars.forEach((s, i) => {
                    if (i <= index) {
                        s.classList.add('text-yellow-400');
                    }
                });
            });
            
            container.addEventListener('mouseleave', function() {
                const currentRating = parseInt(input.value) || 0;
                stars.forEach((s, i) => {
                    s.classList.remove('text-yellow-400');
                    if (i < currentRating) {
                        s.classList.add('text-yellow-500');
                    }
                });
            });
        });
    });
}

// Smooth Scroll to Element
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Course Comparison
function addToComparison(courseId) {
    let comparison = JSON.parse(localStorage.getItem('courseComparison') || '[]');
    
    if (comparison.length >= 3) {
        showNotification('Maximum 3 courses can be compared', 'warning');
        return;
    }
    
    if (comparison.includes(courseId)) {
        showNotification('Course already in comparison', 'info');
        return;
    }
    
    comparison.push(courseId);
    localStorage.setItem('courseComparison', JSON.stringify(comparison));
    showNotification('Course added to comparison', 'success');
    updateComparisonBadge();
}

function removeFromComparison(courseId) {
    let comparison = JSON.parse(localStorage.getItem('courseComparison') || '[]');
    comparison = comparison.filter(id => id !== courseId);
    localStorage.setItem('courseComparison', JSON.stringify(comparison));
    showNotification('Course removed from comparison', 'info');
    updateComparisonBadge();
}

function updateComparisonBadge() {
    const comparison = JSON.parse(localStorage.getItem('courseComparison') || '[]');
    const badge = document.querySelector('.comparison-badge');
    if (badge) {
        badge.textContent = comparison.length;
        badge.style.display = comparison.length > 0 ? 'block' : 'none';
    }
}

// Wishlist Functions
function toggleWishlist(courseId) {
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    
    if (wishlist.includes(courseId)) {
        wishlist = wishlist.filter(id => id !== courseId);
        showNotification('Removed from wishlist', 'info');
    } else {
        wishlist.push(courseId);
        showNotification('Added to wishlist', 'success');
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    updateWishlistUI(courseId);
}

function updateWishlistUI(courseId) {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const buttons = document.querySelectorAll(`[data-course-id="${courseId}"]`);
    
    buttons.forEach(button => {
        const icon = button.querySelector('i');
        if (wishlist.includes(courseId)) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.add('text-red-500');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.classList.remove('text-red-500');
        }
    });
}

// Share Course
function shareCourse(url, title) {
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).then(() => {
            showNotification('Course shared successfully', 'success');
        }).catch(() => {
            // Fallback to copy link
            copyToClipboard(url);
        });
    } else {
        // Fallback to copy link
        copyToClipboard(url);
    }
}

// Track Video Progress
function trackVideoProgress(courseId, lessonId, currentTime, duration) {
    // Only track every 10 seconds
    if (Math.floor(currentTime) % 10 !== 0) return;
    
    const progress = (currentTime / duration) * 100;
    
    // Send AJAX request to save progress
    fetch('/api/progress.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            course_id: courseId,
            lesson_id: lessonId,
            progress: progress,
            time_spent: Math.floor(currentTime)
        })
    });
}

// Format Duration
function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    
    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize rating stars if present
    initializeRatingStars();
    
    // Update comparison badge
    updateComparisonBadge();
    
    // Initialize video player if present
    const videoElement = document.querySelector('video');
    if (videoElement) {
        initializeVideoPlayer(videoElement);
    }
    
    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            smoothScrollTo(targetId);
        });
    });
});

// Prevent right-click on videos (optional)
function protectVideo() {
    const videos = document.querySelectorAll('video');
    videos.forEach(video => {
        video.addEventListener('contextmenu', e => e.preventDefault());
    });
}

// Call this if you want to prevent video downloads
// protectVideo();