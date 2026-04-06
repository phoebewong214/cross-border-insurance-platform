<?php
// Database connection configuration
$link = @mysqli_connect('localhost', 'root', '', 'insurance_system');
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($link, 'SET NAMES utf8');

// Get filter conditions and prevent SQL injection
$selectedTypes = isset($_GET['types']) ? array_map(function ($type) use ($link) {
    return mysqli_real_escape_string($link, $type);
}, $_GET['types']) : [];
$selectedNames = isset($_GET['names']) ? $_GET['names'] : [];
$selectedIds = isset($_GET['ids']) ? $_GET['ids'] : [];
$selectedFitFor = isset($_GET['fit_for']) ? array_map(function ($fit) use ($link) {
    return mysqli_real_escape_string($link, $fit);
}, $_GET['fit_for']) : [];

// Build WHERE clause
$conditions = [];

if (!empty($selectedTypes)) {
    $typeConditions = array_map(function ($type) use ($link) {
        return "Product_Code = '" . mysqli_real_escape_string($link, $type) . "'";
    }, $selectedTypes);
    $conditions[] = "(" . implode(" OR ", $typeConditions) . ")";
}

if (!empty($selectedNames)) {
    $nameConditions = array_map(function ($name) use ($link) {
        return "Product_Name = '" . mysqli_real_escape_string($link, $name) . "'";
    }, $selectedNames);
    $conditions[] = "(" . implode(" OR ", $nameConditions) . ")";
}

if (!empty($selectedIds)) {
    $idConditions = array_map(function ($id) use ($link) {
        return "Product_ID = '" . mysqli_real_escape_string($link, $id) . "'";
    }, $selectedIds);
    $conditions[] = "(" . implode(" OR ", $idConditions) . ")";
}

if (!empty($selectedFitFor)) {
    $fitForConditions = array_map(function ($fit) use ($link) {
        return "Fit_for = '" . mysqli_real_escape_string($link, $fit) . "'";
    }, $selectedFitFor);
    $conditions[] = "(" . implode(" OR ", $fitForConditions) . ")";
}

$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get product type distribution data
$typeQuery = "SELECT 
                    Product_Code as type,
                    COUNT(*) as count, 
                    AVG(Basic_premium) as avg_premium,
                    AVG(Medical_coverage) as avg_medical,
                    AVG(Material_coverage) as avg_material
                  FROM product 
                  $whereClause
                  GROUP BY Product_Code
                  ORDER BY Product_Code";

$result = mysqli_query($link, $typeQuery);
$typeData = [];
$typeLabels = [];
$typeCounts = [];
$avgPremiums = [];
$avgMedical = [];
$avgMaterial = [];

while ($row = mysqli_fetch_assoc($result)) {
    $typeLabels[] = $row['type'];
    $typeCounts[] = intval($row['count']);
    $avgPremiums[] = round($row['avg_premium'], 2);
    $avgMedical[] = round($row['avg_medical'], 2);
    $avgMaterial[] = round($row['avg_material'], 2);
}

// Get status distribution data
$statusQuery = "SELECT 
    Status,
    COUNT(*) as count
    FROM product 
    $whereClause 
    GROUP BY Status";

// Get premium range distribution data
$premiumQuery = "SELECT 
    CASE 
        WHEN Basic_premium <= 1000 THEN '0-1000'
        WHEN Basic_premium <= 2000 THEN '1001-2000'
        WHEN Basic_premium <= 3000 THEN '2001-3000'
        WHEN Basic_premium <= 4000 THEN '3001-4000'
        ELSE '4000+'
    END as premium_range,
    COUNT(*) as count
    FROM product
    $whereClause
    GROUP BY premium_range
    ORDER BY premium_range";
$premiumResult = mysqli_query($link, $premiumQuery);
$premiumRanges = [];
$premiumCounts = [];
while ($row = mysqli_fetch_assoc($premiumResult)) {
    $premiumRanges[] = $row['premium_range'];
    $premiumCounts[] = intval($row['count']);
}

// Get all available filter options
$filterOptions = [
    'types' => [],
    'names' => [],
    'ids' => [],
    'fit_for' => []
];

// 获取所有产品类型
$typeQuery = "SELECT DISTINCT Product_Code FROM product ORDER BY Product_Code";
$typeResult = mysqli_query($link, $typeQuery);
while ($row = mysqli_fetch_assoc($typeResult)) {
    $filterOptions['types'][] = $row['Product_Code'];
}

// 获取所有产品名称
$nameQuery = "SELECT DISTINCT Product_Name FROM product ORDER BY Product_Name";
$nameResult = mysqli_query($link, $nameQuery);
while ($row = mysqli_fetch_assoc($nameResult)) {
    $filterOptions['names'][] = $row['Product_Name'];
}

// 获取所有产品ID
$idQuery = "SELECT DISTINCT Product_ID FROM product ORDER BY Product_ID";
$idResult = mysqli_query($link, $idQuery);
while ($row = mysqli_fetch_assoc($idResult)) {
    $filterOptions['ids'][] = $row['Product_ID'];
}

// 获取所有可用的Fit_for选项
$fitForQuery = "SELECT DISTINCT Fit_for FROM product ORDER BY Fit_for";
$fitForResult = mysqli_query($link, $fitForQuery);
while ($row = mysqli_fetch_assoc($fitForResult)) {
    $filterOptions['fit_for'][] = $row['Fit_for'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Analysis</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.0.0/chartjs-plugin-datalabels.min.js"></script>
    <script>
        Chart.register(ChartDataLabels);
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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

        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            min-height: 42px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e2e8f0;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            margin: 4px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            margin-right: 5px;
            color: #4a5568;
        }

        .select2-dropdown {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .select2-search__field {
            padding: 8px !important;
        }

        .select2-results__option {
            padding: 8px 12px;
        }

        .select2-results__option--highlighted {
            background-color: #4299e1 !important;
        }

        .apply-btn {
            margin-top: 10px;
            width: auto;
            min-width: 200px;
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
            resize: both;
            overflow: auto;
            min-width: 300px;
            min-height: 300px;
        }

        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 20px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f5;
        }

        .chart-container {
            height: 400px;
            position: relative;
            width: 100%;
            min-height: 300px;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 350px;
            }

            .dashboard {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .filter-item {
                min-width: 100%;
            }

            .chart-container {
                height: 300px;
            }

            .dashboard-title {
                font-size: 24px;
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

        /* 调整应用按钮样式 */
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

        /* 添加新的拉伸手柄样式 */
        .chart-card {
            position: relative;
            resize: both;
            /* 允许双向调整大小 */
            overflow: auto;
        }

        /* 自定义拉伸手柄的样式 */
        .chart-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 15px;
            height: 15px;
            cursor: nw-resize;
            background:
                linear-gradient(45deg, transparent 2px, #36A2EB 2px, #36A2EB 3px, transparent 3px),
                linear-gradient(-45deg, transparent 2px, #36A2EB 2px, #36A2EB 3px, transparent 3px),
                linear-gradient(135deg, transparent 2px, #36A2EB 2px, #36A2EB 3px, transparent 3px),
                linear-gradient(-135deg, transparent 2px, #36A2EB 2px, #36A2EB 3px, transparent 3px);
            background-size: 10px 10px;
            background-position: center;
            opacity: 0.7;
        }

        .chart-card:hover::after {
            opacity: 1;
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
            color: #1a237e;
            transform: translateY(-1px);
        }

        .navbar .btn-light i {
            margin-right: 0.5rem;
        }

        /* 添加新的样式 */
        .filter-btn {
            background: #36A2EB;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: #2c8ac0;
            transform: translateY(-1px);
        }

        #exportBtn {
            background: #28a745;
        }

        #exportBtn:hover {
            background: #218838;
        }

        .filter-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .filter-modal-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filter-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-modal-title {
            font-size: 18px;
            font-weight: 500;
            color: #2c3e50;
        }

        .filter-modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #666;
        }

        .filter-input-group {
            margin-bottom: 20px;
        }

        .filter-input-label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .filter-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 14px;
        }

        .filter-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .filter-modal-btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-modal-btn.cancel {
            background: #f1f3f5;
            color: #4a5568;
            border: none;
        }

        .filter-modal-btn.confirm {
            background: #36A2EB;
            color: white;
            border: none;
        }

        .filter-modal-btn:hover {
            transform: translateY(-1px);
        }

        .time-dimension-dropdown {
            position: relative;
        }

        .time-dimension-content {
            display: none;
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            min-width: 150px;
            z-index: 1000;
        }

        .time-dimension-option {
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .time-dimension-option:hover {
            background-color: #f5f5f5;
        }

        .time-dimension-dropdown.active .time-dimension-content {
            display: block;
        }
    </style>
</head>

<body>
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
            <h1 class="dashboard-title">Product Analysis Dashboard</h1>
        </div>

        <div class="filter-section">
            <h2 class="filter-title">Filter Options</h2>
            <form id="filterForm" method="GET">
                <div class="filter-grid">
                    <div class="filter-item">
                        <label class="filter-label">Product Code</label>
                        <div class="filter-dropdown" id="codeDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedTypes) ? implode(', ', $selectedTypes) : 'Select product codes'; ?>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllCodes" />
                                    <label for="selectAllCodes">All</label>
                                </div>
                                <?php foreach ($filterOptions['types'] as $code): ?>
                                    <div class="filter-option-item">
                                        <input type="checkbox" name="types[]" value="<?php echo $code; ?>"
                                            <?php echo in_array($code, $selectedTypes) ? 'checked' : ''; ?> />
                                        <label><?php echo $code; ?></label>
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
                        <label class="filter-label">Product Name</label>
                        <div class="filter-dropdown" id="nameDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedNames) ? implode(', ', $selectedNames) : 'Select product names'; ?>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllNames" />
                                    <label for="selectAllNames">All</label>
                                </div>
                                <?php foreach ($filterOptions['names'] as $name): ?>
                                    <div class="filter-option-item">
                                        <input type="checkbox" name="names[]" value="<?php echo $name; ?>"
                                            <?php echo in_array($name, $selectedNames) ? 'checked' : ''; ?> />
                                        <label><?php echo $name; ?></label>
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
                        <label class="filter-label">Product ID</label>
                        <div class="filter-dropdown" id="idDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedIds) ? implode(', ', $selectedIds) : 'Select product IDs'; ?>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllIds" />
                                    <label for="selectAllIds">All</label>
                                </div>
                                <?php foreach ($filterOptions['ids'] as $id): ?>
                                    <div class="filter-option-item">
                                        <input type="checkbox" name="ids[]" value="<?php echo $id; ?>"
                                            <?php echo in_array($id, $selectedIds) ? 'checked' : ''; ?> />
                                        <label><?php echo $id; ?></label>
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
                        <label class="filter-label">Fit for</label>
                        <div class="filter-dropdown" id="fitForDropdown">
                            <button type="button" class="filter-button">
                                <?php echo !empty($selectedFitFor) ? implode(', ', $selectedFitFor) : 'Select fit for'; ?>
                            </button>
                            <div class="filter-dropdown-content">
                                <div class="filter-option-item">
                                    <input type="checkbox" id="selectAllFitFor" />
                                    <label for="selectAllFitFor">All</label>
                                </div>
                                <?php foreach ($filterOptions['fit_for'] as $fit): ?>
                                    <div class="filter-option-item">
                                        <input type="checkbox" name="fit_for[]" value="<?php echo $fit; ?>"
                                            <?php echo in_array($fit, $selectedFitFor) ? 'checked' : ''; ?> />
                                        <label><?php echo $fit; ?></label>
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

        <div class="charts-grid">
            <div class="chart-card">
                <h2 class="chart-title">Product Type Distribution</h2>
                <div class="chart-container">
                    <canvas id="typeDistribution"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h2 class="chart-title">Average Premium by Type</h2>
                <div class="chart-container">
                    <canvas id="avgPremium"></canvas>
                </div>
            </div>

            <div class="chart-card" style="grid-column: span 2;">
                <div class="chart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 class="chart-title">Product Order Statistics</h2>
                    <div style="display: flex; gap: 10px;">
                        <div class="time-dimension-dropdown">
                            <button class="filter-btn" id="timeDimensionBtn">
                                <i class="fas fa-calendar-alt"></i> Time Dimension
                            </button>
                            <div class="time-dimension-content">
                                <div class="time-dimension-option" data-value="daily">Daily</div>
                                <div class="time-dimension-option" data-value="monthly">Monthly</div>
                                <div class="time-dimension-option" data-value="quarterly">Quarterly</div>
                                <div class="time-dimension-option" data-value="annually">Annually</div>
                                <div class="time-dimension-option" data-value="clear">Clear Filter</div>
                            </div>
                        </div>
                        <button class="filter-btn" id="policyFilterBtn">
                            <i class="fas fa-filter"></i> Filter by Policy Count
                        </button>
                        <button class="filter-btn" id="exportBtn">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover" id="productOrderTable">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Basic Premium</th>
                                <th>Number of Policies</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // 获取产品订购统计
                            $productOrderQuery = "
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
                                $whereClause
                                GROUP BY p.Product_ID, p.Product_Code, p.Product_Name, p.Basic_premium
                                ORDER BY policy_count DESC";

                            $result = mysqli_query($link, $productOrderQuery);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['Product_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Product_Code']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Product_Name']) . "</td>";
                                echo "<td>" . number_format($row['Basic_premium'], 2) . "</td>";
                                echo "<td>" . $row['policy_count'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="strategy-section">
            <h2 class="section-title">Marketing Strategy Recommendations</h2>
            <div class="strategy-cards" id="strategyContainer">
                <!-- 建议将在这里动态生成 -->
            </div>
        </div>
    </div>

    <script>
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
                        button.innerHTML = selectedOptions.map(text =>
                            `<span class="selected-item">${text}</span>`
                        ).join('');
                    } else {
                        // Set default text based on dropdown type
                        if (dropdown.id === 'codeDropdown') {
                            button.innerHTML = 'Select product codes';
                        } else if (dropdown.id === 'nameDropdown') {
                            button.innerHTML = 'Select product names';
                        } else if (dropdown.id === 'idDropdown') {
                            button.innerHTML = 'Select product IDs';
                        } else if (dropdown.id === 'fitForDropdown') {
                            button.innerHTML = 'Select fit for';
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

        // Chart configurations
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        font: {
                            size: 13
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 13
                    },
                    textAlign: 'center',
                    offset: 8
                }
            }
        };

        // Product type distribution chart
        const typeDistribution = new Chart(document.getElementById('typeDistribution'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($typeLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($typeCounts); ?>,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#4BC0C0',
                        '#FFCE56',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    datalabels: {
                        formatter: (value, ctx) => {
                            const total = ctx.dataset.data.reduce((acc, curr) => acc + curr, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${percentage}%\n(${value})`;
                        }
                    }
                }
            }
        });
        document.getElementById('typeDistribution').chart = typeDistribution;

        // Average premium chart
        const avgPremium = new Chart(document.getElementById('avgPremium'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($typeLabels); ?>,
                datasets: [{
                    label: 'Average Premium',
                    data: <?php echo json_encode($avgPremiums); ?>,
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    datalabels: {
                        color: '#333',
                        anchor: 'end',
                        align: 'top',
                        formatter: (value) => value.toFixed(2)
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        document.getElementById('avgPremium').chart = avgPremium;

        function initializeCharts() {
            const resizeObserver = new ResizeObserver(entries => {
                entries.forEach(entry => {
                    const chartContainer = entry.target;
                    const canvas = chartContainer.querySelector('canvas');
                    if (canvas && canvas.chart) {
                        canvas.chart.resize();
                    }
                });
            });

            // Add size observer to each chart container
            document.querySelectorAll('.chart-container').forEach(container => {
                resizeObserver.observe(container);
            });
        }

        // Initialize chart size adjustment functionality
        initializeCharts();

        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.chart-card');

            cards.forEach(card => {
                const resizeHandle = document.createElement('div');
                resizeHandle.className = 'resize-handle';
                card.appendChild(resizeHandle);

                let isResizing = false;
                let originalWidth;
                let originalHeight;
                let originalX;
                let originalY;

                resizeHandle.addEventListener('mousedown', function(e) {
                    isResizing = true;
                    originalWidth = card.offsetWidth;
                    originalHeight = card.offsetHeight;
                    originalX = e.clientX;
                    originalY = e.clientY;

                    document.addEventListener('mousemove', resize);
                    document.addEventListener('mouseup', stopResize);
                    e.preventDefault();
                });

                function resize(e) {
                    if (!isResizing) return;

                    const width = originalWidth + (e.clientX - originalX);
                    const height = originalHeight + (e.clientY - originalY);

                    if (width > 300) {
                        card.style.width = width + 'px';
                    }
                    if (height > 300) {
                        card.style.height = height + 'px';
                    }

                    // Trigger chart redraw
                    const canvas = card.querySelector('canvas');
                    if (canvas && canvas.chart) {
                        canvas.chart.resize();
                    }
                }

                function stopResize() {
                    isResizing = false;
                    document.removeEventListener('mousemove', resize);
                    document.removeEventListener('mouseup', stopResize);
                }
            });
        });

        function generateStrategies() {
            const strategyContainer = document.getElementById('strategyContainer');
            strategyContainer.innerHTML = ''; // 清空现有建议

            // 获取当前筛选后的数据
            const productTable = document.getElementById('productOrderTable');
            const visibleRows = Array.from(productTable.getElementsByTagName('tr')).filter(row => row.style.display !== 'none' && !row.closest('thead'));

            // 如果没有数据，显示相应提示
            if (visibleRows.length === 0) {
                const noDataCard = document.createElement('div');
                noDataCard.className = 'strategy-card';
                noDataCard.innerHTML = `
                    <h3 class="strategy-title">No Data Available</h3>
                    <p class="strategy-content">No products match the current filter criteria. Consider adjusting your filters to view more products and their corresponding strategies.</p>
                `;
                strategyContainer.appendChild(noDataCard);
                return;
            }

            // 分析当前显示的数据
            const productsData = visibleRows.map(row => ({
                productId: row.cells[0].textContent,
                productCode: row.cells[1].textContent,
                productName: row.cells[2].textContent,
                basicPremium: parseFloat(row.cells[3].textContent.replace(/,/g, '')),
                policyCount: parseInt(row.cells[4].textContent)
            }));

            // 计算关键指标
            const totalPolicies = productsData.reduce((sum, product) => sum + product.policyCount, 0);
            const avgPremium = productsData.reduce((sum, product) => sum + product.basicPremium, 0) / productsData.length;
            const maxPolicyProduct = productsData.reduce((max, product) =>
                product.policyCount > max.policyCount ? product : max, productsData[0]);
            const minPolicyProduct = productsData.reduce((min, product) =>
                product.policyCount < min.policyCount ? product : min, productsData[0]);

            // 生成策略建议
            const strategies = [];

            // 1. 市场表现分析
            strategies.push({
                title: 'Market Performance Analysis',
                content: `Based on current filter results (${visibleRows.length} products): The most popular product is ${maxPolicyProduct.productName} (${maxPolicyProduct.policyCount} policies), while ${minPolicyProduct.productName} shows potential for growth (${minPolicyProduct.policyCount} policies). Average premium across filtered products: ${avgPremium.toFixed(2)}.`
            });

            // 2. 产品组合建议
            const productTypes = [...new Set(productsData.map(p => p.productCode))];
            strategies.push({
                title: 'Product Portfolio Strategy',
                content: `Current portfolio includes ${productTypes.length} product types. ${
                    productTypes.length === 1 
                    ? 'Consider diversifying the product range to reach more market segments.' 
                    : 'Maintain this diverse product range while optimizing each product\'s performance.'
                }`
            });

            // 3. 定价策略
            const premiumRange = productsData.map(p => p.basicPremium);
            const maxPremium = Math.max(...premiumRange);
            const minPremium = Math.min(...premiumRange);
            strategies.push({
                title: 'Pricing Strategy Recommendations',
                content: `Premium range analysis: Highest ${maxPremium.toFixed(2)} to Lowest ${minPremium.toFixed(2)}. ${
                    maxPremium - minPremium > 500 
                    ? 'Current pricing structure covers a wide range, suitable for different customer segments.' 
                    : 'Consider expanding premium range to attract more diverse customer segments.'
                }`
            });

            // 4. 增长机会
            const lowPerformers = productsData.filter(p => p.policyCount < totalPolicies / productsData.length);
            strategies.push({
                title: 'Growth Opportunities',
                content: `${
                    lowPerformers.length > 0
                    ? `Identified ${lowPerformers.length} products below average performance. Consider promotional campaigns for: ${lowPerformers.map(p => p.productName).join(', ')}.`
                    : 'All products in current selection are performing well. Focus on maintaining current success while exploring new market opportunities.'
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

        // 在筛选条件改变时更新策略
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            setTimeout(generateStrategies, 100);
        });

        // 在Policy Count筛选后也更新策略
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.querySelector('.filter-modal');
            if (modal) {
                const confirmBtn = modal.querySelector('.filter-modal-btn.confirm');
                if (confirmBtn) {
                    confirmBtn.addEventListener('click', function() {
                        setTimeout(generateStrategies, 100);
                    });
                }
            }
        });

        // 初始化策略
        document.addEventListener('DOMContentLoaded', function() {
            generateStrategies();
        });

        // 添加筛选功能
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtn = document.getElementById('policyFilterBtn');
            const table = document.getElementById('productOrderTable');
            const rows = table.getElementsByTagName('tr');

            // 创建模态框
            const modal = document.createElement('div');
            modal.className = 'filter-modal';
            modal.innerHTML = `
                <div class="filter-modal-content">
                    <div class="filter-modal-header">
                        <h3 class="filter-modal-title">Filter by Policy Count</h3>
                        <button class="filter-modal-close">&times;</button>
                    </div>
                    <div class="filter-input-group">
                        <label class="filter-input-label">Minimum Number of Policies</label>
                        <input type="number" class="filter-input" id="minPolicyCount" min="0">
                    </div>
                    <div class="filter-modal-actions">
                        <button class="filter-modal-btn cancel">Cancel</button>
                        <button class="filter-modal-btn confirm">Confirm</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // 显示模态框
            filterBtn.addEventListener('click', () => {
                modal.style.display = 'flex';
            });

            // 关闭模态框
            modal.querySelector('.filter-modal-close').addEventListener('click', () => {
                modal.style.display = 'none';
            });

            modal.querySelector('.filter-modal-btn.cancel').addEventListener('click', () => {
                modal.style.display = 'none';
            });

            // 确认筛选
            modal.querySelector('.filter-modal-btn.confirm').addEventListener('click', () => {
                const minCount = parseInt(modal.querySelector('#minPolicyCount').value) || 0;

                // 遍历表格行（跳过表头）
                for (let i = 1; i < rows.length; i++) {
                    const row = rows[i];
                    const policyCount = parseInt(row.cells[4].textContent);
                    row.style.display = policyCount >= minCount ? '' : 'none';
                }

                modal.style.display = 'none';
            });

            // 点击模态框外部关闭
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // 添加导出Excel功能
        document.addEventListener('DOMContentLoaded', function() {
            const exportBtn = document.getElementById('exportBtn');
            const table = document.getElementById('productOrderTable');

            exportBtn.addEventListener('click', function() {
                // 获取当前可见的行
                const visibleRows = Array.from(table.getElementsByTagName('tr')).filter(row => row.style.display !== 'none');

                // 创建CSV内容
                let csvRows = [];

                // 添加表头
                const headers = Array.from(visibleRows[0].getElementsByTagName('th'))
                    .slice(0, 5)
                    .map(th => `"${th.textContent.trim()}"`);
                csvRows.push(headers.join(','));

                // 添加数据行
                for (let i = 1; i < visibleRows.length; i++) {
                    const row = visibleRows[i];
                    const cells = Array.from(row.getElementsByTagName('td'))
                        .slice(0, 5)
                        .map(td => `"${td.textContent.trim()}"`);
                    csvRows.push(cells.join(','));
                }

                // 合并所有行并添加BOM以支持中文
                const csvContent = '\ufeff' + csvRows.join('\n');

                // 创建Blob对象
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });

                // 创建下载链接
                const link = document.createElement('a');
                if (navigator.msSaveBlob) { // IE 10+
                    navigator.msSaveBlob(blob, 'product_order_statistics.csv');
                } else {
                    link.href = URL.createObjectURL(blob);
                    link.download = 'product_order_statistics.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });

        // 時間維度篩選功能
        document.addEventListener('DOMContentLoaded', function() {
            const timeDimensionBtn = document.getElementById('timeDimensionBtn');
            const timeDimensionContent = document.querySelector('.time-dimension-content');
            const timeDimensionOptions = document.querySelectorAll('.time-dimension-option');
            const productOrderTable = document.getElementById('productOrderTable');
            const originalTableData = Array.from(productOrderTable.querySelectorAll('tbody tr')).map(row => row.cloneNode(true));

            timeDimensionBtn.addEventListener('click', function() {
                timeDimensionContent.parentElement.classList.toggle('active');
            });

            timeDimensionOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const value = this.dataset.value;

                    // 移除所有選項的active類
                    timeDimensionOptions.forEach(opt => opt.classList.remove('active'));
                    // 添加當前選項的active類
                    this.classList.add('active');

                    // 更新按鈕文本
                    timeDimensionBtn.innerHTML = `<i class="fas fa-calendar-alt"></i> ${this.textContent}`;

                    // 根據選擇的時間維度篩選數據
                    if (value === 'clear') {
                        // 清除篩選，恢復原始數據
                        const tbody = productOrderTable.querySelector('tbody');
                        tbody.innerHTML = '';
                        originalTableData.forEach(row => tbody.appendChild(row.cloneNode(true)));
                        timeDimensionBtn.innerHTML = '<i class="fas fa-calendar-alt"></i> Time Dimension';
                    } else {
                        // 這裡需要根據實際數據庫結構實現篩選邏輯
                        // 示例：根據時間維度重新查詢數據
                        filterByTimeDimension(value);
                    }

                    // 關閉下拉菜單
                    timeDimensionContent.parentElement.classList.remove('active');
                });
            });

            // 點擊其他地方關閉下拉菜單
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.time-dimension-dropdown')) {
                    timeDimensionContent.parentElement.classList.remove('active');
                }
            });

            // 根據時間維度篩選數據的函數
            function filterByTimeDimension(dimension) {
                // 獲取當前日期
                const today = new Date();
                let startDate = new Date();

                // 根據選擇的時間維度設置開始日期
                switch (dimension) {
                    case 'daily':
                        // 今天
                        startDate.setHours(0, 0, 0, 0);
                        break;
                    case 'monthly':
                        // 本月第一天
                        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                        break;
                    case 'quarterly':
                        // 本季度第一天
                        const quarter = Math.floor(today.getMonth() / 3);
                        startDate = new Date(today.getFullYear(), quarter * 3, 1);
                        break;
                    case 'annually':
                        // 本年第一天
                        startDate = new Date(today.getFullYear(), 0, 1);
                        break;
                }

                // 格式化日期為 YYYY-MM-DD
                const formatDate = (date) => {
                    return date.toISOString().split('T')[0];
                };

                // 發送 AJAX 請求獲取篩選後的數據
                fetch(`get_product_orders.php?time_dimension=${dimension}&start_date=${formatDate(startDate)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const tbody = productOrderTable.querySelector('tbody');
                        tbody.innerHTML = '';

                        data.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${row.Product_ID}</td>
                                <td>${row.Product_Code}</td>
                                <td>${row.Product_Name}</td>
                                <td>${row.Basic_premium}</td>
                                <td>${row.policy_count}</td>
                            `;
                            tbody.appendChild(tr);
                        });

                        // 更新策略建議
                        generateStrategies();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('獲取數據時發生錯誤，請稍後再試。');
                    });
            }
        });
    </script>
</body>

</html>