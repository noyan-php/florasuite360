<?php
header('Content-Type: application/json');

// data klasörünü oluştur
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

function loadSubscriptions() {
    $file = 'data/subscriptions.json';
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function saveSubscriptions($subscriptions) {
    $file = 'data/subscriptions.json';
    return file_put_contents($file, json_encode($subscriptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data) {
        $subscriptions = loadSubscriptions();
        
        // ID oluştur
        $id = time() . '-' . rand(1000, 9999);
        
        $subscriptions[] = [
            'id' => $id,
            'subscription' => $data,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        saveSubscriptions($subscriptions);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}


