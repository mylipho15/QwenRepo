/**
 * SISDM Absensi Siswa - Main JavaScript
 * Theme Management, UI Interactions, and Dynamic Features
 */

// Theme and Mode Management
class ThemeManager {
    constructor() {
        this.themes = ['fluent', 'material', 'glassmorphism', 'cyberpunk'];
        this.modes = ['white', 'light-gray', 'dark-gray', 'black'];
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
        
        // Update UI
        const themeSelect = document.getElementById('theme-select');
        if (themeSelect) {
            themeSelect.value = theme;
        }
    }

    applyMode(mode) {
        document.documentElement.setAttribute('data-mode', mode);
        localStorage.setItem('mode', mode);
        this.currentMode = mode;
        
        // Update UI
        const modeSelect = document.getElementById('mode-select');
        if (modeSelect) {
            modeSelect.value = mode;
        }
    }

    setTheme(theme) {
        if (this.themes.includes(theme)) {
            this.applyTheme(theme);
        }
    }

    setMode(mode) {
        if (this.modes.includes(mode)) {
            this.applyMode(mode);
        }
    }

    toggleMode() {
        const currentIndex = this.modes.indexOf(this.currentMode);
        const nextIndex = (currentIndex + 1) % this.modes.length;
        this.setMode(this.modes[nextIndex]);
    }

    setupEventListeners() {
        // Theme selector
        const themeSelect = document.getElementById('theme-select');
        if (themeSelect) {
            themeSelect.addEventListener('change', (e) => {
                this.setTheme(e.target.value);
            });
        }

        // Mode selector
        const modeSelect = document.getElementById('mode-select');
        if (modeSelect) {
            modeSelect.addEventListener('change', (e) => {
                this.setMode(e.target.value);
            });
        }

        // Background image upload
        const bgUpload = document.getElementById('bg-upload');
        if (bgUpload) {
            bgUpload.addEventListener('change', (e) => {
                this.handleBackgroundUpload(e);
            });
        }

        // Transparency slider
        const transparencySlider = document.getElementById('transparency-slider');
        if (transparencySlider) {
            transparencySlider.addEventListener('input', (e) => {
                this.updateTransparency(e.target.value);
            });
        }

        // Blur slider
        const blurSlider = document.getElementById('blur-slider');
        if (blurSlider) {
            blurSlider.addEventListener('input', (e) => {
                this.updateBlur(e.target.value);
            });
        }
    }

    handleBackgroundUpload(event) {
        const file = event.target.files[0];
        if (file && (file.type === 'image/jpeg' || file.type === 'image/png')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    document.body.style.backgroundImage = `url(${e.target.result})`;
                    document.body.classList.add('bg-image');
                    
                    // Save to localStorage
                    localStorage.setItem('background', e.target.result);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    updateTransparency(value) {
        document.documentElement.style.setProperty('--transparency', value);
        localStorage.setItem('transparency', value);
    }

    updateBlur(value) {
        document.documentElement.style.setProperty('--blur', value + 'px');
        localStorage.setItem('blur', value);
    }

    loadSavedSettings() {
        const savedBg = localStorage.getItem('background');
        if (savedBg) {
            document.body.style.backgroundImage = `url(${savedBg})`;
            document.body.classList.add('bg-image');
        }

        const savedTransparency = localStorage.getItem('transparency');
        if (savedTransparency) {
            this.updateTransparency(savedTransparency);
        }

        const savedBlur = localStorage.getItem('blur');
        if (savedBlur) {
            this.updateBlur(savedBlur);
        }
    }
}

// Modal Management
class ModalManager {
    static open(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    }

    static close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
        }
    }

    static closeAll() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
    }
}

// Sidebar Toggle for Mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Confirm Delete
function confirmDelete(url) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        window.location.href = url;
    }
}

// Auto-dismiss alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Initialize theme manager
    window.themeManager = new ThemeManager();
    window.themeManager.loadSavedSettings();

    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                ModalManager.close(modal.id);
            }
        });
    });

    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            ModalManager.closeAll();
        }
    });
});

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

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
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        return { success: false, message: 'Terjadi kesalahan' };
    }
}

// Real-time clock
function updateClock() {
    const clockElement = document.getElementById('realtime-clock');
    if (clockElement) {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        clockElement.textContent = now.toLocaleDateString('id-ID', options);
    }
}

setInterval(updateClock, 1000);
updateClock();

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = [];
        cols.forEach(col => {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// Print function
function printSection(elementId) {
    const content = document.getElementById(elementId).innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}

console.log('SISDM Absensi Siswa loaded successfully!');
