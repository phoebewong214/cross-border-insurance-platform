<?php
require_once 'db_config.php';

// 添加錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 獲取搜索結果列表
$search_results = [];
if (isset($_GET['policy_no']) && !empty($_GET['policy_no'])) {
    try {
        // 通過quotation表獲取保單和客戶信息
        $stmt = $pdo->prepare("
            SELECT p.*, q.Party_No, q.Registration_No, pt.Name as Party_Name, q.Total_Coverage,
                   q.Total_Premium, pr.Start_Date, pr.End_Date,
                   p1.Product_Code as Product1_Code, p1.Product_Name as Product1_Name,
                   p2.Product_Code as Product2_Code, p2.Product_Name as Product2_Name,
                   p3.Product_Code as Product3_Code, p3.Product_Name as Product3_Name
            FROM policy p 
            JOIN quotation q ON p.Quotation_ID = q.Quotation_ID 
            JOIN party pt ON q.Party_No = pt.Party_No 
            JOIN proposal pr ON p.Proposal_ID = pr.Proposal_ID
            LEFT JOIN product p1 ON q.Product1_ID = p1.Product_ID
            LEFT JOIN product p2 ON q.Product2_ID = p2.Product_ID
            LEFT JOIN product p3 ON q.Product3_ID = p3.Product_ID
            WHERE p.Policy_No LIKE ?
        ");
        $stmt->execute([$_GET['policy_no']]);
        $policy_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($policy_info) {
            $search_results[] = $policy_info;
            $debug_message = "Policy Found：" . $_GET['policy_no'] . "，Customer：" . $policy_info['Party_Name'] . "，Registration Number：" . $policy_info['Registration_No'];
        } else {
            $debug_message = "Policy Not Found：" . $_GET['policy_no'];
        }
    } catch (Exception $e) {
        $debug_message = "Query Error：" . $e->getMessage();
    }
}

// 獲取選中的保單信息
$policy_info = null;
if (isset($_GET['selected_policy_no'])) {
    $stmt = $pdo->prepare("
        SELECT p.*, q.Party_No, q.Registration_No, pt.Name as Party_Name, q.Total_Coverage,
               q.Total_Premium, pr.Start_Date, pr.End_Date,
               p1.Product_Code as Product1_Code, p1.Product_Name as Product1_Name,
               p2.Product_Code as Product2_Code, p2.Product_Name as Product2_Name,
               p3.Product_Code as Product3_Code, p3.Product_Name as Product3_Name
        FROM policy p 
        JOIN quotation q ON p.Quotation_ID = q.Quotation_ID 
        JOIN party pt ON q.Party_No = pt.Party_No 
        JOIN proposal pr ON p.Proposal_ID = pr.Proposal_ID
        LEFT JOIN product p1 ON q.Product1_ID = p1.Product_ID
        LEFT JOIN product p2 ON q.Product2_ID = p2.Product_ID
        LEFT JOIN product p3 ON q.Product3_ID = p3.Product_ID
        WHERE p.Policy_No = ?
    ");
    $stmt->execute([$_GET['selected_policy_no']]);
    $policy_info = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 處理索賠記錄提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // 禁用錯誤顯示
    ini_set('display_errors', 0);
    error_reporting(0);

    try {
        // 檢查是否為空值
        if (isset($_POST['correct_amount'])) {
            if (empty($_POST['correct_amount']) && $_POST['correct_amount'] !== '0') {
                throw new Exception('Please enter a valid claim amount');
            }
            $amount = floatval($_POST['correct_amount']);
        } else {
            if (empty($_POST['claim_amount']) && $_POST['claim_amount'] !== '0') {
                throw new Exception('Please enter a valid claim amount');
            }
            $amount = floatval($_POST['claim_amount']);
        }

        // 驗證金額是否為有效數字
        if (is_nan($amount) || is_infinite($amount)) {
            throw new Exception('Please enter a valid claim amount');
        }

        // 驗證金額是否在合理範圍內（例如：-1,000,000,000 到 1,000,000,000）
        if ($amount < -1000000000 || $amount > 1000000000) {
            throw new Exception('Claim amount must be between -1,000,000,000 and 1,000,000,000');
        }

        $pdo->beginTransaction();

        // 獲取保單信息
        $stmt = $pdo->prepare("
            SELECT p.*, q.Party_No, q.Total_Coverage 
            FROM policy p 
            JOIN quotation q ON p.Quotation_ID = q.Quotation_ID 
            WHERE p.Policy_No = ?
        ");
        $stmt->execute([$_POST['policy_no']]);
        $policy = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$policy) {
            throw new Exception('Policy not found');
        }

        // 判斷是更新還是校正索賠金額
        if (isset($_POST['correct_amount'])) {
            // 校正索賠金額
            $old_amount = floatval($policy['Claim_Amount']);
            $new_amount = $amount;
            $difference = $new_amount - $old_amount;

            // 驗證新金額是否超過總保額
            if ($new_amount > $policy['Total_Coverage']) {
                throw new Exception('Claim amount cannot exceed total coverage (MOP ' . number_format($policy['Total_Coverage'], 2) . ')');
            }

            // 驗證新金額是否小於0
            if ($new_amount < 0) {
                throw new Exception('Claim amount cannot be less than 0');
            }

            // 更新保單的索賠金額
            $stmt = $pdo->prepare("
                UPDATE policy 
                SET Claim_Amount = ? 
                WHERE Policy_No = ?
            ");
            $stmt->execute([$new_amount, $_POST['policy_no']]);

            // 更新客戶的總索賠金額
            $stmt = $pdo->prepare("
                UPDATE party 
                SET Total_Claim_Amount = Total_Claim_Amount + ? 
                WHERE Party_No = ?
            ");
            $stmt->execute([$difference, $policy['Party_No']]);

            $message = 'Claim amount has been successfully corrected';
        } else {
            // 驗證新索賠金額是否超過總保額
            $new_total = floatval($policy['Claim_Amount']) + $amount;
            if ($new_total > $policy['Total_Coverage']) {
                throw new Exception('Claim amount cannot exceed total coverage (MOP ' . number_format($policy['Total_Coverage'], 2) . ')');
            }

            // 驗證新索賠金額是否小於0
            if ($new_total < 0) {
                throw new Exception('Claim amount cannot be less than 0');
            }

            // 原有的更新索賠金額邏輯
            $stmt = $pdo->prepare("
                UPDATE policy 
                SET Claim_Amount = Claim_Amount + ? 
                WHERE Policy_No = ?
            ");
            $stmt->execute([$amount, $_POST['policy_no']]);

            // 更新客戶的總索賠金額
            $stmt = $pdo->prepare("
                UPDATE party 
                SET Total_Claim_Amount = Total_Claim_Amount + ? 
                WHERE Party_No = ?
            ");
            $stmt->execute([$amount, $policy['Party_No']]);

            $message = 'Claim amount has been successfully updated';
        }

        // 獲取更新後的索賠金額
        $stmt = $pdo->prepare("
            SELECT p.Claim_Amount, q.Total_Coverage 
            FROM policy p 
            JOIN quotation q ON p.Quotation_ID = q.Quotation_ID 
            WHERE p.Policy_No = ?
        ");
        $stmt->execute([$_POST['policy_no']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_amount = floatval($result['Claim_Amount']);
        $total_coverage = floatval($result['Total_Coverage']);

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => $message,
            'new_amount' => number_format($new_amount, 2),
            'total_coverage' => $total_coverage
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Amount Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: #01459C !important;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #01459C;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem;
        }

        .btn-primary {
            background-color: #01459C;
            border-color: #01459C;
        }

        .btn-primary:hover {
            background-color: #003d7a;
            border-color: #003d7a;
        }

        .search-results {
            max-height: 300px;
            overflow-y: auto;
        }

        .search-result-item {
            cursor: pointer;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .search-result-item:hover {
            background-color: #f8f9fa;
        }

        /* 添加浮動數字動畫樣式 */
        .floating-number {
            position: absolute;
            font-weight: bold;
            pointer-events: none;
            animation: floatUp 2s ease-out forwards;
        }

        .floating-number.positive {
            color: #28a745;
            text-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
        }

        .floating-number.negative {
            color: #dc3545;
            text-shadow: 0 0 5px rgba(220, 53, 69, 0.5);
        }

        @keyframes floatUp {
            0% {
                transform: translateY(0);
                opacity: 1;
            }

            100% {
                transform: translateY(-50px);
                opacity: 0;
            }
        }

        /* 為索賠金額容器添加相對定位 */
        .claim-amount-container {
            position: relative;
        }
    </style>
</head>

<body>
    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand">Claims Management</a>
            <div class="ms-auto">
                <a href="../admin.php" class="btn btn-outline-primary">Back</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Search Policy</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <?php if (isset($debug_message)): ?>
                            <div class="alert alert-info"><?php echo $debug_message; ?></div>
                        <?php endif; ?>

                        <!-- 搜索表單 -->
                        <form method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="policy_no" class="form-control" placeholder="Please enter the policy number" value="<?php echo isset($_GET['policy_no']) ? htmlspecialchars($_GET['policy_no']) : ''; ?>">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                        </form>

                        <!-- 搜索結果列表 -->
                        <?php if (!empty($search_results)): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Search Results</h5>
                                </div>
                                <div class="card-body search-results">
                                    <?php foreach ($search_results as $result): ?>
                                        <a href="?policy_no=<?php echo htmlspecialchars($_GET['policy_no']); ?>&selected_policy_no=<?php echo htmlspecialchars($result['Policy_No']); ?>" class="text-decoration-none text-dark">
                                            <div class="search-result-item">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <strong>Policy Number：</strong><br>
                                                        <?php echo htmlspecialchars($result['Policy_No']); ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Customer Name：</strong><br>
                                                        <?php echo htmlspecialchars($result['Party_Name']); ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Registration Number：</strong><br>
                                                        <?php echo htmlspecialchars($result['Registration_No']); ?>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>Policy Status：</strong><br>
                                                        <span class="badge <?php echo $result['Policy_Status'] === 'Inforce' ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?php echo htmlspecialchars($result['Policy_Status']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($policy_info): ?>
                            <!-- 保單信息 -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Policy Information</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Policy Number：</strong><?php echo htmlspecialchars($policy_info['Policy_No']); ?></p>
                                            <p><strong>Customer Name：</strong><?php echo htmlspecialchars($policy_info['Party_Name']); ?></p>
                                            <p><strong>Registration Number：</strong><?php echo htmlspecialchars($policy_info['Registration_No']); ?></p>
                                            <p><strong>Product Information：</strong></p>
                                            <?php
                                            $products = [];
                                            if (!empty($policy_info['Product1_Name'])) {
                                                $products[] = $policy_info['Product1_Code'] . ' - ' . $policy_info['Product1_Name'];
                                            }
                                            if (!empty($policy_info['Product2_Name'])) {
                                                $products[] = $policy_info['Product2_Code'] . ' - ' . $policy_info['Product2_Name'];
                                            }
                                            if (!empty($policy_info['Product3_Name'])) {
                                                $products[] = $policy_info['Product3_Code'] . ' - ' . $policy_info['Product3_Name'];
                                            }
                                            if (!empty($products)) {
                                                echo implode('<br>', $products);
                                            } else {
                                                echo 'No product information';
                                            }
                                            ?>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="claim-amount-container"><strong>Current Claim Amount：</strong>MOP <span class="current-claim-amount"><?php echo number_format($policy_info['Claim_Amount'], 2); ?></span></p>
                                            <p><strong>Remaining Claim Amount：</strong>MOP <span class="remaining-amount"><?php echo number_format($policy_info['Total_Coverage'] - $policy_info['Claim_Amount'], 2); ?></span></p>
                                            <p><strong>Premium：</strong>MOP <?php echo number_format($policy_info['Total_Premium'], 2); ?></p>
                                            <p><strong>Period：</strong><?php echo date('Y-m-d', strtotime($policy_info['Start_Date'])); ?> to <?php echo date('Y-m-d', strtotime($policy_info['End_Date'])); ?></p>
                                            <div id="maxClaimWarning" class="alert alert-warning d-none">
                                                <i class="fas fa-exclamation-triangle"></i> This policy has reached its maximum claim amount！
                                                <button type="button" class="btn btn-danger ms-3" id="cancelPolicyBtn">
                                                    <i class="fas fa-ban"></i> Cancel this policy
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 索賠記錄表單 -->
                            <form method="POST" id="claimForm">
                                <input type="hidden" name="policy_no" value="<?php echo htmlspecialchars($policy_info['Policy_No']); ?>">
                                <div class="mb-3">
                                    <label for="claim_amount" class="form-label">Enter Claim Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">MOP</span>
                                        <input type="number" step="0.01" class="form-control" id="claim_amount" name="claim_amount" <?php echo $policy_info['Policy_Status'] !== 'Inforce' ? 'disabled' : ''; ?> required>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" id="updateClaimBtn" <?php echo $policy_info['Policy_Status'] !== 'Inforce' ? 'disabled' : ''; ?>>Update Claim Amount</button>
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#correctAmountModal" <?php echo $policy_info['Policy_Status'] !== 'Inforce' ? 'disabled' : ''; ?>>
                                        Correct Claim Amount
                                    </button>
                                </div>
                                <?php if ($policy_info['Policy_Status'] !== 'Inforce'): ?>
                                    <div class="alert alert-warning mt-3">
                                        <i class="fas fa-exclamation-triangle"></i> This policy status is <?php echo htmlspecialchars($policy_info['Policy_Status']); ?>，no claim can be made on it.
                                    </div>
                                <?php endif; ?>
                            </form>

                            <!-- 校正索賠金額的模態框 -->
                            <div class="modal fade" id="correctAmountModal" tabindex="-1" aria-labelledby="correctAmountModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="correctAmountModalLabel">Correct Claim Amount</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="modalErrorAlert" class="alert alert-danger d-none"></div>
                                            <form id="correctAmountForm" onsubmit="return false;">
                                                <input type="hidden" name="action" value="correct_amount">
                                                <input type="hidden" name="policy_no" value="<?php echo $policy_info['Policy_No']; ?>">
                                                <div class="mb-3">
                                                    <label for="correct_amount" class="form-label">Enter Claim Amount</label>
                                                    <input type="number" class="form-control" id="correct_amount" name="correct_amount" step="0.01" required>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-warning" id="confirmCorrectAmount">Correct Claim Amount</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 添加錯誤處理函數
        function showError(message) {
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger';
            errorAlert.textContent = message;
            document.querySelector('.card-body').insertBefore(errorAlert, document.querySelector('.card-body').firstChild);

            // 3秒後移除錯誤消息
            setTimeout(() => {
                errorAlert.remove();
            }, 3000);
        }

        // 添加模態框錯誤處理函數
        function showModalError(message) {
            const modalErrorAlert = document.getElementById('modalErrorAlert');
            modalErrorAlert.textContent = message;
            modalErrorAlert.classList.remove('d-none');

            // 3秒後隱藏錯誤消息
            setTimeout(() => {
                modalErrorAlert.classList.add('d-none');
            }, 3000);
        }

        // 添加通用的 fetch 處理函數
        async function handleFetch(formData) {
            try {
                const response = await fetch('record_claim.php', {
                    method: 'POST',
                    body: formData
                });

                let data;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    try {
                        data = await response.json();
                    } catch (e) {
                        throw new Error('Invalid data format returned by the server');
                    }
                } else {
                    throw new Error('Invalid data format returned by the server');
                }

                if (!data.success) {
                    throw new Error(data.message || 'Operation failed, please try again');
                }

                return data;
            } catch (error) {
                console.error('Error:', error);
                throw new Error(error.message || 'Operation failed, please try again');
            }
        }

        // 添加控制索賠金額限制狀態的函數
        function setClaimLimitStatus(isLimited) {
            const claimInput = document.getElementById('claim_amount');
            const updateBtn = document.getElementById('updateClaimBtn');
            const maxClaimWarning = document.getElementById('maxClaimWarning');
            const cancelPolicyBtn = document.getElementById('cancelPolicyBtn');
            const correctAmountBtn = document.querySelector('[data-bs-target="#correctAmountModal"]');
            const policyStatus = '<?php echo $policy_info['Policy_Status']; ?>';

            // 如果保單狀態不是Inforce，禁用所有編輯功能
            if (policyStatus !== 'Inforce') {
                claimInput.disabled = true;
                updateBtn.disabled = true;
                correctAmountBtn.disabled = true;
                return;
            }

            if (isLimited) {
                claimInput.disabled = true;
                updateBtn.disabled = true;
                maxClaimWarning.classList.remove('d-none');
                cancelPolicyBtn.style.display = 'inline-block';
            } else {
                claimInput.disabled = false;
                updateBtn.disabled = false;
                maxClaimWarning.classList.add('d-none');
                cancelPolicyBtn.style.display = 'none';
            }
        }

        // 在頁面加載時檢查索賠金額限制狀態
        document.addEventListener('DOMContentLoaded', function() {
            const policyStatus = '<?php echo $policy_info['Policy_Status']; ?>';

            // 如果保單狀態不是Inforce，直接禁用所有編輯功能
            if (policyStatus !== 'Inforce') {
                setClaimLimitStatus(true);
                return;
            }

            const currentAmount = parseFloat(document.querySelector('.current-claim-amount').textContent.replace(/,/g, ''));
            const totalCoverage = parseFloat(document.querySelector('.remaining-amount').textContent.replace(/,/g, '')) + currentAmount;

            // 如果當前索賠金額等於總保額，則設置為限制狀態
            if (currentAmount >= totalCoverage) {
                setClaimLimitStatus(true);
            }
        });

        // 更新索賠金額顯示的函數
        function updateClaimAmount(newAmount, totalCoverage) {
            // 移除所有逗號後再解析數值
            newAmount = parseFloat(newAmount.toString().replace(/,/g, ''));
            totalCoverage = parseFloat(totalCoverage.toString().replace(/,/g, ''));
            const currentAmount = parseFloat(document.querySelector('.current-claim-amount').textContent.replace(/,/g, ''));

            // 驗證新金額是否為負數且超過當前索賠金額
            if (newAmount < 0) {
                const absAmount = Math.abs(newAmount);
                if (absAmount > currentAmount) {
                    alert('Claim amount cannot be negative and cannot exceed the current claim amount！');
                    return;
                }
            }

            // 驗證新金額是否超過總保額
            if (newAmount > totalCoverage) {
                alert('Claim amount cannot exceed total coverage！');
                return;
            }

            // 驗證新金額是否小於0
            if (newAmount < 0) {
                alert('Claim amount cannot be less than 0！');
                return;
            }

            const remainingAmount = (totalCoverage - newAmount).toFixed(2);

            // 更新顯示的金額
            document.querySelector('.current-claim-amount').textContent = newAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            document.querySelector('.remaining-amount').textContent = remainingAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

            // 檢查是否需要更新限制狀態
            if (newAmount >= totalCoverage) {
                setClaimLimitStatus(true);
            } else {
                setClaimLimitStatus(false);
            }

            // 更新隱藏輸入框的值
            document.getElementById('claim_amount').value = newAmount;

            // 更新浮動數字
            const floatingNumber = document.createElement('div');
            floatingNumber.className = 'floating-number ' + (newAmount > currentAmount ? 'positive' : 'negative');
            floatingNumber.textContent = (newAmount > currentAmount ? '+' : '') + (newAmount - currentAmount).toFixed(2);

            const container = document.querySelector('.claim-amount-container');
            container.appendChild(floatingNumber);

            // 3秒後移除浮動數字
            setTimeout(() => {
                floatingNumber.remove();
            }, 3000);
        }

        document.getElementById('claimForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const claimAmount = parseFloat(document.getElementById('claim_amount').value.replace(/,/g, ''));
            const remainingAmount = parseFloat(document.querySelector('.remaining-amount').textContent.replace(/,/g, ''));

            // 前端驗證
            if (isNaN(claimAmount)) {
                showError('Please enter a valid claim amount');
                return;
            }

            // 驗證是否超過剩餘可報銷金額
            if (claimAmount > remainingAmount) {
                showError('Claim amount cannot exceed the remaining claim amount (MOP ' + remainingAmount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ')');
                return;
            }

            try {
                const data = await handleFetch(formData);
                // 更新索賠金額顯示
                updateClaimAmount(data.new_amount, data.total_coverage);

                // 創建浮動數字
                const floatingNumber = document.createElement('div');
                floatingNumber.className = 'floating-number';

                // 根據金額的正負值設置不同的樣式和顯示格式
                if (claimAmount >= 0) {
                    floatingNumber.classList.add('positive');
                    floatingNumber.textContent = '+' + claimAmount.toFixed(2);
                } else {
                    floatingNumber.classList.add('negative');
                    floatingNumber.textContent = claimAmount.toFixed(2);
                }

                // 獲取索賠金額容器的位置
                const container = document.querySelector('.claim-amount-container');
                const rect = container.getBoundingClientRect();

                // 設置浮動數字的位置
                floatingNumber.style.left = (rect.right - 100) + 'px';
                floatingNumber.style.top = (rect.top - 20) + 'px';

                // 添加浮動數字到頁面
                document.body.appendChild(floatingNumber);

                // 動畫結束後移除元素
                floatingNumber.addEventListener('animationend', () => {
                    setTimeout(() => {
                        floatingNumber.remove();
                    }, 2000);
                });

                // 顯示成功消息
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success';
                successAlert.textContent = data.message;
                document.querySelector('.card-body').insertBefore(successAlert, document.querySelector('.card-body').firstChild);

                // 清空輸入框
                document.getElementById('claim_amount').value = '';

                // 3秒後移除成功消息
                setTimeout(() => {
                    successAlert.remove();
                }, 3000);
            } catch (error) {
                showError(error.message);
            }
        });

        // 修改校正索賠金額的處理邏輯
        document.getElementById('confirmCorrectAmount').addEventListener('click', async function() {
            const form = document.getElementById('correctAmountForm');
            const formData = new FormData(form);
            // 移除所有逗號後再解析數值
            const correctAmount = parseFloat(document.getElementById('correct_amount').value.replace(/,/g, ''));

            // 前端驗證
            if (isNaN(correctAmount)) {
                showModalError('Please enter a valid claim amount');
                return;
            }

            try {
                const data = await handleFetch(formData);
                // 更新索賠金額顯示
                const newAmount = parseFloat(data.new_amount.replace(/,/g, ''));
                const totalCoverage = parseFloat(data.total_coverage);

                // 更新當前索賠金額
                document.querySelector('.current-claim-amount').textContent = newAmount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // 更新剩餘索賠金額
                const remainingAmount = (totalCoverage - newAmount).toFixed(2);
                document.querySelector('.remaining-amount').textContent = remainingAmount.replace(/\B(?=(\d{3})+(?!\d))/g, ",");

                // 創建浮動數字
                const currentAmount = parseFloat(document.querySelector('.current-claim-amount').textContent.replace(/,/g, ''));
                const difference = correctAmount - currentAmount;
                createFloatingNumber(difference);

                // 關閉模態框
                const modal = bootstrap.Modal.getInstance(document.getElementById('correctAmountModal'));
                modal.hide();

                // 清空輸入框
                document.getElementById('correct_amount').value = '';

                // 顯示成功消息
                showSuccess('Claim amount has been successfully updated');

                // 檢查是否需要更新限制狀態
                if (newAmount >= totalCoverage) {
                    setClaimLimitStatus(true);
                } else {
                    setClaimLimitStatus(false);
                }
            } catch (error) {
                showModalError(error.message);
            }
        });

        // 添加輸入驗證函數
        function validateEnterClaimAmount(input) {
            const value = parseFloat(input.value);
            const currentAmount = parseFloat(document.querySelector('.current-claim-amount').textContent.replace(/,/g, ''));
            const remainingAmount = parseFloat(document.querySelector('.remaining-amount').textContent.replace(/,/g, ''));

            if (isNaN(value)) {
                input.value = '0.00';
                return;
            }

            if (value > 0) {
                // 正數不能超過剩餘可報銷金額
                if (value > remainingAmount) {
                    input.value = remainingAmount.toFixed(2);
                } else {
                    input.value = value.toFixed(2);
                }
            } else {
                // 負數的絕對值不能超過當前索賠金額
                const absValue = Math.abs(value);
                if (absValue > currentAmount) {
                    input.value = (-currentAmount).toFixed(2);
                } else {
                    input.value = value.toFixed(2);
                }
            }
        }

        // 添加校正索賠金額輸入框的專門驗證函數
        function validateCorrectAmountInput(input) {
            // 移除所有逗號
            const rawValue = input.value.replace(/,/g, '');
            const value = parseFloat(rawValue);
            const totalCoverage = <?php echo $policy_info['Total_Coverage']; ?>;

            if (isNaN(value)) {
                input.value = '';
                return;
            }

            // 限制在0到總保額之間
            if (value < 0) {
                input.value = '0.00';
            } else if (value > totalCoverage) {
                input.value = totalCoverage.toFixed(2);
            } else {
                // 確保顯示兩位小數
                input.value = value.toFixed(2);
            }
        }

        // 添加事件監聽器
        document.addEventListener('DOMContentLoaded', function() {
            // Enter Claim Amount 輸入框驗證
            const enterClaimInput = document.getElementById('claim_amount');
            if (enterClaimInput) {
                enterClaimInput.addEventListener('blur', function() {
                    validateEnterClaimAmount(this);
                });
            }

            // Correct Claim Amount 輸入框驗證
            const correctAmountInput = document.getElementById('correct_amount');
            if (correctAmountInput) {
                correctAmountInput.addEventListener('blur', function() {
                    validateCorrectAmountInput(this);
                });
            }
        });

        // 添加回車鍵處理
        document.getElementById('correct_amount').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('confirmCorrectAmount').click();
            }
        });

        // 添加浮動數字創建函數
        function createFloatingNumber(difference) {
            const floatingNumber = document.createElement('div');
            floatingNumber.className = 'floating-number';

            // 根據差值設置不同的樣式和顯示格式
            if (difference >= 0) {
                floatingNumber.classList.add('positive');
                floatingNumber.textContent = '+' + difference.toFixed(2);
            } else {
                floatingNumber.classList.add('negative');
                floatingNumber.textContent = difference.toFixed(2);
            }

            // 獲取索賠金額容器的位置
            const container = document.querySelector('.claim-amount-container');
            const rect = container.getBoundingClientRect();

            // 設置浮動數字的位置
            floatingNumber.style.left = (rect.right - 100) + 'px';
            floatingNumber.style.top = (rect.top - 20) + 'px';

            // 添加浮動數字到頁面
            document.body.appendChild(floatingNumber);

            // 動畫結束後移除元素
            floatingNumber.addEventListener('animationend', () => {
                setTimeout(() => {
                    floatingNumber.remove();
                }, 2000);
            });
        }

        // 添加成功消息顯示函數
        function showSuccess(message) {
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success';
            successAlert.textContent = message;
            document.querySelector('.card-body').insertBefore(successAlert, document.querySelector('.card-body').firstChild);

            // 3秒後移除成功消息
            setTimeout(() => {
                successAlert.remove();
            }, 3000);
        }

        // 添加取消保單的處理函數
        async function handleCancelPolicy() {
            const policyNo = '<?php echo $policy_info['Policy_No']; ?>';

            // 第一次確認
            if (!confirm('Are you sure you want to cancel this policy? This action cannot be undone.\n\nPolicy Number: ' + policyNo)) {
                return;
            }

            // 第二次確認
            if (!confirm('Please confirm again: Are you sure you want to cancel this policy?\n\nPolicy Number: ' + policyNo)) {
                return;
            }

            // 第三次確認
            if (!confirm('Final confirmation: Are you sure you want to cancel this policy?\n\nPolicy Number: ' + policyNo + '\n\nAfter clicking confirm, the policy will be canceled, and the page will reload in 3 seconds.')) {
                return;
            }

            try {
                const response = await fetch('cancel_policy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        policy_no: policyNo
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess('Policy has been successfully canceled');
                    // 3秒後重新加載當前頁面
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    showError(data.message || 'Policy cancellation failed, please try again');
                }
            } catch (error) {
                showError('Error occurred while canceling policy, please try again');
            }
        }

        // 添加取消保單按鈕的事件監聽器
        document.addEventListener('DOMContentLoaded', function() {
            const cancelPolicyBtn = document.getElementById('cancelPolicyBtn');
            if (cancelPolicyBtn) {
                cancelPolicyBtn.addEventListener('click', handleCancelPolicy);
            }
        });
    </script>
</body>

</html>