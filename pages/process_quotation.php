<?php
session_start(); // 添加session启动

// 檢查用戶是否已登錄（普通用戶或管理員）
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_admin'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in',
        'redirect' => '../login.php'
    ]);
    exit;
}

require_once 'db_config.php';

// 5.2 收集报价信息
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'collect_quotation') {
    // 设置错误报告
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', 'C:/xampp/php/logs/php_error.log');

    header('Content-Type: application/json');
    try {
        // 记录请求信息
        error_log("=== New Quotation Request ===");
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Request URI: " . $_SERVER['REQUEST_URI']);
        error_log("Request Headers: " . print_r(getallheaders(), true));

        // 确保数据库连接
        if (!isset($pdo)) {
            error_log("Database connection not established");
            throw new Exception('Database connection not established');
        }

        $input = file_get_contents('php://input');
        error_log("Received input: " . $input);

        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }

        // 记录解析后的数据
        error_log("Parsed data: " . print_r($data, true));

        // 验证必要字段
        $required_fields = ['product_type', 'vehicle_id', 'coverage_options', 'premium'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                error_log("Missing required field: {$field}");
                throw new Exception("Missing required field: {$field}");
            }
        }

        // 获取车辆信息
        error_log("Fetching vehicle info for ID: " . $data['vehicle_id']);
        $stmt = $pdo->prepare("SELECT * FROM car WHERE Registration_No = ?");
        $stmt->execute([$data['vehicle_id']]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            error_log("Vehicle not found: " . $data['vehicle_id']);
            throw new Exception('Vehicle not found');
        }

        error_log("Vehicle info: " . print_r($car, true));

        // 根据产品类型处理
        $productTypeData = [];
        if ($data['product_type'] === 'commercial') {
            error_log("Processing commercial insurance");
            // 商业险：使用两个产品ID
            $productId = $data['coverage_options']['third_party_limit'];
            $product2Id = $data['coverage_options']['passenger_limit'];

            // 验证产品是否存在
            $stmt = $pdo->prepare("SELECT Product_ID, Product_Name FROM product WHERE Product_ID = ?");
            $stmt->execute([$productId]);
            $product1 = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product1) {
                throw new Exception('Third party liability insurance product not found');
            }

            $stmt->execute([$product2Id]);
            $product2 = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product2) {
                throw new Exception('Passenger liability insurance product not found');
            }

            // 将产品信息存储在JSON格式中
            $productTypeData = [
                'type' => 'commercial',
                'products' => [
                    'third_party' => [
                        'id' => $productId,
                        'name' => $product1['Product_Name']
                    ],
                    'passenger' => [
                        'id' => $product2Id,
                        'name' => $product2['Product_Name']
                    ]
                ]
            ];
        } else {
            error_log("Processing " . $data['product_type'] . " insurance");
            // 交强险和香港车险：使用单个产品ID
            if ($data['product_type'] === 'compulsory') {
                // 交强险：根据座位数匹配MTJ产品
                $seats = $car['Seats'];
                $fitFor = $seats <= 5 ? 'Less_than_5_seats' : 'Over_5_seats';

                $stmt = $pdo->prepare("
                    SELECT Product_ID, Product_Name 
                    FROM product 
                    WHERE Product_Code = 'MTJ'
                    AND Fit_for = ?
                ");
                $stmt->execute([$fitFor]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception('Compulsory insurance product not found');
                }

                $productTypeData = [
                    'type' => 'compulsory',
                    'products' => [
                        'main' => [
                            'id' => $product['Product_ID'],
                            'name' => $product['Product_Name']
                        ]
                    ]
                ];
            } else if ($data['product_type'] === 'hk_private') {
                // 香港车险：直接使用MTY001
                $stmt = $pdo->prepare("SELECT Product_ID, Product_Name FROM product WHERE Product_ID = 'MTY001'");
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception('Hong Kong private car insurance product not found');
                }

                $productTypeData = [
                    'type' => 'hk_private',
                    'products' => [
                        'main' => [
                            'id' => 'MTY001',
                            'name' => $product['Product_Name']
                        ]
                    ]
                ];
            } else {
                throw new Exception('Invalid product type');
            }
        }

        // 计算总保费
        $totalPremium = $data['premium'];

        // 为了调试，记录一些信息
        error_log("Vehicle ID received: " . $data['vehicle_id']);
        error_log("Product Type Data: " . json_encode($productTypeData));

        // 开始事务
        error_log("Starting database transaction");
        $pdo->beginTransaction();

        try {
            // 存储报价信息到pending_review表
            error_log("Inserting into pending_review table");
            $stmt = $pdo->prepare("
                INSERT INTO pending_review 
                (Party_No, Registration_No, Generate_Time, User_ID, Product_Type) 
                VALUES (?, ?, NOW(), ?, ?)
            ");

            $stmt->execute([
                $car['Owner1_ID_No'],
                $data['vehicle_id'],
                $_SESSION['user_id'],  // 使用session中的user_id
                json_encode($productTypeData)
            ]);

            // 获取新插入的ID
            $pending_review_id = $pdo->lastInsertId();
            error_log("New pending_review ID: " . $pending_review_id);

            // 发送通知邮件
            error_log("Sending notification email");
            $to = "admin@example.com";
            $subject = "New Insurance Quote Review Required - ID: " . $pending_review_id;
            $message = "A new insurance quote requires your review.\n\n";
            $message .= "Review ID: " . $pending_review_id . "\n";
            $message .= "Product Type: " . json_encode($productTypeData) . "\n";
            $message .= "Vehicle: " . $data['vehicle_id'] . "\n";
            $message .= "Premium: " . $totalPremium . "\n";

            $mail_result = mail($to, $subject, $message);
            error_log("Mail sending result: " . ($mail_result ? "Success" : "Failed"));

            // 提交事务
            error_log("Committing transaction");
            $pdo->commit();

            // 返回成功响应
            $response = [
                'success' => true,
                'message' => 'Quote submitted for review',
                'review_id' => $pending_review_id
            ];
            error_log("Sending success response: " . json_encode($response));
            echo json_encode($response);
        } catch (Exception $e) {
            // 回滚事务
            error_log("Rolling back transaction due to error: " . $e->getMessage());
            $pdo->rollBack();
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Error in collect_quotation: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        http_response_code(400);
        $error_response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        error_log("Sending error response: " . json_encode($error_response));
        echo json_encode($error_response);
    }
    error_log("=== End of Quotation Request ===");
    exit;
}

// 5.3 验证信息
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'validate_info') {
    header('Content-Type: application/json');
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['review_id'])) {
            throw new Exception('Review ID is required');
        }

        // 获取待审核信息
        $stmt = $pdo->prepare("SELECT * FROM pending_review WHERE id = ?");
        $stmt->execute([$data['review_id']]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            throw new Exception('Review not found');
        }

        // 更新审核状态
        $stmt = $pdo->prepare("
            UPDATE pending_review 
            SET status = ?, 
                admin_comments = ?,
                reviewed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->execute([
            $data['status'],
            $data['comments'] ?? null,
            $data['review_id']
        ]);

        // 如果审核通过，创建保单
        if ($data['status'] === 'approved') {
            $stmt = $pdo->prepare("
                INSERT INTO product 
                (product_type, vehicle_id, coverage_options, premium) 
                SELECT product_type, vehicle_id, coverage_options, premium 
                FROM pending_review 
                WHERE id = ?
            ");
            $stmt->execute([$data['review_id']]);
        }

        // 5.4 发送结果通知
        // 这里应该获取用户邮箱并发送通知
        $to = "user@example.com"; // 应该从数据库获取用户邮箱
        $subject = "Insurance Quote Review Result";
        $message = "Your insurance quote has been " . $data['status'] . ".\n";
        if (isset($data['comments'])) {
            $message .= "\nComments: " . $data['comments'];
        }

        mail($to, $subject, $message);

        echo json_encode([
            'success' => true,
            'message' => 'Review completed',
            'status' => $data['status']
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 5.5 修改信息
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'modify_info') {
    header('Content-Type: application/json');
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['review_id'])) {
            throw new Exception('Review ID is required');
        }

        // 更新待审核信息
        $stmt = $pdo->prepare("
            UPDATE pending_review 
            SET coverage_options = ?,
                premium = ?,
                modified_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->execute([
            json_encode($data['coverage_options']),
            $data['premium'],
            $data['review_id']
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Information updated successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 生成报价单号
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'confirm_quotation') {
    header('Content-Type: application/json');
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("Received data: " . print_r($data, true));

        // 验证必要字段
        $required_fields = ['vehicle_id', 'coverage_options', 'premium', 'review_id'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                error_log("Missing field: " . $field);
                throw new Exception("Missing required field: {$field}");
            }
        }

        // 从car表获取Party_No
        $stmt = $pdo->prepare("SELECT Party_No FROM car WHERE Registration_No = ?");
        $stmt->execute([$data['vehicle_id']]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Car data: " . print_r($car, true));

        if (!$car) {
            error_log("Car not found for Registration_No: " . $data['vehicle_id']);
            throw new Exception('Vehicle not found');
        }

        if (empty($car['Party_No'])) {
            error_log("Party_No is empty for vehicle: " . $data['vehicle_id']);
            throw new Exception('Party_No is empty in car record');
        }

        // 生成报价单号
        $quotation_id = 'O' . date('ymd') . sprintf('%04d', rand(1, 9999));

        // 记录要插入的数据
        $insertData = [
            'quotation_id' => $quotation_id,
            'product_type' => $data['product_type'] ?? 'MVI',
            'coverage_amount' => $data['coverage_amount'] ?? 0,
            'excess' => $data['excess'] ?? 0,
            'ncd' => $data['ncd'] ?? 0,
            'premium' => $data['premium'],
            'party_no' => $car['Party_No'],
            'vehicle_id' => $data['vehicle_id']
        ];
        error_log("Insert data: " . print_r($insertData, true));

        // 开始事务
        $pdo->beginTransaction();

        try {
            // 插入报价信息到quotation表
            $stmt = $pdo->prepare("
                INSERT INTO quotation 
                (Quotation_ID, Product1_ID, Total_Coverage, Final_Excess, NCD, Total_Premium, Party_No, Registration_No, Created_Date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)
            ");

            $stmt->execute([
                $quotation_id,
                $data['product_type'] ?? 'MVI',
                $data['coverage_amount'] ?? 0,
                $data['excess'] ?? 0,
                $data['ncd'] ?? 0,
                $data['premium'],
                $car['Party_No'],
                $data['vehicle_id']
            ]);
            error_log("Quotation inserted successfully");

            // 生成 pending_quotation_id
            $today = date('dmy');
            $stmt = $pdo->prepare("
                SELECT MAX(SUBSTRING(Pending_Quotation_ID, 8, 4)) as max_seq 
                FROM pending_quotation 
                WHERE Pending_Quotation_ID LIKE 'O" . $today . "%'
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $sequence = $result['max_seq'] ? str_pad((intval($result['max_seq']) + 1), 4, '0', STR_PAD_LEFT) : '0001';
            $pending_quotation_id = 'O' . $today . $sequence;

            // 同时插入到 pending_quotation 表
            $stmt = $pdo->prepare("
                INSERT INTO pending_quotation 
                (Pending_Quotation_ID, Party_No, Registration_No, Generate_Time, User_ID, Product_Type) 
                VALUES (?, ?, ?, NOW(), ?, ?)
            ");

            // 获取当前用户ID（这里暂时使用固定值，之后应该从session获取）
            $user_id = 'U25000007';

            // 记录要插入的数据
            error_log("Inserting into pending_quotation with values: " . print_r([
                'Pending_Quotation_ID' => $pending_quotation_id,
                'Party_No' => $car['Party_No'],
                'Registration_No' => $data['vehicle_id'],
                'User_ID' => $user_id,
                'Product_Type' => $data['product_type'] ?? 'MVI'
            ], true));

            $stmt->execute([
                $pending_quotation_id,
                $car['Party_No'],
                $data['vehicle_id'],
                $user_id,
                $data['product_type'] ?? 'MVI'
            ]);
            error_log("Pending quotation inserted successfully");

            // 提交事务
            $pdo->commit();
            error_log("Transaction committed successfully");

            echo json_encode([
                'success' => true,
                'message' => 'Quotation confirmed',
                'quotation_id' => $quotation_id
            ]);
        } catch (PDOException $e) {
            // 回滚事务
            $pdo->rollBack();
            error_log("Database error: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Error Info: " . print_r($stmt->errorInfo(), true));
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Error in confirm_quotation: " . $e->getMessage());
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 获取报价单详情
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_quotation') {
    header('Content-Type: application/json');
    try {
        if (!isset($_GET['id'])) {
            throw new Exception('Quotation ID is required');
        }

        $quotationId = $_GET['id'];

        // 获取报价单信息
        $stmt = $pdo->prepare("
            SELECT 
                q.*,
                c.Car_Make_and_Model as car_make_model,
                c.Seats as seats,
                p.Name as owner_name,
                p.ID_No as owner_id,
                p1.Product_Name as product1_name,
                p2.Product_Name as product2_name,
                p3.Product_Name as product3_name
            FROM quotation q
            LEFT JOIN car c ON q.Registration_No = c.Registration_No
            LEFT JOIN party p ON q.Party_No = p.Party_No
            LEFT JOIN product p1 ON q.Product1_ID = p1.Product_ID
            LEFT JOIN product p2 ON q.Product2_ID = p2.Product_ID
            LEFT JOIN product p3 ON q.Product3_ID = p3.Product_ID
            WHERE q.Quotation_ID = ?
        ");
        $stmt->execute([$quotationId]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$quotation) {
            throw new Exception('Quotation not found');
        }

        // 格式化返回数据
        $response_data = [
            'id' => $quotation['Quotation_ID'],
            'owner_name' => $quotation['owner_name'],
            'phone' => '', // 设置为空字符串
            'address' => '', // 设置为空字符串
            'nationality' => '', // 设置为空字符串
            'vehicle_id' => $quotation['Registration_No'],
            'car_make_model' => $quotation['car_make_model'],
            'seats' => $quotation['seats'],
            'coverage_options' => [
                'Total_Coverage' => $quotation['Total_Coverage'],
                'Final_Excess' => $quotation['Final_Excess'],
                'NCD' => $quotation['NCD']
            ],
            'premium' => $quotation['Total_Premium'],
            'product1_id' => $quotation['Product1_ID'],
            'product2_id' => $quotation['Product2_ID'],
            'product3_id' => $quotation['Product3_ID'],
            'product1_name' => $quotation['product1_name'],
            'product2_name' => $quotation['product2_name'],
            'product3_name' => $quotation['product3_name']
        ];

        echo json_encode([
            'success' => true,
            'quotation' => $response_data
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 5.1 处理报价审批
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'approve') {
    header('Content-Type: application/json');
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['review_id'])) {
            throw new Exception('Missing review_id');
        }

        // 获取待审核记录信息
        $stmt = $pdo->prepare("
            SELECT pr.*, c.Owner1_ID_No as Party_No 
            FROM pending_review pr
            JOIN car c ON pr.Registration_No = c.Registration_No
            WHERE pr.Review_ID = ?
        ");
        $stmt->execute([$data['review_id']]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            throw new Exception('Review record not found');
        }

        // 开始事务
        $pdo->beginTransaction();

        // 生成新的报价ID
        $quotation_id = 'Q' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // 插入报价记录
        $stmt = $pdo->prepare("
            INSERT INTO quotation 
            (Quotation_ID, Party_No, Registration_No, Product_Type, Generate_Time, User_ID) 
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([
            $quotation_id,
            $review['Party_No'],
            $review['Registration_No'],
            $review['Product_Type'],
            $review['User_ID']
        ]);

        // 解析产品类型数据
        $productTypeData = json_decode($review['Product_Type'], true);

        // 根据产品类型插入相应的报价详情
        if ($productTypeData['type'] === 'compulsory') {
            $stmt = $pdo->prepare("
                INSERT INTO compulsory_quotation 
                (Quotation_ID, Product_ID, Premium) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $quotation_id,
                $productTypeData['products']['main']['id'],
                $data['premium'] ?? 0
            ]);
        } else if ($productTypeData['type'] === 'commercial') {
            $stmt = $pdo->prepare("
                INSERT INTO commercial_quotation 
                (Quotation_ID, Product_ID, Product2_ID, Premium) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $quotation_id,
                $productTypeData['products']['third_party']['id'],
                $productTypeData['products']['passenger']['id'],
                $data['premium'] ?? 0
            ]);
        } else if ($productTypeData['type'] === 'hk_private') {
            $stmt = $pdo->prepare("
                INSERT INTO hk_private_quotation 
                (Quotation_ID, Product_ID, Premium) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $quotation_id,
                $productTypeData['products']['main']['id'],
                $data['premium'] ?? 0
            ]);
        }

        // 删除待审核记录
        $stmt = $pdo->prepare("DELETE FROM pending_review WHERE Review_ID = ?");
        $stmt->execute([$data['review_id']]);

        // 提交事务
        $pdo->commit();

        // 返回成功响应
        echo json_encode([
            'success' => true,
            'message' => 'Quotation approved successfully',
            'quotation_id' => $quotation_id
        ]);
    } catch (Exception $e) {
        // 回滚事务
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // 记录错误日志
        error_log("Error in approve action: " . $e->getMessage());

        // 返回错误响应
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 处理审批和拒绝操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        if (!isset($_POST['action'])) {
            throw new Exception('Action is required');
        }

        if (!isset($_POST['quotation_id'])) {
            throw new Exception('Quotation ID is required');
        }

        $action = $_POST['action'];
        $quotationId = $_POST['quotation_id'];

        if ($action === 'approve') {
            // 开始事务
            $pdo->beginTransaction();

            try {
                // 先获取 pending_review 的数据
                $stmt = $pdo->prepare("
                    SELECT pr.*, p.Party_No, c.Registration_No 
                    FROM pending_review pr
                    LEFT JOIN party p ON pr.Party_No = p.Party_No
                    LEFT JOIN car c ON pr.Registration_No = c.Registration_No
                    WHERE pr.Pending_review_ID = ?
                ");
                $stmt->execute([$quotationId]);
                $reviewData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$reviewData) {
                    throw new Exception('Review data not found');
                }

                // 生成 pending_quotation_id
                $today = date('dmy');
                $stmt = $pdo->prepare("
                    SELECT MAX(SUBSTRING(Pending_Quotation_ID, 8, 4)) as max_seq 
                    FROM pending_quotation 
                    WHERE Pending_Quotation_ID LIKE 'O" . $today . "%'
                ");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $sequence = $result['max_seq'] ? str_pad((intval($result['max_seq']) + 1), 4, '0', STR_PAD_LEFT) : '0001';
                $pending_quotation_id = 'O' . $today . $sequence;

                // 插入数据到 pending_quotation
                $stmt = $pdo->prepare("
                    INSERT INTO pending_quotation 
                    (Pending_Quotation_ID, Party_No, Registration_No, Generate_Time, User_ID, Product_Type) 
                    VALUES (?, ?, ?, NOW(), ?, ?)
                ");

                error_log("Inserting into pending_quotation with values: " . print_r([
                    'Pending_Quotation_ID' => $pending_quotation_id,
                    'Party_No' => $reviewData['Party_No'],
                    'Registration_No' => $reviewData['Registration_No'],
                    'User_ID' => $reviewData['User_ID'],
                    'Product_Type' => $reviewData['Product_Type']
                ], true));

                $stmt->execute([
                    $pending_quotation_id,
                    $reviewData['Party_No'],
                    $reviewData['Registration_No'],
                    $reviewData['User_ID'],
                    $reviewData['Product_Type']
                ]);

                // 删除待审核记录
                $stmt = $pdo->prepare("DELETE FROM pending_review WHERE Pending_review_ID = ?");
                $stmt->execute([$quotationId]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception('Failed to delete pending review record');
                }

                // 提交事务
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Quotation approved and moved to pending_quotation successfully'
                ]);
            } catch (Exception $e) {
                // 回滚事务
                $pdo->rollBack();
                throw $e;
            }
        } else if ($action === 'reject') {
            if (!isset($_POST['reason'])) {
                throw new Exception('Rejection reason is required');
            }

            // 开始事务
            $pdo->beginTransaction();

            try {
                // 更新 pending_review 表的 admin_comment
                $stmt = $pdo->prepare("
                    UPDATE pending_review 
                    SET Admin_Comment = ? 
                    WHERE Pending_review_ID = ?
                ");
                $stmt->execute([$_POST['reason'], $quotationId]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception('Quotation not found or already processed');
                }

                // 提交事务
                $pdo->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Quotation rejected successfully'
                ]);
            } catch (Exception $e) {
                // 回滚事务
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                // 记录错误日志
                error_log("Error in reject action: " . $e->getMessage());

                // 返回错误响应
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
