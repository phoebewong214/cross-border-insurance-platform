<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists('../config/database.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration file not found'
    ]);
    exit;
}

require_once '../config/database.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

try {
    // 验证配置常量
    if (!defined('DB_DSN') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Database configuration constants are not defined');
    }

    // 数据库连接
    try {
        $db = new PDO(DB_DSN, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }

    // 获取POST数据
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['orderId']) || !isset($data['paymentMethod'])) {
        throw new Exception('Missing required parameters: orderId or paymentMethod');
    }

    $orderId = filter_var($data['orderId'], FILTER_SANITIZE_NUMBER_INT);
    $paymentMethod = sanitize_input($data['paymentMethod']);

    // 验证订单
    $stmt = $db->prepare("SELECT id, status, premium FROM quotations WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found');
    }

    if ($order['status'] !== 'signed') {
        throw new Exception('Order is not signed yet');
    }

    // 生成支付信息
    $paymentInfo = [
        'amount' => $order['premium'],
        'currency' => 'MOP',
        'method' => $paymentMethod,
        // 根据支付方式生成不同的支付信息
        'paymentData' => generatePaymentData($paymentMethod, $order)
    ];

    // 更新数据库
    $stmt = $db->prepare("UPDATE quotations SET 
        payment_method = ?,
        status = 'processing_payment',
        updated_at = NOW()
        WHERE id = ?");

    if (!$stmt->execute([$paymentMethod, $orderId])) {
        throw new Exception('Failed to update payment information');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Payment initiated successfully',
        'paymentInfo' => $paymentInfo
    ]);
} catch (Exception $e) {
    error_log("Process Payment Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generatePaymentData($method, $order)
{
    switch ($method) {
        case 'credit_card':
            return [
                'gateway' => 'stripe',
                'amount' => $order['premium'],
                'currency' => 'MOP',
                'redirect_url' => "payment_gateway.php?id={$order['id']}&method=credit_card"
            ];
        case 'alipay':
            return [
                'qr_code' => "generate_alipay_qr.php?id={$order['id']}",
                'amount' => $order['premium'],
                'currency' => 'MOP'
            ];
        case 'wechat':
            return [
                'qr_code' => "generate_wechat_qr.php?id={$order['id']}",
                'amount' => $order['premium'],
                'currency' => 'MOP'
            ];
        default:
            throw new Exception('Invalid payment method');
    }
}
