<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php?error=' . urlencode('Lütfen giriş yapın'));
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı';

// Müşteri ve e-veresiye verilerini yükle
function loadCustomers() {
    $file = 'data/customers.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function loadDebts() {
    $file = 'data/debts.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

$customers = loadCustomers();
$debts = loadDebts();
$customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : null;

// Belirli müşteri için filtrele
$filteredDebts = [];
if ($customerId !== null) {
    if (isset($debts[$customerId])) {
        $filteredDebts[$customerId] = $debts[$customerId];
    }
} else {
    $filteredDebts = $debts;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Veresiye - Borç/Alacak Yönetimi</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/debts.css">
</head>
<body>
    <!-- Fixed Navbar -->
    <nav class="skeuomorphic-navbar">
        <div class="navbar-left">
            <div class="navbar-logo" id="menuToggle">
                <img src="svg/start-here.svg" alt="menu" width="24" height="24">
            </div>
            <a href="dashboard.php" class="navbar-title" style="text-decoration: none; color: inherit;">
                E-Veresiye
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
                    <a href="debts.php" class="menu-link active">
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
                        <img src="svg/view-sort-ascending.svg" alt="debts" width="24" height="24" style="vertical-align: middle; margin-right: 10px;">
                        E-Veresiye Takibi
                    </h1>
                </div>
                <div class="header-right">
                    <button class="btn-icon" id="toggleSummaryBtn" title="Özet">
                        <img src="svg/view-sort-ascending.svg" alt="summary" width="18" height="18">
                    </button>
                    <button class="btn-icon" onclick="window.print()" title="Yazdır">
                        <img src="svg/document-print.svg" alt="print" width="18" height="18">
                    </button>
                    <button class="add-transaction-btn" id="addTransactionBtn">
                        <img src="svg/list-add.svg" alt="add" width="18" height="18">
                        Yeni İşlem
                    </button>
                </div>
            </div>

            <?php if (empty($filteredDebts)): ?>
                <div class="empty-state">
                    <img src="svg/view-sort-ascending.svg" alt="empty" width="64" height="64">
                    <h3>Henüz e-veresiye hesabı bulunmamaktadır</h3>
                    <p>Müşterilerinize e-veresiye hesabı ekleyerek başlayabilirsiniz</p>
                    <a href="customers.php" class="btn btn-primary" style="margin-top: 15px; display: inline-flex;">
                        Müşterilere Git
                    </a>
                </div>
            <?php else: ?>
                <!-- Toplam Bakiyeler -->
                <div class="debts-summary-table collapsed" id="summaryTable">
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Özet</th>
                                <th class="text-right">Tutar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Toplam Alacak</td>
                                <td class="text-positive text-right">
                                    <?php
                                    $totalPositive = 0;
                                    foreach ($filteredDebts as $debt) {
                                        if ($debt['balance'] > 0) {
                                            $totalPositive += $debt['balance'];
                                        }
                                    }
                                    echo '+ ' . number_format($totalPositive, 2);
                                    ?> ₺
                                </td>
                            </tr>
                            <tr>
                                <td>Toplam Borç</td>
                                <td class="text-negative text-right">
                                    <?php
                                    $totalNegative = 0;
                                    foreach ($filteredDebts as $debt) {
                                        if ($debt['balance'] < 0) {
                                            $totalNegative += abs($debt['balance']);
                                        }
                                    }
                                    echo '- ' . number_format($totalNegative, 2);
                                    ?> ₺
                                </td>
                            </tr>
                            <tr class="total-row">
                                <td><strong>Net Bakiye</strong></td>
                                <td class="text-right">
                                    <strong class="<?php 
                                    $netBalance = 0;
                                    foreach ($filteredDebts as $debt) {
                                        $netBalance += $debt['balance'];
                                    }
                                    echo $netBalance >= 0 ? 'text-positive' : 'text-negative';
                                    ?>">
                                        <?php echo $netBalance >= 0 ? '+' : ''; ?>
                                        <?php echo number_format($netBalance, 2); ?> ₺
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Müşteri Hesapları Listesi -->
                <div class="debts-table-container">
                    <table class="debts-table">
                        <thead>
                            <tr>
                                <th>Müşteri Adı</th>
                                <th>Telefon</th>
                                <th>Bakiye</th>
                                <th>İşlem Sayısı</th>
                                <th>Son İşlem</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredDebts as $id => $debt): ?>
                                <?php
                                $customer = null;
                                if (isset($customers[$id])) {
                                    $customer = $customers[$id];
                                }
                                ?>
                            <tr>
                                <td class="customer-name"><?php echo htmlspecialchars($debt['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone'] ?? 'Belirtilmemiş'); ?></td>
                                <td>
                                    <span class="balance-badge <?php echo $debt['balance'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $debt['balance'] >= 0 ? '+' : ''; ?>
                                        <?php echo number_format($debt['balance'], 2); ?> ₺
                                    </span>
                                </td>
                                <td>
                                    <img src="svg/system-file-manager.svg" alt="transactions" width="14" height="14" style="vertical-align: middle; margin-right: 4px;">
                                    <?php echo count($debt['transactions'] ?? []); ?>
                                </td>
                                <td class="last-activity">
                                    <?php 
                                    if (!empty($debt['transactions'])) {
                                        $lastTrans = end($debt['transactions']);
                                        echo date('d.m.Y H:i', strtotime($lastTrans['date']));
                                    } else {
                                        echo 'Henüz işlem yok';
                                    }
                                    ?>
                                </td>
                                <td class="action-buttons">
                                    <button class="action-btn view-btn" onclick="window.location.href='debt_detail.php?id=<?php echo $id; ?>'" title="Detay">
                                        <img src="svg/system-search.svg" alt="detail" width="16" height="16">
                                    </button>
                                    <button class="action-btn edit-btn" onclick="window.location.href='debt_detail.php?id=<?php echo $id; ?>&add_transaction=1'" title="İşlem Ekle">
                                        <img src="svg/list-add.svg" alt="add" width="16" height="16">
                                    </button>
                                    <button class="action-btn delete-btn archive-btn" data-id="<?php echo $id; ?>" title="Arşivle">
                                        <img src="svg/view-restore.svg" alt="archive" width="16" height="16">
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Transaction Modal -->
    <div id="transactionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Yeni İşlem Ekle</h2>
                <button class="modal-close">&times;</button>
            </div>
            <form id="transactionForm" action="debt_action.php" method="POST">
                <input type="hidden" name="action" value="add_transaction">
                <input type="hidden" name="customer_id" id="transactionCustomerId">
                
                <div class="form-group">
                    <label for="transactionType">İşlem Türü *</label>
                    <select id="transactionType" name="type" required>
                        <option value="">Seçiniz</option>
                        <option value="alacak">Alacak (Müşteri bize borçlu)</option>
                        <option value="borc">Borç (Müşteriye alacaklı)</option>
                        <option value="odemeyap">Ödeme Yap</option>
                        <option value="odemeral">Ödeme Al</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transactionAmount">Tutar *</label>
                    <input type="number" id="transactionAmount" name="amount" step="0.01" min="0.01" required placeholder="0.00">
                </div>

                <div class="form-group">
                    <label for="transactionDescription">Açıklama</label>
                    <textarea id="transactionDescription" name="description" rows="3" placeholder="İşlem açıklaması..."></textarea>
                </div>

                <div class="form-group">
                    <label for="transactionDate">Tarih *</label>
                    <input type="datetime-local" id="transactionDate" name="date" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-cancel modal-close">İptal</button>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script src="js/debts.js"></script>
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

