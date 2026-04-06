<?php
session_start();

// 檢查是否為管理員
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => '未授權訪問']);
    exit;
}

// 獲取請求的動作
$action = $_GET['action'] ?? '';

// 處理不同的動作
switch ($action) {
    case 'get_quotation_stats':
        try {
            // 連接數據庫
            $link = mysqli_connect('localhost', 'root', '', 'insurance_system');

            if (!$link) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }

            mysqli_query($link, 'SET NAMES utf8');

            // 獲取待處理報價數量（從pending_review表）
            $sql = "SELECT COUNT(*) as pending FROM pending_review";
            $result = mysqli_query($link, $sql);
            if (!$result) {
                throw new Exception("Error: " . mysqli_error($link));
            }
            $pending = mysqli_fetch_assoc($result)['pending'];

            // 獲取總報價數量（從quotation表）
            $sql = "SELECT COUNT(*) as total FROM quotation";
            $result = mysqli_query($link, $sql);
            if (!$result) {
                throw new Exception("Error: " . mysqli_error($link));
            }
            $total = mysqli_fetch_assoc($result)['total'];

            mysqli_close($link);

            echo json_encode([
                'success' => true,
                'pending' => $pending,
                'total' => $total
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => '數據庫錯誤：' . $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => '無效的動作'
        ]);
        break;
}
