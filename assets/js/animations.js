/**
 * SIMS - Animations JavaScript
 * Student Information Management System
 * 
 * Advanced animations and visual effects
 */

// Animation Configuration
const AnimationConfig = {
    particleCount: 0,
    animationSpeed: 1,
    enableParticles: false,
    enableGlitch: false
};

// Initialize Animations
document.addEventListener('DOMContentLoaded', () => {
    initializeAnimations();
    initializeScrollAnimations();
    initializeHoverEffects();
    initializeGlitchEffect();
    // initializeMatrixEffect(); // Removed - matrix rain effect disabled
    initializeTypewriterEffect();
});

// Initialize All Animations
function initializeAnimations() {
    console.log('SIMS Animations - Initializing...');
}

// Scroll Animations
function initializeScrollAnimations() {
    const animatedElements = document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .fade-in-up, .scale-in');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
}

// Hover Effects
function initializeHoverEffects() {
    // Neon glow on hover
    const glowElements = document.querySelectorAll('.neon-btn, .glass-card');
    
    glowElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            el.style.transition = 'all 0.3s ease';
        });
        
        el.addEventListener('mouseleave', () => {
            el.style.transition = 'all 0.3s ease';
        });
    });
    
    // Magnetic effect for buttons
    const magneticButtons = document.querySelectorAll('.neon-btn');
    
    magneticButtons.forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            
            btn.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px) scale(1.05)`;
        });
        
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = 'translate(0, 0) scale(1)';
        });
    });
}

// Glitch Effect
function initializeGlitchEffect() {
    if (!AnimationConfig.enableGlitch) return;
    
    const glitchElements = document.querySelectorAll('.glitch-effect');
    
    glitchElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            triggerGlitch(el);
        });
    });
}

function triggerGlitch(element) {
    const originalText = element.textContent;
    const glitchChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';
    
    let iterations = 0;
    const maxIterations = 10;
    
    const interval = setInterval(() => {
        element.textContent = originalText
            .split('')
            .map((char, index) => {
                if (index < iterations) {
                    return originalText[index];
                }
                return glitchChars[Math.floor(Math.random() * glitchChars.length)];
            })
            .join('');
        
        iterations += 1 / 3;
        
        if (iterations >= originalText.length) {
            clearInterval(interval);
            element.textContent = originalText;
        }
    }, 30);
}

// Matrix Rain Effect
function initializeMatrixEffect() {
    const canvas = document.createElement('canvas');
    canvas.className = 'matrix-canvas';
    canvas.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
        opacity: 0.1;
    `;
    document.body.appendChild(canvas);
    
    const ctx = canvas.getContext('2d');
    
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    const chars = 'SIMSABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*';
    const charArray = chars.split('');
    
    const fontSize = 14;
    const columns = canvas.width / fontSize;
    
    const drops = [];
    for (let i = 0; i < columns; i++) {
        drops[i] = 1;
    }
    
    function drawMatrix() {
        ctx.fillStyle = 'rgba(10, 10, 15, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        ctx.fillStyle = '#00f0ff';
        ctx.font = fontSize + 'px monospace';
        
        for (let i = 0; i < drops.length; i++) {
            const text = charArray[Math.floor(Math.random() * charArray.length)];
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);
            
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            
            drops[i]++;
        }
    }
    
    // Run matrix effect at reduced interval
    setInterval(drawMatrix, 50);
    
    // Resize handler
    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}

// Typewriter Effect
function initializeTypewriterEffect() {
    const typewriterElements = document.querySelectorAll('.typewriter-text');
    
    typewriterElements.forEach(el => {
        const text = el.textContent;
        el.textContent = '';
        
        let i = 0;
        const speed = 50;
        
        function type() {
            if (i < text.length) {
                el.textContent += text.charAt(i);
                i++;
                setTimeout(type, speed);
            }
        }
        
        // Start typing when element is in view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    type();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(el);
    });
}

// Loading Animation
function showLoadingAnimation() {
    const loader = document.createElement('div');
    loader.className = 'loading-overlay';
    loader.innerHTML = `
        <div class="loading-spinner"></div>
        <div class="loading-text">Initializing SIMS...</div>
    `;
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 10, 15, 0.95);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    document.body.appendChild(loader);
    
    return loader;
}

function hideLoadingAnimation(loader) {
    if (loader) {
        loader.style.opacity = '0';
        loader.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            loader.remove();
        }, 500);
    }
}

// Page Transition
function pageTransition(callback) {
    const overlay = document.createElement('div');
    overlay.className = 'page-transition';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #00f0ff, #ff00ff);
        z-index: 9998;
        transform: translateX(-100%);
        transition: transform 0.5s ease;
    `;
    document.body.appendChild(overlay);
    
    // Animate in
    setTimeout(() => {
        overlay.style.transform = 'translateX(0)';
    }, 10);
    
    // Execute callback and animate out
    setTimeout(() => {
        if (callback) callback();
        
        setTimeout(() => {
            overlay.style.transform = 'translateX(100%)';
            setTimeout(() => {
                overlay.remove();
            }, 500);
        }, 100);
    }, 600);
}

// Counter Animation
function animateCounter(element, target, duration = 2000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Progress Bar Animation
function animateProgressBar(element, target, duration = 1500) {
    element.style.width = '0%';
    element.style.transition = `width ${duration}ms ease`;
    
    setTimeout(() => {
        element.style.width = target + '%';
    }, 100);
}

// Ripple Effect
function createRipple(event, element) {
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    
    ripple.style.cssText = `
        position: absolute;
        width: ${size}px;
        height: ${size}px;
        left: ${event.clientX - rect.left - size / 2}px;
        top: ${event.clientY - rect.top - size / 2}px;
        background: rgba(0, 240, 255, 0.3);
        border-radius: 50%;
        transform: scale(0);
        animation: ripple 0.6s ease-out;
        pointer-events: none;
    `;
    
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

// Add ripple animation keyframes
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleStyle);

// Parallax Effect
function initializeParallax() {
    const parallaxElements = document.querySelectorAll('[data-parallax]');
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        
        parallaxElements.forEach(el => {
            const speed = el.dataset.parallax || 0.5;
            el.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}

// Export Animation Functions
window.SIMSAnimations = {
    triggerGlitch,
    showLoadingAnimation,
    hideLoadingAnimation,
    pageTransition,
    animateCounter,
    animateProgressBar,
    createRipple,
    initializeParallax
};
