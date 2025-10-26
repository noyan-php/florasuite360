<?php
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: index.php?error=' . urlencode('Lütfen giriş yapın'));
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Kullanıcı';
$customerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verileri yükle
function loadDebts() {
    $file = 'data/debts.json';
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

$debts = loadDebts();
$customers = loadCustomers();

if (!isset($debts[$customerId])) {
    header('Location: debts.php?error=' . urlencode('Hesap bulunamadı'));
    exit;
}

$debt = $debts[$customerId];
$customer = isset($customers[$customerId]) ? $customers[$customerId] : null;
$transactions = $debt['transactions'] ?? [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesap Detayı - <?php echo htmlspecialchars($debt['customer_name']); ?></title>
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
                Hesap Detayı
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
                        <img src="svg/system-search.svg" alt="detail" width="24" height="24" style="vertical-align: middle; margin-right: 10px;">
                        <?php echo htmlspecialchars($debt['customer_name']); ?> - Hesap Detayı
                    </h1>
                </div>
                <div class="header-right">
                    <button class="btn-icon" id="toggleCustomerInfo" title="Müşteri Bilgileri">
                        <img src="svg/user-info.svg" alt="info" width="18" height="18">
                    </button>
                    <button class="btn-icon" id="toggleBalance" title="Bakiye">
                        <img src="svg/view-sort-ascending.svg" alt="balance" width="18" height="18">
                    </button>
                    <button class="btn-icon" id="addTransactionBtn" title="Yeni İşlem">
                        <img src="svg/list-add.svg" alt="add" width="18" height="18">
                    </button>
                    <button class="btn-icon" onclick="window.print()" title="Yazdır">
                        <img src="svg/document-print.svg" alt="print" width="18" height="18">
                    </button>
                </div>
            </div>

            <!-- Müşteri Bilgileri - Floating Panel -->
            <?php if ($customer): ?>
            <div class="floating-panel collapsed" id="customerInfoPanel">
                <div class="floating-panel-header">
                    <img src="svg/user-info.svg" alt="customer" width="18" height="18" style="vertical-align: middle; margin-right: 8px;">
                    Müşteri Bilgileri
                </div>
                <div class="floating-panel-body">
                    <div class="info-item">
                        <span class="info-label">
                            <img src="svg/user-info.svg" alt="name" width="14" height="14" style="vertical-align: middle; margin-right: 6px;">
                            Ad/Kurum:
                        </span>
                        <span class="info-value"><?php echo htmlspecialchars($customer['name']); ?></span>
                    </div>
                    <?php if (!empty($customer['phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">
                            <img src="svg/appointment-new.svg" alt="phone" width="14" height="14" style="vertical-align: middle; margin-right: 6px;">
                            Telefon:
                        </span>
                        <span class="info-value"><?php echo htmlspecialchars($customer['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($customer['email'])): ?>
                    <div class="info-item">
                        <span class="info-label">
                            <img src="svg/mail-read.svg" alt="email" width="14" height="14" style="vertical-align: middle; margin-right: 6px;">
                            E-posta:
                        </span>
                        <span class="info-value"><?php echo htmlspecialchars($customer['email']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($customer['address'])): ?>
                    <div class="info-item">
                        <span class="info-label">
                            <img src="svg/user-home.svg" alt="address" width="14" height="14" style="vertical-align: middle; margin-right: 6px;">
                            Adres:
                        </span>
                        <span class="info-value"><?php echo htmlspecialchars($customer['address']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Bakiye Özeti - Floating Panel -->
            <div class="floating-panel collapsed" id="balancePanel">
                <div class="floating-panel-header">
                    <img src="svg/view-sort-ascending.svg" alt="balance" width="18" height="18" style="vertical-align: middle; margin-right: 8px;">
                    Güncel Bakiye
                </div>
                <div class="floating-panel-body">
                    <div class="balance-display-simple <?php echo $debt['balance'] >= 0 ? 'positive' : 'negative'; ?>">
                        <div class="balance-amount-simple">
                            <?php echo $debt['balance'] >= 0 ? '+' : ''; ?>
                            <?php echo number_format($debt['balance'], 2); ?> ₺
                        </div>
                        <div class="balance-sublabel-simple">
                            <?php echo $debt['balance'] >= 0 ? 'Bize borçlu' : 'Biz alacaklıyız'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Modal -->
            <div id="transactionModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Yeni İşlem Ekle</h2>
                        <button class="modal-close">&times;</button>
                    </div>
                    <form id="transactionForm" action="debt_action.php" method="POST">
                        <input type="hidden" name="action" value="add_transaction">
                        <input type="hidden" name="customer_id" value="<?php echo $customerId; ?>">
                        
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

            <script>
                // Toggle panels
                const toggleCustomerInfo = document.getElementById('toggleCustomerInfo');
                const customerInfoPanel = document.getElementById('customerInfoPanel');
                const toggleBalance = document.getElementById('toggleBalance');
                const balancePanel = document.getElementById('balancePanel');
                
                if (toggleCustomerInfo && customerInfoPanel) {
                    toggleCustomerInfo.addEventListener('click', function() {
                        customerInfoPanel.classList.toggle('collapsed');
                    });
                }
                
                if (toggleBalance && balancePanel) {
                    toggleBalance.addEventListener('click', function() {
                        balancePanel.classList.toggle('collapsed');
                    });
                }

                // Transaction Modal
                const modal = document.getElementById('transactionModal');
                const addTransactionBtn = document.getElementById('addTransactionBtn');
                const closeBtns = document.querySelectorAll('.modal-close');
                
                // Open modal
                if (addTransactionBtn) {
                    addTransactionBtn.addEventListener('click', function() {
                        modal.classList.add('show');
                        document.body.style.overflow = 'hidden';
                        
                        // Set default date to now
                        const now = new Date();
                        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                        document.getElementById('transactionDate').value = now.toISOString().slice(0, 16);
                    });
                }
                
                // Close modal
                closeBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (modal) {
                            modal.classList.remove('show');
                            document.body.style.overflow = '';
                        }
                    });
                });
                
                // Close modal on outside click
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            modal.classList.remove('show');
                            document.body.style.overflow = '';
                        }
                    });
                }
            </script>

            <!-- İşlem Listesi -->
            <div class="transactions-box">
                <h3 class="box-title">
                    <img src="svg/system-file-manager.svg" alt="transactions" width="18" height="18" style="vertical-align: middle; margin-right: 8px;">
                    İşlem Geçmişi (<?php echo count($transactions); ?>)
                </h3>
                
                <?php if (empty($transactions)): ?>
                    <div class="empty-transactions">
                        <img src="svg/system-file-manager.svg" alt="empty" width="48" height="48" style="opacity: 0.3;">
                        <p>Henüz işlem kaydı yok</p>
                    </div>
                <?php else: ?>
                    <div class="transactions-table-container-compact">
                        <table class="transactions-table-compact">
                            <thead>
                                <tr>
                                    <th>Tarih</th>
                                    <th>Tür</th>
                                    <th>Açıklama</th>
                                    <th class="text-right">Tutar</th>
                                    <th width="50">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($transactions) as $index => $transaction): ?>
                                <tr>
                                    <td class="transaction-date-cell">
                                        <?php echo date('d.m.Y H:i', strtotime($transaction['date'])); ?>
                                    </td>
                                    <td class="transaction-type-cell">
                                        <span class="type-badge type-<?php echo $transaction['type']; ?>">
                                            <?php
                                            $typeLabels = [
                                                'alacak' => 'Alacak',
                                                'borc' => 'Borç',
                                                'odemeyap' => 'Ödeme Yap',
                                                'odemeral' => 'Ödeme Al'
                                            ];
                                            echo $typeLabels[$transaction['type']] ?? $transaction['type'];
                                            ?>
                                        </span>
                                    </td>
                                    <td class="transaction-description-cell">
                                        <?php echo !empty($transaction['description']) ? htmlspecialchars($transaction['description']) : '-'; ?>
                                    </td>
                                    <td class="transaction-amount-cell text-right <?php echo in_array($transaction['type'], ['alacak']) ? 'text-positive' : 'text-negative'; ?>">
                                        <?php echo in_array($transaction['type'], ['alacak']) ? '+' : '-'; ?>
                                        <?php echo number_format($transaction['amount'], 2); ?> ₺
                                    </td>
                                    <td class="transaction-action-cell">
                                        <button class="icon-btn-small delete-transaction" data-id="<?php echo $transaction['id']; ?>" title="Sil">
                                            <img src="svg/edit-delete.svg" alt="delete" width="14" height="14">
                                        </button>
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

