<?php
session_start();
require_once 'db_config.php';

// 設置響應頭為JSON
header('Content-Type: application/json');

// 檢查是否有ID號碼參數
if (!isset($_GET['id_no'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No ID number provided'
    ]);
    exit;
}

$id_no = trim($_GET['id_no']);

// 驗證ID號碼格式
if (!preg_match('/^(\d{8}|[A-Z][A-Z0-9]{7,8})$/', $id_no)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID number format'
    ]);
    exit;
}

try {
    // 準備SQL查詢
    $sql = "SELECT COUNT(*) as count FROM party WHERE ID_No = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_no]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // 返回結果
    echo json_encode([
        'success' => true,
        'exists' => $result['count'] > 0,
        'message' => $result['count'] > 0 ? 'Party exists' : 'Party not found'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
