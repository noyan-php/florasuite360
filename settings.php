<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php?error=' . urlencode('Lütfen giriş yapın'));
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı';
$loginTime = isset($_SESSION['login_time']) ? $_SESSION['login_time'] : time();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>
    <!-- Fixed Navbar -->
    <nav class="skeuomorphic-navbar">
        <div class="navbar-left">
            <div class="navbar-logo" id="menuToggle">
                <img src="svg/start-here.svg" alt="menu" width="24" height="24">
            </div>
            <a href="dashboard.php" class="navbar-title" style="text-decoration: none; color: inherit;">
                Dashboard
            </a>
        </div>
        <div class="navbar-right">
            <div class="navbar-user">
                <div class="user-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
            </div>
            <a href="logout.php" class="logout-btn">
                <img src="svg/system-log-out.svg" alt="logout" width="16" height="16">
                <span>Çıkış</span>
            </a>
        </div>
    </nav>

    <!-- Fixed Sidebar -->
    <aside class="skeuomorphic-sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="dashboard.php" class="menu-link">
                    <img src="svg/go-home.svg" alt="home" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                    Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="settings.php" class="menu-link active">
                    <img src="svg/preferences-system.svg" alt="settings" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                    Ayarlar
                </a>
            </li>
            <li class="menu-item">
                <a href="announcement.php" class="menu-link">
                    <img src="svg/mail-read.svg" alt="messages" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                    Duyurular
                </a>
            </li>
        </ul>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Modüller</div>
            <ul class="sidebar-menu">
                <li class="menu-item">
                    <a href="customers.php" class="menu-link">
                        <img src="svg/address-book-new.svg" alt="customers" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                        E-Kurumsal
                    </a>
                </li>
                <li class="menu-item">
                    <a href="debts.php" class="menu-link">
                        <img src="svg/view-sort-ascending.svg" alt="debts" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                        E-Veresiye
                    </a>
                </li>
                <li class="menu-item">
                    <a href="edefter.php" class="menu-link">
                        <img src="svg/document-new.svg" alt="edefter" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                        E-Defter
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="skeuomorphic-main">
        <div class="content-box">
            <div class="content-header">
                <h1 class="content-title">
                    <img src="svg/preferences-system.svg" alt="settings" width="24" height="24" style="vertical-align: middle; margin-right: 10px;">
                    Ayarlar
                </h1>
            </div>

            <!-- Görünüm Ayarları -->
            <div class="settings-section">
                <h2 class="section-title">Görünüm Ayarları</h2>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3 class="setting-name">Tema</h3>
                        <p class="setting-description">Açık veya koyu mod arasında seçim yapın</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="darkModeToggle">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h3 class="setting-name">Renk Şeması</h3>
                        <p class="setting-description">Hedef rengini seçin</p>
                    </div>
                    <div class="setting-control">
                        <div class="color-picker-grid">
                            <button class="color-option" data-color="blue" style="background: #5181b8;" title="Mavi (Varsayılan)"></button>
                            <button class="color-option" data-color="green" style="background: #4caf50;" title="Yeşil"></button>
                            <button class="color-option" data-color="purple" style="background: #9c27b0;" title="Mor"></button>
                            <button class="color-option" data-color="orange" style="background: #ff9800;" title="Turuncu"></button>
                            <button class="color-option" data-color="red" style="background: #f44336;" title="Kırmızı"></button>
                            <button class="color-option" data-color="teal" style="background: #009688;" title="Teal"></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Genel Ayarlar -->
            <div class="settings-section">
                <h2 class="section-title">Genel Ayarlar</h2>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3 class="setting-name">Bildirimler</h3>
                        <p class="setting-description">Sistem bildirimlerini etkinleştir</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="notificationsToggle" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h3 class="setting-name">Otomatik Çıkış</h3>
                        <p class="setting-description">30 dakika hareketsizlikte otomatik çıkış yap</p>
                    </div>
                    <div class="setting-control">
                        <label class="toggle-switch">
                            <input type="checkbox" id="autoLogoutToggle">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Kişisel Bilgiler -->
            <div class="settings-section">
                <h2 class="section-title">Kişisel Bilgiler</h2>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h3 class="setting-name">Kullanıcı Adı</h3>
                        <p class="setting-value"><?php echo htmlspecialchars($username); ?></p>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h3 class="setting-name">Giriş Zamanı</h3>
                        <p class="setting-value"><?php echo date('d.m.Y H:i:s', $loginTime); ?></p>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="setting-info">
                        <h3 class="setting-name">Durum</h3>
                        <p class="setting-value"><span class="badge">Aktif</span></p>
                    </div>
                </div>
            </div>

            <!-- Kaydet Butonu -->
            <div class="settings-actions">
                <button class="save-btn" id="saveSettingsBtn">
                    <img src="svg/document-save.svg" alt="save" width="18" height="18" style="vertical-align: middle; margin-right: 6px;">
                    Ayarları Kaydet
                </button>
                <button class="reset-btn" id="resetSettingsBtn">
                    Varsayılanlara Dön
                </button>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
    <script src="js/settings.js"></script>
    <script>
        // Apply theme settings on page load
        (function() {
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
        })();
    </script>
</body>
</html>

