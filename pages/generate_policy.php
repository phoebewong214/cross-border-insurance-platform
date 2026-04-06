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
    // 获取URL参数
    $quotationId = $_GET['id'] ?? null;

    if (!$quotationId) {
        throw new Exception('Missing quotation ID');
    }

    // 数据库连接
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 验证交易状态并获取保险开始日期
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            pr.Proposal_ID,
            p.User_ID,
            pr.Start_Date,
            pq.Pending_Quotation_ID
        FROM transaction t
        JOIN quotation q ON t.Quotation_ID = q.Quotation_ID
        LEFT JOIN party p ON q.Party_No = p.Party_No
        LEFT JOIN proposal pr ON q.Quotation_ID = pr.Quotation_ID
        LEFT JOIN pending_quotation pq ON q.Registration_No = pq.Registration_No
        WHERE t.Quotation_ID = ?
        AND t.Trading_Status = 'Written_off'
        ORDER BY t.Bank_Time DESC
        LIMIT 1
    ");
    $stmt->execute([$quotationId]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        throw new Exception('No valid transaction found');
    }

    // 从Pending_Quotation_ID中提取Previous_Policy_No
    $previous_policy_no = null;
    if (!empty($transaction['Pending_Quotation_ID']) && strpos($transaction['Pending_Quotation_ID'], 'R') === 0) {
        // 獲取Product_Type中的previous_policy_no
        $stmt = $pdo->prepare("
            SELECT Product_Type 
            FROM pending_quotation 
            WHERE Pending_Quotation_ID = ?
        ");
        $stmt->execute([$transaction['Pending_Quotation_ID']]);
        $productTypeData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($productTypeData) {
            $productType = json_decode($productTypeData['Product_Type'], true);
            if (isset($productType['previous_policy_no'])) {
                $previous_policy_no = $productType['previous_policy_no'];
                error_log("從Product_Type中獲取到原保單號: " . $previous_policy_no);
            } else {
                error_log("Product_Type中未找到previous_policy_no: " . print_r($productType, true));
            }
        } else {
            error_log("未找到Product_Type數據，Pending_Quotation_ID: " . $transaction['Pending_Quotation_ID']);
        }
    }

    // 根据开始日期决定保单状态
    $startDate = new DateTime($transaction['Start_Date']);
    $today = new DateTime();
    $policyStatus = ($startDate > $today) ? 'Waiting' : 'Inforce';

    error_log("準備插入保單記錄，Previous_Policy_No: " . $previous_policy_no);

    // 插入保单记录
    $stmt = $pdo->prepare("
        INSERT INTO policy (
            Quotation_ID,
            Proposal_ID,
            Policy_Issue_Date,
            Policy_Status,
            Previous_Policy_No,
            User_ID
        ) VALUES (
            ?, ?, CURDATE(), ?, ?, ?
        )
    ");

    if (!$stmt->execute([
        $quotationId,
        $transaction['Proposal_ID'],
        $policyStatus,
        $previous_policy_no,
        $transaction['User_ID']
    ])) {
        throw new Exception('Failed to create policy record');
    }

    // 获取新生成的保单号
    $stmt = $pdo->prepare("
        SELECT Policy_No 
        FROM policy 
        WHERE Quotation_ID = ? 
        AND Proposal_ID = ? 
        ORDER BY Policy_Issue_Date DESC 
        LIMIT 1
    ");
    $stmt->execute([$quotationId, $transaction['Proposal_ID']]);
    $policy = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$policy) {
        throw new Exception('Failed to retrieve generated policy number');
    }

    error_log("新生成的保單號: " . $policy['Policy_No']);

    // 获取保单相关信息
    $stmt = $pdo->prepare("
        SELECT q.Registration_No
        FROM policy pol
        JOIN quotation q ON pol.Quotation_ID = q.Quotation_ID
        WHERE pol.Policy_No = ?
    ");
    $stmt->execute([$policy['Policy_No']]);
    $policy_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($policy_info) {
        // 删除pending_quotation中匹配的记录
        $stmt = $pdo->prepare("
            DELETE FROM pending_quotation 
            WHERE Registration_No = ?
        ");

        if (!$stmt->execute([$policy_info['Registration_No']])) {
            error_log('Warning: Failed to delete pending_quotation record for Registration_No: ' . $policy_info['Registration_No']);
            // 这里我们不抛出异常，因为保单已经成功生成，删除pending记录失败不应该影响整个流程
        }
    }

    // 更新前序保单的续保保单号
    if ($previous_policy_no) {
        error_log("準備更新前序保單的續保保單號，Previous_Policy_No: " . $previous_policy_no . ", New_Policy_No: " . $policy['Policy_No']);
        $stmt = $pdo->prepare("
            UPDATE policy 
            SET Renew_Policy_No = ?, Policy_Status = 'Renewed'
            WHERE Policy_No = ?
        ");
        if (!$stmt->execute([$policy['Policy_No'], $previous_policy_no])) {
            error_log("更新前序保單失敗: " . print_r($stmt->errorInfo(), true));
        } else {
            error_log("成功更新前序保單的續保保單號");
        }
    } else {
        error_log("沒有找到前序保單號，跳過更新");
    }

    // 重定向到成功页面
    header("Location: policy_success.php?policy_no=" . $policy['Policy_No'] . "&policy_status=" . $policyStatus);
    exit;
} catch (Exception $e) {
    error_log('Error in generate_policy.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
