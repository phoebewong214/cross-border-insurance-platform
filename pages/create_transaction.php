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

    // 获取报价单信息
    $stmt = $pdo->prepare("
        SELECT q.*, p.User_ID 
        FROM quotation q
        LEFT JOIN party p ON q.Party_No = p.Party_No
        WHERE q.Quotation_ID = ?
    ");
    $stmt->execute([$quotationId]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quotation) {
        throw new Exception('Quotation not found');
    }

    // 生成交易ID
    $transactionId = 'T' . date('ymdHis') . rand(1000, 9999);

    // 根据支付方式生成卡号
    $cardNo = '';
    switch ($paymentMethod) {
        case 'alipay':
            $cardNo = 'ALI' . date('ymdHis') . rand(1000, 9999);
            break;
        case 'wechat':
            $cardNo = 'WX' . date('ymdHis') . rand(1000, 9999);
            break;
        case 'credit_card':
            $cardNo = 'CC' . date('ymdHis') . rand(1000, 9999);
            break;
        default:
            throw new Exception('Invalid payment method');
    }

    // 插入交易记录
    $stmt = $pdo->prepare("
        INSERT INTO transaction 
        (Transaction_ID, Card_No, Quotation_ID, Bank_Time, Balance_OS, Trading_Status, User_ID) 
        VALUES (?, ?, ?, NOW(), ?, 'Unwritten_off', ?)
    ");

    if (!$stmt->execute([
        $transactionId,
        $cardNo,
        $quotationId,
        $quotation['Total_Premium'],
        $quotation['User_ID']
    ])) {
        throw new Exception('Failed to create transaction record');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Transaction created successfully',
        'transaction_id' => $transactionId
    ]);
} catch (Exception $e) {
    error_log('Error in create_transaction.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
