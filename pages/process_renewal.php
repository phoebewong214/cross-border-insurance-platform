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

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists('../config/database.php')) {
    error_log("数据库配置文件未找到");
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

    $action = $_GET['action'] ?? '';
    error_log("处理续保请求 - 动作: " . $action);

    switch ($action) {
        case 'prepare_renewal':
            try {
                if (!isset($_GET['policy_id'])) {
                    error_log("续保请求错误: 未提供保单号");
                    throw new Exception('Policy ID is required');
                }

                $policy_id = $_GET['policy_id'];
                error_log("开始处理续保请求 - 保单号: " . $policy_id);

                // 获取原始保单信息
                $stmt = $pdo->prepare("
                    SELECT 
                        p.*,
                        q.Total_Premium,
                        q.Total_Coverage,
                        q.Final_Excess,
                        q.NCD,
                        q.Party_No,
                        q.Registration_No,
                        q.Product1_ID,
                        q.Product2_ID,
                        q.Product3_ID,
                        c.Car_Make_and_Model,
                        c.Seats,
                        c.Date_of_Registration,
                        c.Chasis_No,
                        pa.Name as Customer_Name,
                        pa.ID_No as Customer_ID
                    FROM policy p
                    JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                    JOIN car c ON q.Registration_No = c.Registration_No
                    JOIN party pa ON q.Party_No = pa.Party_No
                    WHERE p.Policy_No = ?
                ");
                $stmt->execute([$policy_id]);
                $policy_data = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("原始保单信息: " . print_r($policy_data, true));

                if (!$policy_data) {
                    error_log("未找到保单: " . $policy_id);
                    throw new Exception('Policy not found');
                }

                // 检查是否已续保
                if ($policy_data['Renew_Policy_No']) {
                    error_log("保单已续保 - 新保单号: " . $policy_data['Renew_Policy_No']);
                    throw new Exception('This policy has already been renewed.');
                }

                // 检查是否有待处理的续保申请
                $stmt = $pdo->prepare("
                    SELECT pq.Pending_quotation_ID, pq.Registration_No, pq.Product_Type
                    FROM pending_quotation pq
                    JOIN quotation q ON pq.Registration_No = q.Registration_No
                    JOIN policy p ON q.Quotation_ID = p.Quotation_ID
                    WHERE p.Policy_No = ?
                ");
                $stmt->execute([$policy_id]);
                $pending_renewal = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($pending_renewal) {
                    error_log("发现待处理续保申请 - 申请ID: " . $pending_renewal['Pending_quotation_ID']);
                    throw new Exception('A renewal application for this policy already exists.');
                }

                // 获取所有产品信息
                $products = [];
                $product_ids = array_filter([
                    $policy_data['Product1_ID'],
                    $policy_data['Product2_ID'],
                    $policy_data['Product3_ID']
                ]);

                if (!empty($product_ids)) {
                    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
                    $stmt = $pdo->prepare("
                        SELECT Product_ID, Product_Name, Product_Code
                        FROM product
                        WHERE Product_ID IN ($placeholders)
                    ");
                    $stmt->execute($product_ids);
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                error_log("获取到的产品信息: " . print_r($products, true));

                // 构建产品类型JSON
                $product_type_json = [
                    'type' => $products[0]['Product_Code'] ?? 'Unknown', // 使用第一个产品的代码作为类型
                    'products' => []
                ];

                // 添加所有产品信息
                foreach ($products as $product) {
                    $product_type_json['products'][] = [
                        'id' => $product['Product_ID'],
                        'name' => $product['Product_Name'],
                        'code' => $product['Product_Code']
                    ];
                }

                // 构建续保数据
                $renewal_data = [
                    'policy' => [
                        'policy_no' => $policy_data['Policy_No'],
                        'total_premium' => $policy_data['Total_Premium'],
                        'total_coverage' => $policy_data['Total_Coverage'],
                        'final_excess' => $policy_data['Final_Excess'],
                        'ncd' => $policy_data['NCD']
                    ],
                    'product' => $product_type_json,
                    'vehicle' => [
                        'registration_no' => $policy_data['Registration_No'],
                        'make_model' => $policy_data['Car_Make_and_Model'],
                        'seats' => $policy_data['Seats'],
                        'registration_date' => $policy_data['Date_of_Registration'],
                        'chasis_no' => $policy_data['Chasis_No']
                    ],
                    'customer' => [
                        'name' => $policy_data['Customer_Name'],
                        'id_no' => $policy_data['Customer_ID'],
                        'party_no' => $policy_data['Party_No']
                    ]
                ];

                error_log("构建的续保数据: " . print_r($renewal_data, true));

                echo json_encode([
                    'success' => true,
                    'renewal_data' => $renewal_data
                ]);
            } catch (Exception $e) {
                error_log("续保处理错误: " . $e->getMessage());
                error_log("错误堆栈: " . $e->getTraceAsString());
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        case 'create_renewal':
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                if (!$input) {
                    throw new Exception('Invalid input data');
                }

                error_log("接收到的续保数据: " . print_r($input, true));

                // 开始事务
                $pdo->beginTransaction();

                // 从原保单获取Party_No
                $stmt = $pdo->prepare("
                    SELECT q.Party_No
                    FROM policy p
                    JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                    WHERE p.Policy_No = ?
                ");
                $stmt->execute([$input['policy']['policy_no']]);
                $party_data = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$party_data) {
                    throw new Exception('无法找到原保单的客户信息');
                }

                error_log("获取到的Party_No: " . $party_data['Party_No']);

                // 生成續保訂單ID
                $date = date('Ymd'); // 獲取當前日期，格式為yyyymmdd
                $date_prefix = substr($date, 6, 2) . substr($date, 4, 2) . substr($date, 2, 2); // 轉換為ddmmyy格式

                // 獲取當天的最大序列號
                $max_id_sql = "SELECT MAX(CAST(SUBSTRING(Pending_Quotation_ID, 9) AS UNSIGNED)) as max_id 
                              FROM pending_quotation 
                              WHERE Pending_Quotation_ID LIKE 'R{$date_prefix}%'";
                $max_id_result = $pdo->query($max_id_sql)->fetch(PDO::FETCH_ASSOC);
                $next_sequence = ($max_id_result['max_id'] ?? 0) + 1;

                // 生成新的訂單ID
                $pending_quotation_id = 'R' . $date_prefix . str_pad($next_sequence, 4, '0', STR_PAD_LEFT);
                error_log("生成的續保訂單ID: " . $pending_quotation_id);

                // 插入待处理续保申请
                $stmt = $pdo->prepare("
                    INSERT INTO pending_quotation (
                        Pending_Quotation_ID,
                        Party_No,
                        Registration_No,
                        Product_Type,
                        User_ID,
                        Generate_Time
                    ) VALUES (
                        :pending_quotation_id,
                        :party_no,
                        :registration_no,
                        :product_type,
                        :user_id,
                        NOW()
                    )
                ");
                $stmt->execute([
                    ':pending_quotation_id' => $pending_quotation_id,
                    ':party_no' => $party_data['Party_No'],
                    ':registration_no' => $input['vehicle']['registration_no'],
                    ':product_type' => json_encode($input['product']),
                    ':user_id' => $_SESSION['user_id']  // 使用session中的user_id
                ]);

                error_log("创建待处理续保申请 - ID: " . $pending_quotation_id);

                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Renewal application created successfully',
                    'pending_quotation_id' => $pending_quotation_id
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("创建续保申请错误: " . $e->getMessage());
                error_log("错误堆栈: " . $e->getTraceAsString());
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        default:
            error_log("无效的动作: " . $action);
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    error_log('Error in process_renewal.php: ' . $e->getMessage());
    error_log('Error stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
