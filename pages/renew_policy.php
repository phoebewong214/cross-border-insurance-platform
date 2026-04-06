<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Renewal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 2rem;
        }

        .back-button {
            position: fixed;
            top: 1.5rem;
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
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .back-button:hover {
            background-color: #f8f9fa;
            color: #013579;
            text-decoration: none;
        }

        .renewal-container {
            max-width: 800px;
            margin: 4rem auto 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .renewal-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .renewal-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .renewal-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .section-title {
            color: #01459C;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            width: 200px;
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            flex: 1;
            color: #212529;
        }

        .comparison-table {
            width: 100%;
            margin-bottom: 1.5rem;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .comparison-table th,
        .comparison-table td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .comparison-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            text-align: left;
        }

        .comparison-table tr:last-child td {
            border-bottom: none;
        }

        .comparison-table .highlight {
            background-color: #e8f4ff;
        }

        .renewal-notice {
            background-color: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .renewal-notice i {
            font-size: 1.5rem;
        }

        .btn-proceed {
            background-color: #01459C;
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 12px;
            width: 100%;
            font-weight: 600;
            margin-top: 2rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-proceed:hover {
            background-color: #013579;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(1, 69, 156, 0.2);
        }

        .btn-proceed:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .renewal-container {
                margin: 3rem auto 1rem;
                padding: 1.5rem;
            }

            .info-row {
                flex-direction: column;
            }

            .info-label {
                width: 100%;
                margin-bottom: 0.25rem;
            }

            .back-button {
                position: static;
                margin-bottom: 1rem;
                display: inline-flex;
            }
        }

        /* 添加新的網格布局樣式 */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 2rem;
        }

        .grid-item {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .grid-item-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .grid-item-header i {
            color: #01459C;
            font-size: 1.5rem;
        }

        .grid-item-header h3 {
            color: #01459C;
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <a href="policy_management.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Policy Management
    </a>

    <div class="renewal-container">
        <div class="renewal-header">
            <h1 class="renewal-title">Policy Renewal</h1>
            <div class="renewal-subtitle" id="policyNumber"></div>
        </div>

        <div class="renewal-notice">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Important Notice:</strong> Please review all the information carefully before proceeding with the renewal. The new policy period will start immediately after your current policy expires.
            </div>
        </div>

        <!-- 使用網格布局重新組織內容 -->
        <div class="grid-container">
            <!-- 客戶信息模塊 -->
            <div class="grid-item">
                <div class="grid-item-header">
                    <i class="fas fa-user"></i>
                    <h3>Customer Information</h3>
                </div>
                <div class="info-row">
                    <div class="info-label">Name:</div>
                    <div class="info-value" id="customerName"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">ID Number:</div>
                    <div class="info-value" id="customerId"></div>
                </div>
            </div>

            <!-- 車輛信息模塊 -->
            <div class="grid-item">
                <div class="grid-item-header">
                    <i class="fas fa-car"></i>
                    <h3>Vehicle Information</h3>
                </div>
                <div class="info-row">
                    <div class="info-label">Registration Number:</div>
                    <div class="info-value" id="registrationNo"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Make and Model:</div>
                    <div class="info-value" id="makeModel"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Number of Seats:</div>
                    <div class="info-value" id="seats"></div>
                </div>
            </div>

            <!-- 保險詳情模塊 -->
            <div class="grid-item">
                <div class="grid-item-header">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Insurance Details</h3>
                </div>
                <div class="info-row">
                    <div class="info-label">Product Type:</div>
                    <div class="info-value" id="productType"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Current Period:</div>
                    <div class="info-value" id="currentPeriod"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Premium:</div>
                    <div class="info-value" id="premium"></div>
                </div>
            </div>

            <!-- 新保險期間模塊 -->
            <div class="grid-item">
                <div class="grid-item-header">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>New Policy Period</h3>
                </div>
                <div class="info-row">
                    <div class="info-label">New Period:</div>
                    <div class="info-value" id="newPeriod"></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">Pending Renewal</div>
                </div>
            </div>
        </div>

        <!-- 續保按鈕 -->
        <button class="btn btn-proceed" onclick="initiateRenewal()">
            <i class="fas fa-sync-alt"></i>
            Proceed with Renewal
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadPolicyDetails();
        });

        function loadPolicyDetails() {
            const urlParams = new URLSearchParams(window.location.search);
            const policyId = urlParams.get('id');

            if (!policyId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Policy ID not found',
                    icon: 'error'
                }).then(() => {
                    window.location.href = 'policy_management.php';
                });
                return;
            }

            fetch(`process_policy.php?action=get_policy&id=${policyId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        populatePolicyDetails(data.policy);
                    } else {
                        throw new Error(data.message || 'Failed to load policy details');
                    }
                })
                .catch(error => {
                    console.error('Error loading policy details:', error);
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error'
                    });
                });
        }

        function populatePolicyDetails(policy) {
            // 基本保單信息
            document.getElementById('policyNumber').textContent = policy.Policy_No || '-';

            // 客戶信息 - 使用正確的字段
            document.getElementById('customerName').textContent = policy.Customer_Name || '-';
            document.getElementById('customerId').textContent = policy.Customer_ID || '-';

            // 車輛信息
            document.getElementById('registrationNo').textContent = policy.Registration_No || '-';
            document.getElementById('makeModel').textContent = policy.Car_Make_and_Model || '-';
            document.getElementById('seats').textContent = policy.Seats || '-';

            // 產品信息
            document.getElementById('productType').textContent = policy.Product_Name || '-';
            document.getElementById('premium').textContent = policy.Total_Premium ?
                `MOP ${parseFloat(policy.Total_Premium).toFixed(2)}` : 'MOP 0.00';

            // 保險期間
            const startDate = new Date(policy.insurance_start);
            const endDate = new Date(policy.insurance_end);

            document.getElementById('currentPeriod').textContent =
                `${startDate.toLocaleDateString()} to ${endDate.toLocaleDateString()}`;

            // 新的保險期間
            const newStartDate = new Date(endDate);
            newStartDate.setDate(newStartDate.getDate() + 1);
            const newEndDate = new Date(newStartDate);
            newEndDate.setFullYear(newEndDate.getFullYear() + 1);
            newEndDate.setDate(newEndDate.getDate() - 1);

            document.getElementById('newPeriod').textContent =
                `${newStartDate.toLocaleDateString()} to ${newEndDate.toLocaleDateString()}`;

            // 檢查是否可以續保
            const today = new Date();
            const daysUntilExpiry = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));
            const renewButton = document.querySelector('.btn-proceed');

            if (daysUntilExpiry > 30) {
                renewButton.disabled = true;
                renewButton.title = 'Renewal is only available within 30 days of expiry';

                const noticeDiv = document.createElement('div');
                noticeDiv.className = 'alert alert-warning mt-3';
                noticeDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    Renewal is only available within 30 days of policy expiry. 
                    Current policy expires in ${daysUntilExpiry} days.
                `;
                renewButton.parentNode.insertBefore(noticeDiv, renewButton.nextSibling);
            } else {
                renewButton.disabled = false;
                renewButton.title = 'Click to proceed with renewal';
            }
        }

        function initiateRenewal() {
            const urlParams = new URLSearchParams(window.location.search);
            const policyId = urlParams.get('id');

            console.log('开始续保流程 - 保单号:', policyId);
            console.log('当前URL参数:', Object.fromEntries(urlParams.entries()));

            Swal.fire({
                title: 'Confirm Renewal',
                text: 'Are you sure you want to proceed with the policy renewal?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'No, cancel',
                confirmButtonColor: '#01459C',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing',
                        text: 'Please wait while we process your renewal request...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    console.log('发送续保请求 - 保单号:', policyId);

                    // 第一步：准备续保数据
                    fetch(`process_renewal.php?action=prepare_renewal&policy_id=${policyId}`)
                        .then(response => {
                            console.log('服务器响应状态:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('服务器返回数据:', data);
                            if (data.success) {
                                // 第二步：创建续保申请
                                return fetch('process_renewal.php?action=create_renewal', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(data.renewal_data)
                                });
                            } else {
                                throw new Error(data.message || 'Failed to prepare renewal data');
                            }
                        })
                        .then(response => {
                            console.log('创建续保申请响应状态:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('创建续保申请结果:', data);
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success',
                                    text: 'Your renewal application has been submitted successfully.',
                                    icon: 'success',
                                    confirmButtonColor: '#01459C'
                                }).then(() => {
                                    window.location.href = 'order_management.php';
                                });
                            } else {
                                throw new Error(data.message || 'Failed to create renewal application');
                            }
                        })
                        .catch(error => {
                            console.error('续保处理错误:', error);
                            console.error('错误详情:', {
                                message: error.message,
                                stack: error.stack,
                                name: error.name
                            });
                            Swal.fire({
                                title: 'Error',
                                text: error.message,
                                icon: 'error'
                            });
                        });
                }
            });
        }
    </script>
</body>

</html>