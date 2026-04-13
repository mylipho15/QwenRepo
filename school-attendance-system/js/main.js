// Theme and Mode Management
class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'fluent';
        this.currentMode = localStorage.getItem('mode') || 'light';
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.applyMode(this.currentMode);
        this.setupEventListeners();
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.currentTheme = theme;
        
        // Update active state in theme selector
        const themeButtons = document.querySelectorAll('[data-theme-option]');
        themeButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.themeOption === theme);
        });
    }

    applyMode(mode) {
        document.documentElement.setAttribute('data-mode', mode);
        localStorage.setItem('mode', mode);
        this.currentMode = mode;
        
        // Update active state in mode selector
        const modeButtons = document.querySelectorAll('[data-mode-option]');
        modeButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.modeOption === mode);
        });
    }

    toggleTheme() {
        const themes = ['fluent', 'material', 'glassmorphism', 'cyberpunk'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themes.length;
        this.applyTheme(themes[nextIndex]);
    }

    toggleMode() {
        const modes = ['light', 'light-gray', 'dark-gray', 'black'];
        const currentIndex = modes.indexOf(this.currentMode);
        const nextIndex = (currentIndex + 1) % modes.length;
        this.applyMode(modes[nextIndex]);
    }

    setupEventListeners() {
        // Theme toggle button
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }

        // Mode toggle button
        const modeToggle = document.querySelector('.mode-toggle');
        if (modeToggle) {
            modeToggle.addEventListener('click', () => this.toggleMode());
        }

        // Theme selector buttons
        document.querySelectorAll('[data-theme-option]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.applyTheme(e.target.dataset.themeOption);
            });
        });

        // Mode selector buttons
        document.querySelectorAll('[data-mode-option]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.applyMode(e.target.dataset.modeOption);
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 't') {
                e.preventDefault();
                this.toggleTheme();
            }
            if (e.ctrlKey && e.key === 'm') {
                e.preventDefault();
                this.toggleMode();
            }
        });
    }
}

// Modal Management
class ModalManager {
    constructor() {
        this.init();
    }

    init() {
        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.close(modal);
                }
            });
        });

        // Close modal with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    this.close(openModal);
                }
            }
        });

        // Close button clicks
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                if (modal) {
                    this.close(modal);
                }
            });
        });
    }

    open(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    close(modal) {
        if (typeof modal === 'string') {
            modal = document.getElementById(modal);
        }
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
}

// Table Actions
class TableActions {
    constructor() {
        this.init();
    }

    init() {
        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                const type = e.target.dataset.type;
                this.editItem(id, type);
            });
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.dataset.id;
                const type = e.target.dataset.type;
                this.deleteItem(id, type);
            });
        });
    }

    editItem(id, type) {
        // This will be overridden by specific page implementations
        console.log(`Edit ${type} with ID: ${id}`);
    }

    deleteItem(id, type) {
        if (confirm(`Are you sure you want to delete this ${type}?`)) {
            // This will be overridden by specific page implementations
            console.log(`Delete ${type} with ID: ${id}`);
        }
    }
}

// Form Validation
class FormValidator {
    static required(field, message = 'This field is required') {
        if (!field.value.trim()) {
            return message;
        }
        return null;
    }

    static email(field, message = 'Please enter a valid email') {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!re.test(field.value)) {
            return message;
        }
        return null;
    }

    static minLength(field, length, message = `Minimum length is ${length}`) {
        if (field.value.length < length) {
            return message;
        }
        return null;
    }

    static maxLength(field, length, message = `Maximum length is ${length}`) {
        if (field.value.length > length) {
            return message;
        }
        return null;
    }

    static number(field, message = 'Please enter a valid number') {
        if (isNaN(field.value)) {
            return message;
        }
        return null;
    }

    static validate(form, rules) {
        const errors = {};
        
        for (const [fieldName, validators] of Object.entries(rules)) {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (!field) continue;

            for (const validator of validators) {
                const error = validator(field);
                if (error) {
                    errors[fieldName] = error;
                    field.classList.add('error');
                    break;
                } else {
                    field.classList.remove('error');
                }
            }
        }

        return Object.keys(errors).length === 0 ? null : errors;
    }
}

// Notification System
class Notification {
    static show(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '300px';
        notification.style.animation = 'slideIn 0.3s';

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'fadeIn 0.3s reverse';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    static success(message) {
        this.show(message, 'success');
    }

    static error(message) {
        this.show(message, 'danger');
    }

    static warning(message) {
        this.show(message, 'warning');
    }

    static info(message) {
        this.show(message, 'info');
    }
}

// Sidebar Toggle for Mobile
class SidebarManager {
    constructor() {
        this.init();
    }

    init() {
        const toggleBtn = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new ThemeManager();
    new ModalManager();
    new TableActions();
    new SidebarManager();
});

// AJAX Helper
async function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Request failed');
        }
        
        return result;
    } catch (error) {
        console.error('AJAX Error:', error);
        Notification.error(error.message);
        throw error;
    }
}

// Date Format Helper
function formatDate(date, format = 'YYYY-MM-DD') {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    
    return format
        .replace('YYYY', year)
        .replace('MM', month)
        .replace('DD', day)
        .replace('HH', hours)
        .replace('mm', minutes);
}

// Get current date in various formats
function getCurrentDate() {
    return {
        mysql: formatDate(new Date(), 'YYYY-MM-DD'),
        display: formatDate(new Date(), 'DD/MM/YYYY'),
        timestamp: Math.floor(Date.now() / 1000)
    };
}
