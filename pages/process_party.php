<?php
// 設置錯誤處理
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    exit;
});

session_start(); // 添加session启动

// 設置響應頭為JSON
header('Content-Type: application/json');

// 啟用錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 0); // 關閉錯誤顯示，改為返回JSON

// 錯誤處理函數
function handleError($message)
{
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// 檢查用戶是否已登錄
if (!isset($_SESSION['user_id'])) {
    handleError('User not logged in');
}

// 檢查數據庫配置文件
if (!file_exists('../config/database.php')) {
    handleError('Database configuration file not found');
}

require_once '../config/database.php';

try {
    // 數據庫連接
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 處理獲取parties數量的請求
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_party_count') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM party");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'count' => intval($result['count'])
        ]);
        exit;
    }

    // 處理POST請求（添加新party）
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 開啟事務
        $pdo->beginTransaction();

        try {
            // 生成自動Party_No
            $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(Party_No, 2) AS UNSIGNED)) as max_no FROM party");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_number = ($result['max_no'] ?? 0) + 1;
            $party_no = 'P' . str_pad($next_number, 5, '0', STR_PAD_LEFT);

            // 處理日期
            $date_of_birth = null;
            $date_of_b_class = null;
            $date_of_c_class = null;

            if (isset($_POST['type']) && $_POST['type'] === 'Individual') {
                if (!empty($_POST['date_of_birth'])) {
                    $date_of_birth = $_POST['date_of_birth'];
                }
                if (!empty($_POST['date_of_b_class'])) {
                    $date_of_b_class = $_POST['date_of_b_class'];
                }
                if (!empty($_POST['date_of_c_class'])) {
                    $date_of_c_class = $_POST['date_of_c_class'];
                }
            }

            // 處理上傳的文件
            $id_image_front = null;
            $dl_image_front = null;
            $dl_image_back = null;

            if (isset($_FILES['id_image_front']) && $_FILES['id_image_front']['error'] == 0) {
                $id_image_front = file_get_contents($_FILES['id_image_front']['tmp_name']);
            }

            if (isset($_POST['type']) && $_POST['type'] === 'Individual') {
                if (isset($_FILES['dl_image_front']) && $_FILES['dl_image_front']['error'] == 0) {
                    $dl_image_front = file_get_contents($_FILES['dl_image_front']['tmp_name']);
                }
                if (isset($_FILES['dl_image_back']) && $_FILES['dl_image_back']['error'] == 0) {
                    $dl_image_back = file_get_contents($_FILES['dl_image_back']['tmp_name']);
                }
            }

            // 準備SQL語句
            $sql = "INSERT INTO party (
                Party_No, Type, Name, ID_No, Date_of_Birth, DL_No, 
                Date_of_B_class, Date_of_C_class, ID_Image_file_front, 
                DL_image_file_front, DL_image_file_back, 
                Total_Claim_Amount, Total_Contributed_Premium, User_ID
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, 
                ?, ?, 
                ?, ?, ?
            )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $party_no,
                $_POST['type'],
                $_POST['name'],
                $_POST['id_no'],
                $date_of_birth,
                $_POST['dl_no'],
                $date_of_b_class,
                $date_of_c_class,
                $id_image_front,
                $dl_image_front,
                $dl_image_back,
                $_POST['total_claim_amount'],
                $_POST['total_contributed_premium'],
                $_SESSION['user_id']
            ]);

            // 提交事務
            $pdo->commit();

            // 返回成功響應
            echo json_encode([
                'success' => true,
                'partyNo' => $party_no,
                'message' => 'Party information saved successfully'
            ]);
        } catch (Exception $e) {
            // 回滾事務
            $pdo->rollBack();
            handleError($e->getMessage());
        }
    }
} catch (PDOException $e) {
    handleError('Database connection failed: ' . $e->getMessage());
} catch (Exception $e) {
    handleError($e->getMessage());
}
