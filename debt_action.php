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

// JSON dosyası yolu
$debtsFile = 'data/debts.json';
$customersFile = 'data/customers.json';
$archivesFile = 'data/debt_archives.json';

// E-veresiye verilerini yükle
function loadDebts() {
    global $debtsFile;
    if (file_exists($debtsFile)) {
        $data = file_get_contents($debtsFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// E-veresiye verilerini kaydet
function saveDebts($debts) {
    global $debtsFile;
    return file_put_contents($debtsFile, json_encode($debts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Arşiv verilerini yükle
function loadArchives() {
    global $archivesFile;
    if (file_exists($archivesFile)) {
        $data = file_get_contents($archivesFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// Arşiv verilerini kaydet
function saveArchives($archives) {
    global $archivesFile;
    return file_put_contents($archivesFile, json_encode($archives, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Müşterileri yükle
function loadCustomers() {
    global $customersFile;
    if (file_exists($customersFile)) {
        $data = file_get_contents($customersFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

$action = $_POST['action'] ?? '';
$debts = loadDebts();

if ($action === 'add_transaction') {
    $customerId = intval($_POST['customer_id']);
    $type = $_POST['type'];
    $amount = floatval($_POST['amount']);
    $description = $_POST['description'] ?? '';
    $date = $_POST['date'];
    
    if (!isset($debts[$customerId])) {
        header('Location: debts.php?error=' . urlencode('Müşteri hesabı bulunamadı'));
        exit;
    }
    
    // İşlem oluştur
    $transaction = [
        'id' => time() . '-' . rand(1000, 9999),
        'type' => $type,
        'amount' => $amount,
        'description' => $description,
        'date' => $date,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Bakiyeyi güncelle
    switch ($type) {
        case 'alacak':
            // Müşteri bize borçlu (artış)
            $debts[$customerId]['balance'] += $amount;
            break;
        case 'borc':
            // Biz müşteriye borçluyuz (azalış)
            $debts[$customerId]['balance'] -= $amount;
            break;
        case 'odemeyap':
            // Borcu ödüyoruz (azalış)
            $debts[$customerId]['balance'] -= $amount;
            break;
        case 'odemeral':
            // Alacağımızı tahsil ediyoruz (azalış)
            $debts[$customerId]['balance'] -= $amount;
            break;
    }
    
    // İşlemi ekle
    if (!isset($debts[$customerId]['transactions'])) {
        $debts[$customerId]['transactions'] = [];
    }
    $debts[$customerId]['transactions'][] = $transaction;
    $debts[$customerId]['last_updated'] = date('Y-m-d H:i:s');
    
    saveDebts($debts);
    
    header('Location: debts.php?success=' . urlencode('İşlem başarıyla eklendi'));
    exit;
    
} elseif ($action === 'delete_transaction') {
    $customerId = intval($_POST['customer_id']);
    $transactionId = $_POST['transaction_id'];
    
    if (isset($debts[$customerId]['transactions'])) {
        // İşlemi bul ve bakiye hesabını geri al
        foreach ($debts[$customerId]['transactions'] as $index => $transaction) {
            if ($transaction['id'] === $transactionId) {
                // Bakiyeyi geri al
                switch ($transaction['type']) {
                    case 'alacak':
                        $debts[$customerId]['balance'] -= $transaction['amount'];
                        break;
                    case 'borc':
                        $debts[$customerId]['balance'] += $transaction['amount'];
                        break;
                    case 'odemeyap':
                        $debts[$customerId]['balance'] += $transaction['amount'];
                        break;
                    case 'odemeral':
                        $debts[$customerId]['balance'] += $transaction['amount'];
                        break;
                }
                
                // İşlemi sil
                unset($debts[$customerId]['transactions'][$index]);
                $debts[$customerId]['transactions'] = array_values($debts[$customerId]['transactions']);
                $debts[$customerId]['last_updated'] = date('Y-m-d H:i:s');
                
                saveDebts($debts);
                header('Location: debt_detail.php?id=' . $customerId . '&success=' . urlencode('İşlem silindi'));
                exit;
            }
        }
    }
    
    header('Location: debt_detail.php?id=' . $customerId . '&error=' . urlencode('İşlem bulunamadı'));
    exit;
    
} elseif ($action === 'archive') {
    $customerId = intval($_POST['customer_id']);
    
    if (isset($debts[$customerId])) {
        // Arşive taşı
        $archives = loadArchives();
        $archives[$customerId] = $debts[$customerId];
        $archives[$customerId]['archived_at'] = date('Y-m-d H:i:s');
        saveArchives($archives);
        
        // Ana listeden sil
        unset($debts[$customerId]);
        saveDebts($debts);
        
        // Müşteride e-veresiye işaretini kaldır
        $customers = loadCustomers();
        if (isset($customers[$customerId])) {
            $customers[$customerId]['has_debt'] = false;
            file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        header('Location: debts.php?success=' . urlencode('Hesap arşivlendi'));
        exit;
    }
    
    header('Location: debts.php?error=' . urlencode('Hesap bulunamadı'));
    exit;
}

header('Location: debts.php');



