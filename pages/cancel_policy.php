<?php
require_once 'db_config.php';

// 設置響應頭
header('Content-Type: application/json');

// 獲取 POST 數據
$data = json_decode(file_get_contents('php://input'), true);
$policy_no = $data['policy_no'] ?? '';

if (empty($policy_no)) {
    echo json_encode([
        'success' => false,
        'message' => 'Policy number cannot be empty'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 更新保單狀態
    $stmt = $pdo->prepare("
        UPDATE policy 
        SET Policy_Status = 'Cancel' 
        WHERE Policy_No = ?
    ");
    $stmt->execute([$policy_no]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Policy not found');
    }

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Policy has been successfully canceled'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
