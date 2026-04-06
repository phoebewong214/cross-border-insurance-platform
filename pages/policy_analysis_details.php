<?php
// 检查会话是否已启动
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 检查是否有必要的参数
$hasParameters = isset($_GET['coverage']) && isset($_GET['excess']);

// 只有在有参数时才连接数据库
if ($hasParameters) {
    // Database connection configuration
    $link = @mysqli_connect('localhost', 'root', '', 'insurance_system');
    if (!$link) {
        die("Connection failed: " . mysqli_connect_error());
    }
    mysqli_query($link, 'SET NAMES utf8');

    // 获取筛选参数
    $coverage = $_GET['coverage'];
    $excess = $_GET['excess'];

    // 构建查询条件
    $coverageCondition = "";
    switch($coverage) {
        case 'High':
            $coverageCondition = "q.Total_Coverage >= 10000000";
            break;
        case 'Medium':
            $coverageCondition = "q.Total_Coverage >= 5000000 AND q.Total_Coverage < 10000000";
            break;
        case 'Low':
            $coverageCondition = "q.Total_Coverage < 5000000";
            break;
    }

    $excessCondition = "";
    switch($excess) {
        case 'High':
            $excessCondition = "q.Final_Excess >= 8000";
            break;
        case 'Medium':
            $excessCondition = "q.Final_Excess >= 4000 AND q.Final_Excess < 8000";
            break;
        case 'Low':
            $excessCondition = "q.Final_Excess < 4000";
            break;
    }

    // 查询数据
    $query = "
        SELECT 
            p.Policy_No,
            p.Policy_Issue_Date,
            p.Policy_Status,
            q.Total_Coverage,
            q.Final_Excess,
            q.Total_Premium
        FROM policy p
        LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
        WHERE $coverageCondition AND $excessCondition
        ORDER BY p.Policy_Issue_Date DESC";

    $result = mysqli_query($link, $query);
    $policies = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $policies[] = $row;
    }

    mysqli_close($link);

    // 在查询数据后添加分析逻辑
    $totalPolicies = count($policies);
    $totalCoverage = array_sum(array_column($policies, 'Total_Coverage'));
    $totalExcess = array_sum(array_column($policies, 'Final_Excess'));
    $totalPremium = array_sum(array_column($policies, 'Total_Premium'));

    // 计算平均保费和风险比率
    $averagePremium = $totalPolicies > 0 ? $totalPremium / $totalPolicies : 0;
    $riskRatio = $totalPolicies > 0 ? $totalExcess / $totalCoverage : 0;

    // 生成分析建议
    $analysis = [];
    $riskLevel = '';

    // 基于覆盖范围和超额水平的风险等级判断
    if ($coverage === 'High' && $excess === 'High') {
        $riskLevel = 'High Risk';
        $analysis[] = [
            'title' => 'High Risk Portfolio Alert',
            'content' => 'Current portfolio has high coverage and high excess characteristics, recommendations:',
            'suggestions' => [
                'Consider increasing excess amount to reduce underwriting risk',
                'Strengthen risk assessment process for high-value policies',
                'Recommend risk diversification to avoid large single policy amounts',
                'Consider introducing reinsurance mechanism'
            ]
        ];
    } elseif ($coverage === 'High' && $excess === 'Medium') {
        $riskLevel = 'Medium-High Risk';
        $analysis[] = [
            'title' => 'Medium-High Risk Portfolio Analysis',
            'content' => 'Current portfolio has high coverage and medium excess characteristics, recommendations:',
            'suggestions' => [
                'Evaluate excess level rationality and consider appropriate increase',
                'Strengthen customer credit assessment',
                'Recommend implementing stricter risk control measures',
                'Consider increasing guarantee requirements'
            ]
        ];
    } elseif ($coverage === 'Medium' && $excess === 'High') {
        $riskLevel = 'Medium Risk';
        $analysis[] = [
            'title' => 'Medium Risk Portfolio Analysis',
            'content' => 'Current portfolio has medium coverage and high excess characteristics, recommendations:',
            'suggestions' => [
                'Evaluate excess level rationality and consider appropriate reduction for competitiveness',
                'Strengthen customer service to improve renewal rate',
                'Consider expanding coverage scope',
                'Recommend optimizing pricing strategy'
            ]
        ];
    } else {
        $riskLevel = 'Low Risk';
        $analysis[] = [
            'title' => 'Low Risk Portfolio Analysis',
            'content' => 'Current portfolio has low risk characteristics, recommendations:',
            'suggestions' => [
                'Consider reducing excess to improve market competitiveness',
                'Can expand coverage scope to increase business volume',
                'Recommend strengthening customer relationship management',
                'Consider launching more value-added services'
            ]
        ];
    }

    // 基于保费分析
    if ($averagePremium > 10000) {
        $analysis[] = [
            'title' => 'High Premium Analysis',
            'content' => 'Current average premium is high, recommendations:',
            'suggestions' => [
                'Evaluate pricing strategy rationality',
                'Consider introducing installment payment options',
                'Can design more flexible premium plans',
                'Recommend strengthening high-value customer service'
            ]
        ];
    }

    // 基于风险比率分析
    if ($riskRatio > 0.1) {
        $analysis[] = [
            'title' => 'Risk Ratio Analysis',
            'content' => 'Current risk ratio is high, recommendations:',
            'suggestions' => [
                'Re-evaluate excess settings',
                'Strengthen risk control measures',
                'Consider adjusting underwriting strategy',
                'Recommend increasing risk reserves'
            ]
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }

        .header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, #36A2EB, #4BC0C0);
        }

        .title {
            font-size: 32px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 18px;
            color: #666;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #2c3e50;
            font-weight: 500;
        }

        .table td {
            vertical-align: middle;
        }

        .back-btn {
            background: linear-gradient(to right, #36A2EB, #4BC0C0);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .navbar .admin-text {
            color: #1a237e;
            font-weight: 700;
        }

        .navbar .btn-light {
            color: #2c3e50;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .navbar .btn-light:hover {
            background-color: #f8f9fa;
            color: #36A2EB;
            transform: translateY(-1px);
        }

        .navbar .btn-light i {
            margin-right: 0.5rem;
        }

        .analysis-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .analysis-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f3f5;
        }

        .analysis-title {
            font-size: 24px;
            font-weight: 500;
            color: #2c3e50;
            margin: 0;
        }

        .risk-level {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 16px;
        }

        .risk-level.high-risk {
            background-color: #ffebee;
            color: #c62828;
        }

        .risk-level.medium-high-risk {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .risk-level.medium-risk {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .risk-level.low-risk {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .analysis-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .analysis-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .analysis-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .analysis-card-title {
            font-size: 18px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .analysis-card-content {
            color: #666;
            margin-bottom: 15px;
        }

        .analysis-suggestions {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .analysis-suggestions li {
            padding: 8px 0;
            color: #2c3e50;
            position: relative;
            padding-left: 20px;
        }

        .analysis-suggestions li:before {
            content: '•';
            color: #36A2EB;
            position: absolute;
            left: 0;
        }

        @media (max-width: 768px) {
            .analysis-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <div class="navbar-brand admin-text">Admin Center</div>
            <div class="ms-auto">
                <a href="DataAnalysisCatalog.php" class="btn btn-light me-2">
                    <i class="fas fa-th-large me-1"></i>Back to Catalog
                </a>
                <a href="logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1 class="title">Policy Details</h1>
            <?php if ($hasParameters): ?>
            <div class="subtitle">
                Coverage Level: <?php echo $coverage; ?> | Excess Level: <?php echo $excess; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($hasParameters): ?>
        <a href="policy_analysis.php" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to Analysis
        </a>

        <!-- Analysis Section -->
        <div class="analysis-section mb-4">
            <div class="analysis-header">
                <h2 class="analysis-title">Business Analysis & Recommendations</h2>
                <div class="risk-level <?php echo strtolower(str_replace(' ', '-', $riskLevel)); ?>">
                    <?php echo $riskLevel; ?>
                </div>
            </div>
            
            <div class="analysis-grid">
                <?php foreach ($analysis as $item): ?>
                <div class="analysis-card">
                    <h3 class="analysis-card-title"><?php echo $item['title']; ?></h3>
                    <p class="analysis-card-content"><?php echo $item['content']; ?></p>
                    <ul class="analysis-suggestions">
                        <?php foreach ($item['suggestions'] as $suggestion): ?>
                        <li><?php echo $suggestion; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Policy No</th>
                        <th>Issue Date</th>
                        <th>Status</th>
                        <th>Total Coverage</th>
                        <th>Final Excess</th>
                        <th>Total Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($policies as $policy): ?>
                    <tr>
                        <td><?php echo $policy['Policy_No']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($policy['Policy_Issue_Date'])); ?></td>
                        <td><?php echo $policy['Policy_Status']; ?></td>
                        <td>$<?php echo number_format($policy['Total_Coverage'], 2); ?></td>
                        <td>$<?php echo number_format($policy['Final_Excess'], 2); ?></td>
                        <td>$<?php echo number_format($policy['Total_Premium'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state text-center py-5">
            <i class="fas fa-chart-bar fa-3x mb-3 text-muted"></i>
            <h3 class="text-muted">No Data Available</h3>
            <p class="text-muted">Please access this page through the Policy Analysis dashboard.</p>
            <a href="policy_analysis.php" class="btn btn-primary mt-3">
                <i class="fas fa-arrow-left me-2"></i>Go to Policy Analysis
            </a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html> 