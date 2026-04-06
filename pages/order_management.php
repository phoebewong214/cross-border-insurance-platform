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
    <title>Order Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
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

        .order-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-body {
            padding: 1.5rem;
        }

        .order-footer {
            padding: 1.5rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn-view {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.5rem;
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

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #eee;
            padding: 1.5rem;
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #721c24;
        }

        .notice-box {
            background-color: #e2e3e5;
            border: 1px solid #d6d8db;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #383d41;
        }
    </style>
</head>

<body>
    <a href="../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Personal Center
    </a>

    <div class="container">
        <h1 class="text-center mb-5" style="color: #01459C; font-weight: 700;">Order Management</h1>

        <div class="orders-container">
            <!-- Orders will be loaded here -->
        </div>
    </div>

    <!-- Rejection Details Modal -->
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rejection Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span id="rejectionReason"></span>
                    </div>
                    <div class="notice-box">
                        <i class="fas fa-info-circle me-2"></i>
                        Please make the necessary corrections and submit a new application.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnReapply">Reapply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to delete this order and start a new application?
                        <br>
                        <small class="text-muted">This action cannot be undone.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 加载订单数据
        function loadOrders() {
            const container = document.querySelector('.orders-container');
            container.innerHTML = ''; // 清空现有内容

            // 加载待审核的订单
            fetch('process_order.php?action=get_orders')
                .then(response => response.json())
                .then(data => {
                    console.log('待审核订单数据:', data); // 添加日志
                    if (data.success) {
                        displayOrders(data.orders);
                    } else {
                        throw new Error(data.message || 'Failed to load pending orders');
                    }
                })
                .catch(error => {
                    console.error('加载待审核订单时出错:', error); // 添加详细错误日志
                    console.error('错误详情:', {
                        message: error.message,
                        stack: error.stack,
                        name: error.name
                    });
                    alert('Failed to load pending orders. Please try again.');
                });

            // 加载已接受的订单
            fetch('process_quotation_order.php?action=get_accepted_orders')
                .then(response => response.json())
                .then(data => {
                    console.log('已接受订单数据:', data); // 添加日志
                    if (data.success) {
                        displayOrders(data.accepted_orders);
                    } else {
                        throw new Error(data.message || 'Failed to load accepted orders');
                    }
                })
                .catch(error => {
                    console.error('加载已接受订单时出错:', error); // 添加详细错误日志
                    console.error('错误详情:', {
                        message: error.message,
                        stack: error.stack,
                        name: error.name
                    });
                    alert('Failed to load accepted orders. Please try again.');
                });
        }

        // 显示订单
        function displayOrders(orders) {
            const container = document.querySelector('.orders-container');

            orders.forEach(order => {
                console.log('处理订单数据:', order); // 添加日志

                const statusClass = {
                    'pending': 'status-pending',
                    'approved': 'status-approved',
                    'rejected': 'status-rejected'
                } [order.status] || '';

                const statusText = {
                    'pending': 'Pending Review',
                    'approved': 'Quotation Ready',
                    'rejected': 'Rejected'
                } [order.status] || order.status;

                // 处理日期显示
                let displayDate;
                if (order.created_at) {
                    displayDate = new Date(order.created_at).toLocaleDateString();
                } else if (order.Generate_Time) {
                    displayDate = new Date(order.Generate_Time).toLocaleDateString();
                } else {
                    displayDate = 'Date not available';
                }

                const card = document.createElement('div');
                card.className = 'order-card';

                // 构建按钮HTML
                let buttonHtml = '';
                if (order.status === 'approved') {
                    console.log('创建View Quotation按钮，order_id:', order.order_id);
                    buttonHtml = `
                        <button class="btn btn-success btn-view" onclick="viewQuotation('${order.order_id}')">
                            View Quotation
                        </button>
                    `;
                } else if (order.status === 'rejected') {
                    buttonHtml = `
                        <button class="btn btn-danger btn-view" onclick="viewRejectionDetails('${order.id || order.Pending_review_ID}')">
                            View Details
                        </button>
                    `;
                }

                card.innerHTML = `
                    <div class="order-header">
                        <div>
                            <h5 class="mb-0">Order #${order.id || order.Pending_review_ID || order.order_id}</h5>
                            <small class="text-muted">Submitted on ${displayDate}</small>
                        </div>
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </div>
                    <div class="order-body">
                        <div class="info-row">
                            <div class="info-label">Insurance Type:</div>
                            <div class="info-value">${order.product_type_text || 'Loading...'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Vehicle:</div>
                            <div class="info-value">${order.vehicle_id || order.Registration_No}</div>
                        </div>
                        ${order.Car_Make_and_Model ? `
                        <div class="info-row">
                            <div class="info-label">Car Model:</div>
                            <div class="info-value">${order.Car_Make_and_Model}</div>
                        </div>
                        ` : ''}
                    </div>
                    <div class="order-footer">
                        <div>
                            ${order.status === 'rejected' ? 
                                `<small class="text-danger">
                                    <i class="fas fa-exclamation-circle"></i> 
                                    Review failed. Click to see details.
                                </small>` : 
                                order.status === 'pending' ?
                                `<small class="text-warning">
                                    <i class="fas fa-clock"></i> 
                                    Waiting for review
                                </small>` : 
                                order.status === 'approved' ?
                                `<small class="text-success">
                                    <i class="fas fa-check-circle"></i> 
                                    Quotation ready
                                </small>` : ''}
                        </div>
                        <div>
                            ${buttonHtml}
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // 查看報價單
        async function viewQuotation(orderId) {
            try {
                console.log('開始查看報價單:', orderId);

                // 發送請求到 calculate_quotation.php
                const response = await fetch('calculate_quotation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                });

                const result = await response.json();
                console.log('收到響應:', result);

                if (result.success) {
                    // 使用返回的 quotation_id 進行跳轉
                    window.location.href = `view_quotation.php?id=${result.quotation_id}`;
                } else {
                    throw new Error(result.message || '生成報價單失敗');
                }
            } catch (error) {
                console.error('錯誤:', error);
                Swal.fire({
                    icon: 'error',
                    title: '錯誤',
                    text: error.message || '發送請求失敗',
                    confirmButtonText: '確定'
                });
            }
        }

        // 查看拒絕原因
        function viewRejectionDetails(orderId) {
            fetch(`process_order.php?action=get_rejection_details&id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('rejectionReason').textContent = data.reason;
                        new bootstrap.Modal(document.getElementById('rejectionModal')).show();
                    } else {
                        throw new Error(data.message || 'Failed to load rejection details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load rejection details. Please try again.');
                });
        }

        // 重新申請
        document.getElementById('btnReapply').addEventListener('click', function() {
            window.location.href = 'select_product.php';
        });

        // 頁面加載完成後加載訂單數據
        document.addEventListener('DOMContentLoaded', loadOrders);
    </script>
</body>

</html>