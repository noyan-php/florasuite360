// Debts Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Toggle Summary
    const toggleSummaryBtn = document.getElementById('toggleSummaryBtn');
    const summaryTable = document.getElementById('summaryTable');
    
    if (toggleSummaryBtn && summaryTable) {
        // Toggle summary when clicked
        toggleSummaryBtn.addEventListener('click', function() {
            summaryTable.classList.toggle('collapsed');
            const icon = this.querySelector('img');
            if (summaryTable.classList.contains('collapsed')) {
                icon.src = 'svg/view-sort-ascending.svg';
                icon.style.transform = 'rotate(0deg)';
            } else {
                icon.src = 'svg/view-restore.svg';
                icon.style.transform = 'rotate(180deg)';
            }
        });
        
        // Set initial icon based on collapsed state
        const icon = toggleSummaryBtn.querySelector('img');
        if (summaryTable.classList.contains('collapsed')) {
            icon.src = 'svg/view-sort-ascending.svg';
        } else {
            icon.src = 'svg/view-restore.svg';
        }
    }
    
    // Transaction Modal
    const modal = document.getElementById('transactionModal');
    const addTransactionBtn = document.getElementById('addTransactionBtn');
    const closeBtns = document.querySelectorAll('.modal-close');
    
    // Open modal
    if (addTransactionBtn) {
        addTransactionBtn.addEventListener('click', function() {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Set default date to now
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('transactionDate').value = now.toISOString().slice(0, 16);
        });
    }
    
    // Close modal
    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        });
    });
    
    // Close modal on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
    
    // Archive button
    const archiveBtns = document.querySelectorAll('.archive-btn');
    archiveBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bu hesabı arşivlemek istediğinizden emin misiniz?')) {
                const customerId = this.getAttribute('data-id');
                archiveDebt(customerId);
            }
        });
    });
    
    // Form validation
    const transactionForm = document.getElementById('transactionForm');
    if (transactionForm) {
        transactionForm.addEventListener('submit', function(e) {
            const type = document.getElementById('transactionType').value;
            const amount = document.getElementById('transactionAmount').value;
            const date = document.getElementById('transactionDate').value;
            
            if (!type) {
                e.preventDefault();
                showNotification('Lütfen işlem türünü seçiniz', 'error');
                return false;
            }
            
            if (!amount || parseFloat(amount) <= 0) {
                e.preventDefault();
                showNotification('Lütfen geçerli bir tutar giriniz', 'error');
                document.getElementById('transactionAmount').focus();
                return false;
            }
            
            if (!date) {
                e.preventDefault();
                showNotification('Lütfen tarih seçiniz', 'error');
                document.getElementById('transactionDate').focus();
                return false;
            }
            
            return true;
        });
    }
    
    // Delete transaction buttons
    const deleteTransactionBtns = document.querySelectorAll('.delete-transaction');
    deleteTransactionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bu işlemi silmek istediğinizden emin misiniz?')) {
                const transactionId = this.getAttribute('data-id');
                const customerId = getCustomerIdFromURL();
                deleteTransaction(customerId, transactionId);
            }
        });
    });
    
    // Check for success/error messages
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');
    
    if (success) {
        showNotification(success, 'success');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    if (error) {
        showNotification(error, 'error');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// Get customer ID from URL
function getCustomerIdFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

// Delete Transaction Function
function deleteTransaction(customerId, transactionId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'debt_action.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete_transaction';
    
    const customerInput = document.createElement('input');
    customerInput.type = 'hidden';
    customerInput.name = 'customer_id';
    customerInput.value = customerId;
    
    const transactionInput = document.createElement('input');
    transactionInput.type = 'hidden';
    transactionInput.name = 'transaction_id';
    transactionInput.value = transactionId;
    
    form.appendChild(actionInput);
    form.appendChild(customerInput);
    form.appendChild(transactionInput);
    document.body.appendChild(form);
    form.submit();
}

// Archive Debt Function
function archiveDebt(customerId) {
    // Show loading state
    const btn = event.target.closest('.archive-btn');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span>Arşivleniyor...</span>';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // In real implementation, this would be an AJAX call
        showNotification('Hesap arşivlendi', 'success');
        location.reload();
    }, 500);
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

