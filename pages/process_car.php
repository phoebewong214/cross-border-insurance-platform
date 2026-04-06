<?php
session_start(); // 添加session启动

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'redirect' => '../login.php'
    ]);
    exit;
}

// 设置超时时间和内存限制
ini_set('max_execution_time', 300); // 300秒
ini_set('memory_limit', '256M');    // 256MB
ini_set('post_max_size', '64M');    // 64MB
ini_set('upload_max_filesize', '64M'); // 64MB

// 设置响应头，支持多种格式
$accept_header = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

// 默认为JSON格式
$response_format = 'json';

// 根据Accept头确定响应格式
if (strpos($accept_header, 'text/html') !== false) {
    $response_format = 'html';
} elseif (strpos($accept_header, 'application/xml') !== false) {
    $response_format = 'xml';
}

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 数据库连接配置
$host = 'localhost';
$dbname = 'insurance_system';
$username = 'root';
$password = '';

try {
    // 设置PDO连接选项
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_LOCAL_INFILE => true,
        // 增加超时设置
        PDO::ATTR_TIMEOUT => 300,
    ];

    // 尝试连接数据库
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, $options);

    // 开启事务
    $pdo->beginTransaction();

    // 验证所有者ID是否存在于party_file表中
    function validateOwnerID($pdo, $id_no)
    {
        if (empty($id_no)) return true; // 如果ID为空（可选字段），返回true

        // 检查是否存在users表
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE 'users'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                // 如果存在users表，使用它来验证ID
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE ID_No = ? OR user_id = ?");
                $stmt->execute([$id_no, $id_no]);
                return $stmt->fetchColumn() > 0;
            }
        } catch (Exception $e) {
            // 如果出错，记录错误但继续执行
            error_log("Error checking users table: " . $e->getMessage());
        }

        // 如果没有合适的表或出错，暂时返回true以允许操作继续
        return true;
    }

    // 验证所有者ID
    $owner1_id = $_POST['owner1IDNo'];
    $owner2_id = !empty($_POST['owner2IDNo']) ? $_POST['owner2IDNo'] : null;
    $owner3_id = !empty($_POST['owner3IDNo']) ? $_POST['owner3IDNo'] : null;

    if (!validateOwnerID($pdo, $owner1_id)) {
        throw new Exception('Owner 1 ID Number does not match any registered party.');
    }
    if ($owner2_id && !validateOwnerID($pdo, $owner2_id)) {
        throw new Exception('Owner 2 ID Number does not match any registered party.');
    }
    if ($owner3_id && !validateOwnerID($pdo, $owner3_id)) {
        throw new Exception('Owner 3 ID Number does not match any registered party.');
    }

    // 处理日期
    $date_of_registration = $_POST['date_of_registration'];

    // 检查文件大小
    $max_file_size = 16 * 1024 * 1024; // 16MB
    $file_error = false;
    $file_error_message = '';

    if (isset($_FILES['ownershipRegistrationCertificate']) && $_FILES['ownershipRegistrationCertificate']['size'] > $max_file_size) {
        $file_error = true;
        $file_error_message = 'Ownership Registration Certificate file is too large (max 16MB)';
    }

    if (isset($_FILES['vehicleRegistrationCard']) && $_FILES['vehicleRegistrationCard']['size'] > $max_file_size) {
        $file_error = true;
        $file_error_message = 'Vehicle Registration Card file is too large (max 16MB)';
    }

    if ($file_error) {
        throw new Exception($file_error_message);
    }

    // 处理上传的文件
    $ownership_certificate = null;
    $registration_card = null;

    if (isset($_FILES['ownershipRegistrationCertificate']) && $_FILES['ownershipRegistrationCertificate']['error'] == 0) {
        $ownership_certificate = file_get_contents($_FILES['ownershipRegistrationCertificate']['tmp_name']);
    }

    if (isset($_FILES['vehicleRegistrationCard']) && $_FILES['vehicleRegistrationCard']['error'] == 0) {
        $registration_card = file_get_contents($_FILES['vehicleRegistrationCard']['tmp_name']);
    }

    // 首先根据ID_No查询对应的Party_No
    $stmt = $pdo->prepare("SELECT Party_No FROM party WHERE ID_No = ?");
    $stmt->execute([$_POST['owner1IDNo']]);  // 使用表单提交的ID_No
    $owner1_party = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$owner1_party) {
        throw new Exception('Owner 1 (ID: ' . $_POST['owner1IDNo'] . ') not found in party table. Please register the party first.');
    }

    $owner1_party_no = $owner1_party['Party_No'];

    // 如果提供了owner2的ID_No，查询其Party_No
    $owner2_party_no = null;
    if (!empty($_POST['owner2IDNo'])) {
        $stmt->execute([$_POST['owner2IDNo']]);
        $owner2_party = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($owner2_party) {
            $owner2_party_no = $owner2_party['Party_No'];
        }
    }

    // 如果提供了owner3的ID_No，查询其Party_No
    $owner3_party_no = null;
    if (!empty($_POST['owner3IDNo'])) {
        $stmt->execute([$_POST['owner3IDNo']]);
        $owner3_party = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($owner3_party) {
            $owner3_party_no = $owner3_party['Party_No'];
        }
    }

    // 插入car记录，使用查询到的Party_No
    $sql = "INSERT INTO car (
        Registration_No, Car_Make_and_Model, Seats, Date_of_Registration,
        Chasis_No, Ownership_Registration_Certificate_Image_file, Vehicle_Registration_Card_Image_file,
        Owner1_ID_No, Owner2_ID_No, Owner3_ID_No, User_ID
    ) VALUES (
        ?, ?, ?, ?, 
        ?, ?, ?, 
        ?, ?, ?, ?
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['registrationNo'],
        $_POST['carMakeAndModel'],
        $_POST['seats'],
        $date_of_registration,
        $_POST['chasisNo'],
        $ownership_certificate,
        $registration_card,
        $owner1_party_no,  // 使用查询到的Party_No
        $owner2_party_no,  // 使用查询到的Party_No（如果有）
        $owner3_party_no,  // 使用查询到的Party_No（如果有）
        $_SESSION['user_id']  // 使用session中的user_id
    ]);

    // 提交事务
    $pdo->commit();

    // 根据请求的格式返回响应
    switch ($response_format) {
        case 'html':
            header('Content-Type: text/html');
            echo '<!DOCTYPE html>
                <html>
                <head>
                    <title>Success</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
                        .success { color: green; font-size: 24px; }
                    </style>
                </head>
                <body>
                    <h1 class="success">Car information saved successfully!</h1>
                    <p>Registration Number: ' . htmlspecialchars($_POST['registrationNo']) . '</p>
                    <a href="manage_car.php">Register Another Car</a>
                </body>
                </html>';
            break;

        case 'xml':
            header('Content-Type: application/xml');
            echo '<?xml version="1.0" encoding="UTF-8"?>
                <response>
                    <success>true</success>
                    <message>Car information saved successfully</message>
                    <registrationNo>' . htmlspecialchars($_POST['registrationNo']) . '</registrationNo>
                </response>';
            break;

        case 'json':
        default:
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Car information saved successfully',
                'registrationNo' => $_POST['registrationNo']
            ]);
            break;
    }
} catch (PDOException $e) {
    // 回滚事务
    if (isset($pdo)) {
        $pdo->rollBack();
    }

    // 根据请求的格式返回错误响应
    switch ($response_format) {
        case 'html':
            header('Content-Type: text/html');
            echo '<!DOCTYPE html>
                <html>
                <head>
                    <title>Error</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
                        .error { color: red; font-size: 24px; }
                    </style>
                </head>
                <body>
                    <h1 class="error">Error!</h1>
                    <p>' . htmlspecialchars($e->getMessage()) . '</p>
                    <a href="manage_car.php">Try Again</a>
                </body>
                </html>';
            break;

        case 'xml':
            header('Content-Type: application/xml');
            echo '<?xml version="1.0" encoding="UTF-8"?>
                <response>
                    <success>false</success>
                    <message>' . htmlspecialchars($e->getMessage()) . '</message>
                </response>';
            break;

        case 'json':
        default:
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'error_details' => [
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
            break;
    }
} catch (Exception $e) {
    // 处理其他异常
    switch ($response_format) {
        case 'html':
            header('Content-Type: text/html');
            echo '<!DOCTYPE html>
                <html>
                <head>
                    <title>Error</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
                        .error { color: red; font-size: 24px; }
                    </style>
                </head>
                <body>
                    <h1 class="error">Error!</h1>
                    <p>' . htmlspecialchars($e->getMessage()) . '</p>
                    <a href="manage_car.php">Try Again</a>
                </body>
                </html>';
            break;

        case 'xml':
            header('Content-Type: application/xml');
            echo '<?xml version="1.0" encoding="UTF-8"?>
                <response>
                    <success>false</success>
                    <message>' . htmlspecialchars($e->getMessage()) . '</message>
                </response>';
            break;

        case 'json':
        default:
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'General error: ' . $e->getMessage()
            ]);
            break;
    }
}
