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
$customersFile = 'data/customers.json';
$debtsFile = 'data/debts.json';

// Müşteri verilerini yükle
function loadCustomers() {
    global $customersFile;
    if (file_exists($customersFile)) {
        $data = file_get_contents($customersFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// Müşteri verilerini kaydet
function saveCustomers($customers) {
    global $customersFile;
    return file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

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

$action = $_POST['action'] ?? '';
$customers = loadCustomers();

if ($action === 'add') {
    // Yeni müşteri ekle
    $customer = [
        'name' => $_POST['name'],
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'address' => $_POST['address'] ?? '',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'has_debt' => isset($_POST['create_debt']) && $_POST['create_debt'] === 'on'
    ];
    
    $customers[] = $customer;
    saveCustomers($customers);
    
    // E-Veresiye oluştur
    if ($customer['has_debt']) {
        $debts = loadDebts();
        $customerId = count($customers) - 1;
        $debts[$customerId] = [
            'customer_id' => $customerId,
            'customer_name' => $customer['name'],
            'balance' => 0,
            'transactions' => [],
            'created_at' => date('Y-m-d H:i:s')
        ];
        saveDebts($debts);
    }
    
    header('Location: customers.php?success=' . urlencode('Müşteri başarıyla eklendi'));
    exit;
    
} elseif ($action === 'edit') {
    // Müşteri düzenle
    $id = intval($_POST['id']);
    
    if (isset($customers[$id])) {
        $customers[$id]['name'] = $_POST['name'];
        $customers[$id]['phone'] = $_POST['phone'] ?? '';
        $customers[$id]['email'] = $_POST['email'] ?? '';
        $customers[$id]['address'] = $_POST['address'] ?? '';
        $customers[$id]['updated_at'] = date('Y-m-d H:i:s');
        
        // E-Veresiye kontrolü
        $hadDebt = !empty($customers[$id]['has_debt']);
        $willHaveDebt = isset($_POST['create_debt']) && $_POST['create_debt'] === 'on';
        
        if (!$hadDebt && $willHaveDebt) {
            // Yeni e-veresiye oluştur
            $customers[$id]['has_debt'] = true;
            $debts = loadDebts();
            $debts[$id] = [
                'customer_id' => $id,
                'customer_name' => $customers[$id]['name'],
                'balance' => 0,
                'transactions' => [],
                'created_at' => date('Y-m-d H:i:s')
            ];
            saveDebts($debts);
        } elseif ($hadDebt && !$willHaveDebt) {
            // E-Veresiye silme (opsiyonel - burada sadece işareti kaldırıyoruz)
            $customers[$id]['has_debt'] = false;
        } else {
            $customers[$id]['has_debt'] = $hadDebt;
        }
        
        saveCustomers($customers);
        
        header('Location: customers.php?success=' . urlencode('Müşteri başarıyla güncellendi'));
        exit;
    } else {
        header('Location: customers.php?error=' . urlencode('Müşteri bulunamadı'));
        exit;
    }
    
} elseif ($action === 'delete') {
    // Müşteri sil
    $id = intval($_POST['id']);
    
    if (isset($customers[$id])) {
        // E-Veresiye varsa onu da sil
        $debts = loadDebts();
        if (isset($debts[$id])) {
            unset($debts[$id]);
            saveDebts($debts);
        }
        
        // Müşteriyi sil
        unset($customers[$id]);
        $customers = array_values($customers); // İndeksleri yeniden düzenle
        
        saveCustomers($customers);
        
        header('Location: customers.php?success=' . urlencode('Müşteri başarıyla silindi'));
        exit;
    } else {
        header('Location: customers.php?error=' . urlencode('Müşteri bulunamadı'));
        exit;
    }
}

header('Location: customers.php');



