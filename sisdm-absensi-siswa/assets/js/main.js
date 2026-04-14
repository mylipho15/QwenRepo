/**
 * SISDM Absensi Siswa - Main JavaScript
 * Theme Management, UI Interactions, and AJAX Operations
 */

// Theme Management
class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'fluent-ui';
        this.currentColorMode = localStorage.getItem('colorMode') || 'light';
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.applyColorMode(this.currentColorMode);
        this.setupEventListeners();
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.currentTheme = theme;
        
        // Update active state in theme selector
        document.querySelectorAll('[data-theme-option]').forEach(el => {
            el.classList.remove('active');
            if (el.dataset.themeOption === theme) {
                el.classList.add('active');
            }
        });
    }

    applyColorMode(mode) {
        document.documentElement.setAttribute('data-color-mode', mode);
        localStorage.setItem('colorMode', mode);
        this.currentColorMode = mode;
        
        // Update active state in color mode selector
        document.querySelectorAll('[data-color-mode-option]').forEach(el => {
            el.classList.remove('active');
            if (el.dataset.colorModeOption === mode) {
                el.classList.add('active');
            }
        });
    }

    toggleTheme() {
        const themes = ['fluent-ui', 'material-ui', 'glassmorphism', 'cyberpunk'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themes.length;
        this.applyTheme(themes[nextIndex]);
    }

    toggleColorMode() {
        const modes = ['light', 'light-gray', 'dark-gray', 'dark'];
        const currentIndex = modes.indexOf(this.currentColorMode);
        const nextIndex = (currentIndex + 1) % modes.length;
        this.applyColorMode(modes[nextIndex]);
    }

    setupEventListeners() {
        // Theme switcher buttons
        document.querySelectorAll('[data-theme-option]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.applyTheme(btn.dataset.themeOption);
            });
        });

        // Color mode switcher buttons
        document.querySelectorAll('[data-color-mode-option]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.applyColorMode(btn.dataset.colorModeOption);
            });
        });

        // Transparency slider
        const transparencySlider = document.getElementById('transparencySlider');
        if (transparencySlider) {
            transparencySlider.addEventListener('input', (e) => {
                this.updateTransparency(e.target.value);
            });
        }

        // Blur slider
        const blurSlider = document.getElementById('blurSlider');
        if (blurSlider) {
            blurSlider.addEventListener('input', (e) => {
                this.updateBlur(e.target.value);
            });
        }
    }

    updateTransparency(value) {
        document.documentElement.style.setProperty('--app-transparency', value / 100);
        localStorage.setItem('transparency', value / 100);
    }

    updateBlur(value) {
        document.documentElement.style.setProperty('--app-blur', value + 'px');
        localStorage.setItem('blur', value);
    }

    setBackgroundImage(url) {
        document.body.style.backgroundImage = `url(${url})`;
        document.body.classList.add('bg-image');
        localStorage.setItem('backgroundImage', url);
    }

    removeBackgroundImage() {
        document.body.style.backgroundImage = 'none';
        document.body.classList.remove('bg-image');
        localStorage.removeItem('backgroundImage');
    }
}

// Modal Manager
class ModalManager {
    constructor() {
        this.init();
    }

    init() {
        // Close modal on close button click
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', () => {
                this.closeModal(btn.closest('.modal'));
            });
        });

        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });

        // Open modal buttons
        document.querySelectorAll('[data-modal-target]').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.modalTarget;
                const modal = document.getElementById(targetId);
                if (modal) {
                    this.openModal(modal, btn.dataset);
                }
            });
        });
    }

    openModal(modal, data = {}) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Load dynamic content if needed
        if (data.loadUrl) {
            this.loadModalContent(modal, data.loadUrl, data);
        }
    }

    closeModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    loadModalContent(modal, url, data) {
        const body = modal.querySelector('.modal-body');
        if (body) {
            body.innerHTML = '<div class="text-center">Loading...</div>';
            
            fetch(url + '?' + new URLSearchParams(data))
                .then(response => response.text())
                .then(html => {
                    body.innerHTML = html;
                })
                .catch(error => {
                    body.innerHTML = '<div class="alert alert-danger">Error loading content</div>';
                });
        }
    }
}

// Form Handler
class FormHandler {
    static async submit(form, options = {}) {
        const formData = new FormData(form);
        const url = form.action || window.location.href;
        const method = form.method || 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                if (options.onSuccess) {
                    options.onSuccess(result);
                } else {
                    showAlert(result.message || 'Operation successful', 'success');
                    if (options.closeModal) {
                        document.querySelector('.modal.active')?.classList.remove('active');
                    }
                    if (options.reload) {
                        setTimeout(() => location.reload(), 1000);
                    }
                }
            } else {
                showAlert(result.message || 'Operation failed', 'danger');
            }

            return result;
        } catch (error) {
            showAlert('An error occurred', 'danger');
            console.error(error);
        }
    }

    static async delete(url, options = {}) {
        if (!confirm('Are you sure you want to delete this item?')) {
            return;
        }

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message || 'Deleted successfully', 'success');
                if (options.reload) {
                    setTimeout(() => location.reload(), 1000);
                }
            } else {
                showAlert(result.message || 'Delete failed', 'danger');
            }

            return result;
        } catch (error) {
            showAlert('An error occurred', 'danger');
            console.error(error);
        }
    }
}

// Alert Helper
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fade-in`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.alert-container') || document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

// Table Search
function setupTableSearch() {
    document.querySelectorAll('[data-table-search]').forEach(input => {
        input.addEventListener('keyup', function() {
            const tableId = this.dataset.tableSearch;
            const table = document.getElementById(tableId);
            if (!table) return;

            const filter = this.value.toUpperCase();
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let showRow = false;
                const td = tr[i].getElementsByTagName('td');
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            showRow = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = showRow ? '' : 'none';
            }
        });
    });
}

// Date/Time Utilities
const DateTimeUtils = {
    formatDate(date) {
        const d = new Date(date);
        return d.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    formatTime(date) {
        const d = new Date(date);
        return d.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    formatDateTime(date) {
        return this.formatDate(date) + ' ' + this.formatTime(date);
    },

    getCurrentDateTime() {
        return new Date().toISOString().slice(0, 19).replace('T', ' ');
    }
};

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    window.themeManager = new ThemeManager();
    window.modalManager = new ModalManager();
    setupTableSearch();

    // Auto-dismiss alerts
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // Confirm delete actions
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirmDelete || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

// Export for use in other scripts
window.SISDM = {
    ThemeManager,
    ModalManager,
    FormHandler,
    DateTimeUtils,
    showAlert
};
