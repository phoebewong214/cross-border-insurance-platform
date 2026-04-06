<?php
// 顯示所有錯誤
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 檢查用戶是否已登錄
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'redirect' => '../login.php'
    ]);
    exit;
}

// 數據庫連接
require_once('db_config.php');

// ===================== 測試環境配置 =====================
// 在生產環境中移除這段代碼！
$_SESSION['user_id'] = 'U25000007';  // 測試用戶ID
// =====================================================

// 記錄接收到的參數
error_log("Received parameters: " . print_r($_GET, true));

try {
    // 檢查是否是續保請求
    if (!isset($_GET['renewal']) || $_GET['renewal'] !== '1') {
        throw new Exception('Invalid renewal parameter');
    }

    // 檢查必要參數
    $requiredParams = [
        'original_policy',
        'party_no',
        'registration_no',
        'product_id'
    ];

    foreach ($requiredParams as $param) {
        if (!isset($_GET[$param]) || empty($_GET[$param])) {
            throw new Exception("Missing required parameter: {$param}");
        }
    }

    // 檢查是否已經存在相同的續保申請
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM pending_quotation 
        WHERE Party_No = ? 
        AND Registration_No = ? 
        AND Product_ID = ?
    ");

    $stmt->execute([
        $_GET['party_no'],
        $_GET['registration_no'],
        $_GET['product_id']
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        throw new Exception('A renewal application for this policy already exists. Please check Order Management for the status of your existing application.');
    }

    // 生成新的 Pending Quotation ID
    // 格式：O + DDMMYY + 4位序號
    // 將日期放在最前，然後是月份，最後是年份的後兩位
    $year = date('y');  // 25
    $month = date('m'); // 03
    $day = date('d');   // 20
    $today = $day . $month . $year;  // 結果為：200325

    // 查詢今天已存在的最大序號
    $stmt = $pdo->prepare("
        SELECT MAX(SUBSTRING(Pending_Quotation_ID, 8, 4)) as max_seq 
        FROM pending_quotation 
        WHERE Pending_Quotation_ID LIKE 'O" . $today . "%'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // 如果今天還沒有記錄，從 0001 開始
    // 如果有記錄，則取最大序號 + 1
    $sequence = $result['max_seq'] ? str_pad((intval($result['max_seq']) + 1), 4, '0', STR_PAD_LEFT) : '0001';

    // 組合成最終的 ID
    $pendingQuotationId = 'O' . $today . $sequence;  // 結果為：O2003250001

    // 開始事務處理
    $pdo->beginTransaction();

    try {
        // 插入到 pending_quotation 表
        $stmt = $pdo->prepare("
            INSERT INTO pending_quotation (
                Pending_Quotation_ID,
                Party_No,
                Registration_No,
                Product_ID,
                User_ID,
                Generate_Time
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $result = $stmt->execute([
            $pendingQuotationId,
            $_GET['party_no'],
            $_GET['registration_no'],
            $_GET['product_id'],
            $_SESSION['user_id']
        ]);

        if (!$result) {
            error_log('SQL Error: ' . print_r($stmt->errorInfo(), true));
            throw new Exception('Failed to insert into pending_quotation');
        }

        // 提交事務
        $pdo->commit();

        // 顯示成功頁面
?>
        <!DOCTYPE html>
        <html lang="zh">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>續保申請成功</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            <style>
                .success-container {
                    max-width: 600px;
                    margin: 100px auto;
                    text-align: center;
                    padding: 30px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    border-radius: 10px;
                }

                .success-icon {
                    color: #28a745;
                    font-size: 60px;
                    margin-bottom: 20px;
                }

                .countdown {
                    font-size: 24px;
                    color: #6c757d;
                    margin-top: 20px;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <div class="success-container bg-white">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2 class="mb-4">Renewal Application Submitted Successfully!</h2>
                    <p class="lead mb-3">Your renewal application has been submitted.</p>
                    <p class="mb-4">Please go to Order Management to check the progress of your renewal request.</p>
                    <div class="countdown" id="successCountdown">5</div>
                    <p class="mt-3 text-muted">Redirecting to homepage in seconds...</p>

                    <div class="mt-4">
                        <a href="order_management.php" class="btn btn-primary me-2">
                            <i class="fas fa-tasks me-2"></i>Go to Order Management
                        </a>
                        <a href="../index.php" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>Back to Homepage
                        </a>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                let successCountdown = 5;
                const successElement = document.getElementById('successCountdown');

                const countdownTimer = setInterval(() => {
                    successCountdown--;
                    successElement.textContent = successCountdown;
                    if (successCountdown <= 0) {
                        clearInterval(countdownTimer);
                        window.location.href = '../index.php';
                    }
                }, 1000);
            </script>
        </body>

        </html>
    <?php
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Database error: ' . $e->getMessage());
        throw $e;
    }
} catch (Exception $e) {
    error_log('Error in create_renew_quotation.php: ' . $e->getMessage());
    http_response_code(500);

    // 顯示友好的錯誤頁面
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Renewal Application Notice</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            .notice-container {
                max-width: 600px;
                margin: 100px auto;
                text-align: center;
                padding: 30px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
            }

            .notice-icon {
                color: #ffc107;
                font-size: 60px;
                margin-bottom: 20px;
            }

            .countdown {
                font-size: 24px;
                color: #6c757d;
                margin-top: 20px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="notice-container bg-white">
                <i class="fas fa-exclamation-triangle notice-icon"></i>
                <h2 class="mb-4">Renewal Application Notice</h2>
                <p class="lead mb-3">A renewal application for this policy already exists.</p>
                <p class="mb-4">Please check Order Management for the status of your existing application.</p>
                <div class="countdown" id="errorCountdown">5</div>
                <p class="mt-3 text-muted">Redirecting to Order Management in seconds...</p>

                <div class="mt-4">
                    <a href="order_management.php" class="btn btn-primary me-2">
                        <i class="fas fa-tasks me-2"></i>Go to Order Management
                    </a>
                    <a href="../index.php" class="btn btn-secondary">
                        <i class="fas fa-home me-2"></i>Back to Homepage
                    </a>
                </div>
            </div>
        </div>

        <script>
            let errorCountdown = 5;
            const errorElement = document.getElementById('errorCountdown');

            const countdown = setInterval(() => {
                errorCountdown--;
                errorElement.textContent = errorCountdown;
                if (errorCountdown <= 0) {
                    clearInterval(countdown);
                    window.location.href = 'order_management.php';
                }
            }, 1000);
        </script>
    </body>

    </html>
<?php
    exit;
}
?>