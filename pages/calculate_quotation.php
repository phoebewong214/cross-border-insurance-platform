<?php
session_start();
require_once('db_config.php');
require_once('calculate_hk_private.php');
require_once('calculate_compulsory.php');
require_once('calculate_commercial.php');

function calculateQuotation($order_id)
{
    global $pdo;

    try {
        // 判斷訂單類型（續保或新訂單）
        $is_renewal = strpos($order_id, 'R') === 0;
        $is_new_order = strpos($order_id, 'O') === 0;

        if (!$is_renewal && !$is_new_order) {
            throw new Exception('Invalid order ID format');
        }

        // 根據訂單類型選擇不同的查詢邏輯
        if ($is_renewal) {
            // 續保訂單查詢
            $sql = "SELECT 
                    pq.*,
                    p.Name as Customer_Name,
                    p.ID_No as Customer_ID,
                    p.Date_of_Birth,
                    p.DL_No,
                    p.Date_of_B_class,
                    p.Date_of_C_class,
                    c.Car_Make_and_Model,
                    c.Seats,
                    c.Date_of_Registration,
                    c.Chasis_No,
                    c.Owner1_ID_No,
                    c.Owner2_ID_No,
                    c.Owner3_ID_No,
                    prod.Product_Name,
                    prod.Death_coverage,
                    prod.Medical_coverage,
                    prod.Material_coverage,
                    prod.Excess,
                    prod.Basic_premium
                    FROM pending_quotation pq
                    LEFT JOIN party p ON pq.Party_No = p.Party_No
                    LEFT JOIN car c ON pq.Registration_No = c.Registration_No
                    LEFT JOIN product prod ON pq.Product_Type = prod.Product_Code
                    WHERE pq.Pending_Quotation_ID = ?";

            error_log("Executing SQL query: " . $sql);
            error_log("Query parameters (order_id): " . $order_id);

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$order_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new Exception('No valid quotation found: ' . $order_id);
            }

            error_log("Retrieved data: " . print_r($data, true));

            // 檢查是否已經存在相同的報價單
            $checkSql = "SELECT q.* 
                         FROM quotation q
                         WHERE q.Registration_No = ? 
                         AND q.Party_No = ? 
                         AND q.Created_Date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                         ORDER BY q.Created_Date DESC
                         LIMIT 1";

            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$data['Registration_No'], $data['Party_No']]);
            $existingQuotation = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingQuotation) {
                // 如果找到相同的報價單，直接返回該報價單ID
                return [
                    'success' => true,
                    'quotation_id' => $existingQuotation['Quotation_ID'],
                    'calculation_details' => [
                        'previous_premium' => $existingQuotation['Total_Premium'],
                        'new_premium' => $existingQuotation['Total_Premium'],
                        'previous_ncd' => $existingQuotation['NCD'],
                        'new_ncd' => $existingQuotation['NCD']
                    ]
                ];
            }

            // 獲取上一張保單信息
            $lastQuotationSql = "SELECT q.*, p.Claim_Amount 
                                FROM quotation q
                                LEFT JOIN policy p ON q.Quotation_ID = p.Quotation_ID
                                WHERE q.Registration_No = ? 
                                AND q.Party_No = ? 
                                ORDER BY q.Created_Date DESC 
                                LIMIT 1";

            $lastQuotationStmt = $pdo->prepare($lastQuotationSql);
            $lastQuotationStmt->execute([$data['Registration_No'], $data['Party_No']]);
            $lastQuotation = $lastQuotationStmt->fetch(PDO::FETCH_ASSOC);

            if (!$lastQuotation) {
                throw new Exception('No valid previous policy found');
            }

            // 解析 Product_Type JSON
            $productIds = [];
            if (!empty($data['Product_Type'])) {
                error_log("Try to parse Product_Type: " . $data['Product_Type']);
                $productTypeData = json_decode($data['Product_Type'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    error_log("Successfully parsed JSON format");
                    if (isset($productTypeData['products']) && is_array($productTypeData['products'])) {
                        foreach ($productTypeData['products'] as $product) {
                            if (isset($product['id'])) {
                                $productIds[] = $product['id'];
                            }
                        }
                    }
                    error_log("Retrieved product IDs: " . implode(', ', $productIds));
                } else {
                    error_log("Failed to parse JSON: " . json_last_error_msg());
                    throw new Exception('Invalid product type data format');
                }
            } else if (!empty($data['Product_ID'])) {
                error_log("Using old Product_ID format");
                $productIds = [$data['Product_ID']];
            } else {
                throw new Exception('No product information found');
            }

            // 檢查上一期保單是否有理賠
            $hasClaims = isset($lastQuotation['Claim_Amount']) && $lastQuotation['Claim_Amount'] > 0;

            // 計算新的 NCD
            $newNCD = $lastQuotation['NCD'];
            if ($hasClaims) {
                // 如果有索賠，NCD減少0.3，但不低於0
                $newNCD = max(0, floatval($lastQuotation['NCD']) - 0.3);
            } else {
                // 如果沒有索賠，NCD增加0.1，但不超過0.5
                $newNCD = min(0.6, floatval($lastQuotation['NCD']) + 0.1);
            }

            // 計算新的保費（原保費 * (1 - 新NCD)）
            $newPremium = $lastQuotation['Total_Premium'] * (1 - $newNCD);

            // 獲取用戶折扣率
            if (isset($data['User_ID'])) {
                $user_id = $data['User_ID'];
                $discountSql = "SELECT discount FROM user WHERE User_ID = ?";
                $discountStmt = $pdo->prepare($discountSql);
                $discountStmt->execute([$user_id]);
                $discountResult = $discountStmt->fetch(PDO::FETCH_ASSOC);
                if ($discountResult) {
                    // 應用用戶折扣
                    $newPremium = $newPremium * (1 - ($discountResult['discount'] / 100));
                }
            }

            // 插入新的報價記錄
            $insertSql = "INSERT INTO quotation (
                            Party_No,
                            Registration_No,
                            Product1_ID,
                            Product2_ID,
                            Product3_ID,
                            Total_Coverage,
                            Final_Excess,
                            NCD,
                            Total_Premium,
                            Created_Date,
                            User_ID
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?
                        )";

            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                $data['Party_No'],
                $data['Registration_No'],
                $productIds[0] ?? null,
                $productIds[1] ?? null,
                $productIds[2] ?? null,
                $lastQuotation['Total_Coverage'],
                $lastQuotation['Final_Excess'],
                $newNCD,
                $newPremium,
                $data['User_ID']
            ]);

            // 獲取新生成的報價單ID
            $quotation_id = $pdo->query("SELECT Quotation_ID FROM quotation WHERE Party_No = '{$data['Party_No']}' AND Registration_No = '{$data['Registration_No']}' AND Created_Date = CURDATE() ORDER BY Quotation_ID DESC LIMIT 1")->fetchColumn();
            error_log("Newly generated quotation ID: " . $quotation_id);

            if (!$quotation_id) {
                throw new Exception('Failed to generate quotation');
            }

            return [
                'success' => true,
                'quotation_id' => $quotation_id,
                'calculation_details' => [
                    'previous_premium' => $lastQuotation['Total_Premium'],
                    'new_premium' => $newPremium,
                    'previous_ncd' => $lastQuotation['NCD'],
                    'new_ncd' => $newNCD
                ]
            ];
        } else {
            error_log("Processing new order");
            // 新訂單查詢
            $sql = "SELECT 
                    pq.*,
                    p.Name as Customer_Name,
                    p.ID_No as Customer_ID,
                    p.Date_of_Birth,
                    p.DL_No,
                    p.Date_of_B_class,
                    p.Date_of_C_class,
                    c.Car_Make_and_Model,
                    c.Seats,
                    c.Date_of_Registration,
                    c.Chasis_No,
                    c.Owner1_ID_No,
                    c.Owner2_ID_No,
                    c.Owner3_ID_No,
                    prod.Product_Name,
                    prod.Death_coverage,
                    prod.Medical_coverage,
                    prod.Material_coverage,
                    prod.Excess,
                    prod.Basic_premium
                    FROM pending_quotation pq
                    LEFT JOIN party p ON pq.Party_No = p.Party_No
                    LEFT JOIN car c ON pq.Registration_No = c.Registration_No
                    LEFT JOIN product prod ON pq.Product_Type = prod.Product_Code
                    WHERE pq.Pending_Quotation_ID = ?";

            error_log("執行SQL查詢: " . $sql);
            error_log("查詢參數 (order_id): " . $order_id);

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$order_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new Exception('No valid quotation found: ' . $order_id);
            }

            error_log("Retrieved data: " . print_r($data, true));

            // 解析Product_Type JSON
            $productIds = [];
            if (!empty($data['Product_Type'])) {
                error_log("Try to parse Product_Type: " . $data['Product_Type']);
                $productTypeData = json_decode($data['Product_Type'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    error_log("Successfully parsed JSON format");
                    if (isset($productTypeData['products']) && is_array($productTypeData['products'])) {
                        foreach ($productTypeData['products'] as $product) {
                            if (isset($product['id'])) {
                                $productIds[] = $product['id'];
                            }
                        }
                    }
                    error_log("Retrieved product IDs: " . implode(', ', $productIds));
                } else {
                    error_log("Failed to parse JSON: " . json_last_error_msg());
                    throw new Exception('Invalid product type data format');
                }
            } else if (!empty($data['Product_ID'])) {
                error_log("Using old Product_ID format");
                $productIds = [$data['Product_ID']];
            } else {
                throw new Exception('No product information found');
            }

            // 獲取第一個產品的信息用於確定保險類型
            $mainProductId = $productIds[0];
            $productSql = "SELECT * FROM product WHERE Product_ID = ?";
            $productStmt = $pdo->prepare($productSql);
            $productStmt->execute([$mainProductId]);
            $product = $productStmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new Exception('No product found: ' . $mainProductId);
            }

            // 根據產品類型調用不同的計算模塊
            $calculationResult = null;
            switch ($product['Product_Code']) {
                case 'MTY':
                    // 香港私家車保險計算
                    $calculationData = [
                        'birth_year' => date('Y', strtotime($data['Date_of_Birth'])),
                        'license_year' => date('Y', strtotime($data['Date_of_B_class'])),
                        'registration_year' => date('Y', strtotime($data['Date_of_Registration'])),
                        'product_id' => $mainProductId
                    ];
                    error_log("Calling HK private car insurance calculation, parameters: " . json_encode($calculationData));
                    $calculationResult = calculateHKPrivatePremium($calculationData);
                    error_log("HK private car insurance calculation result: " . print_r($calculationResult, true));
                    break;

                case 'MTJ':
                    // 交強險計算
                    $calculationData = [
                        'registration_date' => $data['Date_of_Registration'],
                        'seats' => $data['Seats']
                    ];
                    error_log("Calling compulsory insurance calculation, parameters: " . json_encode($calculationData));
                    $calculationResult = calculateCompulsoryPremium($calculationData);
                    break;

                case 'MTZ':
                    // 商業險計算
                    $calculationData = [
                        'product_ids' => $productIds,
                        'registration_no' => $data['Registration_No'],
                        'party_no' => $data['Party_No']
                    ];
                    error_log("Calling commercial insurance calculation, parameters: " . json_encode($calculationData));
                    $calculationResult = calculateCommercialPremium($calculationData);
                    break;

                default:
                    throw new Exception('Unsupported product type: ' . $product['Product_Code']);
            }

            if (!$calculationResult || !isset($calculationResult['success']) || !$calculationResult['success']) {
                throw new Exception('Calculation failed: ' . ($calculationResult['message'] ?? 'Unknown error'));
            }

            // 獲取基礎保費
            $basePremium = $calculationResult['premium'] ?? $calculationResult['final_premium'];
            error_log("Base premium calculation result: " . $basePremium);

            if ($basePremium === null) {
                throw new Exception('Premium calculation result is null');
            }

            // 新投保訂單，NCD為0
            $ncd = 0;
            $finalPremium = $basePremium;
            error_log("New policy order, NCD is 0");

            // 獲取用戶折扣率
            if (isset($data['User_ID'])) {
                $user_id = $data['User_ID'];
                $discountSql = "SELECT discount FROM user WHERE User_ID = ?";
                $discountStmt = $pdo->prepare($discountSql);
                $discountStmt->execute([$user_id]);
                $discountResult = $discountStmt->fetch(PDO::FETCH_ASSOC);
                if ($discountResult) {
                    // 應用用戶折扣
                    $finalPremium = $finalPremium * (1 - ($discountResult['discount'] / 100));
                    error_log("Applied user discount: " . $discountResult['discount'] . "%");
                }
            }

            error_log("Final premium (including NCD discount and user discount): " . $finalPremium);

            // 獲取自付額
            $finalExcess = $calculationResult['excess'] ?? $calculationResult['final_excess'] ?? 0;

            // 計算總保障額
            $totalCoverage = 0;
            foreach ($productIds as $productId) {
                if ($productId) {
                    $coverageSql = "SELECT Death_coverage, Medical_coverage, Material_coverage FROM product WHERE Product_ID = ?";
                    $coverageStmt = $pdo->prepare($coverageSql);
                    $coverageStmt->execute([$productId]);
                    $coverageData = $coverageStmt->fetch(PDO::FETCH_ASSOC);

                    // 將所有類型的保障額加起來
                    $totalCoverage += $coverageData['Death_coverage'] ?? 0;
                    $totalCoverage += $coverageData['Medical_coverage'] ?? 0;
                    $totalCoverage += $coverageData['Material_coverage'] ?? 0;
                }
            }

            error_log("Total coverage calculation result: " . $totalCoverage);

            error_log("Final premium (including NCD discount): " . $finalPremium);

            error_log("Preparing to insert data: " . print_r([
                'Party_No' => $data['Party_No'],
                'Registration_No' => $data['Registration_No'],
                'Product1_ID' => $productIds[0] ?? null,
                'Product2_ID' => $productIds[1] ?? null,
                'Product3_ID' => $productIds[2] ?? null,
                'Total_Coverage' => $totalCoverage,
                'Final_Excess' => $finalExcess,
                'NCD' => $ncd,
                'Total_Premium' => $finalPremium,
                'User_ID' => $data['User_ID']
            ], true));

            // 插入報價單記錄
            $insertSql = "INSERT INTO quotation (
                Party_No,
                Registration_No,
                Product1_ID,
                Product2_ID,
                Product3_ID,
                Total_Coverage,
                Final_Excess,
                NCD,
                Total_Premium,
                Created_Date,
                User_ID
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?
            )";

            // 先檢查是否存在一個月內的相同報價單
            $checkSql = "SELECT Quotation_ID 
                         FROM quotation 
                         WHERE Registration_No = ? 
                         AND Total_Premium = ? 
                         AND Created_Date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                         ORDER BY Created_Date DESC 
                         LIMIT 1";

            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$data['Registration_No'], $finalPremium]);
            $existingQuotation = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingQuotation) {
                // 如果存在，直接返回現有的報價單ID
                return [
                    'success' => true,
                    'quotation_id' => $existingQuotation['Quotation_ID'],
                    'message' => 'Using existing quotationxisting quotation',
                    'details' => [
                        'total_premium' => $finalPremium,
                        'total_coverage' => $totalCoverage,
                        'final_excess' => $finalExcess,
                        'ncd' => $ncd
                    ]
                ];
            }

            // 如果不存在，則插入新記錄
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([
                $data['Party_No'],
                $data['Registration_No'],
                $productIds[0] ?? null,
                $productIds[1] ?? null,
                $productIds[2] ?? null,
                $totalCoverage,
                $finalExcess,
                $ncd,
                $finalPremium,
                $data['User_ID']
            ]);

            // 獲取新生成的報價單ID
            $quotation_id = $pdo->query("SELECT Quotation_ID FROM quotation WHERE Party_No = '{$data['Party_No']}' AND Registration_No = '{$data['Registration_No']}' AND Created_Date = CURDATE() ORDER BY Quotation_ID DESC LIMIT 1")->fetchColumn();

            return [
                'success' => true,
                'quotation_id' => $quotation_id,
                'calculation_details' => [
                    'base_premium' => $basePremium,
                    'ncd' => $ncd,
                    'final_premium' => $finalPremium,
                    'calculation_result' => $calculationResult
                ]
            ];
        }
    } catch (Exception $e) {
        error_log("Error calculating quotation: " . $e->getMessage());
        error_log("Error stack: " . $e->getTraceAsString());
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'debug' => [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]
        ];
    }
}

// 處理API請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 設置錯誤處理
        ini_set('display_errors', 0);
        error_reporting(E_ALL);

        // 設置自定義錯誤處理函數
        function customErrorHandler($errno, $errstr, $errfile, $errline)
        {
            error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
            return true;
        }
        set_error_handler('customErrorHandler');

        // 確保沒有任何輸出緩衝
        if (ob_get_level()) ob_end_clean();

        // 記錄接收到的原始數據
        $raw_input = file_get_contents('php://input');
        error_log("Received raw data: " . $raw_input);

        $data = json_decode($raw_input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON parsing error: ' . json_last_error_msg());
        }

        if (!isset($data['order_id'])) {
            throw new Exception('Missing order ID');
        }

        error_log("Processing order: " . $data['order_id']);

        // 檢查必要的文件是否存在
        $required_files = [
            'calculate_hk_private.php',
            'calculate_compulsory.php',
            'calculate_commercial.php'
        ];

        foreach ($required_files as $file) {
            if (!file_exists($file)) {
                throw new Exception("Missing required calculation file: {$file}");
            }
        }

        // 檢查數據庫連接
        if (!isset($pdo)) {
            throw new Exception('Database connection failed');
        }

        $result = calculateQuotation($data['order_id']);

        // 返回 JSON 響應
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        error_log("Error stack: " . $e->getTraceAsString());

        // 返回錯誤信息
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'debug' => [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
        exit;
    }
}

// 如果不是 POST 請求，返回錯誤
header('Content-Type: application/json');
http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => '無效的請求方法'
]);
exit;
