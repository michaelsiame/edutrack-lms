/**
 * Learning Interface JavaScript
 * Interactive features for course learning
 */

// Track video progress
let progressInterval;
let lastProgressUpdate = 0;
let videoStartTime = Date.now();

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeVideoTracking();
    initializeKeyboardShortcuts();
    loadModuleStates();
    trackTimeSpent();
});

/**
 * Initialize video progress tracking
 */
function initializeVideoTracking() {
    const video = document.querySelector('video');
    const iframe = document.querySelector('iframe');
    
    if (video) {
        // Native video element
        video.addEventListener('timeupdate', function() {
            updateVideoProgress(video.currentTime, video.duration);
        });
        
        video.addEventListener('ended', function() {
            suggestMarkComplete();
        });
        
        // Resume from last position
        if (typeof lastPosition !== 'undefined' && lastPosition > 0) {
            video.currentTime = lastPosition;
        }
    }
    
    if (iframe) {
        // YouTube/Vimeo iframe - limited tracking
        // Would need YouTube/Vimeo API for full tracking
    }
}

/**
 * Update video progress
 */
function updateVideoProgress(currentTime, duration) {
    // Only update every 10 seconds
    if (Math.floor(currentTime) - lastProgressUpdate >= 10) {
        lastProgressUpdate = Math.floor(currentTime);
        
        fetch('/api/progress.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_progress',
                lesson_id: lessonId,
                course_id: courseId,
                progress_seconds: Math.floor(currentTime),
                last_position: Math.floor(currentTime)
            })
        });
    }
    
    // Auto-mark complete at 90% watched
    if (currentTime / duration >= 0.9 && !isLessonCompleted()) {
        suggestMarkComplete();
    }
}

/**
 * Suggest marking lesson as complete
 */
function suggestMarkComplete() {
    const btn = document.getElementById('mark-complete-btn');
    if (btn) {
        btn.classList.add('animate-pulse');
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Mark as Complete (Recommended)';
    }
}

/**
 * Check if lesson is already completed
 */
function isLessonCompleted() {
    return document.querySelector('.bg-green-600') !== null;
}

/**
 * Track time spent in course
 */
function trackTimeSpent() {
    // Send time update every 60 seconds
    setInterval(function() {
        const timeSpent = Math.floor((Date.now() - videoStartTime) / 1000);
        
        if (timeSpent >= 60) {
            fetch('/api/progress.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'add_time',
                    course_id: courseId,
                    lesson_id: lessonId,
                    seconds: timeSpent
                })
            });
            
            videoStartTime = Date.now(); // Reset timer
        }
    }, 60000); // Every minute
}

/**
 * Keyboard shortcuts
 */
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        const video = document.querySelector('video');
        
        // Space - Play/Pause
        if (e.code === 'Space' && video && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            if (video.paused) {
                video.play();
            } else {
                video.pause();
            }
        }
        
        // Arrow Right - Skip forward 10s
        if (e.code === 'ArrowRight' && video) {
            video.currentTime += 10;
        }
        
        // Arrow Left - Skip backward 10s
        if (e.code === 'ArrowLeft' && video) {
            video.currentTime -= 10;
        }
        
        // M - Toggle mute
        if (e.code === 'KeyM' && video) {
            video.muted = !video.muted;
        }
        
        // F - Fullscreen
        if (e.code === 'KeyF' && video) {
            toggleFullscreen(video);
        }
        
        // N - Toggle notes
        if (e.code === 'KeyN' && e.ctrlKey) {
            e.preventDefault();
            toggleNotes();
        }
        
        // C - Mark complete
        if (e.code === 'KeyC' && e.ctrlKey && !isLessonCompleted()) {
            e.preventDefault();
            const btn = document.getElementById('mark-complete-btn');
            if (btn) btn.click();
        }
    });
    
    // Show keyboard hint on first visit
    if (!localStorage.getItem('keyboard_hint_shown')) {
        showKeyboardHint();
        localStorage.setItem('keyboard_hint_shown', 'true');
    }
}

/**
 * Show keyboard shortcuts hint
 */
function showKeyboardHint() {
    const hint = document.createElement('div');
    hint.className = 'keyboard-hint show';
    hint.innerHTML = `
        <div class="font-semibold mb-2">Keyboard Shortcuts</div>
        <div class="space-y-1">
            <div><kbd>Space</kbd> Play/Pause</div>
            <div><kbd>←</kbd> <kbd>→</kbd> Skip 10s</div>
            <div><kbd>M</kbd> Mute</div>
            <div><kbd>F</kbd> Fullscreen</div>
            <div><kbd>Ctrl+N</kbd> Notes</div>
        </div>
    `;
    document.body.appendChild(hint);
    
    setTimeout(() => {
        hint.classList.remove('show');
        setTimeout(() => hint.remove(), 300);
    }, 5000);
}

/**
 * Toggle fullscreen
 */
function toggleFullscreen(element) {
    if (!document.fullscreenElement) {
        element.requestFullscreen().catch(err => {
            console.error('Error attempting to enable fullscreen:', err);
        });
    } else {
        document.exitFullscreen();
    }
}

/**
 * Toggle module section in sidebar
 */
function toggleModuleSection(moduleId) {
    const section = document.getElementById('module-' + moduleId);
    const icon = event.currentTarget.querySelector('.module-toggle-icon');
    
    if (section && icon) {
        const isHidden = section.classList.contains('hidden');
        
        if (isHidden) {
            section.classList.remove('hidden');
            section.classList.add('expanded');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
            
            // Save state
            saveModuleState(moduleId, true);
        } else {
            section.classList.remove('expanded');
            setTimeout(() => section.classList.add('hidden'), 300);
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
            
            // Save state
            saveModuleState(moduleId, false);
        }
    }
}

/**
 * Save module expanded state
 */
function saveModuleState(moduleId, expanded) {
    const states = JSON.parse(localStorage.getItem('module_states') || '{}');
    states[moduleId] = expanded;
    localStorage.setItem('module_states', JSON.stringify(states));
}

/**
 * Load module expanded states
 */
function loadModuleStates() {
    const states = JSON.parse(localStorage.getItem('module_states') || '{}');
    
    Object.keys(states).forEach(moduleId => {
        if (states[moduleId]) {
            const section = document.getElementById('module-' + moduleId);
            const button = section?.previousElementSibling;
            const icon = button?.querySelector('.module-toggle-icon');
            
            if (section && icon) {
                section.classList.remove('hidden');
                section.classList.add('expanded');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }
    });
    
    // Always expand module containing current lesson
    const currentLesson = document.querySelector('.module-lessons a.bg-gray-750');
    if (currentLesson) {
        const module = currentLesson.closest('.module-section');
        if (module) {
            const section = module.querySelector('.module-lessons');
            const button = section?.previousElementSibling;
            const icon = button?.querySelector('.module-toggle-icon');
            
            if (section && icon) {
                section.classList.remove('hidden');
                section.classList.add('expanded');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        }
    }
}

/**
 * Toggle notes section
 */
function toggleNotes() {
    const notesSection = document.getElementById('notes-section');
    if (notesSection) {
        notesSection.classList.toggle('hidden');
        
        if (!notesSection.classList.contains('hidden')) {
            document.getElementById('lesson-notes').focus();
        }
    }
}

/**
 * Save notes
 */
function saveNotes() {
    const notes = document.getElementById('lesson-notes').value;
    
    fetch('/api/notes.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            lesson_id: lessonId,
            course_id: courseId,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Notes saved successfully!', 'success');
        } else {
            showNotification('Failed to save notes', 'error');
        }
    })
    .catch(error => {
        showNotification('Error saving notes', 'error');
    });
}

/**
 * Auto-save notes (debounced)
 */
let autoSaveTimeout;
function autoSaveNotes() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(saveNotes, 2000);
}

// Add auto-save listener
const notesTextarea = document.getElementById('lesson-notes');
if (notesTextarea) {
    notesTextarea.addEventListener('input', autoSaveNotes);
}

/**
 * Toggle sidebar on mobile
 */
function toggleSidebar() {
    const sidebar = document.getElementById('curriculum-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (sidebar && overlay) {
        const isHidden = sidebar.classList.contains('hidden');
        
        if (isHidden) {
            sidebar.classList.remove('hidden');
            sidebar.classList.add('fixed', 'inset-y-0', 'right-0', 'z-50');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            sidebar.classList.add('hidden');
            sidebar.classList.remove('fixed', 'inset-y-0', 'right-0', 'z-50');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
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
    
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 10);
    
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

/**
 * Completion celebration
 */
function celebrateCompletion() {
    // Create confetti effect
    const colors = ['#2E70DA', '#F6B745', '#10B981', '#F59E0B'];
    const confettiCount = 50;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'fixed';
        confetti.style.width = '10px';
        confetti.style.height = '10px';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.left = Math.random() * window.innerWidth + 'px';
        confetti.style.top = '-10px';
        confetti.style.opacity = '1';
        confetti.style.zIndex = '10000';
        confetti.style.pointerEvents = 'none';
        
        document.body.appendChild(confetti);
        
        const animation = confetti.animate([
            { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
            { transform: `translateY(${window.innerHeight + 10}px) rotate(${Math.random() * 720}deg)`, opacity: 0 }
        ], {
            duration: 3000 + Math.random() * 2000,
            easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
        });
        
        animation.onfinish = () => confetti.remove();
    }
}

/**
 * Prevent accidental page leave
 */
window.addEventListener('beforeunload', function(e) {
    // Don't prevent if lesson is completed
    if (isLessonCompleted()) {
        return;
    }
    
    // Show warning if there's unsaved progress
    const video = document.querySelector('video');
    if (video && video.currentTime > 0 && !video.ended) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

/**
 * Track page visibility for accurate time tracking
 */
let pageHiddenTime = null;
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        pageHiddenTime = Date.now();
        // Pause video if playing
        const video = document.querySelector('video');
        if (video && !video.paused) {
            video.pause();
        }
    } else {
        if (pageHiddenTime) {
            const hiddenDuration = Date.now() - pageHiddenTime;
            // Don't count time when page was hidden
            videoStartTime += hiddenDuration;
            pageHiddenTime = null;
        }
    }
});