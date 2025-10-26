<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php?error=' . urlencode('Lütfen giriş yapın'));
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı';

// Verileri yükle
function loadOrders() {
    $file = 'data/orders.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function loadCustomers() {
    $file = 'data/customers.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

$orders = loadOrders();
$customers = loadCustomers();

// Seçili tarih
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selectedDateObj = new DateTime($selectedDate);

// O günün siparişlerini filtrele
$dayOrders = [];
foreach ($orders as $order) {
    $orderDate = substr($order['date'], 0, 10);
    if ($orderDate === $selectedDate) {
        $dayOrders[] = $order;
    }
}

// Müşteri bilgilerini ekle
foreach ($dayOrders as &$order) {
    if ($order['customer_id'] === 'manual' && isset($order['customer_name'])) {
        $order['customer'] = ['name' => $order['customer_name']];
    } elseif (isset($customers[$order['customer_id']])) {
        $order['customer'] = $customers[$order['customer_id']];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Defter - Ajanda</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/edefter.css">
</head>
<body>
    <!-- Fixed Navbar -->
    <nav class="skeuomorphic-navbar">
        <div class="navbar-left">
            <div class="navbar-logo" id="menuToggle">
                <img src="svg/start-here.svg" alt="menu" width="24" height="24">
            </div>
            <a href="dashboard.php" class="navbar-title" style="text-decoration: none; color: inherit;">
                E-Defter
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
                    <a href="edefter.php" class="menu-link active">
                        <img src="svg/document-new.svg" alt="edefter" width="16" height="16" style="vertical-align: middle; margin-right: 8px;">
                        E-Defter
                    </a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="skeuomorphic-main">
        <!-- Tarih Navigasyonu ve Sipariş Ekle -->
        <div class="edefter-header">
            <div class="date-navigation">
                <button class="nav-btn" onclick="changeDate(-1)" title="Önceki Gün">
                    <img src="svg/go-previous.svg" alt="prev" width="20" height="20">
                </button>
                
                <div class="selected-date">
                    <span class="day-name"><?php 
                    $days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
                    echo $days[$selectedDateObj->format('w')];
                    ?></span>
                    <span class="date-main"><?php 
                    $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
                    echo $selectedDateObj->format('d') . ' ' . $months[intval($selectedDateObj->format('m'))] . ' ' . $selectedDateObj->format('Y');
                    ?></span>
                    <button class="today-btn" onclick="goToToday()">Bugün</button>
                </div>
                
                <button class="nav-btn" onclick="changeDate(1)" title="Sonraki Gün">
                    <img src="svg/go-next.svg" alt="next" width="20" height="20">
                </button>
            </div>
            
            <button class="btn-add-order-compact" id="addOrderBtn" title="Sipariş Ekle">
                <img src="svg/list-add.svg" alt="add" width="20" height="20">
            </button>
        </div>

        <div class="content-box">

            <!-- Sipariş Listesi -->
            <div class="orders-section">
                <h3 class="section-title">
                    <img src="svg/view-fullscreen.svg" alt="orders" width="18" height="18" style="vertical-align: middle; margin-right: 8px;">
                    Siparişler (<?php echo count($dayOrders); ?>)
                </h3>

                <?php if (empty($dayOrders)): ?>
                    <div class="empty-orders">
                        <img src="svg/document-new.svg" alt="empty" width="64" height="64">
                        <p>Bu gün için sipariş kaydı bulunmamaktadır</p>
                    </div>
                <?php else: ?>
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Müşteri</th>
                                    <th>Saat</th>
                                    <th>Adres</th>
                                    <th>Tür</th>
                                    <th class="text-right">Tutar</th>
                                    <th>Durum</th>
                                    <th width="180">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dayOrders as $index => $order): ?>
                                <tr>
                                    <td class="order-product-cell">
                                        <?php echo !empty($order['product']) ? htmlspecialchars($order['product']) : '-'; ?>
                                    </td>
                                    <td class="order-customer-cell">
                                        <?php echo htmlspecialchars($order['customer']['name'] ?? 'Müşteri bulunamadı'); ?>
                                    </td>
                                    <td class="order-time-cell">
                                        <?php echo date('H:i', strtotime($order['time'])); ?>
                                    </td>
                                    <td class="order-address-cell">
                                        <?php echo htmlspecialchars($order['address']); ?>
                                    </td>
                                    <td class="order-type-cell">
                                        <span class="type-badge <?php echo $order['is_wedding'] ? 'type-wedding' : 'type-normal'; ?>">
                                            <?php if ($order['is_wedding']): ?>
                                                <img src="svg/folder.svg" alt="wedding" width="14" height="14" style="vertical-align: middle; margin-right: 4px;">
                                                Düğün
                                            <?php else: ?>
                                                <img src="svg/text-x-generic.svg" alt="normal" width="14" height="14" style="vertical-align: middle; margin-right: 4px;">
                                                Normal
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="order-amount-cell text-right">
                                        <?php echo number_format($order['amount'], 2); ?> ₺
                                    </td>
                                    <td class="order-status-cell">
                                        <?php if (!empty($order['delivered'])): ?>
                                            <span class="status-badge delivered">✓ Teslim</span>
                                        <?php else: ?>
                                            <span class="status-badge pending">Beklemede</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="order-action-cell">
                                        <div class="action-buttons">
                                            <?php if (empty($order['delivered'])): ?>
                                            <button class="icon-btn-small mark-delivered-btn" data-id="<?php echo $order['id']; ?>" title="Teslim Et">
                                                <img src="svg/dialog-apply.svg" alt="delivered" width="14" height="14">
                                            </button>
                                            <?php endif; ?>
                                            <button class="icon-btn-small delete-order-btn" data-id="<?php echo $order['id']; ?>" title="Sil">
                                                <img src="svg/edit-delete.svg" alt="delete" width="14" height="14">
                                            </button>
                                            <?php if (!empty($order['customer_id']) && $order['customer_id'] !== 'manual' && $order['customer_id'] !== '0' && isset($customers[$order['customer_id']]) && empty($order['delivered'])): ?>
                                            <button class="icon-btn-small order-action-btn" data-id="<?php echo $order['id']; ?>" data-customer-id="<?php echo $order['customer_id']; ?>" title="Veresiye'ye Ekle">
                                                <img src="svg/list-add.svg" alt="process" width="14" height="14">
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Order Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Yeni Sipariş Ekle</h2>
                <button class="modal-close">&times;</button>
            </div>
            <form id="orderForm" action="order_action.php" method="POST">
                <input type="hidden" name="action" value="add_order">
                
                <div class="form-group">
                    <label for="customerSearch">Müşteri Ara</label>
                    <input type="text" id="customerSearch" name="customer_search" placeholder="Müşteri adı ile ara..." autocomplete="off">
                    <div class="customer-dropdown" id="customerDropdown" style="display: none;">
                        <div class="customer-option customer-option-manual" data-value="manual">
                            <img src="svg/text-x-generic.svg" alt="manual" width="16" height="16">
                            <span>Manuel İsim</span>
                        </div>
                        <?php foreach ($customers as $id => $customer): ?>
                        <div class="customer-option" data-value="<?php echo $id; ?>">
                            <img src="svg/user-info.svg" alt="customer" width="16" height="16">
                            <span><?php echo htmlspecialchars($customer['name']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="customerSelect" name="customer_id" required>
                    <input type="text" id="customerNameManual" name="customer_name" placeholder="Müşteri adı" style="display: none; margin-top: 8px;">
                </div>

                <div class="form-group">
                    <label>Sipariş Türü *</label>
                    <div class="order-type-toggle">
                        <button type="button" class="toggle-btn normal-btn active" data-type="normal">
                            <img src="svg/text-x-generic.svg" alt="normal" width="16" height="16">
                            Normal
                        </button>
                        <button type="button" class="toggle-btn wedding-btn" data-type="wedding">
                            <img src="svg/folder.svg" alt="wedding" width="16" height="16">
                            Düğün
                        </button>
                        <input type="hidden" name="is_wedding" id="orderType" value="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="orderProduct">Ürün</label>
                    <div class="product-input-row">
                        <input type="text" id="orderProduct" name="product" placeholder="Ürün adı">
                        <div class="quick-product-buttons">
                            <button type="button" class="quick-btn" onclick="setProduct('E.Ç')">E.Ç</button>
                            <button type="button" class="quick-btn" onclick="setProduct('G.D')">G.D</button>
                            <button type="button" class="quick-btn" onclick="setProduct('SAKSI')">SAKSI</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="orderAddress">Adres *</label>
                    <input type="text" id="orderAddress" name="address" required placeholder="Teslimat adresi">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="orderDate">Tarih *</label>
                        <input type="date" id="orderDate" name="order_date" required>
                    </div>

                    <div class="form-group">
                        <label for="orderTime">Saat *</label>
                        <div class="time-input-row">
                            <input type="time" id="orderTime" name="time" required>
                            <div class="quick-time-buttons">
                                <button type="button" class="quick-btn" onclick="setTime('13:00')">13:00</button>
                                <button type="button" class="quick-btn" onclick="setTime('19:00')">19:00</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="orderAmount">Tutar *</label>
                    <input type="number" id="orderAmount" name="amount" step="0.01" min="0.01" required placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="orderNotes">Notlar</label>
                    <textarea id="orderNotes" name="notes" rows="3" placeholder="Ek notlar..."></textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="addToDebt" name="add_to_debt" value="1" style="width: 18px; height: 18px; cursor: pointer;">
                        <img src="svg/list-add.svg" alt="debt" width="16" height="16" style="vertical-align: middle;">
                        <span>Bu tutarı müşterinin hesabına ekle</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-cancel modal-close">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script src="js/edefter.js"></script>
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

