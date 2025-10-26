<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php?error=' . urlencode('Lütfen giriş yapın'));
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı';

// Müşteri verilerini yükle
function loadCustomers() {
    $file = 'data/customers.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

$customers = loadCustomers();
$editing = isset($_GET['id']);
$customer = null;
$isEdit = false;

if ($editing) {
    $id = intval($_GET['id']);
    if (isset($customers[$id])) {
        $customer = $customers[$id];
        $isEdit = true;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Müşteri Düzenle' : 'Yeni Müşteri'; ?> - E-Kurumsal</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/customers.css">
</head>
<body>
    <!-- Fixed Navbar -->
    <nav class="skeuomorphic-navbar">
        <div class="navbar-left">
            <div class="navbar-logo" id="menuToggle">
                <img src="svg/start-here.svg" alt="menu" width="24" height="24">
            </div>
            <a href="dashboard.php" class="navbar-title" style="text-decoration: none; color: inherit;">
                <?php echo $isEdit ? 'Müşteri Düzenle' : 'Yeni Müşteri'; ?>
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
        </ul>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Modüller</div>
            <ul class="sidebar-menu">
                <li class="menu-item">
                    <a href="customers.php" class="menu-link active">
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
                    <?php if ($isEdit): ?>
                        <img src="svg/edit-paste.svg" alt="edit" width="24" height="24" style="vertical-align: middle; margin-right: 10px;">
                        Müşteri Düzenle
                    <?php else: ?>
                        <img src="svg/list-add.svg" alt="add" width="24" height="24" style="vertical-align: middle; margin-right: 10px;">
                        Yeni Müşteri Ekle
                    <?php endif; ?>
                </h1>
            </div>

            <form id="customerForm" action="customer_action.php" method="POST">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="action" value="add">
                <?php endif; ?>

                <div class="form-section">
                    <h2 class="section-title">Temel Bilgiler</h2>
                    
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="name">
                                <img src="svg/user-info.svg" alt="name" width="16" height="16" style="vertical-align: middle; margin-right: 6px;">
                                Adı veya Kurum İsmi *
                            </label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo $customer ? htmlspecialchars($customer['name']) : ''; ?>"
                                   placeholder="Örn: Ahmet Yılmaz veya ABC A.Ş.">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">
                                <img src="svg/appointment-new.svg" alt="phone" width="16" height="16" style="vertical-align: middle; margin-right: 6px;">
                                Telefon
                            </label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo $customer ? htmlspecialchars($customer['phone'] ?? '') : ''; ?>"
                                   placeholder="0 (5xx) xxx xx xx">
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <img src="svg/mail-read.svg" alt="email" width="16" height="16" style="vertical-align: middle; margin-right: 6px;">
                                E-posta
                            </label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo $customer ? htmlspecialchars($customer['email'] ?? '') : ''; ?>"
                                   placeholder="ornek@email.com">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">Adres Bilgileri</h2>
                    
                    <div class="form-group full-width">
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" rows="3" 
                                  placeholder="Detaylı adres bilgisi girebilirsiniz"><?php echo $customer ? htmlspecialchars($customer['address'] ?? '') : ''; ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="section-title">E-Veresiye Ayarları</h2>
                    
                    <div class="form-group full-width">
                        <label class="checkbox-label">
                            <input type="checkbox" name="create_debt" id="createDebt" 
                                   <?php echo (!$isEdit || !empty($customer['has_debt'])) ? 'checked' : ''; ?>>
                            <span>Bu müşteri için E-Veresiye hesabı oluştur</span>
                        </label>
                        <p class="form-hint">E-Veresiye ile müşterinin borç/alacak işlemlerini takip edebilirsiniz.</p>
                    </div>
                </div>

                <?php if ($isEdit && !empty($customer['notes'])): ?>
                <div class="form-section">
                    <h2 class="section-title">Notlar</h2>
                    <div class="notes-display">
                        <?php echo nl2br(htmlspecialchars($customer['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <a href="customers.php" class="btn btn-cancel">
                        <img src="svg/view-restore.svg" alt="cancel" width="18" height="18">
                        İptal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <img src="svg/document-save.svg" alt="save" width="18" height="18">
                        <?php echo $isEdit ? 'Güncelle' : 'Kaydet'; ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
    <script src="js/customers.js"></script>
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
    </script>
</body>
</html>

