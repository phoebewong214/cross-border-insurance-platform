<?php
header('Content-Type: application/json');

function calculateCompulsoryPremium($data)
{
    $basePremium = 0;
    $discount = 0;

    // 根据座位数确定基础保费
    if ($data['seat_count'] === '1-5') {
        $basePremium = 950;
    } else {
        $basePremium = 1100;
    }

    // 如果是年付，给予8%折扣
    if ($data['payment_period'] === 'annual') {
        $discount = $basePremium * 0.08;
    }

    $finalPremium = $basePremium - $discount;

    return [
        'success' => true,
        'premium' => $finalPremium,
        'breakdown' => [
            'basePremium' => $basePremium,
            'discount' => $discount,
            'finalPremium' => $finalPremium
        ]
    ];
}

// 处理API请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = calculateCompulsoryPremium($data);
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
