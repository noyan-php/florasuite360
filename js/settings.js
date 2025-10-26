// Settings Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize settings from localStorage
    loadSettings();
    
    // Dark Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    darkModeToggle.addEventListener('change', function() {
        toggleDarkMode(this.checked);
        saveSettings();
    });
    
    // Color Options
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from all options
            colorOptions.forEach(opt => opt.classList.remove('active'));
            // Add active class to clicked option
            this.classList.add('active');
            
            // Change theme color
            const color = this.getAttribute('data-color');
            changeThemeColor(color);
            saveSettings();
        });
    });
    
    // Notifications Toggle
    const notificationsToggle = document.getElementById('notificationsToggle');
    notificationsToggle.addEventListener('change', function() {
        saveSettings();
        showNotification('Bildirim ayarı ' + (this.checked ? 'açıldı' : 'kapatıldı'));
    });
    
    // Auto Logout Toggle
    const autoLogoutToggle = document.getElementById('autoLogoutToggle');
    autoLogoutToggle.addEventListener('change', function() {
        saveSettings();
        showNotification('Otomatik çıkış ' + (this.checked ? 'açıldı' : 'kapatıldı'));
    });
    
    // Save Settings Button
    const saveSettingsBtn = document.getElementById('saveSettingsBtn');
    saveSettingsBtn.addEventListener('click', function() {
        saveSettings();
        showNotification('Ayarlar kaydedildi!', 'success');
    });
    
    // Reset Settings Button
    const resetSettingsBtn = document.getElementById('resetSettingsBtn');
    resetSettingsBtn.addEventListener('click', function() {
        if (confirm('Tüm ayarları varsayılan değerlere döndürmek istediğinizden emin misiniz?')) {
            resetSettings();
            showNotification('Ayarlar varsayılan değerlere döndürüldü', 'success');
        }
    });
});

// Load settings from localStorage
function loadSettings() {
    const settings = {
        darkMode: localStorage.getItem('darkMode') === 'true',
        themeColor: localStorage.getItem('themeColor') || 'blue',
        notifications: localStorage.getItem('notifications') !== 'false',
        autoLogout: localStorage.getItem('autoLogout') === 'true'
    };
    
    // Apply dark mode
    document.getElementById('darkModeToggle').checked = settings.darkMode;
    toggleDarkMode(settings.darkMode);
    
    // Apply theme color
    document.querySelector(`.color-option[data-color="${settings.themeColor}"]`).classList.add('active');
    changeThemeColor(settings.themeColor);
    
    // Apply notification settings
    document.getElementById('notificationsToggle').checked = settings.notifications;
    document.getElementById('autoLogoutToggle').checked = settings.autoLogout;
}

// Toggle dark mode
function toggleDarkMode(enabled) {
    if (enabled) {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
    localStorage.setItem('darkMode', enabled);
}

// Change theme color
function changeThemeColor(color) {
    const colorMap = {
        blue: { primary: '#5181b8', dark: '#3d6ba3' },
        green: { primary: '#4caf50', dark: '#3ba43f' },
        purple: { primary: '#9c27b0', dark: '#7b1fa2' },
        orange: { primary: '#ff9800', dark: '#f57c00' },
        red: { primary: '#f44336', dark: '#d32f2f' },
        teal: { primary: '#009688', dark: '#00796b' }
    };
    
    const colors = colorMap[color] || colorMap.blue;
    
    // Set CSS variables
    document.documentElement.style.setProperty('--primary-color', colors.primary);
    document.documentElement.style.setProperty('--primary-dark', colors.dark);
    
    localStorage.setItem('themeColor', color);
}

// Save all settings
function saveSettings() {
    const settings = {
        darkMode: document.getElementById('darkModeToggle').checked,
        themeColor: document.querySelector('.color-option.active')?.getAttribute('data-color') || 'blue',
        notifications: document.getElementById('notificationsToggle').checked,
        autoLogout: document.getElementById('autoLogoutToggle').checked
    };
    
    localStorage.setItem('darkMode', settings.darkMode);
    localStorage.setItem('themeColor', settings.themeColor);
    localStorage.setItem('notifications', settings.notifications);
    localStorage.setItem('autoLogout', settings.autoLogout);
}

// Reset settings to default
function resetSettings() {
    // Reset toggles
    document.getElementById('darkModeToggle').checked = false;
    document.getElementById('notificationsToggle').checked = true;
    document.getElementById('autoLogoutToggle').checked = false;
    
    // Reset dark mode
    toggleDarkMode(false);
    
    // Reset color to blue
    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
    document.querySelector('.color-option[data-color="blue"]').classList.add('active');
    changeThemeColor('blue');
    
    // Clear localStorage
    localStorage.removeItem('darkMode');
    localStorage.removeItem('themeColor');
    localStorage.removeItem('notifications');
    localStorage.removeItem('autoLogout');
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + (type === 'success' ? 'success' : '');
    notification.innerHTML = `
        <span>${message}</span>
        <button class="notification-close">×</button>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Close button handler
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Apply settings to other pages
function applySettingsToPage() {
    // Load and apply dark mode
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
    
    // Load and apply theme color
    const themeColor = localStorage.getItem('themeColor') || 'blue';
    changeThemeColor(themeColor);
}

// Auto-apply on page load
applySettingsToPage();
