<?php
session_start();

// 檢查用戶是否已登錄
if (!isset($_SESSION['user_id'])) {
    // 如果是 AJAX 請求，返回 JSON 響應
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in',
            'redirect' => '../login.php'
        ]);
        exit;
    }

    // 如果是普通頁面請求，直接重定向到登錄頁面
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Query</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            padding-top: 2rem;
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
            padding: 2rem;
        }

        h1 {
            color: #01459C;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: #01459C !important;
        }

        .page-header {
            background: linear-gradient(135deg, #01459C, #0056bc);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .policy-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .policy-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .policy-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .policy-body {
            padding: 1.5rem;
        }

        .policy-footer {
            padding: 1.5rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
        }

        .status-inforce {
            background-color: #d4edda;
            color: #155724;
        }

        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-renewed {
            background-color: #cfe2ff;
            color: #084298;
        }

        .status-waiting {
            background-color: #fff3cd;
            color: #664d03;
        }

        .status-cancel {
            background-color: #e2e3e5;
            color: #41464b;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.75rem;
        }

        .info-label {
            width: 150px;
            font-weight: 600;
            color: #6c757d;
        }

        .info-value {
            flex: 1;
            color: #212529;
        }

        .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #01459C;
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #721c24;
        }

        .no-data-message {
            text-align: center;
            padding: 3rem 0;
            color: #6c757d;
        }

        .no-data-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #adb5bd;
        }

        .btn-primary {
            background-color: #01459C;
            border-color: #01459C;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #003a7d;
            border-color: #003a7d;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-warning:hover {
            background-color: #ffca2c;
            border-color: #ffc720;
            color: #000;
        }

        @media (max-width: 768px) {
            .policy-header {
                flex-direction: column;
                gap: 1rem;
            }

            .status-badge {
                align-self: flex-start;
            }

            .policy-footer {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        .alert-banner {
            background-color: #fff3cd;
            color: #856404;
            padding: 8px 0;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 4px;
            position: relative;
        }

        .close-banner {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 16px;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            position: relative;
        }

        .filter-toggle {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            color: #01459C;
        }

        .filter-content {
            transition: all 0.3s ease;
            display: none;
            /* 默認隱藏 */
        }

        .filter-content.expanded {
            display: block;
            /* 展開時顯示 */
        }

        .filter-group {
            margin-bottom: 10px;
        }

        .filter-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .filter-select {
            width: 100%;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 4px;
            min-height: 38px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #01459C;
            border: none;
            color: white;
            margin-top: 5px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }

        .filter-actions {
            margin-top: 15px;
            text-align: right;
        }

        .filter-btn {
            background-color: #01459C;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }

        .filter-btn:hover {
            background-color: #003a7d;
        }

        .filter-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="../index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Personal Center
        </a>
        <h1>Policy Management</h1>

        <!-- 滾動橫幅 -->
        <div class="alert-banner" id="alertBanner">
            <span>If you are involved in an accident, please call the police and notify our claims team at +853 2800 0000.</span>
            <span class="close-banner" onclick="closeBanner()">&times;</span>
        </div>

        <!-- 篩選器部分 -->
        <div class="filter-section">
            <i class="fas fa-filter filter-toggle" onclick="toggleFilters()"></i>
            <div class="filter-content" id="filterContent">
                <div class="row">
                    <div class="col-md-4">
                        <div class="filter-group">
                            <label class="filter-label">Product</label>
                            <select class="filter-select" id="productFilter" multiple>
                                <option value="all">All</option>
                                <option value="MTJ">Statutory Automobile Liability Insurance</option>
                                <option value="MTZ">Commercial Insurance Package</option>
                                <option value="MTY">Hong Kong Private Car Insurance</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="filter-group">
                            <label class="filter-label">Status</label>
                            <select class="filter-select" id="statusFilter" multiple>
                                <option value="all">All</option>
                                <option value="inforce">Inforce</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="renewed">Renewed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="filter-group">
                            <label class="filter-label">Plate Number</label>
                            <select class="filter-select" id="plateFilter" multiple>
                                <option value="all">All</option>
                                <!-- Plate number options will be dynamically added by JavaScript -->
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="policies-container">
            <!-- Loading Indicator -->
            <div id="loading" class="loading-container">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadPolicies();
        });

        function loadPolicies() {
            const loadingElement = document.getElementById('loading');
            const container = document.querySelector('.policies-container');

            // 获取当前用户ID
            fetch('../config/get_current_user.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success || !data.user_id) {
                        throw new Error('Failed to get current user');
                    }

                    // 使用用户ID获取保单
                    return fetch(`policy_processor.php?action=get_policies_by_user&user_id=${data.user_id}`);
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Network response not ok (${response.status}): ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    loadingElement.style.display = 'none';

                    if (!data.success) {
                        container.innerHTML = `
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                ${data.message || 'Failed to load policies'}
                            </div>`;
                        return;
                    }

                    if (!data.policies || data.policies.length === 0) {
                        container.innerHTML = `
                            <div class="no-data-message">
                                <i class="fas fa-file-alt no-data-icon"></i>
                                <p>No policies found</p>
                            </div>`;
                        return;
                    }

                    const policiesHTML = data.policies.map(policy => {
                        // Format dates
                        const issueDate = policy.Policy_Issue_Date ? new Date(policy.Policy_Issue_Date).toLocaleDateString() : '-';
                        const startDate = policy.insurance_start ? new Date(policy.insurance_start).toLocaleDateString() : '-';
                        const endDate = policy.insurance_end ? new Date(policy.insurance_end).toLocaleDateString() : '-';

                        // 计算实际的保单状态
                        const today = new Date();
                        const start = policy.insurance_start ? new Date(policy.insurance_start) : null;
                        const end = policy.insurance_end ? new Date(policy.insurance_end) : null;

                        // 根据日期判断实际状态
                        let actualStatus = policy.Policy_Status;
                        if (start && end) {
                            if (today < start) {
                                actualStatus = 'Waiting';
                            } else if (today >= start && today <= end) {
                                actualStatus = 'Inforce';
                            } else if (today > end) {
                                actualStatus = 'Expired';
                            }
                        }

                        // 计算距离到期天数
                        const daysUntilExpiry = calculateDaysUntilExpiry(policy.insurance_end);

                        // 获取状态样式和文本
                        let statusClass = '';
                        let statusText = actualStatus || '-';

                        switch (actualStatus) {
                            case 'Inforce':
                                statusClass = 'status-inforce';
                                statusText = 'In Force';
                                break;
                            case 'Expired':
                                statusClass = 'status-expired';
                                statusText = 'Expired';
                                break;
                            case 'Renewed':
                                statusClass = 'status-renewed';
                                statusText = 'Renewed';
                                break;
                            case 'Waiting':
                                statusClass = 'status-waiting';
                                statusText = 'Waiting';
                                break;
                            case 'Cancel':
                                statusClass = 'status-cancel';
                                statusText = 'Cancelled';
                                break;
                        }

                        // 获取状态提示信息
                        let statusMessage = '';
                        if (actualStatus === 'Expired') {
                            statusMessage = `<div class="text-danger mt-1">
                                <i class="fas fa-exclamation-circle"></i> 
                                Policy has expired
                            </div>`;
                        } else if (actualStatus === 'Inforce' && daysUntilExpiry <= 30 && daysUntilExpiry > 0) {
                            statusMessage = `<div class="text-warning mt-1">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Expires in ${daysUntilExpiry} days
                            </div>`;
                        }

                        return `
                            <div class="policy-card" 
                                data-product-id="${policy.Policy_No.substring(1, 4)}" 
                                data-status="${actualStatus.toLowerCase()}" 
                                data-plate-number="${policy.vehicle_id || ''}">
                                <div class="policy-header">
                                    <div>
                                        <h5 class="mb-0">Policy #${policy.Policy_No || '-'}</h5>
                                        <small class="text-muted">Issue Date: ${issueDate}</small>
                                    </div>
                                    <div class="status-badge ${statusClass}">
                                        ${statusText}
                                    </div>
                                </div>
                                <div class="policy-body">
                                    <div class="info-row">
                                        <div class="info-label">Vehicle:</div>
                                        <div class="info-value">${policy.vehicle_id || '-'}</div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Product Name:</div>
                                        <div class="info-value">${policy.product_name || '-'}</div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Coverage:</div>
                                        <div class="info-value">${policy.coverage ? 'MOP ' + parseFloat(policy.coverage).toLocaleString() : '-'}</div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Premium:</div>
                                        <div class="info-value">${policy.premium ? 'MOP ' + parseFloat(policy.premium).toLocaleString() : '-'}</div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-label">Period:</div>
                                        <div class="info-value">${startDate} to ${endDate}</div>
                                    </div>
                                </div>
                                <div class="policy-footer">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            ${statusMessage}
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-primary" onclick="viewPolicyDetails('${policy.Policy_No}')">
                                                <i class="fas fa-file-alt me-2"></i>View Details
                                            </button>
                                            ${actualStatus === 'Inforce' && daysUntilExpiry <= 30 && daysUntilExpiry > 0 && !policy.Renew_Policy_No ? 
                                                `<button class="btn btn-warning" onclick="renewPolicy('${policy.Policy_No}')">
                                                    <i class="fas fa-sync-alt me-2"></i>Renew
                                                </button>` : 
                                                ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');

                    container.innerHTML = policiesHTML;

                    // 在保單加載完成後初始化篩選器
                    initializeFilters();
                })
                .catch(error => {
                    loadingElement.style.display = 'none';
                    container.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${error.message || 'An error occurred while loading policies'}
                        </div>`;
                });
        }

        function calculateDaysUntilExpiry(endDate) {
            if (!endDate) return -1;
            const end = new Date(endDate);
            const today = new Date();
            const diffTime = end - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays;
        }

        function viewPolicyDetails(policyNo) {
            window.location.href = `policy_detail.php?policy_no=${policyNo}`;
        }

        function renewPolicy(policyNo) {
            window.location.href = `renew_policy.php?id=${policyNo}`;
        }

        // 橫幅控制
        function closeBanner() {
            document.getElementById('alertBanner').style.display = 'none';
        }

        // 篩選器收縮功能
        function toggleFilters() {
            const filterContent = document.getElementById('filterContent');
            filterContent.classList.toggle('expanded');
        }

        // 初始化篩選器
        function initializeFilters() {
            // 初始化選擇器
            $('.filter-select').select2({
                placeholder: 'Please select',
                allowClear: true,
                width: '100%',
                closeOnSelect: false
            }).on('change', function() {
                // 當選擇改變時自動應用篩選
                applyFilters();
            });

            // Get all policy plate numbers
            const policies = document.querySelectorAll('.policy-card');
            const plateNumbers = new Set();
            policies.forEach(policy => {
                const plateNumber = policy.getAttribute('data-plate-number');
                if (plateNumber) {
                    plateNumbers.add(plateNumber);
                }
            });

            // 動態添加車牌選項
            const plateFilter = document.getElementById('plateFilter');
            plateNumbers.forEach(plate => {
                const option = document.createElement('option');
                option.value = plate;
                option.textContent = plate;
                plateFilter.appendChild(option);
            });
        }

        // 應用篩選
        function applyFilters() {
            const policies = document.querySelectorAll('.policy-card');
            const selectedProducts = Array.from(document.getElementById('productFilter').selectedOptions).map(option => option.value);
            const selectedStatus = Array.from(document.getElementById('statusFilter').selectedOptions).map(option => option.value);
            const selectedPlates = Array.from(document.getElementById('plateFilter').selectedOptions).map(option => option.value);

            policies.forEach(policy => {
                const productCode = policy.getAttribute('data-product-id');
                const status = policy.getAttribute('data-status');
                const plateNumber = policy.getAttribute('data-plate-number');

                let showProduct = selectedProducts.length === 0 ||
                    selectedProducts.includes('all') ||
                    selectedProducts.includes(productCode);

                let showStatus = selectedStatus.length === 0 ||
                    selectedStatus.includes('all') ||
                    selectedStatus.includes(status);

                let showPlate = selectedPlates.length === 0 ||
                    selectedPlates.includes('all') ||
                    selectedPlates.includes(plateNumber);

                if (showProduct && showStatus && showPlate) {
                    policy.style.display = '';
                } else {
                    policy.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>