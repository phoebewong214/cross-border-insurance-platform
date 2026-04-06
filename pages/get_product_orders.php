<?php
// 檢查會話是否已啟動
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 數據庫連接配置
$link = @mysqli_connect('localhost', 'root', '', 'insurance_system');
if (!$link) {
    die(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}
mysqli_query($link, 'SET NAMES utf8');

// 獲取篩選參數
$timeDimension = isset($_GET['time_dimension']) ? $_GET['time_dimension'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';

// 構建 WHERE 子句
$whereClause = "WHERE pol.Policy_Issue_Date >= '$startDate'";

// 根據時間維度添加額外的條件
switch ($timeDimension) {
    case 'daily':
        $whereClause .= " AND pol.Policy_Issue_Date < DATE_ADD('$startDate', INTERVAL 1 DAY)";
        break;
    case 'monthly':
        $whereClause .= " AND pol.Policy_Issue_Date < DATE_ADD('$startDate', INTERVAL 1 MONTH)";
        break;
    case 'quarterly':
        $whereClause .= " AND pol.Policy_Issue_Date < DATE_ADD('$startDate', INTERVAL 3 MONTH)";
        break;
    case 'annually':
        $whereClause .= " AND pol.Policy_Issue_Date < DATE_ADD('$startDate', INTERVAL 1 YEAR)";
        break;
}

// 查詢產品訂單統計
$query = "
    WITH product_policies AS (
        SELECT 
            p.Product_ID,
            pol.Policy_No
        FROM product p
        LEFT JOIN (
            SELECT Product1_ID as Product_ID, Quotation_ID FROM quotation WHERE Product1_ID IS NOT NULL
            UNION ALL
            SELECT Product2_ID as Product_ID, Quotation_ID FROM quotation WHERE Product2_ID IS NOT NULL
            UNION ALL
            SELECT Product3_ID as Product_ID, Quotation_ID FROM quotation WHERE Product3_ID IS NOT NULL
        ) q ON p.Product_ID = q.Product_ID
        LEFT JOIN policy pol ON pol.Quotation_ID = q.Quotation_ID
        $whereClause
    )
    SELECT 
        p.Product_ID,
        p.Product_Code,
        p.Product_Name,
        p.Basic_premium,
        COUNT(DISTINCT pp.Policy_No) as policy_count
    FROM product p
    LEFT JOIN product_policies pp ON p.Product_ID = pp.Product_ID
    GROUP BY p.Product_ID, p.Product_Code, p.Product_Name, p.Basic_premium
    ORDER BY policy_count DESC";

$result = mysqli_query($link, $query);
if (!$result) {
    die(json_encode(['error' => 'Query failed: ' . mysqli_error($link)]));
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// 設置響應頭
header('Content-Type: application/json');

// 輸出 JSON 數據
echo json_encode($data);

// 關閉數據庫連接
mysqli_close($link);
