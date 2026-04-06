<?php
session_start();

// Database connection configuration
$link = @mysqli_connect('localhost', 'root', '', 'insurance_system');
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($link, 'SET NAMES utf8');

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedClaimRatio = isset($_POST['claim_ratio']) ? $_POST['claim_ratio'] : '';
    $_SESSION['selected_claim_ratio'] = $selectedClaimRatio;
    // 不再重定向，而是刷新当前页面
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get current claim ratio setting
$selectedClaimRatio = isset($_SESSION['selected_claim_ratio']) ? $_SESSION['selected_claim_ratio'] : 'none';

// Get status distribution data
$statusQuery = "SELECT 
    CASE 
        WHEN " . ($selectedClaimRatio === 'none' ? "1=1" : 
            "(Total_Claim_Amount/Total_Contributed_Premium) < " . 
            ($selectedClaimRatio === 'less_than_5' ? '5' : 
             ($selectedClaimRatio === 'less_than_10' ? '10' : 
              ($selectedClaimRatio === 'less_than_20' ? '20' : '0')))) . " THEN 'Valid'
        ELSE 'Invalid'
    END as status,
    COUNT(*) as count
    FROM party 
    GROUP BY status
    ORDER BY status";

$result = mysqli_query($link, $statusQuery);
$statusData = [];
while ($row = mysqli_fetch_assoc($result)) {
    $statusData[] = $row;
}

// 确保状态数据包含Valid和Invalid
$hasValid = false;
$hasInvalid = false;
foreach ($statusData as $data) {
    if ($data['status'] === 'Valid') $hasValid = true;
    if ($data['status'] === 'Invalid') $hasInvalid = true;
}

if (!$hasValid) {
    $statusData[] = ['status' => 'Valid', 'count' => 0];
}
if (!$hasInvalid) {
    $statusData[] = ['status' => 'Invalid', 'count' => 0];
}

// 按状态排序
usort($statusData, function($a, $b) {
    return strcmp($a['status'], $b['status']);
});

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Status Edit</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
            flex-shrink: 0;
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

        .navbar .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .navbar .btn-light {
            color: #2c3e50;
            border: 1px solid #e2e8f0;
        }

        .navbar .btn-light:hover {
            background-color: #f8f9fa;
            color: #36A2EB;
            transform: translateY(-1px);
        }

        .content-wrapper {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-top: 30px;
            flex: 1;
            min-height: 0;
        }

        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .filter-title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .claim-ratio-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .claim-ratio-option {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .claim-ratio-option:hover {
            border-color: #4BC0C0;
            background-color: #f8f9fa;
        }

        .claim-ratio-option input[type="radio"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 100%;
            width: 100%;
            left: 0;
            top: 0;
        }

        .claim-ratio-option label {
            font-size: 16px;
            color: #2c3e50;
            cursor: pointer;
            width: 100%;
            padding-left: 30px;
            position: relative;
        }

        .claim-ratio-option label:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            background-color: white;
        }

        .claim-ratio-option input[type="radio"]:checked + label:before {
            border-color: #4BC0C0;
            background-color: #4BC0C0;
        }

        .chart-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
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
            flex: 1;
        }

        .btn-success {
            background-color: #4BC0C0;
            border-color: #4BC0C0;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        .btn-success:hover {
            background-color: #3da8a8;
            border-color: #3da8a8;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .modal-btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .modal-btn-cancel {
            background-color: #f1f3f5;
            color: #4a5568;
        }

        .modal-btn-save {
            background-color: #4BC0C0;
            color: white;
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <div class="navbar-brand admin-text">Admin Center</div>
            <div class="ms-auto">
                <a href="party_analysis.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Back to Party Analysis
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Party Status Edit</h1>
        </div>

        <div class="content-wrapper">
            <!-- Claim Ratio Settings -->
            <div class="filter-section">
                <h2 class="filter-title">Claim Ratio Settings</h2>
                <form id="claimRatioForm" method="POST">
                    <div class="claim-ratio-options">
                        <div class="claim-ratio-option">
                            <input type="radio" id="none" name="claim_ratio" value="none" 
                                   <?php echo $selectedClaimRatio === 'none' ? 'checked' : ''; ?>>
                            <label for="none">None</label>
                        </div>
                        <div class="claim-ratio-option">
                            <input type="radio" id="less_than_5" name="claim_ratio" value="less_than_5" 
                                   <?php echo $selectedClaimRatio === 'less_than_5' ? 'checked' : ''; ?>>
                            <label for="less_than_5">Less than 5</label>
                        </div>
                        <div class="claim-ratio-option">
                            <input type="radio" id="less_than_10" name="claim_ratio" value="less_than_10" 
                                   <?php echo $selectedClaimRatio === 'less_than_10' ? 'checked' : ''; ?>>
                            <label for="less_than_10">Less than 10</label>
                        </div>
                        <div class="claim-ratio-option">
                            <input type="radio" id="less_than_20" name="claim_ratio" value="less_than_20" 
                                   <?php echo $selectedClaimRatio === 'less_than_20' ? 'checked' : ''; ?>>
                            <label for="less_than_20">Less than 20</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </form>
            </div>

            <!-- Status Distribution Chart -->
            <div class="chart-section">
                <h2 class="chart-title">Status Distribution Preview</h2>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Confirmation Modal -->
    <div id="saveModal" class="modal">
        <div class="modal-content">
            <h3 class="modal-title">Confirm Save</h3>
            <p>Are you sure you want to save your changes?</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="hideSaveModal()">Cancel</button>
                <button class="modal-btn modal-btn-save" onclick="submitForm()">Save</button>
            </div>
        </div>
    </div>

    <script>
        Chart.register(ChartDataLabels);

        let statusChart = null;

        // Initialize status chart
        function initChart(data) {
            const statusChartElement = document.getElementById('statusChart');
            if (statusChartElement) {
                if (statusChart) {
                    statusChart.destroy();
                }
                statusChart = new Chart(statusChartElement, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.datasets[0].data,
                            backgroundColor: ['#4BC0C0', '#FF9F40']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    padding: 20,
                                    font: { size: 13 }
                                }
                            },
                            datalabels: {
                                color: '#fff',
                                font: { weight: 'bold', size: 13 },
                                textAlign: 'center',
                                offset: 8,
                                formatter: (value, ctx) => {
                                    const total = ctx.dataset.data.reduce((acc, curr) => acc + curr, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${value}\n(${percentage}%)`;
                                }
                            }
                        }
                    }
                });
            }
        }

        // Initialize with initial data
        initChart({
            labels: <?php echo json_encode(array_column($statusData, 'status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_map('intval', array_column($statusData, 'count'))); ?>
            }]
        });

        // Add real-time preview functionality
        document.querySelectorAll('.claim-ratio-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const formData = new FormData(document.getElementById('claimRatioForm'));
                fetch('get_status_data.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    initChart(data);
                });
            });
        });

        // Show save confirmation modal
        document.getElementById('claimRatioForm').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('saveModal').style.display = 'flex';
        });

        // Hide save confirmation modal
        function hideSaveModal() {
            document.getElementById('saveModal').style.display = 'none';
        }

        // Submit form
        function submitForm() {
            document.getElementById('claimRatioForm').submit();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('saveModal');
            if (event.target === modal) {
                hideSaveModal();
            }
        }
    </script>
</body>
</html> 