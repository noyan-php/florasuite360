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
$ordersFile = 'data/orders.json';
$debtsFile = 'data/debts.json';
$customersFile = 'data/customers.json';

// Sipariş verilerini yükle
function loadOrders() {
    global $ordersFile;
    if (file_exists($ordersFile)) {
        $data = file_get_contents($ordersFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// Sipariş verilerini kaydet
function saveOrders($orders) {
    global $ordersFile;
    return file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// E-Veresiye verilerini yükle
function loadDebts() {
    global $debtsFile;
    if (file_exists($debtsFile)) {
        $data = file_get_contents($debtsFile);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// E-Veresiye verilerini kaydet
function saveDebts($debts) {
    global $debtsFile;
    return file_put_contents($debtsFile, json_encode($debts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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

// Müşterileri kaydet
function saveCustomers($customers) {
    global $customersFile;
    return file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_POST['action'] ?? '';
$orders = loadOrders();

if ($action === 'add_order') {
    // Yeni sipariş ekle
    // Tarih bilgisini al
    $orderDate = $_POST['order_date'] ?? $_POST['date'];
    
    // Manuel müşteri adı kontrolü
    $customerId = $_POST['customer_id'];
    $customerName = $_POST['customer_name'] ?? '';
    
    $order = [
        'id' => time() . '-' . rand(1000, 9999),
        'customer_id' => $customerId,
        'customer_name' => ($customerId === 'manual' && !empty($customerName)) ? $customerName : null,
        'is_wedding' => $_POST['is_wedding'] === '1',
        'product' => $_POST['product'] ?? '',
        'address' => $_POST['address'],
        'amount' => floatval($_POST['amount']),
        'date' => $orderDate . ' 00:00:00',
        'time' => $_POST['time'],
        'notes' => $_POST['notes'] ?? '',
        'created_at' => date('Y-m-d H:i:s'),
        'processed_to_debt' => false,
        'delivered' => false
    ];
    
    $orders[] = $order;
    saveOrders($orders);
    
    // Eğer "add_to_debt" seçilmişse ve müşteri manuel değilse, veresiye hesabına ekle
    $addToDebt = isset($_POST['add_to_debt']) && $_POST['add_to_debt'] === '1';
    
    if ($addToDebt && $customerId !== 'manual') {
        $customers = loadCustomers();
        $debts = loadDebts();
        
        $customerKey = (string)$customerId;
        
        // Eğer hesap yoksa oluştur
        if (!isset($debts[$customerKey])) {
            $debts[$customerKey] = [
                'customer_id' => intval($customerId),
                'customer_name' => isset($customers[$customerId]) ? $customers[$customerId]['name'] : 'Bilinmeyen',
                'balance' => 0,
                'transactions' => [],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Müşteriyi güncelle
            if (isset($customers[$customerId])) {
                $customers[$customerId]['has_debt'] = true;
                saveCustomers($customers);
            }
        }
        
        // Bakiye ve işlem ekle
        $debts[$customerKey]['balance'] += $order['amount'];
        
        $transaction = [
            'id' => time() . '-' . rand(1000, 9999),
            'type' => 'alacak',
            'amount' => $order['amount'],
            'description' => 'Sipariş: ' . $order['address'] . ($order['notes'] ? ' - ' . $order['notes'] : ''),
            'date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $debts[$customerKey]['transactions'][] = $transaction;
        $debts[$customerKey]['last_updated'] = date('Y-m-d H:i:s');
        saveDebts($debts);
        
        // Siparişi işaretle
        $order['processed_to_debt'] = true;
        $orders[count($orders) - 1] = $order;
        saveOrders($orders);
        
        header('Location: edefter.php?date=' . $orderDate . '&success=' . urlencode('Sipariş eklendi ve veresiye hesabına işlendi'));
    } else {
        header('Location: edefter.php?date=' . $orderDate . '&success=' . urlencode('Sipariş başarıyla eklendi'));
    }
    exit;
    
} elseif ($action === 'process_to_debt') {
    // E-Veresiye'ye işle
    $customerId = intval($_POST['customer_id']);
    $orderId = $_POST['order_id'];
    
    // Siparişi bul
    $order = null;
    foreach ($orders as $o) {
        if ($o['id'] === $orderId) {
            $order = $o;
            break;
        }
    }
    
    if (!$order) {
        header('Location: edefter.php?error=' . urlencode('Sipariş bulunamadı'));
        exit;
    }
    
    // Müşterinin e-veresiye hesabını kontrol et
    $customers = loadCustomers();
    $debts = loadDebts();
    
    // Key'i string'e çevir
    $customerKey = (string)$customerId;
    
    if (!isset($debts[$customerKey])) {
        // E-veresiye hesabı yok, oluştur
        $debts[$customerKey] = [
            'customer_id' => $customerId,
            'customer_name' => $customers[$customerId]['name'],
            'balance' => 0,
            'transactions' => [],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Müşteriyi güncelle
        $customers[$customerId]['has_debt'] = true;
        saveCustomers($customers);
    }
    
    // Sipariş tutarını e-veresiye hesabına ekle
    $debts[$customerKey]['balance'] += $order['amount'];
    
    // İşlem ekle
    $transaction = [
        'id' => time() . '-' . rand(1000, 9999),
        'type' => 'alacak',
        'amount' => $order['amount'],
        'description' => 'Sipariş: ' . $order['address'] . ($order['notes'] ? ' - ' . $order['notes'] : ''),
        'date' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $debts[$customerKey]['transactions'][] = $transaction;
    $debts[$customerKey]['last_updated'] = date('Y-m-d H:i:s');
    saveDebts($debts);
    
    // Siparişi işaretle
    foreach ($orders as &$o) {
        if ($o['id'] === $orderId) {
            $o['processed_to_debt'] = true;
            break;
        }
    }
    saveOrders($orders);
    
    header('Location: edefter.php?date=' . date('Y-m-d') . '&success=' . urlencode('Sipariş e-veresiye hesabına işlendi'));
    exit;
    
} elseif ($action === 'mark_delivered') {
    // Siparişi teslim edildi olarak işaretle
    $orderId = $_POST['order_id'];
    
    // Siparişin tarihini al
    $orderDate = date('Y-m-d');
    foreach ($orders as $order) {
        if ($order['id'] === $orderId) {
            $orderDate = substr($order['date'], 0, 10);
            break;
        }
    }
    
    foreach ($orders as &$order) {
        if ($order['id'] === $orderId) {
            $order['delivered'] = true;
            $order['delivered_at'] = date('Y-m-d H:i:s');
            break;
        }
    }
    
    saveOrders($orders);
    
    header('Location: edefter.php?date=' . $orderDate . '&success=' . urlencode('Sipariş teslim edildi olarak işaretlendi'));
    exit;
    
} elseif ($action === 'delete_order') {
    // Siparişi sil
    $orderId = $_POST['order_id'];
    
    // Siparişin tarihini al
    $orderDate = date('Y-m-d');
    foreach ($orders as $order) {
        if ($order['id'] === $orderId) {
            $orderDate = substr($order['date'], 0, 10);
            break;
        }
    }
    
    $orders = array_filter($orders, function($order) use ($orderId) {
        return $order['id'] !== $orderId;
    });
    
    saveOrders(array_values($orders));
    
    header('Location: edefter.php?date=' . $orderDate . '&success=' . urlencode('Sipariş silindi'));
    exit;
}

header('Location: edefter.php');

