<?php
header('Content-Type: application/json');

function calculateHKPrivatePremium($data)
{
    $basePremium = 2000;
    $baseExcess = 6000;

    // 计算各种因素
    $birthYear = intval($data['birth_year']);
    $licenseYear = intval($data['license_year']);
    // 从yyyy-mm-dd格式中提取年份
    $registrationYear = $data['registration_year'];

    // 添加调试信息
    error_log("Input values:");
    error_log("Birth Year: " . $birthYear);
    error_log("License Year: " . $licenseYear);
    error_log("Registration Year: " . $registrationYear);

    // 计算年龄和经验因素
    $ageZ = max(0, $birthYear - 2000);
    $experienceY = max(0, $licenseYear - 2023);
    // 计算车龄
    $carAgeX = 0;
    if ($registrationYear) {
        $registrationYear = substr($registrationYear, 0, 4); // 从日期字符串中提取年份
        error_log("Registration Year: " . $registrationYear);
        if ($registrationYear <= 2009) {
            $carAgeX = 2009 - $registrationYear;
        }
    }
    error_log("Car Age Factor: " . $carAgeX);

    // 添加调试信息
    error_log("Calculated factors:");
    error_log("Age Z: " . $ageZ);
    error_log("Experience Y: " . $experienceY);
    error_log("Car Age X: " . $carAgeX);

    // 计算保费加载
    $agePremiumLoading = 0.15 * $ageZ * $basePremium;
    $experiencePremiumLoading = 0.25 * $experienceY * $basePremium;
    $carAgePremiumLoading = 0.1 * $carAgeX * $basePremium;

    // 添加调试信息
    error_log("Premium loadings:");
    error_log("Age Premium Loading: " . $agePremiumLoading);
    error_log("Experience Premium Loading: " . $experiencePremiumLoading);
    error_log("Car Age Premium Loading: " . $carAgePremiumLoading);

    // 计算自付额加载
    $ageExcessLoading = 2000 * $ageZ;
    $experienceExcessLoading = 5000 * $experienceY;

    // 计算MIB加载
    $subtotalPremium = $basePremium + $agePremiumLoading + $experiencePremiumLoading + $carAgePremiumLoading;
    $mibLoading = $subtotalPremium * 0.0315;

    // 计算最终保费和自付额
    $finalPremium = $subtotalPremium + $mibLoading;
    $finalExcess = $baseExcess + $ageExcessLoading + $experienceExcessLoading;

    return [
        'success' => true,
        'premium' => $finalPremium,
        'excess' => $finalExcess,
        'breakdown' => [
            'basePremium' => $basePremium,
            'baseExcess' => $baseExcess,
            'agePremiumLoading' => $agePremiumLoading,
            'experiencePremiumLoading' => $experiencePremiumLoading,
            'carAgePremiumLoading' => $carAgePremiumLoading,
            'mibLoading' => $mibLoading,
            'ageExcessLoading' => $ageExcessLoading,
            'experienceExcessLoading' => $experienceExcessLoading,
            'finalPremium' => $finalPremium,
            'finalExcess' => $finalExcess
        ]
    ];
}

// 处理API请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = calculateHKPrivatePremium($data);
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
