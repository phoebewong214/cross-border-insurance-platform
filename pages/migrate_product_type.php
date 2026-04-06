<?php
require_once 'db_config.php';

try {
    // 获取所有待审核记录
    $stmt = $pdo->query("SELECT Pending_review_ID, Product_Type FROM pending_review WHERE Product_Type LIKE '%,%'");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $record) {
        // 分割产品ID
        $productIds = explode(',', $record['Product_Type']);

        // 获取产品信息
        $stmt = $pdo->prepare("SELECT Product_ID, Product_Name FROM product WHERE Product_ID = ?");
        $stmt->execute([$productIds[0]]);
        $product1 = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt->execute([$productIds[1]]);
        $product2 = $stmt->fetch(PDO::FETCH_ASSOC);

        // 创建新的JSON格式数据
        $productTypeData = [
            'type' => 'commercial',
            'products' => [
                'third_party' => [
                    'id' => $product1['Product_ID'],
                    'name' => $product1['Product_Name']
                ],
                'passenger' => [
                    'id' => $product2['Product_ID'],
                    'name' => $product2['Product_Name']
                ]
            ]
        ];

        // 更新记录
        $updateStmt = $pdo->prepare("
            UPDATE pending_review 
            SET Product_Type = ? 
            WHERE Pending_review_ID = ?
        ");

        $updateStmt->execute([
            json_encode($productTypeData),
            $record['Pending_review_ID']
        ]);

        echo "Updated record {$record['Pending_review_ID']}\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
