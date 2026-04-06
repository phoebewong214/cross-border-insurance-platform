<?php
// 检查会话是否已启动
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection configuration
$link = @mysqli_connect('localhost', 'root', '', 'insurance_system');
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($link, 'SET NAMES utf8');

// 获取筛选条件
$selectedQuarter = isset($_GET['quarter']) ? (is_array($_GET['quarter']) ? $_GET['quarter'] : [$_GET['quarter']]) : [];
$selectedYear = isset($_GET['year']) ? (is_array($_GET['year']) ? $_GET['year'] : [$_GET['year']]) : [];
$selectedStatus = isset($_GET['status']) ? (is_array($_GET['status']) ? $_GET['status'] : [$_GET['status']]) : [];
$selectedQuotationId = isset($_GET['quotation_id']) ? $_GET['quotation_id'] : '';
$selectedProposalId = isset($_GET['proposal_id']) ? $_GET['proposal_id'] : '';
$selectedMonth = isset($_GET['month']) ? (is_array($_GET['month']) ? $_GET['month'] : [$_GET['month']]) : [];

// 构建WHERE子句
$conditions = [];
if (!empty($selectedYear)) {
    $yearConditions = array_map(function ($year) use ($link) {
        return "YEAR(Policy_Issue_Date) = '" . mysqli_real_escape_string($link, $year) . "'";
    }, $selectedYear);
    $conditions[] = "(" . implode(" OR ", $yearConditions) . ")";
}

if (!empty($selectedQuarter)) {
    $quarterConditions = array_map(function ($quarter) use ($link) {
        return "QUARTER(Policy_Issue_Date) = '" . mysqli_real_escape_string($link, $quarter) . "'";
    }, $selectedQuarter);
    $conditions[] = "(" . implode(" OR ", $quarterConditions) . ")";
}

if (!empty($selectedMonth)) {
    $monthConditions = array_map(function ($month) use ($link) {
        return "MONTH(Policy_Issue_Date) = '" . mysqli_real_escape_string($link, $month) . "'";
    }, $selectedMonth);
    $conditions[] = "(" . implode(" OR ", $monthConditions) . ")";
}

if (!empty($selectedStatus)) {
    $statusConditions = array_map(function ($status) use ($link) {
        return "Policy_Status = '" . mysqli_real_escape_string($link, $status) . "'";
    }, $selectedStatus);
    $conditions[] = "(" . implode(" OR ", $statusConditions) . ")";
}

if (!empty($selectedQuotationId)) {
    $conditions[] = "Quotation_ID = '$selectedQuotationId'";
}
if (!empty($selectedProposalId)) {
    $conditions[] = "Proposal_ID = '$selectedProposalId'";
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// 获取季度保单数量数据
$quarterlyPolicyQuery = "
    SELECT 
        YEAR(Policy_Issue_Date) as year,
        QUARTER(Policy_Issue_Date) as quarter,
        COUNT(Policy_No) as policy_count
    FROM policy
    $whereClause
    GROUP BY YEAR(Policy_Issue_Date), QUARTER(Policy_Issue_Date)
    ORDER BY year, quarter";

$result = mysqli_query($link, $quarterlyPolicyQuery);
$quarterlyPolicyData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quarterlyPolicyData[] = $row;
}

// 获取季度保费总额数据
$quarterlyPremiumQuery = "
    SELECT 
        YEAR(p.Policy_Issue_Date) as year,
        QUARTER(p.Policy_Issue_Date) as quarter,
        SUM(q.Total_Premium) as total_premium
    FROM policy p
    LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
    $whereClause
    GROUP BY YEAR(p.Policy_Issue_Date), QUARTER(p.Policy_Issue_Date)
    ORDER BY year, quarter";

$result = mysqli_query($link, $quarterlyPremiumQuery);
$quarterlyPremiumData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quarterlyPremiumData[] = $row;
}

// 计算风险分布数据
$riskDistributionQuery = "
    SELECT 
        CASE 
            WHEN q.Total_Coverage >= 10000000 THEN 'High'
            WHEN q.Total_Coverage >= 5000000 THEN 'Medium'
            ELSE 'Low'
        END as coverage_level,
        CASE 
            WHEN q.Final_Excess >= 8000 THEN 'High'
            WHEN q.Final_Excess >= 4000 THEN 'Medium'
            ELSE 'Low'
        END as excess_level,
        COUNT(*) as policy_count,
        SUM(q.Total_Coverage) as total_coverage,
        SUM(q.Final_Excess) as total_excess
    FROM policy p
    LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
    $whereClause
    GROUP BY coverage_level, excess_level";

$result = mysqli_query($link, $riskDistributionQuery);
$riskDistributionData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $riskDistributionData[] = $row;
}

// 计算总风险
$totalRiskQuery = "
    SELECT 
        SUM(q.Total_Coverage) as total_coverage,
        SUM(q.Final_Excess) as total_excess,
        SUM(q.Total_Premium) as total_premium,
        SUM(p.Claim_Amount) as total_claims,
        COUNT(*) as total_policies,
        COUNT(CASE WHEN p.Claim_Amount > 0 THEN 1 END) as number_of_claims
    FROM policy p
    LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
    $whereClause";

$result = mysqli_query($link, $totalRiskQuery);
$totalRiskData = mysqli_fetch_assoc($result);
$totalRisk = ($totalRiskData['total_coverage'] ?? 0) - ($totalRiskData['total_excess'] ?? 0);
$totalCoverage = $totalRiskData['total_coverage'] ?? 0;
$totalPremium = $totalRiskData['total_premium'] ?? 0;
$totalClaims = $totalRiskData['total_claims'] ?? 0;
$totalPolicies = $totalRiskData['total_policies'] ?? 0;
$numberOfClaims = $totalRiskData['number_of_claims'] ?? 0;

$leverage = $totalPremium > 0 ? round($totalCoverage / $totalPremium, 2) : 0;
$lossRatio = $totalPremium > 0 ? round(($totalClaims / $totalPremium) * 100, 2) : 0;
$sumInsuredLossRatio = $totalCoverage > 0 ? round(($totalClaims / $totalCoverage) * 100, 2) : 0;
$claimsFrequency = $totalPolicies > 0 ? round(($numberOfClaims / $totalPolicies) * 100, 2) : 0;
$claimsSeverity = $numberOfClaims > 0 ? round($totalClaims / $numberOfClaims, 2) : 0;

// 获取所有可用的筛选选项
$availableYears = [];
$yearQuery = "SELECT DISTINCT YEAR(Policy_Issue_Date) as year FROM policy ORDER BY year";
$result = mysqli_query($link, $yearQuery);
while ($row = mysqli_fetch_assoc($result)) {
    $availableYears[] = $row['year'];
}

// 修改获取可用状态的查询
$availableStatus = [];
$statusQuery = "SELECT DISTINCT Policy_Status FROM policy WHERE Policy_Status IS NOT NULL";
$result = mysqli_query($link, $statusQuery);
while ($row = mysqli_fetch_assoc($result)) {
    $availableStatus[] = $row['Policy_Status'];
}

// 获取月度数据
$monthlyPolicyQuery = "
    SELECT 
        YEAR(Policy_Issue_Date) as year,
        MONTH(Policy_Issue_Date) as month,
        COUNT(Policy_No) as policy_count
    FROM policy
    $whereClause
    GROUP BY YEAR(Policy_Issue_Date), MONTH(Policy_Issue_Date)
    ORDER BY year, month";

$result = mysqli_query($link, $monthlyPolicyQuery);
$monthlyPolicyData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $monthlyPolicyData[] = $row;
}

$monthlyPremiumQuery = "
    SELECT 
        YEAR(p.Policy_Issue_Date) as year,
        MONTH(p.Policy_Issue_Date) as month,
        SUM(q.Total_Premium) as total_premium
    FROM policy p
    LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
    $whereClause
    GROUP BY YEAR(p.Policy_Issue_Date), MONTH(p.Policy_Issue_Date)
    ORDER BY year, month";

$result = mysqli_query($link, $monthlyPremiumQuery);
$monthlyPremiumData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $monthlyPremiumData[] = $row;
}

// 獲取保單分析數據
$policyAnalysisQuery = "
    SELECT 
        p.Policy_No,
        p.Policy_Issue_Date,
        p.Claim_Amount,
        q.Total_Premium,
        CASE 
            WHEN q.Total_Premium > 0 THEN (p.Claim_Amount / q.Total_Premium) * 100
            ELSE NULL
        END as Loss_Ratio
    FROM policy p
    LEFT JOIN quotation q ON p.Quotation_ID = q.Quotation_ID
    $whereClause
    ORDER BY Loss_Ratio DESC";

$result = mysqli_query($link, $policyAnalysisQuery);
$policyAnalysisData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $policyAnalysisData[] = $row;
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Analysis</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script>
        Chart.register(ChartDataLabels);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .dashboard {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }

        .dashboard-header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, #36A2EB, #4BC0C0);
        }

        .dashboard-title {
            font-size: 32px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .filter-title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f5;
        }

        .chart-title {
            font-size: 20px;
            font-weight: 500;
            color: #2c3e50;
            margin: 0;
        }

        .chart-container {
            height: 400px;
            position: relative;
            width: 100%;
        }

        .apply-btn {
            background: linear-gradient(to right, #36A2EB, #4BC0C0);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            width: auto;
            min-width: 200px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .risk-info {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .risk-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .risk-row:first-child,
        .risk-row:nth-child(2) {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .risk-item {
            flex: 1;
            text-align: center;
            padding: 0 20px;
            position: relative;
        }

        .risk-item:not(:last-child) {
            border-right: 1px solid #e2e8f0;
        }

        .risk-title {
            font-size: 18px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 15px;
            cursor: help;
        }

        .risk-value {
            font-size: 24px;
            font-weight: 700;
            color: #36A2EB;
        }

        .risk-tooltip {
            display: none;
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 8px;
            width: 300px;
            z-index: 1000;
            left: 50%;
            transform: translateX(-50%);
            bottom: 100%;
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.5;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .risk-tooltip::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 10px 10px 0;
            border-style: solid;
            border-color: rgba(0, 0, 0, 0.8) transparent transparent;
        }

        .risk-title:hover+.risk-tooltip {
            display: block;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 350px;
            }
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 300px;
            }
        }

        .filter-dropdown {
            position: relative;
            width: 100%;
        }

        .filter-button {
            width: 100%;
            padding: 10px 15px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            color: #2c3e50;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            align-items: center;
            min-height: 42px;
        }

        .filter-button .selected-item {
            background-color: #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
            color: #2c3e50;
            font-size: 13px;
            display: inline-block;
            margin: 2px;
        }

        .filter-button:after {
            content: '▼';
            font-size: 12px;
            margin-left: auto;
        }

        .filter-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 5px;
        }

        .filter-dropdown.active .filter-dropdown-content {
            display: block;
        }

        .filter-option-item {
            padding: 8px 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .filter-option-item:hover {
            background: #f8f9fa;
        }

        .filter-option-item input[type="checkbox"] {
            margin-right: 10px;
            width: 16px;
            height: 16px;
        }

        .filter-actions {
            padding: 10px 15px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
        }

        .filter-confirm,
        .filter-clear {
            padding: 5px 10px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 13px;
        }

        .filter-confirm {
            background: #36A2EB;
            color: white;
        }

        .filter-clear {
            background: #f1f3f5;
            color: #4a5568;
            margin-right: 10px;
        }

        .selected-count {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 8px;
        }

        /* 修改导航栏样式 */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .navbar .admin-text {
            color: #1a237e;
            /* 深蓝色 */
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

        .time-dimension-dropdown {
            position: relative;
        }

        .time-dimension-btn {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .time-dimension-btn:hover {
            background: #f8f9fa;
        }

        .time-dimension-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 120px;
        }

        .time-dimension-dropdown.active .time-dimension-content {
            display: block;
        }

        .time-dimension-option {
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .time-dimension-option:hover {
            background: #f8f9fa;
        }

        .time-dimension-option.active {
            background: #e3f2fd;
            color: #1565c0;
        }

        .strategy-section {
            margin-top: 40px;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 24px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f5;
        }

        .strategy-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .strategy-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #36A2EB;
            margin-bottom: 10px;
        }

        .strategy-title {
            font-size: 18px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .strategy-content {
            color: #4a5568;
            line-height: 1.6;
        }

        .policy-analysis-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .policy-analysis-section .section-title {
            font-size: 24px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .policy-analysis-section .table {
            margin-bottom: 0;
        }

        .policy-analysis-section .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #2c3e50;
            font-weight: 500;
        }

        .policy-analysis-section .table td {
            vertical-align: middle;
        }

        .policy-analysis-section .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .policy-analysis-section .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-1px);
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
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

    <div class="dashboard">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Policy Analysis Dashboard</h1>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h2 class="filter-title">Filter Options</h2>
            <form id="filterForm" method="GET">
                <div class="filter-grid">
                    <div class="filter-item">
                        <label class="filter-label">Year</label>
                        <div class="filter-dropdown" id="yearDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedYear) ? implode(', ', $selectedYear) : 'Select year'; ?>
                                <span style="margin-left: auto;">▼</span>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllYears" />
                                    <label for="selectAllYears">All</label>
                                </div>
                                <?php foreach ($availableYears as $year): ?>
                                    <div class="filter-option-item">
                                        <input type="checkbox" name="year[]" value="<?php echo $year; ?>"
                                            <?php echo in_array($year, $selectedYear) ? 'checked' : ''; ?> />
                                        <label><?php echo $year; ?></label>
                                    </div>
                                <?php endforeach; ?>
                                <div class="filter-actions">
                                    <div>
                                        <button type="button" class="filter-clear">Clear</button>
                                        <button type="button" class="filter-confirm">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="filter-item">
                        <label class="filter-label">Quarter</label>
                        <div class="filter-dropdown" id="quarterDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedQuarter) ? implode(', ', $selectedQuarter) : 'Select quarter'; ?>
                                <span style="margin-left: auto;">▼</span>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllQuarters" />
                                    <label for="selectAllQuarters">All</label>
                                </div>
                                <div class="filter-option-item">
                                    <input type="checkbox" name="quarter[]" value="1"
                                        <?php echo in_array('1', $selectedQuarter) ? 'checked' : ''; ?> />
                                    <label>Q1</label>
                                </div>
                                <div class="filter-option-item">
                                    <input type="checkbox" name="quarter[]" value="2"
                                        <?php echo in_array('2', $selectedQuarter) ? 'checked' : ''; ?> />
                                    <label>Q2</label>
                                </div>
                                <div class="filter-option-item">
                                    <input type="checkbox" name="quarter[]" value="3"
                                        <?php echo in_array('3', $selectedQuarter) ? 'checked' : ''; ?> />
                                    <label>Q3</label>
                                </div>
                                <div class="filter-option-item">
                                    <input type="checkbox" name="quarter[]" value="4"
                                        <?php echo in_array('4', $selectedQuarter) ? 'checked' : ''; ?> />
                                    <label>Q4</label>
                                </div>
                                <div class="filter-actions">
                                    <div>
                                        <button type="button" class="filter-clear">Clear</button>
                                        <button type="button" class="filter-confirm">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="filter-item">
                        <label class="filter-label">Month</label>
                        <div class="filter-dropdown" id="monthDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedMonth) ? implode(', ', $selectedMonth) : 'Select month'; ?>
                                <span style="margin-left: auto;">▼</span>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllMonths" />
                                    <label for="selectAllMonths">All</label>
                                </div>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <div class="filter-option-item">
                                        <input type="checkbox" name="month[]" value="<?php echo $i; ?>"
                                            <?php echo in_array($i, $selectedMonth) ? 'checked' : ''; ?> />
                                        <label><?php echo date('F', mktime(0, 0, 0, $i, 1)); ?></label>
                                    </div>
                                <?php endfor; ?>
                                <div class="filter-actions">
                                    <div>
                                        <button type="button" class="filter-clear">Clear</button>
                                        <button type="button" class="filter-confirm">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="filter-item">
                        <label class="filter-label">Policy Status</label>
                        <div class="filter-dropdown" id="statusDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedStatus) ? implode(', ', $selectedStatus) : 'Select status'; ?>
                                <span style="margin-left: auto;">▼</span>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllStatus" />
                                    <label for="selectAllStatus">All</label>
                                </div>
                                <?php foreach ($availableStatus as $status): ?>
                                    <div class="filter-option-item">
                                        <input type="checkbox" name="status[]" value="<?php echo $status; ?>"
                                            <?php echo in_array($status, $selectedStatus) ? 'checked' : ''; ?> />
                                        <label><?php echo $status; ?></label>
                                    </div>
                                <?php endforeach; ?>
                                <div class="filter-actions">
                                    <div>
                                        <button type="button" class="filter-clear">Clear</button>
                                        <button type="button" class="filter-confirm">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="apply-btn">Apply Filters</button>
            </form>
        </div>

        <!-- Risk Information -->
        <div class="risk-info">
            <div class="risk-row">
                <div class="risk-item">
                    <h2 class="risk-title">Total Risk Exposure</h2>
                    <div class="risk-tooltip">
                        <strong>Formula:</strong> Total Coverage - Total Excess<br>
                        <strong>Business Meaning:</strong> Represents the actual risk exposure undertaken by the insurance company, which is the net risk after deducting the excess from the total coverage
                    </div>
                    <div class="risk-value">
                        $<?php echo number_format($totalRisk, 2); ?>
                    </div>
                </div>
                <div class="risk-item">
                    <h2 class="risk-title">Total Claims Amount</h2>
                    <div class="risk-tooltip">
                        <strong>Formula:</strong> Sum of all claim amounts<br>
                        <strong>Business Meaning:</strong> Represents the total amount of claims paid out by the insurance company, reflecting the actual cost of insurance risks
                    </div>
                    <div class="risk-value">
                        $<?php echo number_format($totalClaims, 2); ?>
                    </div>
                </div>
            </div>
            <div class="risk-row">
                <div class="risk-item">
                    <h2 class="risk-title">Total Insurance Loss Ratio</h2>
                    <div class="risk-tooltip">
                        <strong>Formula:</strong> (Total Claims / Total Premium) × 100%<br>
                        <strong>Business Meaning:</strong> Represents the premium loss ratio, measuring the proportion of premium income used for claims, reflecting the profitability of insurance business
                    </div>
                    <div class="risk-value">
                        <?php echo $lossRatio; ?>%
                    </div>
                </div>
                <div class="risk-item">
                    <h2 class="risk-title">Total Leverage</h2>
                    <div class="risk-tooltip">
                        <strong>Formula:</strong> Total Coverage / Total Premium<br>
                        <strong>Business Meaning:</strong> Represents the premium leverage ratio, measuring the amount of coverage per unit of premium, reflecting the insurance company's risk-taking efficiency
                    </div>
                    <div class="risk-value">
                        <?php echo $leverage; ?>x
                    </div>
                </div>
            </div>
            <div class="risk-row">
                <div class="risk-item">
                    <h2 class="risk-title">Claims Frequency</h2>
                    <div class="risk-tooltip">
                        <strong>Formula:</strong> (Number of Claims / Total Policies) × 100%<br>
                        <strong>Business Meaning:</strong> Represents the claims frequency, measuring the proportion of policies that resulted in claims, reflecting the probability of insurance risks occurring
                    </div>
                    <div class="risk-value">
                        <?php echo $claimsFrequency; ?>%
                    </div>
                </div>
                <div class="risk-item">
                    <h2 class="risk-title">Claims Severity</h2>
                    <div class="risk-tooltip">
                        <strong>Formula:</strong> Total Claims / Number of Claims<br>
                        <strong>Business Meaning:</strong> Represents the claims severity, measuring the average amount per claim, reflecting the impact level of insurance risks
                    </div>
                    <div class="risk-value">
                        $<?php echo number_format($claimsSeverity, 2); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">Policy Count</h2>
                    <div class="time-dimension-dropdown" id="policyTimeDimension">
                        <button class="time-dimension-btn">
                            <span id="policyTimeText">Quarter</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="time-dimension-content">
                            <div class="time-dimension-option active" data-value="quarter">Quarter</div>
                            <div class="time-dimension-option" data-value="year">Year</div>
                            <div class="time-dimension-option" data-value="month">Month</div>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="policyCountChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">Premium Total</h2>
                    <div class="time-dimension-dropdown" id="premiumTimeDimension">
                        <button class="time-dimension-btn">
                            <span id="premiumTimeText">Quarter</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="time-dimension-content">
                            <div class="time-dimension-option active" data-value="quarter">Quarter</div>
                            <div class="time-dimension-option" data-value="year">Year</div>
                            <div class="time-dimension-option" data-value="month">Month</div>
                        </div>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="premiumChart"></canvas>
                </div>
            </div>

            <div class="chart-card" style="grid-column: span 2;">
                <div class="chart-header">
                    <h2 class="chart-title">Risk Distribution Heatmap</h2>
                </div>
                <div class="chart-container">
                    <canvas id="riskHeatmap"></canvas>
                </div>
            </div>
        </div>

        <!-- Policy Analysis Table Section -->
        <div class="policy-analysis-section mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title">Policy Loss Ratio Analysis</h3>
                <div>
                    <button type="button" class="btn btn-success" onclick="exportPolicyToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export to Excel
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="policyAnalysisTable">
                    <thead>
                        <tr>
                            <th>Policy No.</th>
                            <th>Issue Date</th>
                            <th>Claim Amount</th>
                            <th>Premium</th>
                            <th>Loss Ratio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($policyAnalysisData as $policy): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($policy['Policy_No']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($policy['Policy_Issue_Date'])); ?></td>
                                <td>$<?php echo number_format($policy['Claim_Amount'], 2); ?></td>
                                <td>$<?php echo number_format($policy['Total_Premium'], 2); ?></td>
                                <td><?php echo $policy['Loss_Ratio'] !== null ? number_format($policy['Loss_Ratio'], 2) . '%' : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 添加策略建议部分 -->
        <div class="strategy-section">
            <h2 class="section-title">Policy Strategy Recommendations</h2>
            <div class="strategy-cards" id="strategyContainer">
                <!-- 建议将通过JavaScript动态生成 -->
            </div>
        </div>
    </div>

    <script>
        // 初始化Select2
        $(document).ready(function() {
            $('select[multiple]').select2({
                placeholder: "Select status",
                allowClear: true
            });
        });

        // 计算环比变化
        function calculateGrowthRate(current, previous) {
            if (!previous || previous === 0) return null;
            return ((current - previous) / previous * 100).toFixed(1);
        }

        // 格式化标签
        function formatLabel(item, dimension) {
            switch (dimension) {
                case 'quarter':
                    return `Q${item.quarter} ${item.year}`;
                case 'year':
                    return item.year.toString();
                case 'month':
                    return `${item.month}/${item.year}`;
                default:
                    return '';
            }
        }

        // 创建图表数据
        function createChartData(data, dimension, valueKey) {
            const sortedData = [...data].sort((a, b) => {
                if (a.year !== b.year) return a.year - b.year;
                if (dimension === 'quarter') return a.quarter - b.quarter;
                if (dimension === 'month') return a.month - b.month;
                return 0;
            });

            return {
                labels: sortedData.map(item => formatLabel(item, dimension)),
                datasets: [{
                    label: dimension === 'quarter' ? 'Quarterly' : dimension === 'year' ? 'Yearly' : 'Monthly',
                    data: sortedData.map(item => item[valueKey]),
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            };
        }

        // 初始化图表
        let policyCountChart = new Chart(document.getElementById('policyCountChart'), {
            type: 'line',
            data: createChartData(<?php echo json_encode($quarterlyPolicyData); ?>, 'quarter', 'policy_count'),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const data = <?php echo json_encode($quarterlyPolicyData); ?>;
                                const currentIndex = context.dataIndex;
                                const previousValue = currentIndex > 0 ? data[currentIndex - 1].policy_count : null;
                                const growthRate = calculateGrowthRate(value, previousValue);

                                let label = `Count: ${value}`;
                                if (growthRate !== null) {
                                    const sign = growthRate >= 0 ? '+' : '';
                                    label += ` (${sign}${growthRate}%)`;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        let premiumChart = new Chart(document.getElementById('premiumChart'), {
            type: 'line',
            data: createChartData(<?php echo json_encode($quarterlyPremiumData); ?>, 'quarter', 'total_premium'),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const data = <?php echo json_encode($quarterlyPremiumData); ?>;
                                const currentIndex = context.dataIndex;
                                const previousValue = currentIndex > 0 ? data[currentIndex - 1].total_premium : null;
                                const growthRate = calculateGrowthRate(value, previousValue);

                                let label = `Premium: $${value.toLocaleString()}`;
                                if (growthRate !== null) {
                                    const sign = growthRate >= 0 ? '+' : '';
                                    label += ` (${sign}${growthRate}%)`;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // 时间维度切换功能
        document.querySelectorAll('.time-dimension-dropdown').forEach(dropdown => {
            const button = dropdown.querySelector('.time-dimension-btn');
            const content = dropdown.querySelector('.time-dimension-content');
            const options = dropdown.querySelectorAll('.time-dimension-option');
            const textSpan = dropdown.querySelector('span');

            button.addEventListener('click', () => {
                dropdown.classList.toggle('active');
            });

            options.forEach(option => {
                option.addEventListener('click', () => {
                    const value = option.dataset.value;
                    const chartId = dropdown.id === 'policyTimeDimension' ? 'policyCountChart' : 'premiumChart';
                    const chart = Chart.getChart(chartId);

                    // 更新按钮文本
                    textSpan.textContent = option.textContent;

                    // 根据选择的时间维度获取对应的数据
                    let data;
                    if (chartId === 'policyCountChart') {
                        switch (value) {
                            case 'quarter':
                                data = <?php echo json_encode($quarterlyPolicyData); ?>;
                                break;
                            case 'year':
                                // 将季度数据聚合为年度数据
                                data = <?php echo json_encode($quarterlyPolicyData); ?>.reduce((acc, curr) => {
                                    const existingYear = acc.find(item => item.year === curr.year);
                                    if (existingYear) {
                                        existingYear.policy_count += curr.policy_count;
                                    } else {
                                        acc.push({
                                            year: curr.year,
                                            policy_count: curr.policy_count
                                        });
                                    }
                                    return acc;
                                }, []);
                                break;
                            case 'month':
                                data = <?php echo json_encode($monthlyPolicyData); ?>;
                                break;
                        }
                    } else {
                        switch (value) {
                            case 'quarter':
                                data = <?php echo json_encode($quarterlyPremiumData); ?>;
                                break;
                            case 'year':
                                data = <?php echo json_encode($quarterlyPremiumData); ?>.reduce((acc, curr) => {
                                    if (!curr || !curr.year) return acc;
                                    const existingYear = acc.find(item => item.year === curr.year);
                                    if (existingYear) {
                                        existingYear.total_premium = (parseFloat(existingYear.total_premium) || 0) + (parseFloat(curr.total_premium) || 0);
                                    } else {
                                        acc.push({
                                            year: curr.year,
                                            total_premium: parseFloat(curr.total_premium) || 0
                                        });
                                    }
                                    return acc;
                                }, []).sort((a, b) => a.year - b.year);
                                break;
                            case 'month':
                                data = <?php echo json_encode($monthlyPremiumData); ?>;
                                break;
                        }
                    }

                    const valueKey = chartId === 'policyCountChart' ? 'policy_count' : 'total_premium';
                    chart.data = createChartData(data, value, valueKey);
                    chart.update();

                    // 更新选项状态
                    options.forEach(opt => opt.classList.remove('active'));
                    option.classList.add('active');

                    // 关闭下拉菜单
                    dropdown.classList.remove('active');
                });
            });
        });

        // 点击其他地方关闭下拉菜单
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.time-dimension-dropdown')) {
                document.querySelectorAll('.time-dimension-dropdown').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });

        // 创建热力图数据
        const riskData = <?php echo json_encode($riskDistributionData); ?>;

        // 创建热力图数据矩阵
        const heatmapData = [];
        const levels = ['Low', 'Medium', 'High'];
        const levelValues = {
            'Low': 0,
            'Medium': 1,
            'High': 2
        };

        // 初始化数据矩阵
        riskData.forEach(item => {
            if (item.coverage_level && item.excess_level) {
                heatmapData.push({
                    x: levelValues[item.coverage_level],
                    y: levelValues[item.excess_level],
                    r: Math.sqrt(parseInt(item.policy_count) || 0) * 10,
                    value: parseInt(item.policy_count) || 0,
                    coverage: item.coverage_level,
                    excess: item.excess_level
                });
            }
        });

        const riskHeatmap = new Chart(document.getElementById('riskHeatmap'), {
            type: 'bubble',
            data: {
                datasets: [{
                    label: 'Risk Distribution',
                    data: heatmapData,
                    backgroundColor: function(context) {
                        const value = context.raw?.value || 0;
                        const maxValue = Math.max(...heatmapData.map(d => d.value));
                        const alpha = maxValue > 0 ? value / maxValue : 0;
                        return `rgba(54, 162, 235, ${alpha})`;
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const data = context.raw;
                                const riskLevel = data.coverage === 'High' && data.excess === 'High' ? 'High Risk' :
                                    data.coverage === 'High' && data.excess === 'Medium' ? 'Medium-High Risk' :
                                    data.coverage === 'Medium' && data.excess === 'High' ? 'Medium Risk' :
                                    'Low Risk';
                                return [
                                    `Risk Level: ${riskLevel}`,
                                    `Policy Count: ${data.value}`
                                ];
                            }
                        }
                    },
                    datalabels: {
                        color: (context) => {
                            const value = context.dataset.data[context.dataIndex].value;
                            const maxValue = Math.max(...context.dataset.data.map(d => d.value));
                            return value / maxValue > 0.5 ? 'white' : 'black';
                        },
                        font: {
                            weight: 'bold'
                        },
                        formatter: (value) => value.value
                    }
                },
                scales: {
                    x: {
                        type: 'linear',
                        position: 'bottom',
                        min: -0.5,
                        max: 2.5,
                        ticks: {
                            callback: function(value) {
                                return levels[value];
                            }
                        },
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Coverage Level'
                        }
                    },
                    y: {
                        type: 'linear',
                        min: -0.5,
                        max: 2.5,
                        ticks: {
                            callback: function(value) {
                                return levels[value];
                            }
                        },
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Excess Level'
                        }
                    }
                },
                onClick: function(event, elements) {
                    if (elements.length > 0) {
                        const data = this.data.datasets[0].data[elements[0].index];
                        const coverage = data.coverage;
                        const excess = data.excess;

                        Swal.fire({
                            title: 'View Policy Details?',
                            text: 'Would you like to view detailed policy information and recommendations?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#36A2EB',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, view details',
                            cancelButtonText: 'No, cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = `policy_analysis_details.php?coverage=${coverage}&excess=${excess}`;
                            }
                        });
                    }
                }
            }
        });

        // Dropdown functionality implementation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
                const button = dropdown.querySelector('.filter-button');
                const content = dropdown.querySelector('.filter-dropdown-content');
                const selectAll = dropdown.querySelector('input[id^="selectAll"]');
                const options = dropdown.querySelectorAll('input[type="checkbox"]:not([id^="selectAll"])');
                const clearBtn = dropdown.querySelector('.filter-clear');
                const confirmBtn = dropdown.querySelector('.filter-confirm');

                // Show/hide dropdown content on button click
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('active');
                    // Close other dropdowns
                    document.querySelectorAll('.filter-dropdown').forEach(other => {
                        if (other !== dropdown) other.classList.remove('active');
                    });
                });

                // Update button text function
                function updateButtonText() {
                    const selectedOptions = Array.from(options)
                        .filter(opt => opt.checked)
                        .map(opt => opt.nextElementSibling.textContent.trim());

                    if (selectedOptions.length > 0) {
                        button.innerHTML = selectedOptions.map(opt =>
                            `<span class="selected-item">${opt}</span>`
                        ).join('');
                    } else {
                        // Set default text based on dropdown type
                        if (dropdown.id === 'yearDropdown') {
                            button.innerHTML = 'Select year';
                        } else if (dropdown.id === 'quarterDropdown') {
                            button.innerHTML = 'Select quarter';
                        } else if (dropdown.id === 'monthDropdown') {
                            button.innerHTML = 'Select month';
                        } else if (dropdown.id === 'statusDropdown') {
                            button.innerHTML = 'Select status';
                        }
                    }
                }

                // Select all functionality
                if (selectAll) {
                    selectAll.addEventListener('change', () => {
                        options.forEach(option => {
                            option.checked = selectAll.checked;
                        });
                        updateButtonText();
                    });
                }

                // Option change updates
                options.forEach(option => {
                    option.addEventListener('change', () => {
                        if (selectAll) {
                            selectAll.checked = Array.from(options).every(opt => opt.checked);
                            selectAll.indeterminate = Array.from(options).some(opt => opt.checked) && !selectAll.checked;
                        }
                        updateButtonText();
                    });
                });

                // Clear button functionality
                clearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    options.forEach(option => option.checked = false);
                    if (selectAll) selectAll.checked = false;
                    updateButtonText();
                });

                // Confirm button functionality
                confirmBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    dropdown.classList.remove('active');
                    updateButtonText();
                });

                // Initialize button text
                updateButtonText();
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.filter-dropdown')) {
                    document.querySelectorAll('.filter-dropdown').forEach(dropdown => {
                        dropdown.classList.remove('active');
                    });
                }
            });
        });

        // 添加生成策略建议的函数
        function generatePolicyStrategies() {
            const strategyContainer = document.getElementById('strategyContainer');
            strategyContainer.innerHTML = ''; // 清空现有建议

            // 获取关键指标
            const totalRisk = <?php echo $totalRisk; ?>;
            const leverage = <?php echo $leverage; ?>;
            const lossRatio = <?php echo $lossRatio; ?>;
            const sumInsuredLossRatio = <?php echo $sumInsuredLossRatio; ?>;
            const claimsFrequency = <?php echo $claimsFrequency; ?>;
            const claimsSeverity = <?php echo $claimsSeverity; ?>;

            // 生成策略建议
            const strategies = [];

            // 1. 总风险敞口分析
            strategies.push({
                title: 'Total Risk Exposure Analysis',
                content: `Current total risk exposure is $${totalRisk.toLocaleString()}. ${
                    totalRisk > 10000000 
                    ? 'High risk exposure level detected. Consider: 1) Reviewing reinsurance arrangements; 2) Implementing stricter risk selection criteria; 3) Adjusting coverage limits for high-risk policies.' 
                    : totalRisk < 5000000
                    ? 'Conservative risk exposure level. Opportunity to: 1) Expand underwriting capacity; 2) Consider accepting higher-risk policies with appropriate premium adjustments; 3) Explore new market segments.'
                    : 'Moderate risk exposure level. Maintain current risk management strategy while: 1) Monitoring risk accumulation; 2) Regular review of risk appetite; 3) Balancing growth with risk control.'
                }`
            });

            // 2. 总杠杆率分析
            strategies.push({
                title: 'Total Leverage Analysis',
                content: `Current leverage ratio is ${leverage}x. ${
                    leverage > 100
                    ? 'High leverage indicates significant risk exposure. Recommended actions: 1) Review premium pricing strategy; 2) Consider increasing reinsurance coverage; 3) Implement more stringent risk assessment procedures.' 
                    : leverage < 50
                    ? 'Conservative leverage position. Consider: 1) Offering more competitive premium rates; 2) Expanding coverage options; 3) Exploring new market opportunities while maintaining risk discipline.'
                    : 'Well-balanced leverage ratio. Continue to: 1) Monitor market conditions; 2) Maintain pricing discipline; 3) Regular review of risk-return trade-offs.'
                }`
            });

            // 3. 总保费损失率分析
            strategies.push({
                title: 'Premium Loss Ratio Analysis',
                content: `Current premium loss ratio is ${lossRatio}%. ${
                    lossRatio > 70
                    ? 'High loss ratio indicates profitability concerns. Actions needed: 1) Review pricing adequacy; 2) Strengthen underwriting guidelines; 3) Evaluate claims management processes.' 
                    : lossRatio < 40
                    ? 'Low loss ratio suggests potential market opportunity. Consider: 1) More competitive pricing; 2) Expanding target market; 3) Enhancing product features.'
                    : 'Healthy loss ratio range. Maintain by: 1) Continuous monitoring of claims trends; 2) Regular pricing reviews; 3) Balanced growth strategy.'
                }`
            });

            // 4. 总保险损失率分析
            strategies.push({
                title: 'Insurance Loss Ratio Analysis',
                content: `Current insurance loss ratio is ${sumInsuredLossRatio}%. ${
                    sumInsuredLossRatio > 2
                    ? 'High insurance loss ratio indicates increased risk materialization. Recommendations: 1) Review risk assessment criteria; 2) Adjust coverage limits; 3) Enhance risk mitigation requirements.' 
                    : sumInsuredLossRatio < 0.5
                    ? 'Low insurance loss ratio shows effective risk control. Opportunities to: 1) Review coverage restrictions; 2) Optimize reinsurance arrangements; 3) Consider broader risk acceptance.'
                    : 'Balanced insurance loss ratio. Continue to: 1) Monitor risk quality; 2) Maintain underwriting discipline; 3) Regular review of coverage terms.'
                }`
            });

            // 5. 理赔频率分析
            strategies.push({
                title: 'Claims Frequency Analysis',
                content: `Claims frequency is ${claimsFrequency}%. ${
                    claimsFrequency > 15
                    ? 'High claims frequency requires attention. Actions needed: 1) Enhance risk selection criteria; 2) Review policy terms and conditions; 3) Implement loss prevention programs.' 
                    : claimsFrequency < 5
                    ? 'Low claims frequency indicates strong risk selection. Consider: 1) Expanding target market; 2) Reviewing excess levels; 3) Enhancing customer retention strategies.'
                    : 'Moderate claims frequency. Maintain through: 1) Ongoing risk monitoring; 2) Regular review of underwriting guidelines; 3) Proactive claims management.'
                }`
            });

            // 6. 理赔严重度分析
            strategies.push({
                title: 'Claims Severity Analysis',
                content: `Average claim severity is $${claimsSeverity.toLocaleString()}. ${
                    claimsSeverity > 10000
                    ? 'High claim severity suggests significant risk exposure. Recommendations: 1) Review coverage limits and exclusions; 2) Strengthen risk assessment procedures; 3) Enhance claims management protocols.' 
                    : claimsSeverity < 2000
                    ? 'Low claim severity indicates effective risk control. Consider: 1) Optimizing excess levels; 2) Reviewing premium rates; 3) Expanding coverage options.'
                    : 'Moderate claim severity. Continue with: 1) Regular monitoring of claims patterns; 2) Balanced coverage design; 3) Effective claims handling procedures.'
                }`
            });

            // 渲染策略建议
            strategies.forEach(strategy => {
                const card = document.createElement('div');
                card.className = 'strategy-card';
                card.innerHTML = `
                    <h3 class="strategy-title">${strategy.title}</h3>
                    <p class="strategy-content">${strategy.content}</p>
                `;
                strategyContainer.appendChild(card);
            });
        }

        // 在页面加载和筛选条件改变时更新策略
        document.addEventListener('DOMContentLoaded', function() {
            generatePolicyStrategies();
        });

        document.getElementById('filterForm').addEventListener('submit', function(e) {
            setTimeout(generatePolicyStrategies, 100);
        });

        // 導出到Excel功能
        function exportPolicyToExcel() {
            const table = document.getElementById('policyAnalysisTable');
            const wb = XLSX.utils.table_to_book(table, {
                sheet: "Policy Analysis"
            });
            XLSX.writeFile(wb, 'policy_analysis.xlsx');
        }
    </script>
</body>

</html>