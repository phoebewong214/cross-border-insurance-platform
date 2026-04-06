<?php
header('Content-Type: application/json');
require_once 'db_config.php';

function calculateCommercialPremium($data)
{
    global $pdo;

    try {
        // 记录输入数据
        error_log("Commercial Insurance Calculation - Input Data: " . json_encode($data));

        // 获取第三者责任险基础保费
        $stmt = $pdo->prepare("SELECT Basic_premium, Product_Name FROM product WHERE Product_ID = ?");
        $stmt->execute([$data['third_party_limit']]);
        $thirdPartyProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$thirdPartyProduct) {
            error_log("Third party liability product not found for ID: " . $data['third_party_limit']);
            throw new Exception('Third party liability product not found');
        }

        // 记录第三者责任险产品信息
        error_log("Third Party Product: " . json_encode($thirdPartyProduct));

        // 获取乘客责任险基础保费
        $stmt = $pdo->prepare("SELECT Basic_premium, Product_Name FROM product WHERE Product_ID = ?");
        $stmt->execute([$data['passenger_limit']]);
        $passengerProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$passengerProduct) {
            error_log("Passenger liability product not found for ID: " . $data['passenger_limit']);
            throw new Exception('Passenger liability product not found');
        }

        // 记录乘客责任险产品信息
        error_log("Passenger Product: " . json_encode($passengerProduct));

        // 计算总保费
        $thirdPartyPremium = $thirdPartyProduct['Basic_premium'];
        $passengerPremium = $passengerProduct['Basic_premium'] * $data['seats'];
        $totalPremium = $thirdPartyPremium + $passengerPremium;

        // 记录计算结果
        error_log("Premium Calculation Results: " . json_encode([
            'thirdPartyPremium' => $thirdPartyPremium,
            'passengerPremium' => $passengerPremium,
            'totalPremium' => $totalPremium
        ]));

        return [
            'success' => true,
            'premium' => $totalPremium,
            'breakdown' => [
                'thirdPartyProduct' => [
                    'id' => $data['third_party_limit'],
                    'name' => $thirdPartyProduct['Product_Name'],
                    'premium' => $thirdPartyPremium
                ],
                'passengerProduct' => [
                    'id' => $data['passenger_limit'],
                    'name' => $passengerProduct['Product_Name'],
                    'premium' => $passengerProduct['Basic_premium'],
                    'seats' => $data['seats'],
                    'totalPremium' => $passengerPremium
                ],
                'totalPremium' => $totalPremium
            ]
        ];
    } catch (Exception $e) {
        error_log("Commercial Insurance Calculation Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// 处理API请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("Received API Request Data: " . json_encode($data));
        $result = calculateCommercialPremium($data);
        echo json_encode($result);
    } catch (Exception $e) {
        error_log("API Request Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
