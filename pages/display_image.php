<?php
require_once '../config/database.php';

// 启用错误显示
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 获取图片类型和ID
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

// 记录请求信息
error_log("Display Image Request - Type: $type, ID: $id");
error_log("GET parameters: " . print_r($_GET, true));

if (empty($type) || empty($id)) {
    error_log("Missing parameters - type: $type, id: $id");
    header("HTTP/1.0 400 Bad Request");
    die('Missing parameters');
}

try {
    // 记录数据库连接信息
    error_log("Attempting to connect to database with DSN: " . DB_DSN);
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connection successful");

    // 构建SQL查询
    $sql = "";
    $params = [$id];
    
    switch ($type) {
        case 'id_front':
            $sql = "SELECT ID_Image_file_front as image FROM party WHERE Party_No = ?";
            break;
        case 'dl_front':
            $sql = "SELECT DL_image_file_front as image FROM party WHERE Party_No = ?";
            break;
        case 'dl_back':
            $sql = "SELECT DL_image_file_back as image FROM party WHERE Party_No = ?";
            break;
        case 'ownership_cert':
            $sql = "SELECT Ownership_Registration_Certificate_Image_file as image FROM car WHERE Registration_No = ?";
            break;
        case 'vehicle_reg':
            $sql = "SELECT Vehicle_Registration_Card_Image_file as image FROM car WHERE Registration_No = ?";
            break;
        default:
            error_log("Invalid image type: $type");
            header("HTTP/1.0 400 Bad Request");
            die('Invalid image type');
    }

    error_log("SQL Query: $sql");
    error_log("Parameters: " . print_r($params, true));

    // 执行查询
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // 获取图片数据
    $imageData = $stmt->fetchColumn();
    
    if ($imageData !== false) {
        error_log("Raw image data type: " . gettype($imageData));
        error_log("Raw image data length: " . strlen($imageData));
        
        // 清除任何已有的输出
        while (ob_get_level()) {
            ob_end_clean();
        }

        // 检测文件类型
        $firstBytes = substr($imageData, 0, 4);
        $firstBytesHex = bin2hex($firstBytes);
        error_log("First bytes (hex): $firstBytesHex");

        // 根据文件头设置Content-Type
        if (strpos($firstBytesHex, 'ffd8') === 0) {
            $contentType = 'image/jpeg';
        } elseif (strpos($firstBytesHex, '89504e47') === 0) {
            $contentType = 'image/png';
        } elseif (strpos($imageData, '%PDF') === 0 || strpos($firstBytesHex, '25504446') === 0) {
            $contentType = 'application/pdf';
        } else {
            $contentType = 'application/octet-stream';
        }

        error_log("Content-Type set to: $contentType");

        // 设置响应头
        header("Content-Type: $contentType");
        header("Content-Length: " . strlen($imageData));
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        
        // 输出图片数据
        echo $imageData;
        error_log("Image data sent successfully");
        exit;
    } else {
        error_log("No image data found for type: $type, id: $id");
        header("HTTP/1.0 404 Not Found");
        die('No image data found');
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("HTTP/1.0 500 Internal Server Error");
    die('Database error occurred');
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("HTTP/1.0 500 Internal Server Error");
    die('An error occurred');
} 