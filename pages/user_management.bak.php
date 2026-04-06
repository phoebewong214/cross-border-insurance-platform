<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Insurance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: #01459C !important;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #01459C !important;
        }

        .back-button {
            position: absolute;
            top: 2rem;
            left: 2rem;
            padding: 0.5rem 1rem;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            color: #01459C;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: #f8f9fa;
            color: #013579;
            text-decoration: none;
        }

        .container {
            max-width: 1200px;
            margin-top: 5rem;
        }

        .page-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
        }

        .user-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .car-count {
            background: #01459C;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .car-list {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .car-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .car-details {
            flex: 1;
        }

        .car-registration {
            font-weight: 500;
            color: #01459C;
        }

        .car-model {
            color: #666;
            font-size: 0.9rem;
        }

        .no-cars {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 1rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            padding: 1rem;
            background: #fff3f3;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .stats-container {
            margin-top: 3rem;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .stats-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .stats-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stats-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #01459C;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .stats-users {
            text-align: left;
            max-height: 200px;
            overflow-y: auto;
        }

        .stats-user-item {
            padding: 0.5rem;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .stats-user-item:last-child {
            border-bottom: none;
        }

        .stats-user-name {
            color: #333;
            font-weight: 500;
        }

        .stats-user-cars {
            color: #666;
            font-size: 0.8rem;
        }

        .stats-user-discount {
            color: #666;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .edit-discount-btn {
            background: none;
            border: none;
            color: #01459C;
            cursor: pointer;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
        }

        .edit-discount-btn:hover {
            color: #013579;
        }

        .discount-input {
            width: 60px;
            padding: 0.2rem 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .save-discount-btn {
            background: #01459C;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .save-discount-btn:hover {
            background: #013579;
        }

        .discount-info {
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .discount-item {
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .discount-item:last-child {
            margin-bottom: 0;
        }

        .discount-icon {
            color: #01459C;
            font-size: 1.5rem;
        }

        .discount-text {
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Toast Styles */
        .toast {
            background-color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 400px;
            z-index: 1050;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 12px;
            overflow: hidden;
        }

        .toast-header {
            border-bottom: none;
            background-color: transparent;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toast-body {
            padding: 1.5rem;
            color: #333;
            text-align: center;
            font-weight: 500;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .toast-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1040;
        }

        .bg-success {
            background-color: white !important;
        }

        .bg-danger {
            background-color: white !important;
        }

        .toast.show {
            display: block !important;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .toast-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .toast-icon.success {
            color: #28a745;
        }

        .toast-icon.error {
            color: #dc3545;
        }

        .toast-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 0.5rem;
            padding-bottom: 1rem;
        }

        .toast-button {
            padding: 0.5rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 100px;
        }

        .toast-button.confirm {
            background-color: #01459C;
            color: white;
            border: none;
        }

        .toast-button.confirm:hover {
            background-color: #013579;
        }

        .toast-button.cancel {
            background-color: #f8f9fa;
            color: #666;
            border: 1px solid #dee2e6;
        }

        .toast-button.cancel:hover {
            background-color: #e9ecef;
        }

        .recommendation-box {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 3rem;
        }

        .recommendation-title {
            color: #01459C;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .recommendation-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .recommendation-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.3s ease;
        }

        .recommendation-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .recommendation-icon {
            font-size: 2.5rem;
            color: #01459C;
            margin-bottom: 1rem;
        }

        .recommendation-text h3 {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .recommendation-text p {
            color: #666;
            margin-bottom: 1rem;
        }

        .recommendation-text ul {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .recommendation-text ul li {
            color: #666;
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .recommendation-text ul li:before {
            content: "•";
            color: #01459C;
            position: absolute;
            left: 0;
        }

        .highlight {
            color: #01459C;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .threshold-input {
            width: 60px;
            padding: 0.2rem 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.9rem;
            margin: 0 0.3rem;
        }

        .threshold-input:focus {
            outline: none;
            border-color: #01459C;
            box-shadow: 0 0 0 0.2rem rgba(1, 69, 156, 0.25);
        }

        .history-list {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
        }
        
        .history-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .history-item p {
            margin: 5px 0;
            color: #666;
        }
        
        .history-item .history-date {
            color: #01459C;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <a href="../admin.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Admin Center
    </a>

    <div class="container">
        <h1 class="page-title">Disount Settings</h1>
        <div class="stats-container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stats-box">
                        <div class="stats-number" id="highCarCount">0</div>
                        <div class="stats-label">
                            Users with ≥ <span class="threshold-display" id="highThresholdDisplay">7</span> Transactions
                        </div>
                        <div class="discount-setting mb-3">
                            <label class="form-label">Discount Rate (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="highDiscount" min="0" max="100" value="15">
                            </div>
                        </div>
                        <div class="stats-users" id="highTransactionUsers">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-box">
                        <div class="stats-number" id="mediumCarCount">0</div>
                        <div class="stats-label">
                            Users with <input type="number" class="threshold-input" id="mediumLowThreshold" min="1" value="4"> - 
                            <input type="number" class="threshold-input" id="mediumHighThreshold" min="1" value="7"> Transactions
                        </div>
                        <div class="discount-setting mb-3">
                            <label class="form-label">Discount Rate (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="mediumDiscount" min="0" max="100" value="10">
                            </div>
                        </div>
                        <div class="stats-users" id="mediumTransactionUsers">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-box">
                        <div class="stats-number" id="lowCarCount">0</div>
                        <div class="stats-label">
                            Users with ≤ <span class="threshold-display" id="lowThresholdDisplay">3</span> Transactions
                        </div>
                        <div class="stats-users" id="lowTransactionUsers">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <button class="btn btn-primary" onclick="confirmAllDiscountUpdates()">Confirm All Changes</button>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="recommendation-box">
            <h2 class="recommendation-title">Discount History</h2>
            <div class="recommendation-content">
                <div class="recommendation-item">
                    <i class="fas fa-history recommendation-icon"></i>
                    <div class="recommendation-text">
                        <h3>Current Settings</h3>
                        <p>High Transaction Threshold: <span class="highlight" id="currentHighThreshold">7</span>+</p>
                        <p>Medium Transaction Range: <span class="highlight" id="currentMediumLow">7</span> - <span class="highlight" id="currentMediumHigh">3</span></p>
                        <p>High Transaction Discount: <span class="highlight" id="currentHighDiscount">15</span>%</p>
                        <p>Medium Transaction Discount: <span class="highlight" id="currentMediumDiscount">10</span>%</p>
                        <p>Last Updated: <span class="highlight" id="lastUpdated">-</span></p>
                    </div>
                </div>
                <div class="recommendation-item">
                    <i class="fas fa-chart-line recommendation-icon"></i>
                    <div class="recommendation-text">
                        <h3>Previous 5 Settings</h3>
                        <div id="historyList" class="history-list">
                            <!-- 历史记录将通过JavaScript动态添加 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to fetch users and their transaction counts
        async function fetchUsers() {
            try {
                const response = await fetch('process_user_management.php?action=get_users');
                const data = await response.json();

                if (data.success) {
                    displayStats(data.users);
                } else {
                    showError(data.message);
                }
            } catch (error) {
                showError('Error fetching user data');
                console.error('Error:', error);
            }
        }

        // Function to display statistics
        function displayStats(users) {
            const mediumHighThreshold = parseInt(document.getElementById('mediumHighThreshold').value);
            const mediumLowThreshold = parseInt(document.getElementById('mediumLowThreshold').value);
            const highThreshold = mediumHighThreshold + 1;
            const lowThreshold = mediumLowThreshold - 1;

            // 先对所有用户按ID排序
            users.sort((a, b) => parseInt(a.User_id) - parseInt(b.User_id));

            const highTransactionUsers = users.filter(user => user.transaction_count >= highThreshold);
            const mediumTransactionUsers = users.filter(user => 
                user.transaction_count >= mediumLowThreshold && 
                user.transaction_count <= mediumHighThreshold
            );
            const lowTransactionUsers = users.filter(user => user.transaction_count <= lowThreshold);

            // Update counts
            document.getElementById('highCarCount').textContent = highTransactionUsers.length;
            document.getElementById('mediumCarCount').textContent = mediumTransactionUsers.length;
            document.getElementById('lowCarCount').textContent = lowTransactionUsers.length;

            // Update user lists with proper HTML structure
            document.getElementById('highTransactionUsers').innerHTML = highTransactionUsers.length > 0 ? 
                highTransactionUsers.map(user => `
                    <div class="stats-user-item">
                        <div class="stats-user-name">ID: ${user.User_id} - ${user.User_name}</div>
                        <div class="stats-user-cars">${user.transaction_count} transactions</div>
                    </div>
                `).join('') : '<div class="stats-user-item"><div class="stats-user-name">No users</div></div>';

            document.getElementById('mediumTransactionUsers').innerHTML = mediumTransactionUsers.length > 0 ? 
                mediumTransactionUsers.map(user => `
                    <div class="stats-user-item">
                        <div class="stats-user-name">ID: ${user.User_id} - ${user.User_name}</div>
                        <div class="stats-user-cars">${user.transaction_count} transactions</div>
                    </div>
                `).join('') : '<div class="stats-user-item"><div class="stats-user-name">No users</div></div>';

            document.getElementById('lowTransactionUsers').innerHTML = lowTransactionUsers.length > 0 ? 
                lowTransactionUsers.map(user => `
                    <div class="stats-user-item">
                        <div class="stats-user-name">ID: ${user.User_id} - ${user.User_name}</div>
                        <div class="stats-user-cars">${user.transaction_count} transactions</div>
                    </div>
                `).join('') : '<div class="stats-user-item"><div class="stats-user-name">No users</div></div>';
        }

        // Function to display error messages
        function showError(message) {
            const statsContainer = document.querySelector('.stats-container');
            statsContainer.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> ${message}
                </div>
            `;
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', () => {
            // 首先获取并应用最新设置
            fetchAndApplyLatestSettings();

            // Add event listeners for threshold inputs
            const thresholdInputs = document.querySelectorAll('.threshold-input');
            thresholdInputs.forEach(input => {
                input.addEventListener('input', () => {
                    const mediumHighThreshold = parseInt(document.getElementById('mediumHighThreshold').value);
                    const mediumLowThreshold = parseInt(document.getElementById('mediumLowThreshold').value);
                    
                    if (!isNaN(mediumHighThreshold) && !isNaN(mediumLowThreshold)) {
                        // 更新显示
                        document.getElementById('highThresholdDisplay').textContent = mediumHighThreshold + 1;
                        document.getElementById('lowThresholdDisplay').textContent = mediumLowThreshold - 1;
                        
                        // 更新用户列表
                        fetchUsers();
                    }
                });
            });
        });

        // 确认所有折扣更新
        async function confirmAllDiscountUpdates() {
            const mediumHighThreshold = parseInt(document.getElementById('mediumHighThreshold').value);
            const mediumLowThreshold = parseInt(document.getElementById('mediumLowThreshold').value);
            
            // 验证上下限
            if (mediumHighThreshold <= mediumLowThreshold) {
                showToast('error', 'Upper limit must be greater than lower limit');
                return;
            }
            
            const highDiscount = parseInt(document.getElementById('highDiscount').value);
            const mediumDiscount = parseInt(document.getElementById('mediumDiscount').value);
            
            // 验证折扣率
            if (isNaN(highDiscount) || highDiscount < 0 || highDiscount > 100 ||
                isNaN(mediumDiscount) || mediumDiscount < 0 || mediumDiscount > 100) {
                showToast('error', 'Discount rates must be between 0 and 100');
                return;
            }
            
            // 获取高交易量用户ID
            const highTransactionUsers = Array.from(document.querySelectorAll('#highTransactionUsers tbody tr'))
                .map(row => row.getAttribute('data-user-id'));
            
            // 获取中等交易量用户ID
            const mediumTransactionUsers = Array.from(document.querySelectorAll('#mediumTransactionUsers tbody tr'))
                .map(row => row.getAttribute('data-user-id'));
            
            try {
                const response = await fetch('process_user_management.php?action=update_discount', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        high_discount: highDiscount,
                        medium_discount: mediumDiscount,
                        high_user_ids: highTransactionUsers,
                        medium_user_ids: mediumTransactionUsers,
                        high_threshold: mediumHighThreshold + 1,
                        medium_high_threshold: mediumHighThreshold,
                        medium_low_threshold: mediumLowThreshold
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    showToast('success', 'Discount rates updated successfully');
                    // 更新推荐部分的折扣显示
                    document.getElementById('currentHighDiscount').textContent = highDiscount;
                    document.getElementById('currentMediumDiscount').textContent = mediumDiscount;
                } else {
                    showToast('error', data.message || 'Failed to update discount rates');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'An error occurred while updating discount rates');
            }
        }

        // Function to fetch current discount rates
        async function fetchDiscountRates() {
            try {
                const response = await fetch('process_user_management.php?action=get_discounts');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('highDiscount').value = data.high_discount || 15;
                    document.getElementById('mediumDiscount').value = data.medium_discount || 10;
                }
            } catch (error) {
                console.error('Error fetching discount rates:', error);
            }
        }

        // Update recommendation section with current discount rates
        async function updateRecommendationDiscounts() {
            try {
                const response = await fetch('process_user_management.php?action=get_discounts');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('currentHighDiscount').textContent = data.high_discount || 15;
                    document.getElementById('currentMediumDiscount').textContent = data.medium_discount || 10;
                }
            } catch (error) {
                console.error('Error fetching discount rates:', error);
            }
        }

        // 获取并应用最新设置
        async function fetchAndApplyLatestSettings() {
            try {
                const response = await fetch('process_user_management.php?action=get_latest_settings');
                const data = await response.json();
                
                if (data.success && data.settings) {
                    // 更新过滤范围
                    document.getElementById('highThresholdDisplay').textContent = data.settings.high_threshold;
                    document.getElementById('mediumHighThreshold').value = data.settings.medium_high_threshold;
                    document.getElementById('mediumLowThreshold').value = data.settings.medium_low_threshold;
                    document.getElementById('lowThresholdDisplay').textContent = data.settings.medium_low_threshold - 1;
                    
                    // 更新折扣率
                    document.getElementById('highDiscount').value = data.settings.high_discount;
                    document.getElementById('mediumDiscount').value = data.settings.medium_discount;
                    
                    // 更新当前设置显示
                    document.getElementById('currentHighThreshold').textContent = data.settings.high_threshold;
                    document.getElementById('currentMediumHigh').textContent = data.settings.medium_high_threshold;
                    document.getElementById('currentMediumLow').textContent = data.settings.medium_low_threshold;
                    document.getElementById('currentHighDiscount').textContent = data.settings.high_discount;
                    document.getElementById('currentMediumDiscount').textContent = data.settings.medium_discount;
                    document.getElementById('lastUpdated').textContent = new Date(data.settings.created_at).toLocaleString();
                    
                    console.log('Settings applied:', data.settings);
                    
                    // 设置应用后获取用户列表
                    fetchUsers();
                    
                    // 获取历史记录
                    fetchHistory();
                }
            } catch (error) {
                console.error('Error fetching settings:', error);
            }
        }

        // 获取历史记录
        async function fetchHistory() {
            try {
                const response = await fetch('process_user_management.php?action=get_history');
                const data = await response.json();
                
                if (data.success && data.history) {
                    const historyList = document.getElementById('historyList');
                    historyList.innerHTML = '';
                    
                    // 显示所有历史记录（最多5条）
                    const historyRecords = data.history;
                    
                    if (historyRecords.length === 0) {
                        historyList.innerHTML = '<p>No previous settings found</p>';
                        return;
                    }
                    
                    historyRecords.forEach(record => {
                        const historyItem = document.createElement('div');
                        historyItem.className = 'history-item';
                        historyItem.innerHTML = `
                            <p class="history-date">${new Date(record.created_at).toLocaleString()}</p>
                            <p>High Threshold: ${record.high_threshold}+</p>
                            <p>Medium Range: ${record.medium_high_threshold} - ${record.medium_low_threshold}</p>
                            <p>High Discount: ${record.high_discount}%</p>
                            <p>Medium Discount: ${record.medium_discount}%</p>
                        `;
                        historyList.appendChild(historyItem);
                    });
                } else {
                    document.getElementById('historyList').innerHTML = '<p>No previous settings found</p>';
                }
            } catch (error) {
                console.error('Error fetching history:', error);
                document.getElementById('historyList').innerHTML = '<p>Error loading history</p>';
            }
        }

        // Function to show toast notifications
        function showToast(type, message) {
            // Remove any existing toast containers
            const existingToasts = document.querySelectorAll('.toast-container');
            existingToasts.forEach(toast => toast.remove());

            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            
            const toast = document.createElement('div');
            toast.className = `toast align-items-center bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            
            toast.innerHTML = `
                <div class="d-flex flex-column">
                    <div class="toast-header">
                        <i class="fas fa-${icon} toast-icon ${type}"></i>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                    <div class="toast-buttons">
                        <button type="button" class="toast-button confirm" data-bs-dismiss="toast" aria-label="Close">
                            OK
                        </button>
                    </div>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);
            
            // Show the toast immediately
            toast.style.display = 'block';
            toast.style.opacity = '1';
            toast.style.transform = 'translate(-50%, -50%)';
            
            // Add click event listener to the OK button
            const okButton = toast.querySelector('.toast-button.confirm');
            okButton.addEventListener('click', () => {
                toastContainer.remove();
            });
        }
    </script>
</body>

</html> 