// Core functionality for Click n' Pop
// Version 2.0 - Compatible avec BubbleGame
class App {
    constructor() {
        // Détecter si nous sommes sur la page de jeu
        this.isGamePage = document.querySelector('.game-page') !== null;
        
        if (this.isGamePage) {
            // Initialisation minimale pour la page de jeu
            this.initForGamePage();
        } else {
            // Initialisation complète pour les autres pages
            this.init();
        }
    }

    init() {
        this.setupEventListeners();
        this.initTheme();
        this.initScrollEffects();
        this.initLoading();
        this.initAOS();
        this.initCounters();
        this.initMobileMenu();
    }

    initForGamePage() {
        // Initialisation spécifique pour la page de jeu
        this.initTheme();
        this.initGamePageListeners();
        this.initLoading();
    }

    setupEventListeners() {
        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
        
        // Back to top
        window.addEventListener('scroll', () => this.handleBackToTop());
        const backToTopBtn = document.getElementById('back-to-top');
        if (backToTopBtn) {
            backToTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Header scroll effect
        window.addEventListener('scroll', () => this.handleHeaderScroll());
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    initGamePageListeners() {
        // Header scroll effect seulement
        window.addEventListener('scroll', () => this.handleHeaderScroll());
        
        // Theme toggle pour la page de jeu
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }

    initTheme() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = savedTheme === 'system' ? (prefersDark ? 'dark' : 'light') : savedTheme;
        
        document.documentElement.setAttribute('data-theme', theme);
        this.updateThemeIcon(theme);
        
        // Dispatch event pour informer les autres composants
        document.dispatchEvent(new CustomEvent('themechange', { detail: theme }));
    }

    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        this.updateThemeIcon(newTheme);
        
        // Dispatch event pour informer le jeu
        document.dispatchEvent(new CustomEvent('themechange', { detail: newTheme }));
        
        // Show feedback
        this.showToast(`Switched to ${newTheme} mode`, 'info', 2000);
    }

    updateThemeIcon(theme) {
        const toggleBtn = document.getElementById('theme-toggle');
        if (!toggleBtn) return;
        
        toggleBtn.setAttribute('aria-label', `Switch to ${theme === 'light' ? 'dark' : 'light'} mode`);
        toggleBtn.innerHTML = theme === 'light' ? 
            '<i class="fas fa-moon"></i><i class="fas fa-sun" style="display:none"></i>' :
            '<i class="fas fa-moon" style="display:none"></i><i class="fas fa-sun"></i>';
    }

    handleHeaderScroll() {
        const header = document.querySelector('header');
        if (!header) return;

        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }

    handleBackToTop() {
        const backToTopBtn = document.getElementById('back-to-top');
        if (!backToTopBtn) return;

        if (window.scrollY > 500) {
            backToTopBtn.classList.add('visible');
        } else {
            backToTopBtn.classList.remove('visible');
        }
    }

    initScrollEffects() {
        // Animate on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('aos-animate');
                }
            });
        }, observerOptions);

        // Observe elements with data-aos attribute
        document.querySelectorAll('[data-aos]').forEach(el => observer.observe(el));
    }

    initAOS() {
        // Simple Animate On Scroll implementation
        const aosObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const delay = entry.target.dataset.aosDelay || 0;
                    const duration = entry.target.dataset.aosDuration || '600ms';
                    
                    setTimeout(() => {
                        entry.target.style.transition = `all ${duration} cubic-bezier(0.4, 0, 0.2, 1)`;
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, parseInt(delay));
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('[data-aos]').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            aosObserver.observe(el);
        });
    }

    initLoading() {
        // Hide loader when page loads
        window.addEventListener('load', () => {
            setTimeout(() => {
                const loader = document.getElementById('global-loader');
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.visibility = 'hidden';
                    
                    setTimeout(() => {
                        if (loader.parentNode) {
                            loader.parentNode.removeChild(loader);
                        }
                    }, 500);
                }
            }, 500);
        });
        
        // Fallback si la page prend trop de temps à charger
        setTimeout(() => {
            const loader = document.getElementById('global-loader');
            if (loader && loader.style.visibility !== 'hidden') {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
                
                setTimeout(() => {
                    if (loader.parentNode) {
                        loader.parentNode.removeChild(loader);
                    }
                }, 500);
            }
        }, 3000);
    }

    initCounters() {
        const counters = document.querySelectorAll('[data-count]');
        if (counters.length === 0) return;
        
        counters.forEach(counter => {
            const target = parseInt(counter.dataset.count);
            const increment = target / 100;
            let current = 0;
            
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    counter.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            };
            
            // Start when in viewport
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    updateCounter();
                    observer.unobserve(counter);
                }
            }, { threshold: 0.5 });
            
            observer.observe(counter);
        });
    }

    initMobileMenu() {
        const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (!mobileMenuToggle || !navMenu) return;
        
        mobileMenuToggle.addEventListener('click', () => {
            const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
            mobileMenuToggle.setAttribute('aria-expanded', !isExpanded);
            navMenu.style.display = isExpanded ? 'none' : 'flex';
            
            // Animation
            if (!isExpanded) {
                navMenu.style.opacity = '0';
                navMenu.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    navMenu.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    navMenu.style.opacity = '1';
                    navMenu.style.transform = 'translateY(0)';
                }, 10);
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (navMenu.style.display === 'flex' && 
                !navMenu.contains(e.target) && 
                !mobileMenuToggle.contains(e.target)) {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                navMenu.style.opacity = '0';
                navMenu.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    navMenu.style.display = 'none';
                }, 300);
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.style.display === 'flex') {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                navMenu.style.opacity = '0';
                navMenu.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    navMenu.style.display = 'none';
                }, 300);
            }
        });
    }

    // Toast notifications
    showToast(message, type = 'info', duration = 5000) {
        // Créer le conteneur s'il n'existe pas
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
            
            // Ajouter les styles si nécessaire
            if (!document.querySelector('#toast-styles')) {
                const style = document.createElement('style');
                style.id = 'toast-styles';
                style.textContent = `
                    .toast-container {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 9999;
                        display: flex;
                        flex-direction: column;
                        gap: 10px;
                        max-width: 400px;
                    }
                    
                    .toast {
                        background: var(--color-bg-card);
                        border: 1px solid var(--color-border);
                        border-radius: var(--radius);
                        padding: 1rem;
                        box-shadow: var(--shadow-lg);
                        display: flex;
                        align-items: center;
                        gap: 0.75rem;
                        min-width: 300px;
                        transform: translateX(100%);
                        opacity: 0;
                        animation: toastSlide 0.3s ease forwards;
                    }
                    
                    @keyframes toastSlide {
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    
                    .toast.toast-success {
                        border-left: 4px solid var(--color-success);
                    }
                    
                    .toast.toast-error {
                        border-left: 4px solid var(--color-danger);
                    }
                    
                    .toast.toast-warning {
                        border-left: 4px solid var(--color-accent);
                    }
                    
                    .toast.toast-info {
                        border-left: 4px solid var(--color-primary);
                    }
                    
                    .toast-icon {
                        font-size: 1.2rem;
                    }
                    
                    .toast-success .toast-icon { color: var(--color-success); }
                    .toast-error .toast-icon { color: var(--color-danger); }
                    .toast-warning .toast-icon { color: var(--color-accent); }
                    .toast-info .toast-icon { color: var(--color-primary); }
                    
                    .toast-message {
                        flex: 1;
                        font-size: 0.95rem;
                    }
                    
                    .toast-close {
                        background: none;
                        border: none;
                        font-size: 1.2rem;
                        cursor: pointer;
                        color: var(--color-text-tertiary);
                        padding: 0;
                        width: 24px;
                        height: 24px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 4px;
                    }
                    
                    .toast-close:hover {
                        background: var(--color-bg-secondary);
                        color: var(--color-text-primary);
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icon = this.getToastIcon(type);
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close" aria-label="Close notification">&times;</button>
        `;

        container.appendChild(toast);
        
        // Animation d'entrée
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        }, 10);

        // Auto remove
        const removeTimeout = setTimeout(() => this.removeToast(toast), duration);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            clearTimeout(removeTimeout);
            this.removeToast(toast);
        });
        
        // Toucher pour fermer sur mobile
        toast.addEventListener('click', (e) => {
            if (e.target === toast || e.target.classList.contains('toast-message')) {
                clearTimeout(removeTimeout);
                this.removeToast(toast);
            }
        });
    }

    removeToast(toast) {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        toast.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    getToastIcon(type) {
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-exclamation-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };
        return icons[type] || icons.info;
    }

    // Form validation helper
    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            // Reset previous errors
            input.classList.remove('error');
            const errorMessage = input.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
            
            // Check validation
            if (!input.value.trim()) {
                this.showInputError(input, 'This field is required');
                isValid = false;
            } else if (input.type === 'email' && !this.validateEmail(input.value)) {
                this.showInputError(input, 'Please enter a valid email address');
                isValid = false;
            } else if (input.type === 'password' && input.value.length < 6) {
                this.showInputError(input, 'Password must be at least 6 characters');
                isValid = false;
            }
        });
        
        return isValid;
    }

    validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    showInputError(input, message) {
        input.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: var(--color-danger);
            font-size: 0.85rem;
            margin-top: 0.25rem;
            display: block;
        `;
        
        input.parentNode.appendChild(errorDiv);
        
        // Focus on the invalid input
        input.focus();
    }

    // Utility function to format numbers
    formatNumber(number) {
        return new Intl.NumberFormat().format(number);
    }

    // Utility function to format dates
    formatDate(date, format = 'short') {
        const options = {
            short: {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            },
            long: {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            },
            time: {
                hour: '2-digit',
                minute: '2-digit'
            }
        };
        
        return new Date(date).toLocaleDateString('en-US', options[format] || options.short);
    }

    // Debounce function for performance
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Throttle function for performance
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // Cookie helper functions
    setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    eraseCookie(name) {
        document.cookie = name + '=; Max-Age=-99999999;';
    }

    // Local storage helper
    setLocalStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return false;
        }
    }

    getLocalStorage(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('LocalStorage error:', e);
            return null;
        }
    }

    // Session storage helper
    setSessionStorage(key, value) {
        try {
            sessionStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('SessionStorage error:', e);
            return false;
        }
    }

    getSessionStorage(key) {
        try {
            const item = sessionStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('SessionStorage error:', e);
            return null;
        }
    }

    // Copy to clipboard
    copyToClipboard(text) {
        return new Promise((resolve, reject) => {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(() => resolve(true))
                    .catch(err => reject(err));
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    textArea.remove();
                    resolve(true);
                } catch (err) {
                    textArea.remove();
                    reject(err);
                }
            }
        });
    }

    // Share content
    shareContent({ title, text, url }) {
        if (navigator.share) {
            return navigator.share({ title, text, url });
        } else {
            // Fallback: copy to clipboard
            return this.copyToClipboard(text)
                .then(() => this.showToast('Copied to clipboard!', 'success'))
                .catch(() => this.showToast('Failed to share', 'error'));
        }
    }

    // Responsive image loader
    loadResponsiveImage(imgElement, srcSet, sizes = '100vw') {
        if (!imgElement) return;
        
        imgElement.style.opacity = '0';
        imgElement.style.transition = 'opacity 0.3s ease';
        
        const image = new Image();
        image.onload = () => {
            imgElement.src = image.src;
            if (srcSet) imgElement.srcset = srcSet;
            if (sizes) imgElement.sizes = sizes;
            
            setTimeout(() => {
                imgElement.style.opacity = '1';
            }, 100);
        };
        image.onerror = () => {
            console.error('Failed to load image:', srcSet);
        };
        image.src = srcSet.split(',')[0].split(' ')[0];
    }

    // Lazy load images
    initLazyLoading() {
        const lazyImages = document.querySelectorAll('img[data-src], img[data-srcset]');
        if (!lazyImages.length) return;
        
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        delete img.dataset.src;
                    }
                    
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                        delete img.dataset.srcset;
                    }
                    
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        }, { rootMargin: '50px 0px' });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    }

    // Initialize tooltips
    initTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        if (!tooltipElements.length) return;
        
        tooltipElements.forEach(element => {
            const tooltipText = element.dataset.tooltip;
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = tooltipText;
            tooltip.style.cssText = `
                position: absolute;
                background: var(--color-bg-secondary);
                color: var(--color-text-primary);
                padding: 0.5rem 0.75rem;
                border-radius: var(--radius-sm);
                font-size: 0.85rem;
                white-space: nowrap;
                z-index: 1000;
                opacity: 0;
                visibility: hidden;
                transform: translateY(10px);
                transition: all 0.2s ease;
                box-shadow: var(--shadow-md);
                border: 1px solid var(--color-border);
                pointer-events: none;
            `;
            
            document.body.appendChild(tooltip);
            
            element.addEventListener('mouseenter', (e) => {
                const rect = element.getBoundingClientRect();
                tooltip.style.left = `${rect.left + rect.width / 2}px`;
                tooltip.style.top = `${rect.top - 10}px`;
                tooltip.style.transform = 'translate(-50%, -100%)';
                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
            });
            
            element.addEventListener('mouseleave', () => {
                tooltip.style.opacity = '0';
                tooltip.style.visibility = 'hidden';
                tooltip.style.transform = 'translate(-50%, -90%)';
            });
        });
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Vérifier si le jeu est déjà initialisé
    if (window.game && window.game instanceof BubbleGame) {
        // Si le jeu est présent, initialiser seulement le thème et les fonctionnalités de base
        const app = new App();
        app.initTheme();
        app.handleHeaderScroll();
        
        // Ajouter le thème toggle au jeu s'il n'existe pas
        if (!document.getElementById('theme-toggle')) {
            const themeToggle = document.createElement('button');
            themeToggle.id = 'theme-toggle';
            themeToggle.className = 'theme-toggle';
            themeToggle.innerHTML = '<i class="fas fa-moon"></i><i class="fas fa-sun" style="display:none"></i>';
            themeToggle.setAttribute('aria-label', 'Toggle dark/light mode');
            document.body.appendChild(themeToggle);
            
            themeToggle.addEventListener('click', () => app.toggleTheme());
        }
    } else {
        // Initialiser l'app complète
        window.App = new App();
    }
    
    // Initialize lazy loading pour toutes les pages
    if (window.App && window.App.initLazyLoading) {
        window.App.initLazyLoading();
    }
    
    // Initialize tooltips pour toutes les pages
    if (window.App && window.App.initTooltips) {
        window.App.initTooltips();
    }
});

// Export pour une utilisation dans d'autres fichiers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = App;
}