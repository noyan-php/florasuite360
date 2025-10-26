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
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Fixed Navbar -->
    <nav class="skeuomorphic-navbar">
        <div class="navbar-left">
            <div class="navbar-logo" id="menuToggle">
                <img src="svg/start-here.svg" alt="menu" width="24" height="24">
            </div>
            <div class="navbar-title">Dashboard</div>
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
                <a href="#dashboard" class="menu-link active">
                    <img src="svg/go-home.svg" alt="home" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                    Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="settings.php" class="menu-link">
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
                <h1 class="content-title">Hoşgeldiniz, <?php echo htmlspecialchars($username); ?>!</h1>
            </div>

            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">Giriş Bilgileri</h2>
                </div>
                <div class="info-row">
                    <span class="info-label">Kullanıcı Adı:</span>
                    <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Giriş Zamanı:</span>
                    <span class="info-value"><?php echo date('d.m.Y H:i:s', $loginTime); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Durum:</span>
                    <span class="info-value"><span class="badge">Aktif</span></span>
                </div>
            </div>

            <div class="info-card">
                <div class="card-header">
                    <h2 class="card-title">Sistem Bilgileri</h2>
                </div>
                <div class="info-row">
                    <span class="info-label">PHP Versiyonu:</span>
                    <span class="info-value"><?php echo phpversion(); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sunucu Adı:</span>
                    <span class="info-value"><?php echo $_SERVER['SERVER_NAME']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Sunucu Yazılımı:</span>
                    <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                </div>
            </div>
        </div>
    </main>
    <script src="js/dashboard.js"></script>
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
