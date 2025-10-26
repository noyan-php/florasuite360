<?php
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
    <title>Duyurular</title>
    <link rel="stylesheet" href="css/announcements_view.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0084FF">
</head>
<body>
    <!-- MSN Window -->
    <div class="msn-window">
        <!-- Title Bar -->
        <div class="msn-titlebar">
            <div class="titlebar-icon">
                <img src="svg/mail-read.svg" alt="icon" width="16" height="16">
            </div>
            <div class="titlebar-title">
                Duyurular
            </div>
            <div class="titlebar-buttons">
                <button class="btn-minimize">&#8212;</button>
                <button class="btn-maximize">&#9744;</button>
                <button class="btn-close">&#10006;</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="msn-content">
            <!-- Messages Container -->
            <div class="msn-messages" id="messagesContainer">
                <?php if (empty($announcements)): ?>
                <div class="msn-empty">
                    <div class="empty-icon">💬</div>
                    <h3>Henüz mesaj yok</h3>
                    <p>Yönetim duyuruları burada görünecek</p>
                </div>
                <?php else: ?>
                <?php foreach (array_reverse($announcements) as $announcement): ?>
                <div class="msn-message priority-<?php echo $announcement['priority'] ?? 'normal'; ?>">
                    <div class="message-avatar">
                        <div class="avatar-circle">YM</div>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <div class="header-left">
                                <span class="sender-name">Yönetim</span>
                                <?php if (!empty($announcement['priority']) && $announcement['priority'] !== 'normal'): ?>
                                <div class="priority-indicator priority-<?php echo $announcement['priority']; ?>">
                                    <?php 
                                    $priorityIcons = [
                                        'low' => '🟢 Düşük',
                                        'medium' => '🟡 Orta',
                                        'high' => '🔴 Yüksek',
                                        'urgent' => '🔥 Acil'
                                    ];
                                    echo $priorityIcons[$announcement['priority']] ?? '';
                                    ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <span class="message-time"><?php echo date('d.m.Y H:i', strtotime($announcement['created_at'])); ?></span>
                        </div>
                        <div class="message-text">
                            <?php echo nl2br(htmlspecialchars($announcement['message'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="msn-statusbar">
            <div class="statusbar-left">
                <span class="status-indicator"></span>
                Bağlı
            </div>
            <div class="statusbar-right">
                <?php echo count($announcements); ?> mesaj
            </div>
        </div>
    </div>

    <!-- Notification Button -->
    <div class="notification-fab" id="notificationFab" style="display: none;">
        <button class="btn-notify" id="enableNotifications">
            <span>🔔 Bildirimleri Aç</span>
        </button>
    </div>

    <script>
        // Check if service worker is supported
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            // Show notification button
            const notificationFab = document.getElementById('notificationFab');
            const enableNotificationsBtn = document.getElementById('enableNotifications');
            
            notificationFab.style.display = 'flex';
            
            // Check if already subscribed
            navigator.serviceWorker.ready.then(function(registration) {
                registration.pushManager.getSubscription().then(function(subscription) {
                    if (subscription) {
                        notificationFab.style.display = 'none';
                    }
                });
            });
            
            // Enable notifications button
            enableNotificationsBtn.addEventListener('click', function() {
                enableNotifications();
            });
        }
        
        function enableNotifications() {
            if ('serviceWorker' in navigator && 'PushManager' in window) {
                navigator.serviceWorker.register('service-worker.js')
                    .then(function(registration) {
                        return registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array('<?php echo file_exists('vapid_public_key.txt') ? file_get_contents('vapid_public_key.txt') : 'BEl62iUYgUivxIkv69yViEuiBIa40WRQvDU0-WQ2VJ5QYl2T7GXBvV_BSVZJgYGXQHqHJsUV0DIEWPGehTrjA8'; ?>')
                        });
                    })
                    .then(function(subscription) {
                        console.log('Subscribed:', subscription);
                        // Send subscription to server
                        fetch('notification_subscribe.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(subscription)
                        });
                        
                        document.getElementById('notificationFab').style.display = 'none';
                        alert('Bildirimler aktif! Artık yeni duyurulardan haberdar olacaksınız.');
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        alert('Bildirim izni alınamadı. Lütfen tarayıcı ayarlarından izin verin.');
                    });
            }
        }
        
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    </script>
</body>
</html>

