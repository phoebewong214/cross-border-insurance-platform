<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    if (!isset($_GET['quotation_id'])) {
        throw new Exception('Missing quotation ID');
    }

    $quotationId = $_GET['quotation_id'];

    // 数据库连接
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 检查是否存在重复保单
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM policy p
        WHERE p.Quotation_ID = ?
        AND p.Policy_Status IN ('Waiting', 'Inforce')
    ");

    $stmt->execute([$quotationId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $has_duplicate = $result['count'] > 0;

    echo json_encode([
        'success' => true,
        'has_duplicate' => $has_duplicate
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
