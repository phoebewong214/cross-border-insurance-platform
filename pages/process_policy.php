<?php
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
    error_log("处理请求 - 动作: " . $action);

    switch ($action) {
        case 'get_policies':
            // 从policy表获取所有保单
            $stmt = $pdo->prepare("SELECT * FROM policy ORDER BY created_at DESC");
            $stmt->execute();
            $policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'policies' => $policies
            ]);
            break;

        case 'get_policy':
            try {
                if (!isset($_GET['id'])) {
                    throw new Exception('Policy ID is required');
                }

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
                        pr.Product_Name,
                        c.Car_Make_and_Model,
                        c.Seats,
                        c.Date_of_Registration,
                        c.Chasis_No,
                        pa.Name as Customer_Name,
                        pa.ID_No as Customer_ID,
                        pa.Date_of_Birth,
                        pa.Type
                    FROM policy p
                    LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                    LEFT JOIN product pr ON q.Product1_ID = pr.Product_ID
                    LEFT JOIN car c ON q.Registration_No = c.Registration_No
                    LEFT JOIN party pa ON q.Party_No = pa.Party_No
                    WHERE p.Policy_No = ?
                ");

                $stmt->execute([$_GET['id']]);
                $policy = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$policy) {
                    throw new Exception('Policy not found');
                }

                error_log("Policy data retrieved: " . print_r($policy, true));

                echo json_encode([
                    'success' => true,
                    'policy' => $policy
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        case 'get_renewal_quotation':
            try {
                if (!isset($_GET['policy_id'])) {
                    error_log("续保请求错误: 未提供保单号");
                    throw new Exception('Policy ID is required');
                }

                $policy_id = $_GET['policy_id'];
                error_log("开始处理续保请求 - 保单号: " . $policy_id);

                // 检查保单是否已经续保
                $stmt = $pdo->prepare("
                    SELECT Policy_No, Renew_Policy_No, Policy_Status 
                    FROM policy 
                    WHERE Policy_No = ?
                ");
                $stmt->execute([$policy_id]);
                $policy = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("保单基本信息: " . print_r($policy, true));

                if (!$policy) {
                    error_log("未找到保单: " . $policy_id);
                    throw new Exception('Policy not found');
                }

                if ($policy['Renew_Policy_No']) {
                    error_log("保单已续保 - 新保单号: " . $policy['Renew_Policy_No']);
                    throw new Exception('This policy has already been renewed.');
                }

                // 检查是否有待处理的续保申请
                $stmt = $pdo->prepare("
                    SELECT pq.Pending_quotation_ID, pq.Registration_No, pq.Product_Type,
                           q.Quotation_ID, p.Policy_No
                    FROM pending_quotation pq
                    JOIN quotation q ON pq.Registration_No = q.Registration_No
                    JOIN policy p ON q.Quotation_ID = p.Quotation_ID
                    WHERE p.Policy_No = ?
                ");
                $stmt->execute([$policy_id]);
                $pending_renewal = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("待处理续保申请查询结果: " . print_r($pending_renewal, true));

                if ($pending_renewal) {
                    error_log("发现待处理续保申请 - 申请ID: " . $pending_renewal['Pending_quotation_ID']);
                    error_log("相关车辆信息: " . $pending_renewal['Registration_No']);
                    error_log("产品类型: " . $pending_renewal['Product_Type']);
                    throw new Exception('A renewal application for this policy already exists. Please check Order Management for the status of your existing application.');
                }

                // 获取原始保单信息
                $stmt = $pdo->prepare("
                    SELECT p.*, c.Registration_No, c.Car_Make_and_Model, 
                           pr.Name as owner_name, pr.Party_No
                    FROM policy p
                    JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                    JOIN car c ON q.Registration_No = c.Registration_No
                    JOIN party pr ON c.Owner1_ID_No = pr.Party_No
                    WHERE p.Policy_No = ?
                ");
                $stmt->execute([$policy_id]);
                $policy_data = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("原始保单详细信息: " . print_r($policy_data, true));

                if (!$policy_data) {
                    error_log("未找到保单详细信息");
                    throw new Exception('Policy details not found');
                }

                // 获取产品信息
                $stmt = $pdo->prepare("
                    SELECT Product_Code, Product_Name, Product_Type
                    FROM product
                    WHERE Product_ID = ?
                ");
                $stmt->execute([$policy_data['Product_ID']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("产品信息: " . print_r($product, true));

                if (!$product) {
                    error_log("未找到产品信息");
                    throw new Exception('Product information not found');
                }

                // 构建续保数据
                $renewal_data = [
                    'policy' => $policy_data,
                    'product' => $product,
                    'vehicle' => [
                        'registration_no' => $policy_data['Registration_No'],
                        'make_model' => $policy_data['Car_Make_and_Model']
                    ],
                    'customer' => [
                        'name' => $policy_data['owner_name'],
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

        default:
            error_log("无效的动作: " . $action);
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    error_log('Error in process_policy.php: ' . $e->getMessage());
    error_log('Error stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
