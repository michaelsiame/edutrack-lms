/**
 * Automatic Progress Tracking for Lessons
 * Tracks user engagement and automatically updates lesson progress
 */

class LessonProgressTracker {
    constructor(courseId, lessonId, lessonType) {
        this.courseId = courseId;
        this.lessonId = lessonId;
        this.lessonType = lessonType;
        this.currentProgress = 0;
        this.isCompleted = false;
        this.startTime = Date.now();
        this.lastSaveTime = Date.now();
        this.saveInterval = 10000; // Save every 10 seconds
        this.scrollDepth = 0;
        this.videoProgress = 0;
        this.hasShownCompletionToast = false;

        // Thresholds for auto-completion
        this.completionThresholds = {
            text: {
                scrollDepth: 90, // 90% scroll
                minTimeSeconds: 30 // At least 30 seconds
            },
            video: {
                watchPercentage: 90 // 90% watched
            }
        };

        this.init();
    }

    init() {
        // Track page visibility to pause tracking when tab is inactive
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.saveProgress();
            }
        });

        // Save progress before page unload
        window.addEventListener('beforeunload', () => {
            this.saveProgress(true); // Synchronous save
        });

        // Track based on lesson type
        if (this.lessonType === 'video') {
            this.initVideoTracking();
        } else {
            this.initTextTracking();
        }

        // Periodic progress save
        setInterval(() => this.checkAndSave(), this.saveInterval);
    }

    /**
     * Initialize tracking for text-based lessons
     */
    initTextTracking() {
        // Track scroll depth
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                this.updateScrollDepth();
            }, 150);
        });

        // Initial scroll depth
        this.updateScrollDepth();
    }

    /**
     * Calculate and update scroll depth
     */
    updateScrollDepth() {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        const scrollPercentage = Math.min(
            100,
            Math.round(((scrollTop + windowHeight) / documentHeight) * 100)
        );

        if (scrollPercentage > this.scrollDepth) {
            this.scrollDepth = scrollPercentage;
            this.updateProgress();
        }
    }

    /**
     * Initialize tracking for video lessons (YouTube)
     */
    initVideoTracking() {
        // Check if YouTube iframe exists
        const iframe = document.querySelector('iframe[src*="youtube.com"]');

        if (!iframe) {
            console.warn('No YouTube iframe found for video tracking');
            return;
        }

        // Load YouTube IFrame API
        if (!window.YT) {
            const tag = document.createElement('script');
            tag.src = 'https://www.youtube.com/iframe_api';
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        }

        // Initialize player when API is ready
        window.onYouTubeIframeAPIReady = () => {
            this.initYouTubePlayer(iframe);
        };

        // If API already loaded
        if (window.YT && window.YT.Player) {
            this.initYouTubePlayer(iframe);
        }
    }

    /**
     * Initialize YouTube player for progress tracking
     */
    initYouTubePlayer(iframe) {
        const player = new YT.Player(iframe, {
            events: {
                'onStateChange': (event) => this.onPlayerStateChange(event),
                'onReady': (event) => {
                    // Track progress every 2 seconds while playing
                    setInterval(() => {
                        if (event.target.getPlayerState() === YT.PlayerState.PLAYING) {
                            const duration = event.target.getDuration();
                            const currentTime = event.target.getCurrentTime();
                            const progress = Math.round((currentTime / duration) * 100);

                            if (progress > this.videoProgress) {
                                this.videoProgress = progress;
                                this.updateProgress();
                            }
                        }
                    }, 2000);
                }
            }
        });
    }

    /**
     * Handle YouTube player state changes
     */
    onPlayerStateChange(event) {
        // When video ends
        if (event.data === YT.PlayerState.ENDED) {
            this.videoProgress = 100;
            this.completeLesson();
        }
    }

    /**
     * Calculate current progress based on lesson type
     */
    updateProgress() {
        let newProgress = 0;

        if (this.lessonType === 'video') {
            // For video lessons, progress is based on video watch percentage
            newProgress = this.videoProgress;

            // Auto-complete if watched enough
            if (this.videoProgress >= this.completionThresholds.video.watchPercentage) {
                this.completeLesson();
            }

        } else {
            // For text lessons, combine scroll depth and time spent
            const timeSpentSeconds = (Date.now() - this.startTime) / 1000;
            const hasMinTime = timeSpentSeconds >= this.completionThresholds.text.minTimeSeconds;

            newProgress = this.scrollDepth;

            // Auto-complete if scrolled enough AND spent minimum time
            if (this.scrollDepth >= this.completionThresholds.text.scrollDepth && hasMinTime) {
                this.completeLesson();
            }
        }

        // Only update if progress increased
        if (newProgress > this.currentProgress) {
            this.currentProgress = newProgress;
        }
    }

    /**
     * Check if we should save progress
     */
    checkAndSave() {
        const now = Date.now();

        // Only save if enough time has passed and progress changed
        if (now - this.lastSaveTime >= this.saveInterval && this.currentProgress > 0) {
            this.saveProgress();
        }
    }

    /**
     * Save progress to server
     */
    async saveProgress(sync = false) {
        if (this.isCompleted) {
            return; // Don't save if already completed
        }

        const data = {
            course_id: this.courseId,
            lesson_id: this.lessonId,
            progress_percentage: Math.round(this.currentProgress),
            action: 'update'
        };

        try {
            if (sync) {
                // Synchronous save for page unload
                const blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
                navigator.sendBeacon('/api/track-progress.php', blob);
            } else {
                // Asynchronous save
                const response = await fetch('/api/track-progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    this.lastSaveTime = Date.now();

                    // Update progress display if element exists
                    this.updateProgressDisplay(result.course_progress);
                }
            }
        } catch (error) {
            console.error('Failed to save progress:', error);
        }
    }

    /**
     * Mark lesson as complete
     */
    async completeLesson() {
        if (this.isCompleted) {
            return; // Already completed
        }

        this.isCompleted = true;
        this.currentProgress = 100;

        const data = {
            course_id: this.courseId,
            lesson_id: this.lessonId,
            progress_percentage: 100,
            action: 'complete'
        };

        try {
            const response = await fetch('/api/track-progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success && !this.hasShownCompletionToast) {
                this.hasShownCompletionToast = true;

                // Show completion toast
                if (window.toast) {
                    window.toast.success('Lesson completed! Great job! 🎉');
                }

                // Update progress display
                this.updateProgressDisplay(result.course_progress);

                // Update button state
                this.updateCompleteButton();
            }
        } catch (error) {
            console.error('Failed to complete lesson:', error);
        }
    }

    /**
     * Update progress display in header
     */
    updateProgressDisplay(courseProgress) {
        const progressElements = document.querySelectorAll('[data-course-progress]');
        progressElements.forEach(element => {
            element.textContent = Math.round(courseProgress) + '%';
        });
    }

    /**
     * Update "Mark as Complete" button state
     */
    updateCompleteButton() {
        const completeButton = document.querySelector('button[type="submit"][form], form[action*="mark-lesson-complete"] button[type="submit"]');

        if (completeButton) {
            completeButton.innerHTML = '<i class="fas fa-check mr-2"></i>Completed';
            completeButton.disabled = true;
            completeButton.classList.remove('bg-green-600', 'hover:bg-green-700');
            completeButton.classList.add('bg-gray-400', 'cursor-not-allowed');
        }
    }

    /**
     * Manual completion (when user clicks "Mark as Complete")
     */
    async manualComplete() {
        await this.completeLesson();
    }
}

// Initialize progress tracking when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get lesson data from page
    const learningArea = document.querySelector('[data-lesson-tracking]');

    if (learningArea) {
        const courseId = learningArea.dataset.courseId;
        const lessonId = learningArea.dataset.lessonId;
        const lessonType = learningArea.dataset.lessonType || 'text';

        if (courseId && lessonId) {
            // Initialize progress tracker
            window.progressTracker = new LessonProgressTracker(
                parseInt(courseId),
                parseInt(lessonId),
                lessonType
            );

            // Intercept manual "Mark as Complete" button
            const completeForm = document.querySelector('form[action*="mark-lesson-complete"]');
            if (completeForm) {
                completeForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Use AJAX instead of form submission
                    window.progressTracker.completeLesson().then(() => {
                        // Optionally redirect to next lesson or show success
                        window.toast.success('Lesson marked as complete!');
                    });
                });
            }
        }
    }
});

/**
 * Debug helper - show current progress
 */
window.showLessonProgress = function() {
    if (window.progressTracker) {
        console.log('Current Progress:', window.progressTracker.currentProgress + '%');
        console.log('Scroll Depth:', window.progressTracker.scrollDepth + '%');
        console.log('Video Progress:', window.progressTracker.videoProgress + '%');
        console.log('Is Completed:', window.progressTracker.isCompleted);
    } else {
        console.log('No progress tracker initialized');
    }
};
