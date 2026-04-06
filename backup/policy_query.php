<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Query</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .page-header {
            background: linear-gradient(135deg, #01459C, #0056bc);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(255, 255, 255, 0.1) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(255, 255, 255, 0.1) 75%);
            background-size: 20px 20px;
            opacity: 0.1;
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .card-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            border-top: none;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-primary {
            background-color: #01459C;
            border-color: #01459C;
        }

        .btn-primary:hover {
            background-color: #003a7d;
            border-color: #003a7d;
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

        .loading-text {
            margin-left: 1rem;
            font-size: 1.1rem;
            color: #6c757d;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
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

        .footer {
            background-color: #f1f5f9;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-inforce {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-expired {
            background-color: #f8d7da;
            color: #842029;
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
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">Insurance Management System</a>
            <div class="ms-auto">
                <a href="../index.php" class="nav-link d-inline-block">Back to Home</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title">Policy Query</h1>
            <p class="page-subtitle">View and manage all your insurance policies</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="content-card">
            <h2 class="card-title">Policy List</h2>

            <!-- Loading Indicator -->
            <div id="loading" class="loading-container">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="loading-text">Loading policy data, please wait...</div>
            </div>

            <!-- Error Message -->
            <div id="error" class="error-message" style="display: none;">
                <i class="fas fa-exclamation-circle me-2"></i>
                <span id="errorText">Error loading data</span>
            </div>

            <!-- No Data Message -->
            <div id="noData" class="no-data-message" style="display: none;">
                <div class="no-data-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h4>No Policy Data</h4>
                <p class="text-muted">You currently don't have any insurance policy records</p>
            </div>

            <!-- Policy Table -->
            <div class="table-responsive">
                <table id="policyTable" class="table table-hover" style="display: none;">
                    <thead>
                        <tr>
                            <th>Policy No</th>
                            <th>Vehicle</th>
                            <th>Product Name</th>
                            <th>Coverage</th>
                            <th>Premium</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Issue Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="policyData">
                        <!-- Policy data will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p><i class="fas fa-phone me-2"></i> Customer Service: +853 1234 5678</p>
                    <p><i class="fas fa-envelope me-2"></i> Email: support@insurance.com</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>© 2023 Insurance Management System | All Rights Reserved</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadPolicies();
        });

        function loadPolicies() {
            // Show API request path for debugging
            console.log('Requesting: policy_processor.php?action=get_all_policies');

            /*
            // ==================== SESSION USER FILTERING - START ====================
            // 这段代码用于从Session中获取用户ID，并根据用户ID过滤保单
            // 当Session功能完成后，取消这段注释即可启用用户权限过滤
            
            // 构建API请求URL
            let apiUrl = 'policy_processor.php?action=get_all_policies';
            
            // 检查是否有用户登录（从Session中获取）
            // 假设Session中存储了用户ID和角色信息
            // 如果用户不是管理员，则只能查看自己的保单
            if (typeof sessionUserID !== 'undefined' && sessionUserID) {
                // 检查用户角色
                const isAdmin = (typeof sessionUserRole !== 'undefined' && sessionUserRole === 'admin');
                
                // 如果不是管理员，添加用户ID过滤条件
                if (!isAdmin) {
                    apiUrl += '&user_id=' + sessionUserID;
                    console.log('Filtering policies for user ID:', sessionUserID);
                } else {
                    console.log('Admin user, showing all policies');
                }
            } else {
                console.log('No user logged in or session not available');
                // 可以选择显示错误消息或重定向到登录页面
                // document.getElementById('error').style.display = 'block';
                // document.getElementById('errorText').textContent = 'Please login to view your policies';
                // return;
            }
            
            // 使用构建的URL发送请求
            fetch(apiUrl)
            // ==================== SESSION USER FILTERING - END ====================
            */

            // 原始代码保持不变
            fetch('policy_processor.php?action=get_all_policies')
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Error response:', text);
                            throw new Error(`Network response not ok (${response.status}): ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);
                    document.getElementById('loading').style.display = 'none';

                    if (!data.success) {
                        throw new Error(data.message || 'Failed to get policy data');
                    }

                    if (!data.policies || data.policies.length === 0) {
                        document.getElementById('noData').style.display = 'block';
                        return;
                    }

                    const tableBody = document.getElementById('policyData');
                    tableBody.innerHTML = '';

                    data.policies.forEach(policy => {
                        const row = document.createElement('tr');

                        // Format dates
                        const issueDate = policy.Policy_Issue_Date ? new Date(policy.Policy_Issue_Date).toLocaleDateString() : '-';
                        const startDate = policy.insurance_start ? new Date(policy.insurance_start).toLocaleDateString() : '-';
                        const endDate = policy.insurance_end ? new Date(policy.insurance_end).toLocaleDateString() : '-';

                        // Determine status style
                        let statusClass = '';
                        let statusText = policy.Policy_Status || '-';

                        if (policy.Policy_Status === 'Inforce') {
                            statusClass = 'status-inforce';
                            statusText = 'In Force';
                        } else if (policy.Policy_Status === 'Expired') {
                            statusClass = 'status-expired';
                            statusText = 'Expired';
                        } else if (policy.Policy_Status === 'Renewed') {
                            statusClass = 'status-renewed';
                            statusText = 'Renewed';
                        } else if (policy.Policy_Status === 'Waiting') {
                            statusClass = 'status-waiting';
                            statusText = 'Waiting';
                        } else if (policy.Policy_Status === 'Cancel') {
                            statusClass = 'status-cancel';
                            statusText = 'Cancelled';
                        }

                        // Get product name and coverage
                        const productName = policy.product_name || '-';
                        const coverage = policy.coverage ? 'MOP ' + parseFloat(policy.coverage).toLocaleString() : '-';

                        row.innerHTML = `
                            <td>${policy.Policy_No || '-'}</td>
                            <td>${policy.vehicle_id || '-'}</td>
                            <td>${productName}</td>
                            <td>${coverage}</td>
                            <td>${policy.premium ? 'MOP ' + parseFloat(policy.premium).toLocaleString() : '-'}</td>
                            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                            <td>${startDate}</td>
                            <td>${endDate}</td>
                            <td>${issueDate}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewPolicy('${policy.Policy_No}')">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                            </td>
                        `;

                        tableBody.appendChild(row);
                    });

                    document.getElementById('policyTable').style.display = 'table';
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('error').style.display = 'block';
                    document.getElementById('errorText').textContent = 'Error: ' + error.message;
                    console.error('Error loading policy data:', error);
                });
        }

        function viewPolicy(policyNo) {
            // Redirect to policy detail page
            window.location.href = `policy_detail.php?policy_no=${policyNo}`;
        }
    </script>
</body>

</html>