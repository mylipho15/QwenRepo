/**
 * SISDM ABSENSI SISWA - Main JavaScript
 * Theme Management, Mode Toggle, and UI Interactions
 */

// Theme and Mode Management
class ThemeManager {
    constructor() {
        this.themes = ['fluent', 'material', 'glassmorphism', 'cyberpunk'];
        this.modes = ['white', 'light-gray', 'dark-gray', 'black', 'dark'];
        this.currentTheme = localStorage.getItem('theme') || 'fluent';
        this.currentMode = localStorage.getItem('mode') || 'white';
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
    }

    applyMode(mode) {
        if (mode === 'white') {
            document.documentElement.removeAttribute('data-mode');
        } else {
            document.documentElement.setAttribute('data-mode', mode);
        }
        localStorage.setItem('mode', mode);
        this.currentMode = mode;
    }

    toggleTheme() {
        const currentIndex = this.themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % this.themes.length;
        this.applyTheme(this.themes[nextIndex]);
    }

    toggleMode() {
        const currentIndex = this.modes.indexOf(this.currentMode);
        const nextIndex = (currentIndex + 1) % this.modes.length;
        this.applyMode(this.modes[nextIndex]);
    }

    setupEventListeners() {
        // Theme switcher
        const themeSelect = document.getElementById('theme-select');
        if (themeSelect) {
            themeSelect.value = this.currentTheme;
            themeSelect.addEventListener('change', (e) => {
                this.applyTheme(e.target.value);
            });
        }

        // Mode switcher
        const modeSelect = document.getElementById('mode-select');
        if (modeSelect) {
            modeSelect.value = this.currentMode;
            modeSelect.addEventListener('change', (e) => {
                this.applyMode(e.target.value);
            });
        }

        // Background image upload
        const bgInput = document.getElementById('bg-image-upload');
        if (bgInput) {
            bgInput.addEventListener('change', (e) => {
                this.handleBackgroundUpload(e);
            });
        }

        // Transparency slider
        const transparencySlider = document.getElementById('transparency-slider');
        if (transparencySlider) {
            transparencySlider.addEventListener('input', (e) => {
                document.documentElement.style.setProperty('--transparency', e.target.value);
            });
        }

        // Blur slider
        const blurSlider = document.getElementById('blur-slider');
        if (blurSlider) {
            blurSlider.addEventListener('input', (e) => {
                document.documentElement.style.setProperty('--blur', e.target.value + 'px');
            });
        }
    }

    handleBackgroundUpload(event) {
        const file = event.target.files[0];
        if (file && (file.type === 'image/jpeg' || file.type === 'image/png')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.body.style.backgroundImage = `url(${e.target.result})`;
                document.body.classList.add('bg-image');
                
                // Save to localStorage (note: large images may exceed storage limit)
                try {
                    localStorage.setItem('bg-image', e.target.result);
                } catch (err) {
                    console.warn('Image too large for localStorage');
                }
            };
            reader.readAsDataURL(file);
        }
    }
}

// Modal Management
class ModalManager {
    static open(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    static close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    static closeAll() {
        const modals = document.querySelectorAll('.modal-overlay');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
    }
}

// Form Validation
class FormValidator {
    static validateRequired(input) {
        return input.value.trim() !== '';
    }

    static validateEmail(input) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(input.value);
    }

    static validateMinLength(input, minLength) {
        return input.value.length >= minLength;
    }

    static showError(input, message) {
        const formGroup = input.closest('.form-group');
        let errorEl = formGroup.querySelector('.error-message');
        
        if (!errorEl) {
            errorEl = document.createElement('small');
            errorEl.className = 'error-message';
            errorEl.style.color = 'var(--danger)';
            errorEl.style.fontSize = '0.875rem';
            formGroup.appendChild(errorEl);
        }
        
        errorEl.textContent = message;
        input.style.borderColor = 'var(--danger)';
    }

    static clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorEl = formGroup.querySelector('.error-message');
        if (errorEl) {
            errorEl.remove();
        }
        input.style.borderColor = '';
    }
}

// Table Actions
class TableActions {
    static confirmDelete(actionUrl) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            window.location.href = actionUrl;
        }
    }

    static initDataTable(tableId) {
        // Simple search functionality
        const searchInput = document.querySelector(`#${tableId}-search`);
        if (searchInput) {
            searchInput.addEventListener('keyup', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                const table = document.getElementById(tableId);
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    }
}

// Notification System
class Notification {
    static show(message, type = 'info') {
        const container = document.getElementById('notification-container') || this.createContainer();
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        
        container.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    static createContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
        document.body.appendChild(container);
        return container;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
    
    // Initialize data tables
    document.querySelectorAll('table[data-table]').forEach(table => {
        TableActions.initDataTable(table.id);
    });

    // Auto-hide alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                ModalManager.close(modal.id);
            }
        });
    });
});

// Export for use in other scripts
window.ThemeManager = ThemeManager;
window.ModalManager = ModalManager;
window.FormValidator = FormValidator;
window.TableActions = TableActions;
window.Notification = Notification;
