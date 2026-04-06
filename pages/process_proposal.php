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
    // 获取POST数据
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['quotationId']) || !isset($data['action'])) {
        throw new Exception('Missing required parameters');
    }

    $quotationId = $data['quotationId'];
    $action = $data['action'];

    // 数据库连接
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ($action) {
        case 'update_status':
            if (!isset($data['insuranceStart']) || !isset($data['insuranceEnd'])) {
                throw new Exception('Insurance period is required');
            }

            $insuranceStart = $data['insuranceStart'];
            $insuranceEnd = $data['insuranceEnd'];

            // 验证日期格式
            if (!validateDate($insuranceStart) || !validateDate($insuranceEnd)) {
                throw new Exception('Invalid date format');
            }

            // 首先检查proposal是否存在
            $checkStmt = $pdo->prepare("SELECT Proposal_ID FROM proposal WHERE Quotation_ID = ?");
            $checkStmt->execute([$quotationId]);
            $proposal = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$proposal) {
                // 如果proposal不存在，创建新记录
                // 获取quotation信息以获取User_ID
                $quotationStmt = $pdo->prepare("SELECT User_ID FROM quotation WHERE Quotation_ID = ?");
                $quotationStmt->execute([$quotationId]);
                $quotation = $quotationStmt->fetch(PDO::FETCH_ASSOC);

                if (!$quotation) {
                    throw new Exception('Quotation not found');
                }

                // 插入新的proposal记录
                $insertStmt = $pdo->prepare("INSERT INTO proposal (Quotation_ID, Start_Date, End_Date, User_ID) VALUES (?, ?, ?, ?)");
                if (!$insertStmt->execute([$quotationId, $insuranceStart, $insuranceEnd, $quotation['User_ID']])) {
                    throw new Exception('Failed to create proposal');
                }
            } else {
                // 如果proposal存在，更新记录
                $updateStmt = $pdo->prepare("UPDATE proposal SET 
                    Start_Date = ?,
                    End_Date = ?
                    WHERE Quotation_ID = ?");

                if (!$updateStmt->execute([$insuranceStart, $insuranceEnd, $quotationId])) {
                    throw new Exception('Failed to update proposal dates');
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Proposal dates updated successfully',
                'quotation_id' => $quotationId
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log('Error in process_proposal.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// 验证日期格式
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
