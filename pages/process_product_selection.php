<?php
// 设置响应头
header('Content-Type: application/json');

// 数据库连接配置
$host = 'localhost';
$dbname = 'insurance_system';
$username = 'root';
$password = '';

try {
    // 设置PDO连接选项
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    // 连接数据库
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, $options);

    // 获取车辆总数的API端点
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_car_count') {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM car");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'count' => $result['total']]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 获取用户车辆列表的API端点
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cars') {
        try {
            session_start();
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not logged in');
            }

            $user_id = $_SESSION['user_id'];

            // 查询当前用户的车辆信息
            $stmt = $pdo->prepare("
                SELECT 
                    c.Registration_No,
                    c.Car_Make_and_Model,
                    c.Seats,
                    c.Date_of_Registration,
                    c.Chasis_No,
                    c.Owner1_ID_No,
                    c.Owner2_ID_No,
                    c.Owner3_ID_No,
                    p1.Party_No as Owner1_Party_No,
                    p1.Type as Owner1_Type,
                    p1.Name as Owner1_Name,
                    p1.Date_of_Birth as Owner1_Birth_Date,
                    p1.DL_No as Owner1_DL_No,
                    p1.Date_of_B_class as Owner1_B_License_Date,
                    p1.Date_of_C_class as Owner1_C_License_Date,
                    p1.Total_Claim_Amount as Owner1_Claim_Amount,
                    p1.Total_Contributed_Premium as Owner1_Premium,
                    p2.Party_No as Owner2_Party_No,
                    p2.Type as Owner2_Type,
                    p2.Name as Owner2_Name,
                    p2.Date_of_Birth as Owner2_Birth_Date,
                    p2.DL_No as Owner2_DL_No,
                    p2.Date_of_B_class as Owner2_B_License_Date,
                    p2.Date_of_C_class as Owner2_C_License_Date,
                    p2.Total_Claim_Amount as Owner2_Claim_Amount,
                    p2.Total_Contributed_Premium as Owner2_Premium,
                    p3.Party_No as Owner3_Party_No,
                    p3.Type as Owner3_Type,
                    p3.Name as Owner3_Name,
                    p3.Date_of_Birth as Owner3_Birth_Date,
                    p3.DL_No as Owner3_DL_No,
                    p3.Date_of_B_class as Owner3_B_License_Date,
                    p3.Date_of_C_class as Owner3_C_License_Date,
                    p3.Total_Claim_Amount as Owner3_Claim_Amount,
                    p3.Total_Contributed_Premium as Owner3_Premium
                FROM car c
                LEFT JOIN party p1 ON c.Owner1_ID_No = p1.Party_No
                LEFT JOIN party p2 ON c.Owner2_ID_No = p2.Party_No
                LEFT JOIN party p3 ON c.Owner3_ID_No = p3.Party_No
                WHERE c.User_ID = ?
            ");
            $stmt->execute([$user_id]);
            $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 添加調試日誌
            error_log("SQL Query executed for user_id: " . $user_id);
            error_log("Number of cars found: " . count($cars));
            foreach ($cars as $car) {
                error_log("Car Registration: " . $car['Registration_No']);
                error_log("Owner1 ID: " . $car['Owner1_ID_No']);
                error_log("Owner1 Name: " . $car['Owner1_Name']);
                error_log("Owner1 Birth Date: " . $car['Owner1_Birth_Date']);
                error_log("Owner1 License Date: " . $car['Owner1_B_License_Date']);

                // 檢查party表中是否存在對應的記錄
                if ($car['Owner1_ID_No']) {
                    $checkParty = $pdo->prepare("SELECT * FROM party WHERE Party_No = ?");
                    $checkParty->execute([$car['Owner1_ID_No']]);
                    $partyData = $checkParty->fetch(PDO::FETCH_ASSOC);
                    error_log("Party data for Party_No " . $car['Owner1_ID_No'] . ": " . print_r($partyData, true));
                }
            }

            if ($cars) {
                echo json_encode([
                    'success' => true,
                    'cars' => $cars
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No vehicles found for this user'
                ]);
            }
        } catch (Exception $e) {
            // 错误处理
            error_log("Error in get_cars: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    // 获取产品保费信息
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_product_premiums') {
        try {
            // 获取所有MTZ产品的保费信息
            $stmt = $pdo->prepare("
                SELECT Product_ID, Basic_premium, Fit_for 
                FROM product 
                WHERE Product_Code = 'MTZ'
            ");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 将结果转换为以Product_ID为键的数组
            $premiums = [];
            foreach ($products as $product) {
                $premiums[$product['Product_ID']] = [
                    'basic_premium' => $product['Basic_premium'],
                    'fit_for' => $product['Fit_for']
                ];
            }

            echo json_encode([
                'success' => true,
                'premiums' => $premiums
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    // 处理产品选择
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                throw new Exception('Invalid input data');
            }

            // 处理所有保险产品
            $stmt = $pdo->prepare("INSERT INTO product (product_type, vehicle_id, coverage_options, premium) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $data['product_type'],
                $data['vehicle_id'],
                json_encode($data['coverage_options']),
                $data['premium']
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
