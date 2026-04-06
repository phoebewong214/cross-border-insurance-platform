<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// 获取已接受的订单
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_accepted_orders') {
    try {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not logged in');
        }
        $user_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("
            SELECT 
                pq.Pending_quotation_ID as order_id,
                pq.Product_Type,
                pq.Registration_No as vehicle_id,
                pq.Generate_Time as created_at,
                'approved' as status,
                c.Car_Make_and_Model,
                p.Name as owner_name
            FROM pending_quotation pq
            LEFT JOIN car c ON pq.Registration_No = c.Registration_No
            LEFT JOIN party p ON c.Owner1_ID_No = p.Party_No
            WHERE pq.User_ID = ?
            ORDER BY pq.Generate_Time DESC
        ");

        $stmt->execute([$user_id]);
        $accepted_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 处理每个订单的Product_Type数据
        foreach ($accepted_orders as &$order) {
            // 尝试解析Product_Type为JSON
            $productTypeData = null;
            $productTypeText = '';

            if (!empty($order['Product_Type'])) {
                $productTypeData = json_decode($order['Product_Type'], true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    // 成功解析为JSON，使用新的格式
                    if ($productTypeData['type'] === 'commercial') {
                        $productTypeText = "Commercial Insurance - " .
                            $productTypeData['products']['third_party']['name'] . " + " .
                            $productTypeData['products']['passenger']['name'];
                    } else if ($productTypeData['type'] === 'compulsory') {
                        $productTypeText = "Compulsory Insurance - " .
                            $productTypeData['products']['main']['name'];
                    } else if ($productTypeData['type'] === 'hk_private') {
                        $productTypeText = "Hong Kong Private Car Insurance - " .
                            $productTypeData['products']['main']['name'];
                    }
                } else {
                    // 不是JSON格式，可能是旧的Product_ID
                    try {
                        // 查询product表获取产品信息
                        $productStmt = $pdo->prepare("
                            SELECT Product_Name 
                            FROM product 
                            WHERE Product_ID = ?
                        ");
                        $productStmt->execute([$order['Product_Type']]);
                        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                        if ($product) {
                            $productTypeText = "Insurance Product: " . $product['Product_Name'];
                        } else {
                            $productTypeText = "Insurance Product: " . $order['Product_Type'];
                        }
                    } catch (Exception $e) {
                        $productTypeText = "Insurance Product: " . $order['Product_Type'];
                    }
                }
            }

            $order['product_type_text'] = $productTypeText;
            $order['product_type_data'] = $productTypeData;
        }

        echo json_encode([
            'success' => true,
            'accepted_orders' => $accepted_orders
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
