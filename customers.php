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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Kurumsal - Müşteri Yönetimi</title>
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
                E-Kurumsal
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
                <div class="header-left">
                    <h1 class="content-title">
                        <img src="svg/address-book-new.svg" alt="customers" width="24" height="24" style="vertical-align: middle; margin-right: 10px;">
                        Müşteri Yönetimi
                    </h1>
                </div>
                <div class="header-right">
                    <button class="add-customer-btn" id="addCustomerBtn">
                        <img src="svg/list-add.svg" alt="add" width="18" height="18">
                        Yeni Müşteri Ekle
                    </button>
                </div>
            </div>

            <!-- Müşteri Listesi -->
            <div class="customers-table-container">
                <?php if (empty($customers)): ?>
                    <div class="empty-state">
                        <img src="svg/address-book-new.svg" alt="empty" width="64" height="64">
                        <h3>Henüz müşteri eklenmemiş</h3>
                        <p>İlk müşterinizi eklemek için "Yeni Müşteri Ekle" butonuna tıklayın</p>
                    </div>
                <?php else: ?>
                    <table class="customers-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad/Kurum</th>
                                <th>Telefon</th>
                                <th>Email</th>
                                <th>E-Veresiye</th>
                                <th>Tarih</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $index => $customer): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone'] ?? 'Belirtilmemiş'); ?></td>
                                <td><?php echo htmlspecialchars($customer['email'] ?? 'Belirtilmemiş'); ?></td>
                                <td>
                                    <?php if (!empty($customer['has_debt'])): ?>
                                        <span class="badge badge-success">Var</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Yok</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo isset($customer['created_at']) ? date('d.m.Y', strtotime($customer['created_at'])) : 'Bilinmiyor'; ?></td>
                                <td class="action-buttons">
                                    <a href="customer_form.php?id=<?php echo $index; ?>" class="action-btn edit-btn" title="Düzenle">
                                        <img src="svg/edit-paste.svg" alt="edit" width="16" height="16">
                                    </a>
                                    <button class="action-btn delete-btn" data-id="<?php echo $index; ?>" title="Sil">
                                        <img src="svg/edit-delete.svg" alt="delete" width="16" height="16">
                                    </button>
                                    <?php if (!empty($customer['has_debt'])): ?>
                                    <a href="debts.php?customer_id=<?php echo $index; ?>" class="action-btn debt-btn" title="E-Veresiye">
                                        <img src="svg/view-sort-ascending.svg" alt="debt" width="16" height="16">
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
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

