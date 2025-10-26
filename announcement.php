<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php?error=' . urlencode('Lütfen giriş yapın'));
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı';

// Bildirim anahtarı (gerçek uygulamada veritabanından alınmalı)
$vapidPublicKey = "BEl62iUYgUivxIkv69yViEuiBIa40WRQvDU0-WQ2VJ5QYl2T7GXBvV_BSVZJgYGXQHqHJsUV0DIEWPGehTrjA8"; // Placeholder

// Verileri yükle
function loadAnnouncements() {
    $file = 'data/announcements.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

$announcements = loadAnnouncements();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyurular - Yönetim Paneli</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/announcement.css">
</head>
<body>
    <!-- Fixed Navbar -->
    <nav class="skeuomorphic-navbar">
        <div class="navbar-left">
            <div class="navbar-logo" id="menuToggle">
                <img src="svg/start-here.svg" alt="menu" width="24" height="24">
            </div>
            <a href="dashboard.php" class="navbar-title" style="text-decoration: none; color: inherit;">
                Duyurular
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
                <a href="settings.php" class="menu-link">
                    <img src="svg/preferences-system.svg" alt="settings" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                    Ayarlar
                </a>
            </li>
            <li class="menu-item">
                <a href="announcement.php" class="menu-link active">
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
                <div class="header-left">
                    <h1 class="content-title">
                        <img src="svg/mail-read.svg" alt="announcement" width="24" height="24" style="vertical-align: middle; margin-right: 10px;">
                        Duyuru Yönetimi
                    </h1>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" id="newAnnouncementBtn">
                        <img src="svg/list-add.svg" alt="add" width="16" height="16" style="vertical-align: middle; margin-right: 6px;">
                        Yeni Duyuru
                    </button>
                </div>
            </div>

            <!-- Mesajlaşma Alanı -->
            <div class="chat-container">
                <div class="chat-messages" id="chatMessages">
                    <?php if (empty($announcements)): ?>
                        <div class="empty-message">
                            <img src="svg/mail-read.svg" alt="empty" width="48" height="48" style="opacity: 0.3;">
                            <p>Henüz duyuru yok</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_reverse($announcements) as $announcement): ?>
                        <div class="message-item admin-message">
                            <div class="message-bubble priority-<?php echo $announcement['priority'] ?? 'normal'; ?>">
                                <div class="message-header">
                                    <div class="header-left">
                                        <div class="message-sender">Yönetim</div>
                                        <?php if (!empty($announcement['priority']) && $announcement['priority'] !== 'normal'): ?>
                                        <span class="priority-badge priority-<?php echo $announcement['priority']; ?>">
                                            <?php 
                                            $priorityLabels = [
                                                'low' => '🟢',
                                                'medium' => '🟡',
                                                'high' => '🔴',
                                                'urgent' => '🔥'
                                            ];
                                            echo $priorityLabels[$announcement['priority']] ?? '';
                                            ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="header-right">
                                        <div class="message-time"><?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?></div>
                                        <div class="message-actions">
                                            <button class="btn-edit" data-id="<?php echo $announcement['id']; ?>" title="Düzenle">
                                                <img src="svg/edit.svg" alt="edit" width="14" height="14">
                                            </button>
                                            <button class="btn-delete" data-id="<?php echo $announcement['id']; ?>" title="Sil">
                                                <img src="svg/edit-delete.svg" alt="delete" width="14" height="14">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="message-content"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Yeni Mesaj Formu -->
                <div class="chat-input-container">
                    <form id="announcementForm" action="announcement_action.php" method="POST">
                        <input type="hidden" name="action" value="send_announcement">
                        
                        <!-- Aciliyet Durumu -->
                        <div class="priority-selector">
                            <label>Aciliyet:</label>
                            <div class="priority-buttons">
                                <button type="button" class="priority-btn active" data-priority="normal" title="Normal">
                                    📝
                                </button>
                                <button type="button" class="priority-btn" data-priority="low" title="Düşük">
                                    🟢
                                </button>
                                <button type="button" class="priority-btn" data-priority="medium" title="Orta">
                                    🟡
                                </button>
                                <button type="button" class="priority-btn" data-priority="high" title="Yüksek">
                                    🔴
                                </button>
                                <button type="button" class="priority-btn" data-priority="urgent" title="Acil">
                                    🔥
                                </button>
                            </div>
                            <input type="hidden" name="priority" id="priorityInput" value="normal">
                        </div>
                        
                        <div class="input-group">
                            <textarea name="message" id="announcementMessage" placeholder="Duyuru mesajınızı buraya yazın..." required rows="3"></textarea>
                            <button type="submit" class="btn-send">
                                <img src="svg/mail-send-receive.svg" alt="send" width="20" height="20">
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Paylaşım Linki -->
            <div class="info-box" style="margin-top: 20px;">
                <h3>
                    <img src="svg/bookmark-new.svg" alt="link" width="18" height="18" style="vertical-align: middle; margin-right: 8px;">
                    Çalışanlarınız İçin Paylaşım Linki
                </h3>
                <div class="link-box">
                    <input type="text" id="shareLink" value="<?php echo (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/announcements_view.php'; ?>" readonly>
                    <button class="btn-icon-small" onclick="copyToClipboard()">
                        <img src="svg/edit-copy.svg" alt="copy" width="14" height="14">
                    </button>
                </div>
                <p class="link-note">Bu linki çalışanlarınızla paylaşın. Bu sayfa sadece okuma modundadır.</p>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
    <script src="js/announcement.js"></script>
    <script>
        // Apply theme
        (function() {
            const darkMode = localStorage.getItem('darkMode') === 'true';
            if (darkMode) document.documentElement.setAttribute('data-theme', 'dark');
            
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
        
        function copyToClipboard() {
            const link = document.getElementById('shareLink');
            link.select();
            document.execCommand('copy');
            showNotification('Link kopyalandı!', 'success');
        }
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = 'notification ' + (type === 'success' ? 'success' : 'error');
            notification.innerHTML = `<span>${message}</span>`;
            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>

