// Announcement Module JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const announcementForm = document.getElementById('announcementForm');
    const announcementMessage = document.getElementById('announcementMessage');
    
    // Priority buttons
    const priorityButtons = document.querySelectorAll('.priority-btn');
    const priorityInput = document.getElementById('priorityInput');
    
    priorityButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            priorityButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            priorityInput.value = this.getAttribute('data-priority');
        });
    });
    
    // Delete message buttons
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Bu duyuruyu silmek istediğinizden emin misiniz?')) {
                const id = this.getAttribute('data-id');
                deleteAnnouncement(id);
            }
        });
    });
    
    // Edit message buttons
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            // Edit functionality could be added here
            showNotification('Düzenleme özelliği yakında eklenecek', 'info');
        });
    });
    
    if (announcementForm) {
        announcementForm.addEventListener('submit', function(e) {
            const message = announcementMessage.value.trim();
            
            if (!message) {
                e.preventDefault();
                showNotification('Lütfen bir mesaj yazın', 'error');
                return false;
            }
            
            return true;
        });
    }
    
    // Check for success/error messages
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');
    
    if (success) {
        showNotification(success, 'success');
        window.history.replaceState({}, document.title, window.location.pathname);
        
        // Mesajı temizle
        if (announcementMessage) {
            announcementMessage.value = '';
        }
        
        // Scroll to top
        setTimeout(() => {
            document.querySelector('.chat-messages').scrollTop = 0;
        }, 100);
    }
    
    if (error) {
        showNotification(error, 'error');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + (type === 'success' ? 'success' : type === 'error' ? 'error' : '');
    notification.innerHTML = `<span>${message}</span>`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Delete Announcement
function deleteAnnouncement(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'announcement_action.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete_announcement';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = id;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}

