// Customers Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Add Customer Button
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', function() {
            window.location.href = 'customer_form.php';
        });
    }

    // Delete Customer
    const deleteBtns = document.querySelectorAll('.delete-btn');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bu müşteriyi silmek istediğinizden emin misiniz?')) {
                const customerId = this.getAttribute('data-id');
                deleteCustomer(customerId);
            }
        });
    });

    // Customer Form Validation
    const customerForm = document.getElementById('customerForm');
    if (customerForm) {
        customerForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            
            if (!name) {
                e.preventDefault();
                showNotification('Lütfen müşteri adını giriniz', 'error');
                document.getElementById('name').focus();
                return false;
            }

            if (name.length < 3) {
                e.preventDefault();
                showNotification('Müşteri adı en az 3 karakter olmalıdır', 'error');
                document.getElementById('name').focus();
                return false;
            }
        });
    }

    // Phone Input Formatting
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                // Format: 0 (5xx) xxx xx xx
                let formatted = '0 (';
                if (value.length > 1) formatted += value.substring(1, 4);
                if (value.length > 4) {
                    formatted += ') ' + value.substring(4, 7);
                } else if (value.length > 4) {
                    formatted += ') ' + value.substring(4);
                }
                if (value.length > 7) {
                    formatted += ' ' + value.substring(7, 9);
                } else if (value.length > 7) {
                    formatted += ' ' + value.substring(7);
                }
                if (value.length > 9) {
                    formatted += ' ' + value.substring(9, 11);
                }
                this.value = formatted;
            }
        });
    }

    // Email Validation
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                showNotification('Geçerli bir e-posta adresi giriniz', 'error');
                this.focus();
            }
        });
    }

    // Check Success/Error Messages
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');

    if (success) {
        showNotification(success, 'success');
        // Remove parameter from URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    if (error) {
        showNotification(error, 'error');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Delete Customer Function
function deleteCustomer(customerId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'customer_action.php';

    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete';

    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = customerId;

    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}

// Email Validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show Notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + (type === 'success' ? 'success' : type === 'error' ? 'error' : '');
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

// Apply theme settings
function applyThemeSettings() {
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    const themeColor = localStorage.getItem('themeColor') || 'blue';
    const colorMap = {
        blue: { primary: '#5181b8', dark: '#3d6ba3' },
        green: { primary: '#4caf50', dark: '#3ba43f' },
        purple: { primary: '#9c27b0', dark: '#7b1fa2' },
        orange: { primary: '#ff9800', dark: '#f57c00' },
        red: { primary: '#f44336', dark: '#d32f2f' },
        teal: { primary: '#009688', dark: '#00796b' }
    };

    const colors = colorMap[themeColor] || colorMap.blue;
    document.documentElement.style.setProperty('--primary-color', colors.primary);
    document.documentElement.style.setProperty('--primary-dark', colors.dark);
}

// Initialize theme
applyThemeSettings();

