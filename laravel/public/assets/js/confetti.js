/**
 * Confetti Celebration Utility
 * Lightweight confetti burst for course completion celebrations
 * Usage: Confetti.celebrate({ duration: 3000, particleCount: 60 });
 */

const Confetti = (function() {
    const colors = [
        'var(--accent-primary)',
        'var(--accent-secondary)',
        'var(--status-success)',
        'var(--status-warning)',
        '#EC4899',
        '#8B5CF6'
    ];

    function createParticle(container, x, y) {
        const particle = document.createElement('div');
        const size = Math.random() * 10 + 6;
        const color = colors[Math.floor(Math.random() * colors.length)];
        const angle = Math.random() * Math.PI * 2;
        const velocity = Math.random() * 12 + 4;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity - 8;
        const rotation = Math.random() * 720;
        const rotationSpeed = (Math.random() - 0.5) * 20;
        const gravity = 0.4;
        const drag = 0.96;

        particle.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            border-radius: ${Math.random() > 0.5 ? '50%' : '2px'};
            left: ${x}px;
            top: ${y}px;
            pointer-events: none;
            z-index: 9999;
            will-change: transform;
        `;

        container.appendChild(particle);

        let posX = x;
        let posY = y;
        let velX = vx;
        let velY = vy;
        let rot = rotation;

        function animate() {
            velY += gravity;
            velX *= drag;
            velY *= drag;
            posX += velX;
            posY += velY;
            rot += rotationSpeed;

            particle.style.transform = `translate(${posX - x}px, ${posY - y}px) rotate(${rot}deg)`;
            particle.style.opacity = Math.max(0, 1 - (posY - y) / (window.innerHeight * 0.8));

            if (posY < window.innerHeight + 100 && particle.style.opacity > 0) {
                requestAnimationFrame(animate);
            } else {
                particle.remove();
            }
        }

        requestAnimationFrame(animate);
    }

    function celebrate(options = {}) {
        const { duration = 2500, particleCount = 50, origin = 'center' } = options;
        
        const container = document.createElement('div');
        container.style.cssText = `
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 9998;
            overflow: hidden;
        `;
        document.body.appendChild(container);

        let originX, originY;
        if (origin === 'center') {
            originX = window.innerWidth / 2;
            originY = window.innerHeight / 2;
        } else if (typeof origin === 'object') {
            originX = origin.x;
            originY = origin.y;
        }

        // Burst particles
        for (let i = 0; i < particleCount; i++) {
            setTimeout(() => {
                createParticle(container, originX, originY);
            }, i * 15);
        }

        // Cleanup container after animation
        setTimeout(() => {
            container.remove();
        }, duration + 1000);
    }

    function showCompletionToast(message, options = {}) {
        const { duration = 5000, title = 'Congratulations!' } = options;
        
        const toast = document.createElement('div');
        toast.className = 'completion-toast';
        toast.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 1rem;">
                <div class="completion-toast-icon">
                    <i class="fas fa-trophy" style="color: var(--status-success); font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h4 style="font-weight: 700; color: var(--text-primary); margin: 0 0 0.25rem;">${title}</h4>
                    <p style="color: var(--text-secondary); margin: 0; font-size: 0.875rem; line-height: 1.5;">${message}</p>
                </div>
                <button onclick="this.closest('.completion-toast').remove()" 
                        style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.25rem; margin-left: auto;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(120%)';
            setTimeout(() => toast.remove(), 500);
        }, duration);
    }

    return {
        celebrate,
        showCompletionToast,
        courseComplete: function(courseName) {
            celebrate({ duration: 3000, particleCount: 70 });
            showCompletionToast(
                `You have successfully completed <strong>${courseName}</strong>. Your certificate is now available in My Certificates.`,
                { title: 'Course Complete!', duration: 6000 }
            );
        }
    };
})();

// Auto-trigger if data-celebrate attribute is present
document.addEventListener('DOMContentLoaded', function() {
    const celebrateEl = document.querySelector('[data-celebrate]');
    if (celebrateEl) {
        const courseName = celebrateEl.dataset.celebrate;
        setTimeout(() => Confetti.courseComplete(courseName), 800);
    }
});
