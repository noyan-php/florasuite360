// E-Defter Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Order Modal
    const modal = document.getElementById('orderModal');
    const addOrderBtn = document.getElementById('addOrderBtn');
    const closeBtns = document.querySelectorAll('.modal-close');
    
                // Open modal
                if (addOrderBtn) {
                    addOrderBtn.addEventListener('click', function() {
                        if (modal) {
                            modal.classList.add('show');
                            document.body.style.overflow = 'hidden';
                            
                            // Set default time to now
                            const now = new Date();
                            const timeString = now.toTimeString().slice(0, 5);
                            const timeInput = document.getElementById('orderTime');
                            if (timeInput) {
                                timeInput.value = timeString;
                            }
                            
                            // Set default date to selected date
                            const dateInput = document.getElementById('orderDate');
                            if (dateInput) {
                                // Get selected date from URL
                                const urlParams = new URLSearchParams(window.location.search);
                                let selectedDate = urlParams.get('date');
                                if (!selectedDate) {
                                    selectedDate = new Date().toISOString().split('T')[0];
                                }
                                dateInput.value = selectedDate;
                            }
                            
                            // Reset customer select
                            const customerSearch = document.getElementById('customerSearch');
                            const customerSelect = document.getElementById('customerSelect');
                            const customerManualInput = document.getElementById('customerNameManual');
                            const customerDropdown = document.getElementById('customerDropdown');
                            
                            if (customerSearch) {
                                customerSearch.value = '';
                            }
                            if (customerSelect) {
                                customerSelect.value = '';
                            }
                            if (customerManualInput) {
                                customerManualInput.style.display = 'none';
                                customerManualInput.required = false;
                                customerManualInput.value = '';
                            }
                            if (customerDropdown) {
                                customerDropdown.style.display = 'none';
                            }
                            
                            // Veresiye checkbox'ını başlangıçta gizle
                            const addToDebtCheckbox = document.getElementById('addToDebt');
                            if (addToDebtCheckbox) {
                                addToDebtCheckbox.closest('.form-group').style.display = 'none';
                                addToDebtCheckbox.checked = false;
                            }
                        }
                    });
                }
    
    // Close modal
    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close modal on outside click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    }
    
    // Order Type Toggle
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const orderTypeInput = document.getElementById('orderType');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            toggleBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Update hidden input
            if (orderTypeInput) {
                orderTypeInput.value = this.getAttribute('data-type') === 'wedding' ? '1' : '0';
            }
        });
    });
    
    // Form validation
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            const customerId = document.getElementById('customerSelect').value;
            const address = document.getElementById('orderAddress').value;
            const time = document.getElementById('orderTime').value;
            const amount = document.getElementById('orderAmount').value;
            
            if (!customerId) {
                e.preventDefault();
                showNotification('Lütfen müşteri seçiniz', 'error');
                return false;
            }
            
            if (!address.trim()) {
                e.preventDefault();
                showNotification('Lütfen adres giriniz', 'error');
                document.getElementById('orderAddress').focus();
                return false;
            }
            
            if (!time) {
                e.preventDefault();
                showNotification('Lütfen saat seçiniz', 'error');
                document.getElementById('orderTime').focus();
                return false;
            }
            
            if (!amount || parseFloat(amount) <= 0) {
                e.preventDefault();
                showNotification('Lütfen geçerli bir tutar giriniz', 'error');
                document.getElementById('orderAmount').focus();
                return false;
            }
            
            return true;
        });
    }
    
    // Process to debt button
    const processDebtBtns = document.querySelectorAll('.order-action-btn');
    processDebtBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bu siparişi e-veresiye hesabına işlemek istediğinizden emin misiniz?')) {
                const customerId = this.getAttribute('data-customer-id');
                const orderId = this.getAttribute('data-id');
                processToDebt(customerId, orderId);
            }
        });
    });

    // Mark as delivered
    const markDeliveredBtns = document.querySelectorAll('.mark-delivered-btn');
    markDeliveredBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bu sipariş teslim edildi olarak işaretlensin mi?')) {
                const orderId = this.getAttribute('data-id');
                markAsDelivered(orderId);
            }
        });
    });

    // Delete order
    const deleteOrderBtns = document.querySelectorAll('.delete-order-btn');
    deleteOrderBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bu siparişi silmek istediğinizden emin misiniz?')) {
                const orderId = this.getAttribute('data-id');
                deleteOrder(orderId);
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

    // Customer search
    const customerSearch = document.getElementById('customerSearch');
    const customerDropdown = document.getElementById('customerDropdown');
    const customerSelect = document.getElementById('customerSelect');
    const customerManualInput = document.getElementById('customerNameManual');
    
    if (customerSearch && customerDropdown) {
        let selectedCustomer = null;
        
        // Focus on search input
        customerSearch.addEventListener('focus', function() {
            customerDropdown.style.display = 'block';
        });
        
        // Filter customers on input
        customerSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const options = customerDropdown.querySelectorAll('.customer-option');
            
            options.forEach(option => {
                const text = option.querySelector('span').textContent.toLowerCase();
                if (text.includes(searchTerm) || searchTerm === '') {
                    option.classList.remove('hidden');
                } else {
                    option.classList.add('hidden');
                }
            });
        });
        
        // Select customer
        customerDropdown.addEventListener('click', function(e) {
            const option = e.target.closest('.customer-option');
            if (option) {
                const value = option.getAttribute('data-value');
                const text = option.querySelector('span').textContent;
                
                if (value === 'manual') {
                    customerSelect.value = 'manual';
                    customerManualInput.style.display = 'block';
                    customerManualInput.required = true;
                    customerSearch.value = '';
                    customerDropdown.style.display = 'none';
                    customerManualInput.focus();
                    
                    // Veresiye checkbox'ını gizle
                    const addToDebtCheckbox = document.getElementById('addToDebt');
                    if (addToDebtCheckbox) {
                        addToDebtCheckbox.closest('.form-group').style.display = 'none';
                        addToDebtCheckbox.checked = false;
                    }
                } else {
                    customerSelect.value = value;
                    customerSearch.value = text;
                    customerManualInput.style.display = 'none';
                    customerManualInput.required = false;
                    customerDropdown.style.display = 'none';
                    selectedCustomer = { id: value, name: text };
                    
                    // Veresiye checkbox'ını göster
                    const addToDebtCheckbox = document.getElementById('addToDebt');
                    if (addToDebtCheckbox) {
                        addToDebtCheckbox.closest('.form-group').style.display = 'block';
                    }
                }
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!customerSearch.contains(e.target) && !customerDropdown.contains(e.target)) {
                customerDropdown.style.display = 'none';
            }
        });
    }
});

// Change Date
function changeDate(days) {
    const urlParams = new URLSearchParams(window.location.search);
    let currentDate = urlParams.get('date') || new Date().toISOString().split('T')[0];
    
    const dateObj = new Date(currentDate);
    dateObj.setDate(dateObj.getDate() + days);
    
    const newDate = dateObj.toISOString().split('T')[0];
    window.location.href = 'edefter.php?date=' + newDate;
}

// Go to Today
function goToToday() {
    const today = new Date().toISOString().split('T')[0];
    window.location.href = 'edefter.php?date=' + today;
}

// Process to Debt
function processToDebt(customerId, orderId) {
    // Show loading state
    showNotification('E-veresiye hesabına işleniyor...', 'info');
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'order_action.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'process_to_debt';
    
    const customerInput = document.createElement('input');
    customerInput.type = 'hidden';
    customerInput.name = 'customer_id';
    customerInput.value = customerId;
    
    const orderInput = document.createElement('input');
    orderInput.type = 'hidden';
    orderInput.name = 'order_id';
    orderInput.value = orderId;
    
    form.appendChild(actionInput);
    form.appendChild(customerInput);
    form.appendChild(orderInput);
    document.body.appendChild(form);
    form.submit();
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

// Set Product
function setProduct(value) {
    const productInput = document.getElementById('orderProduct');
    if (productInput) {
        productInput.value = value;
    }
}

// Set Time
function setTime(value) {
    const timeInput = document.getElementById('orderTime');
    if (timeInput) {
        timeInput.value = value;
    }
}

// Mark as Delivered
function markAsDelivered(orderId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'order_action.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'mark_delivered';
    
    const orderInput = document.createElement('input');
    orderInput.type = 'hidden';
    orderInput.name = 'order_id';
    orderInput.value = orderId;
    
    form.appendChild(actionInput);
    form.appendChild(orderInput);
    document.body.appendChild(form);
    form.submit();
}

// Delete Order
function deleteOrder(orderId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'order_action.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete_order';
    
    const orderInput = document.createElement('input');
    orderInput.type = 'hidden';
    orderInput.name = 'order_id';
    orderInput.value = orderId;
    
    form.appendChild(actionInput);
    form.appendChild(orderInput);
    document.body.appendChild(form);
    form.submit();
}
