document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const usernameInput = document.getElementById('username');
    const loginButton = document.querySelector('.login-button');

    // Şifre görünürlüğünü toggle et
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // İkon değiştir - basit görünürlük değişikliği
            if (type === 'text') {
                this.style.opacity = '0.5';
            } else {
                this.style.opacity = '1';
            }
        });
    }

    // Form validasyonu
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            const password = passwordInput.value;

            // Temel validasyon
            if (!username) {
                e.preventDefault();
                showError('Kullanıcı adı boş olamaz');
                usernameInput.focus();
                return false;
            }

            if (!password) {
                e.preventDefault();
                showError('Şifre boş olamaz');
                passwordInput.focus();
                return false;
            }

            if (username.length < 3) {
                e.preventDefault();
                showError('Kullanıcı adı en az 3 karakter olmalıdır');
                usernameInput.focus();
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                showError('Şifre en az 6 karakter olmalıdır');
                passwordInput.focus();
                return false;
            }

            // Loading state
            loginButton.disabled = true;
            loginButton.classList.add('loading');
            
            // Form gönderilebilir
            return true;
        });
    }

    // Error mesajını göster
    function showError(message) {
        // Mevcut error mesajını kaldır
        const existingError = document.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Yeni error mesajı oluştur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d32f2f" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>${message}</span>
        `;

        // Form'un üstüne ekle
        const formGroups = loginForm.querySelectorAll('.form-group');
        loginForm.insertBefore(errorDiv, formGroups[formGroups.length - 1]);

        // 3 saniye sonra kaldır
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }

    // Enter tuşu ile form gönderimi
    usernameInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            passwordInput.focus();
        }
    });

    passwordInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loginForm.requestSubmit();
        }
    });

    // Sayfa yüklendiğinde kullanıcı adına odaklan
    usernameInput.focus();
});
