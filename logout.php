<?php
session_start();

// Tüm session verilerini temizle
$_SESSION = array();

// Session cookie'sini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Remember me cookie'sini sil
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Session'ı yok et
session_destroy();

// Login sayfasına yönlendir
header('Location: index.php?success=' . urlencode('Başarıyla çıkış yapıldı'));
exit;

