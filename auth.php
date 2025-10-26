<?php
session_start();

// Giriş denemeleri için rate limiting (basit)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// 5 yanlış denemeden sonra 15 dakika bekle
if ($_SESSION['login_attempts'] >= 5) {
    $waitTime = 900; // 15 dakika
    if (time() - $_SESSION['last_attempt_time'] < $waitTime) {
        $remaining = $waitTime - (time() - $_SESSION['last_attempt_time']);
        header('Location: index.php?error=' . urlencode('Çok fazla deneme. Lütfen ' . ceil($remaining / 60) . ' dakika sonra tekrar deneyin.'));
        exit;
    } else {
        // Bekleme süresi doldu, sıfırla
        $_SESSION['login_attempts'] = 0;
    }
}

// Formdan gelen verileri al
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$remember = isset($_POST['remember']) ? true : false;

// Temel validasyon
if (empty($username) || empty($password)) {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    header('Location: index.php?error=' . urlencode('Kullanıcı adı ve şifre boş olamaz'));
    exit;
}

// Veritabanı bağlantısı olmadan basit kullanıcı doğrulama
// Bu kısmı kendi sisteminize göre güncelleyin
$valid_users = [
    'admin' => password_hash('admin123', PASSWORD_DEFAULT),
    'demo' => password_hash('demo123', PASSWORD_DEFAULT),
    'test' => password_hash('test123', PASSWORD_DEFAULT)
];

// Eğer hash'ler yoksa (ilk kez), oluştur
// Gerçek üretim ortamında bu dosyada saklanmamalı!
if (!isset($valid_users['admin']) || !password_verify('admin123', $valid_users['admin'])) {
    $valid_users = [
        'admin' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // admin123
        'demo' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // demo123
        'test' => '$2y$10$YourGeneratedHashHere' // test123 - gerçek ortamda üretin
    ];
}

// Kullanıcı doğrulama
if (isset($valid_users[$username]) && password_verify($password, $valid_users[$username])) {
    // Başarılı giriş
    $_SESSION['user_logged_in'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['login_attempts'] = 0; // Sıfırla
    
    // "Beni hatırla" seçeneği
    if ($remember) {
        setcookie('remember_user', $username, time() + (86400 * 30), '/'); // 30 gün
    }
    
    // Dashboard'a yönlendir
    header('Location: dashboard.php');
    exit;
} else {
    // Başarısız giriş
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    
    header('Location: index.php?error=' . urlencode('Kullanıcı adı veya şifre hatalı'));
    exit;
}

