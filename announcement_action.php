<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php?error=' . urlencode('Lütfen giriş yapın'));
    exit;
}

// data klasörünü oluştur
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

function loadAnnouncements() {
    $file = 'data/announcements.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function saveAnnouncements($announcements) {
    $file = 'data/announcements.json';
    return file_put_contents($file, json_encode($announcements, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function loadSubscriptions() {
    $file = 'data/subscriptions.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function sendPushNotification($subscriptions, $message) {
    // Bu kısım gerçek Web Push API için güncellenmeli
    // Şimdilik sadece kayıt yapıyoruz
    return true;
}

$action = $_POST['action'] ?? '';
$announcements = loadAnnouncements();

if ($action === 'send_announcement') {
    $message = $_POST['message'] ?? '';
    
    if (empty($message)) {
        header('Location: announcement.php?error=' . urlencode('Mesaj boş olamaz'));
        exit;
    }
    
    // Yeni duyuru ekle
    $announcement = [
        'id' => time() . '-' . rand(1000, 9999),
        'message' => $message,
        'priority' => $_POST['priority'] ?? 'normal',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $announcements[] = $announcement;
    saveAnnouncements($announcements);
    
    // Push bildirimleri gönder
    $subscriptions = loadSubscriptions();
    if (!empty($subscriptions)) {
        sendPushNotification($subscriptions, $message);
    }
    
    header('Location: announcement.php?success=' . urlencode('Duyuru gönderildi'));
    exit;
    
} elseif ($action === 'delete_announcement') {
    $id = $_POST['id'];
    
    $announcements = array_filter($announcements, function($announcement) use ($id) {
        return $announcement['id'] !== $id;
    });
    
    saveAnnouncements(array_values($announcements));
    
    header('Location: announcement.php?success=' . urlencode('Duyuru silindi'));
    exit;
}

header('Location: announcement.php');

