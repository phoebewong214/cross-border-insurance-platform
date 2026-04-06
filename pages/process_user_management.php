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
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_users':
        getUsers();
        break;
    case 'update_discount':
        updateDiscount();
        break;
    case 'get_discounts':
        getDiscounts();
        break;
    case 'get_history':
        getDiscountHistory();
        break;
    case 'get_latest_settings':
        getLatestSettings();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getUsers() {
    global $pdo;
    
    try {
        $sql = "SELECT u.User_id, u.User_name, u.discount, COUNT(t.Transaction_ID) as transaction_count 
                FROM user u 
                LEFT JOIN transaction t ON u.User_id = t.User_ID AND t.Trading_Status = 'Written_off'
                GROUP BY u.User_id, u.User_name, u.discount 
                ORDER BY u.User_id ASC";  // Sort by user ID
                
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'users' => $users]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching users: ' . $e->getMessage()]);
    }
}

function updateDiscount() {
    global $pdo;
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $highDiscount = $data['high_discount'] ?? 0;
        $mediumDiscount = $data['medium_discount'] ?? 0;
        $highUserIds = $data['high_user_ids'] ?? [];
        $mediumUserIds = $data['medium_user_ids'] ?? [];
        $highThreshold = $data['high_threshold'] ?? 7;
        $mediumHighThreshold = $data['medium_high_threshold'] ?? 7;
        $mediumLowThreshold = $data['medium_low_threshold'] ?? 3;
        
        if (!is_numeric($highDiscount) || $highDiscount < 0 || $highDiscount > 100 ||
            !is_numeric($mediumDiscount) || $mediumDiscount < 0 || $mediumDiscount > 100) {
            echo json_encode(['success' => false, 'message' => 'Invalid discount data']);
            return;
        }

        // Reset all user discounts to 0 first (default discount)
        $resetSql = "UPDATE user SET discount = 0";
        $resetStmt = $pdo->prepare($resetSql);
        $resetStmt->execute();
        
        // Update high transaction users' discounts if any exist
        if (!empty($highUserIds)) {
            $placeholders = str_repeat('?,', count($highUserIds) - 1) . '?';
            $highSql = "UPDATE user SET discount = ? WHERE User_id IN ($placeholders)";
            $highStmt = $pdo->prepare($highSql);
            $highStmt->execute(array_merge([$highDiscount], $highUserIds));
        }
        
        // Update medium transaction users' discounts
        if (!empty($mediumUserIds)) {
            $placeholders = str_repeat('?,', count($mediumUserIds) - 1) . '?';
            $mediumSql = "UPDATE user SET discount = ? WHERE User_id IN ($placeholders)";
            $mediumStmt = $pdo->prepare($mediumSql);
            $mediumStmt->execute(array_merge([$mediumDiscount], $mediumUserIds));
        }
        
        // Save settings to discount settings table
        $settingsSql = "INSERT INTO discount_settings (
            high_threshold,
            medium_high_threshold,
            medium_low_threshold,
            high_discount,
            medium_discount
        ) VALUES (?, ?, ?, ?, ?)";
        
        $settingsStmt = $pdo->prepare($settingsSql);
        $settingsStmt->execute([
            $highThreshold,
            $mediumHighThreshold,
            $mediumLowThreshold,
            $highDiscount,
            $mediumDiscount
        ]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error updating discount: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error updating discount: ' . $e->getMessage()
        ]);
    }
}

function getDiscounts() {
    global $pdo;
    
    try {
        // Get high transaction users' discount
        $highSql = "SELECT u.discount 
                    FROM user u 
                    JOIN transaction t ON u.User_id = t.User_ID 
                    WHERE t.Trading_Status = 'Written_off'
                    GROUP BY u.User_id 
                    HAVING COUNT(t.Transaction_ID) > 7 
                    LIMIT 1";
        $highStmt = $pdo->query($highSql);
        $highDiscount = $highStmt->fetch(PDO::FETCH_ASSOC)['discount'] ?? 15;
        
        // Get medium transaction users' discount
        $mediumSql = "SELECT u.discount 
                      FROM user u 
                      JOIN transaction t ON u.User_id = t.User_ID 
                      WHERE t.Trading_Status = 'Written_off'
                      GROUP BY u.User_id 
                      HAVING COUNT(t.Transaction_ID) BETWEEN 3 AND 7 
                      LIMIT 1";
        $mediumStmt = $pdo->query($mediumSql);
        $mediumDiscount = $mediumStmt->fetch(PDO::FETCH_ASSOC)['discount'] ?? 10;
        
        echo json_encode([
            'success' => true, 
            'high_discount' => $highDiscount,
            'medium_discount' => $mediumDiscount
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching discounts: ' . $e->getMessage()]);
    }
}

function getDiscountHistory() {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM discount_settings ORDER BY created_at DESC LIMIT 6";
        $stmt = $pdo->query($sql);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'history' => $history
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching history: ' . $e->getMessage()
        ]);
    }
}

function getLatestSettings() {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM discount_settings ORDER BY created_at DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($settings) {
            echo json_encode([
                'success' => true,
                'settings' => $settings
            ]);
        } else {
            // 如果没有记录，返回默认值
            echo json_encode([
                'success' => true,
                'settings' => [
                    'high_threshold' => 7,
                    'medium_high_threshold' => 7,
                    'medium_low_threshold' => 3,
                    'high_discount' => 15,
                    'medium_discount' => 10
                ]
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching settings: ' . $e->getMessage()
        ]);
    }
} 