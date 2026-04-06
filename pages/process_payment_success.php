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
    // 获取POST数据
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['orderId']) || !isset($data['paymentMethod'])) {
        throw new Exception('Missing required parameters');
    }

    $quotationId = $data['orderId'];
    $paymentMethod = $data['paymentMethod'];

    // 数据库连接
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 更新交易记录的Balance_OS为0
    $stmt = $pdo->prepare("
        UPDATE transaction 
        SET Balance_OS = 0.00,
            Trading_Status = 'Written_off'
        WHERE Quotation_ID = ?
        AND Trading_Status = 'Unwritten_off'
        ORDER BY Bank_Time DESC
        LIMIT 1
    ");

    if (!$stmt->execute([$quotationId])) {
        throw new Exception('Failed to update transaction record');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully'
    ]);
} catch (Exception $e) {
    error_log('Error in process_payment_success.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
