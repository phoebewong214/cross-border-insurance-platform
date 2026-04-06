<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查数据库配置文件是否存在
if (!file_exists('../config/database.php')) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '找不到数据库配置文件'
    ]);
    exit;
}

require_once '../config/database.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

try {
    // 创建数据库连接
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 获取请求的操作
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_all_policies':
            // 获取所有保单，包括产品名称、保障金额和保险期限
            $stmt = $pdo->prepare("
                SELECT p.*, 
                       q.Total_Premium as premium, 
                       q.Registration_No as vehicle_id,
                       q.Total_Coverage as coverage,
                       q.Product1_ID as product_id,
                       pr.Product_Name as product_name,
                       pr.Product_Code as product_code,
                       c.Car_Make_and_Model as car_make_model, 
                       c.Seats as seats,
                       prop.Start_Date as insurance_start,
                       prop.End_Date as insurance_end,
                       CASE 
                           WHEN p.Renew_Policy_No IS NOT NULL THEN 1 
                           ELSE 0 
                       END as has_renewal
                FROM policy p
                LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                LEFT JOIN product pr ON q.Product1_ID = pr.Product_ID
                LEFT JOIN car c ON q.Registration_No = c.Registration_No
                LEFT JOIN proposal prop ON p.Proposal_ID = prop.Proposal_ID
                ORDER BY p.Policy_Issue_Date DESC
            ");
            $stmt->execute();
            $policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'policies' => $policies
            ]);
            break;

        case 'get_policy':
            // 获取单个保单详情，包括产品名称、保障金额和保险期限
            if (!isset($_GET['policy_no'])) {
                throw new Exception('需要保单号');
            }

            $stmt = $pdo->prepare("
                SELECT p.*, 
                       q.Total_Premium as premium, 
                       q.Registration_No as vehicle_id,
                       q.Total_Coverage as coverage,
                       q.Product1_ID as product_id,
                       q.NCD,
                       q.Final_Excess as Excess,
                       q.Party_No,
                       pr.Product_Name as product_name,
                       pr.Product_Code,
                       pr.Death_coverage,
                       pr.Medical_coverage,
                       pr.Material_coverage,
                       pr.Basic_premium,
                       c.Car_Make_and_Model as car_make_model, 
                       c.Seats as seats,
                       c.Date_of_Registration,
                       c.Chasis_No,
                       pt.Name as Customer_Name, 
                       pt.ID_No as Customer_ID,
                       pt.Type,
                       pt.Date_of_Birth,
                       prop.Start_Date as insurance_start, 
                       prop.End_Date as insurance_end,
                       DATEDIFF(prop.End_Date, CURDATE()) as days_until_expiry
                FROM policy p
                LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                LEFT JOIN product pr ON q.Product1_ID = pr.Product_ID
                LEFT JOIN proposal prop ON p.Proposal_ID = prop.Proposal_ID
                LEFT JOIN car c ON q.Registration_No = c.Registration_No
                LEFT JOIN party pt ON q.Party_No = pt.Party_No
                WHERE p.Policy_No = ?
            ");
            $stmt->execute([$_GET['policy_no']]);
            $policy = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$policy) {
                throw new Exception('找不到保单');
            }

            echo json_encode([
                'success' => true,
                'policy' => $policy
            ]);
            break;

        case 'get_policies_by_user':
            // 获取特定用户的保单，使用正确的表名和列名
            if (!isset($_GET['user_id'])) {
                throw new Exception('需要用户ID');
            }

            $stmt = $pdo->prepare("
                SELECT p.*, 
                       q.Total_Premium as premium, 
                       q.Registration_No as vehicle_id,
                       q.Total_Coverage as coverage,
                       q.Product1_ID as product_id,
                       pr.Product_Name as product_name,
                       pr.Product_Code as product_code,
                       c.Car_Make_and_Model as car_make_model, 
                       c.Seats as seats,
                       prop.Start_Date as insurance_start,
                       prop.End_Date as insurance_end,
                       CASE 
                           WHEN p.Renew_Policy_No IS NOT NULL THEN 1 
                           ELSE 0 
                       END as has_renewal
                FROM policy p
                LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                LEFT JOIN product pr ON q.Product1_ID = pr.Product_ID
                LEFT JOIN car c ON q.Registration_No = c.Registration_No
                LEFT JOIN proposal prop ON p.Proposal_ID = prop.Proposal_ID
                WHERE p.User_ID = ?
                ORDER BY p.Policy_Issue_Date DESC
            ");
            $stmt->execute([$_GET['user_id']]);
            $policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'policies' => $policies
            ]);
            break;

        case 'update_policy_status':
            // 更新保单状态
            if (!isset($_POST['policy_no']) || !isset($_POST['status'])) {
                throw new Exception('需要保单号和状态');
            }

            $validStatuses = ['Expired', 'Renewed', 'Inforce', 'Waiting', 'Cancel'];
            if (!in_array($_POST['status'], $validStatuses)) {
                throw new Exception('无效的保单状态');
            }

            $stmt = $pdo->prepare("
                UPDATE policy 
                SET Policy_Status = ? 
                WHERE Policy_No = ?
            ");
            $stmt->execute([$_POST['status'], $_POST['policy_no']]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('保单更新失败或保单不存在');
            }

            echo json_encode([
                'success' => true,
                'message' => '保单状态已更新'
            ]);
            break;

        case 'create_renewal':
            // 创建续保保单，使用正确的表名和列名
            if (!isset($_POST['original_policy_no'])) {
                throw new Exception('需要原保单号');
            }

            // 开始事务
            $pdo->beginTransaction();

            try {
                // 获取原保单信息
                $stmt = $pdo->prepare("
                    SELECT p.*, q.Total_Premium as premium, q.Registration_No as vehicle_id, 
                           q.Product1_ID as product_type, q.Party_No as party_no,
                           pr.Insurance_Start as insurance_start, pr.Insurance_End as insurance_end
                    FROM policy p
                    LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
                    LEFT JOIN proposal pr ON p.Proposal_ID = pr.Proposal_ID
                    WHERE p.Policy_No = ?
                ");
                $stmt->execute([$_POST['original_policy_no']]);
                $originalPolicy = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$originalPolicy) {
                    throw new Exception('找不到原保单');
                }

                // 生成新的保单号
                $newPolicyNo = 'POL' . date('YmdHis') . rand(100, 999);

                // 创建新的报价单
                $stmt = $pdo->prepare("
                    INSERT INTO quotation (
                        Quotation_ID, Product1_ID, Registration_No, 
                        Total_Coverage, Final_Excess, NCD, Total_Premium, 
                        Party_No, Created_Date, User_ID
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?
                    )
                ");

                $newQuotationId = 'QUO' . date('YmdHis') . rand(100, 999);
                $stmt->execute([
                    $newQuotationId,
                    $originalPolicy['product_type'],
                    $originalPolicy['vehicle_id'],
                    $originalPolicy['Total_Coverage'] ?? 0,
                    $originalPolicy['Final_Excess'] ?? 0,
                    $originalPolicy['NCD'] ?? 0,
                    $originalPolicy['premium'],
                    $originalPolicy['party_no'],
                    $originalPolicy['User_ID']
                ]);

                // 创建新的投保单
                $stmt = $pdo->prepare("
                    INSERT INTO proposal (
                        Proposal_ID, Quotation_ID, Insurance_Start, Insurance_End, Status
                    ) VALUES (
                        ?, ?, ?, ?, 'Approved'
                    )
                ");

                $newProposalId = 'PRO' . date('YmdHis') . rand(100, 999);
                $newStartDate = date('Y-m-d', strtotime($originalPolicy['insurance_end'] . ' +1 day'));
                $newEndDate = date('Y-m-d', strtotime($newStartDate . ' +1 year'));

                $stmt->execute([
                    $newProposalId,
                    $newQuotationId,
                    $newStartDate,
                    $newEndDate
                ]);

                // 创建新的保单
                $stmt = $pdo->prepare("
                    INSERT INTO policy (
                        Policy_No, Quotation_ID, Proposal_ID, Policy_Issue_Date,
                        Policy_Status, Previous_Policy_No, User_ID
                    ) VALUES (
                        ?, ?, ?, CURDATE(), 'Inforce', ?, ?
                    )
                ");

                $stmt->execute([
                    $newPolicyNo,
                    $newQuotationId,
                    $newProposalId,
                    $_POST['original_policy_no'],
                    $originalPolicy['User_ID']
                ]);

                // 更新原保单的续保保单号
                $stmt = $pdo->prepare("
                    UPDATE policy 
                    SET Renew_Policy_No = ?, Policy_Status = 'Renewed'
                    WHERE Policy_No = ?
                ");
                $stmt->execute([$newPolicyNo, $_POST['original_policy_no']]);

                // 提交事务
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => '续保保单已创建',
                    'new_policy_no' => $newPolicyNo
                ]);
            } catch (Exception $e) {
                // 回滚事务
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'get_products':
            // 获取所有产品信息
            $stmt = $pdo->prepare("SELECT * FROM product ORDER BY Product_Name");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'products' => $products
            ]);
            break;

        default:
            throw new Exception('无效的操作');
    }
} catch (Exception $e) {
    error_log('Error in policy_processor.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
