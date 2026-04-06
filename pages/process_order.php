<?php
header('Content-Type: application/json');
require_once 'db_config.php';

// 获取订单列表
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_orders') {
    try {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User not logged in');
        }
        $user_id = $_SESSION['user_id'];

        // 记录开始查询
        error_log("开始查询订单 - 用户ID: " . $user_id);

        $stmt = $pdo->prepare("
            SELECT 
                pr.Pending_review_ID as id,
                pr.Product_Type,
                pr.Registration_No as vehicle_id,
                pr.Generate_Time as created_at,
                pr.Admin_Comment as admin_comments,
                CASE 
                    WHEN pr.Admin_Comment IS NULL THEN 'pending'
                    WHEN pr.Admin_Comment IS NOT NULL THEN 'rejected'
                    ELSE 'pending'
                END as status,
                c.Car_Make_and_Model,
                p.Name as owner_name
            FROM pending_review pr
            LEFT JOIN car c ON pr.Registration_No = c.Registration_No
            LEFT JOIN party p ON c.Owner1_ID_No = p.Party_No
            WHERE pr.User_ID = ?
            ORDER BY pr.Generate_Time DESC
        ");

        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 记录查询结果数量
        error_log("查询到订单数量: " . count($orders));

        // 处理每个订单的Product_Type数据
        foreach ($orders as &$order) {
            error_log("处理订单 ID: " . $order['id']);
            error_log("订单原始数据: " . print_r($order, true));

            // 尝试解析Product_Type为JSON
            $productTypeData = null;
            $productTypeText = '';

            if (!empty($order['Product_Type'])) {
                error_log("尝试解析Product_Type: " . $order['Product_Type']);
                $productTypeData = json_decode($order['Product_Type'], true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    // 成功解析为JSON，使用新的格式
                    error_log("JSON解析成功: " . print_r($productTypeData, true));
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
                    error_log("使用JSON格式生成显示文本: " . $productTypeText);
                } else {
                    // 不是JSON格式，可能是旧的Product_ID
                    error_log("不是JSON格式，尝试作为Product_ID处理: " . $order['Product_Type']);
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
                            error_log("找到产品信息: " . $productTypeText);
                        } else {
                            $productTypeText = "Insurance Product: " . $order['Product_Type'];
                            error_log("未找到产品信息，使用原始Product_ID");
                        }
                    } catch (Exception $e) {
                        error_log("查询产品信息时出错: " . $e->getMessage());
                        $productTypeText = "Insurance Product: " . $order['Product_Type'];
                    }
                }
            } else {
                error_log("Product_Type为空");
            }

            $order['product_type_text'] = $productTypeText;
            $order['product_type_data'] = $productTypeData;
            error_log("订单处理完成: " . print_r($order, true));
        }

        $response = [
            'success' => true,
            'orders' => $orders
        ];
        error_log("准备返回数据: " . print_r($response, true));
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("发生错误: " . $e->getMessage());
        error_log("错误堆栈: " . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 获取拒绝原因详情
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_rejection_details') {
    try {
        if (!isset($_GET['id'])) {
            throw new Exception('Order ID is required');
        }

        $stmt = $pdo->prepare("
            SELECT Admin_Comment
            FROM pending_review
            WHERE Pending_review_ID = ?
        ");
        $stmt->execute([$_GET['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception('Order not found');
        }

        echo json_encode([
            'success' => true,
            'reason' => $order['Admin_Comment']
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 处理删除被拒绝的订单
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_rejected_order') {
    try {
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            throw new Exception('Order ID is required');
        }

        $orderId = $_POST['id'];

        // 开始事务
        $pdo->beginTransaction();

        // 删除 pending_review 表中的记录
        $stmt = $pdo->prepare("DELETE FROM pending_review WHERE Pending_review_ID = ?");
        $stmt->execute([$orderId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception('Order not found or already deleted');
        }

        // 提交事务
        $pdo->commit();

        // 返回成功响应
        echo json_encode([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
        exit;
    } catch (Exception $e) {
        // 回滚事务
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // 返回错误响应
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
