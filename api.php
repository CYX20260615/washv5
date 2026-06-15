<?php
header('Content-Type: application/json; charset=utf-8');
$dir = __DIR__ . '/';
$configFile = $dir . 'config.json';
$ordersFile = $dir . 'orders.json';
$uploadDir = $dir . 'uploads/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

if (!file_exists($configFile)) {
    $defaultConfig = [
        'shoes' => [
            ['name' => '运动鞋（成人）', 'price' => 25, 'enabled' => true],
            ['name' => '皮鞋（成人）', 'price' => 35, 'enabled' => true],
            ['name' => '特殊材质（麂皮/翻毛皮）', 'price' => 40, 'enabled' => true],
            ['name' => '靴子/棉鞋', 'price' => 45, 'enabled' => true],
            ['name' => '凉鞋/拖鞋', 'price' => 20, 'enabled' => true],
            ['name' => '童鞋（运动）', 'price' => 22, 'enabled' => true],
            ['name' => '童鞋（皮鞋）', 'price' => 28, 'enabled' => true],
            ['name' => '帆布鞋', 'price' => 25, 'enabled' => true],
            ['name' => '板鞋', 'price' => 25, 'enabled' => true],
            ['name' => '高跟鞋', 'price' => 30, 'enabled' => true],
            ['name' => '休闲鞋', 'price' => 25, 'enabled' => true]
        ],
        'coupon' => ['enabled' => true, 'full_amount' => 100, 'reduce_amount' => 10],
        'custom_promo' => ''
    ];
    file_put_contents($configFile, json_encode($defaultConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
if (!file_exists($ordersFile)) {
    file_put_contents($ordersFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$action = $_GET['action'] ?? '';

if ($action === 'getConfig') {
    echo file_get_contents($configFile);
    exit;
}
if ($action === 'saveConfig') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data) {
        file_put_contents($configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
    } else echo json_encode(['success' => false]);
    exit;
}
if ($action === 'getOrders') {
    echo file_get_contents($ordersFile);
    exit;
}
if ($action === 'updateOrderStatus') {
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $input['orderId'] ?? '';
    $newStatus = $input['status'] ?? '';
    if ($orderId && in_array($newStatus, ['pending','processing','completed','after_sale'])) {
        $orders = json_decode(file_get_contents($ordersFile), true);
        foreach ($orders as &$order) {
            if ($order['id'] === $orderId) {
                $order['status'] = $newStatus;
                break;
            }
        }
        file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
    } else echo json_encode(['success' => false]);
    exit;
}
if ($action === 'uploadPhoto') {
    $orderId = $_POST['orderId'] ?? '';
    if (!$orderId) {
        echo json_encode(['success' => false, 'msg' => '缺少订单ID']);
        exit;
    }
    $files = $_FILES['photos'] ?? [];
    $uploaded = [];
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $uploaded[] = 'https://chengyuxia.com/washv4/uploads/' . $filename;
            }
        }
    }
    if (!empty($uploaded)) {
        $orders = json_decode(file_get_contents($ordersFile), true);
        foreach ($orders as &$order) {
            if ($order['id'] === $orderId) {
                if (!isset($order['photos'])) $order['photos'] = [];
                $order['photos'] = array_merge($order['photos'], $uploaded);
                break;
            }
        }
        file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true, 'photos' => $uploaded]);
    } else {
        echo json_encode(['success' => false, 'msg' => '没有成功上传照片']);
    }
    exit;
}
if ($action === 'addOrder') {
    $order = json_decode(file_get_contents('php://input'), true);
    if ($order) {
        $orders = json_decode(file_get_contents($ordersFile), true);
        $order['id'] = uniqid();
        $order['status'] = 'pending';
        $order['createdAt'] = date('Y-m-d H:i:s');
        array_unshift($orders, $order);
        file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(['success' => true]);
    } else echo json_encode(['success' => false]);
    exit;
}
if ($action === 'clearOrders') {
    file_put_contents($ordersFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['success' => true]);
    exit;
}
echo json_encode(['success' => false]);