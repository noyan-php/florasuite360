<?php
session_start();

// Eğer kullanıcı zaten giriş yaptıysa dashboard'a yönlendir
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="icon-box">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#5a5a5a" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h1>Giriş Yap</h1>
            </div>

            <form id="loginForm" method="POST" action="auth.php">
                <div class="form-group">
                    <label for="username">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5a5a5a" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Kullanıcı Adı</span>
                    </label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5a5a5a" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Şifre</span>
                    </label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <button type="button" class="toggle-password" id="togglePassword">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#5a5a5a" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>

                <div class="form-group checkbox-group">
                    <label for="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <span>Beni hatırla</span>
                    </label>
                </div>

                <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d32f2f" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
                <?php endif; ?>

                <button type="submit" class="login-button">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2">
                        <polyline points="9 10 4 15 9 20"></polyline>
                        <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                    </svg>
                    <span>Giriş Yap</span>
                </button>
            </form>

            <div class="login-footer">
                <a href="#">Şifremi unuttum</a>
            </div>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>

