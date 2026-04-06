<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Payment</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f5f7fa;
            min-height: 100vh;
            padding: 2rem 1rem;
            position: relative;
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

        .payment-container {
            max-width: 1000px;
            margin: 3rem auto 2rem;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
            position: relative;
        }

        .progress-container {
            grid-column: 1 / -1;
            position: relative;
            margin: 0 auto 3rem;
            width: 100%;
            max-width: 600px;
        }

        .progress-bar-custom {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            height: 3px;
            background-color: #e9ecef;
            width: 100%;
            z-index: 1;
        }

        .progress-bar-custom .progress {
            height: 100%;
            background-color: #01459C;
            width: 100%;
            transition: width 0.3s ease;
        }

        .step-circles {
            position: relative;
            display: flex;
            justify-content: space-between;
            z-index: 2;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
            position: relative;
        }

        .step-circle.active {
            background-color: #01459C;
            border-color: #01459C;
            color: white;
        }

        .step-circle.completed {
            background-color: #01459C;
            border-color: #01459C;
            color: white;
        }

        .step-label {
            position: absolute;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }

        .payment-summary {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            height: fit-content;
        }

        .summary-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f2f5;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.75rem 0;
        }

        .summary-label {
            color: #718096;
            font-weight: 500;
        }

        .summary-value {
            color: #2d3748;
            font-weight: 600;
        }

        .total-amount {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f0f2f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-amount-label {
            font-size: 1.1rem;
            color: #2d3748;
            font-weight: 600;
        }

        .total-amount-value {
            font-size: 1.5rem;
            color: #01459C;
            font-weight: 700;
        }

        .payment-methods-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .payment-methods-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f2f5;
        }

        .payment-methods {
            display: grid;
            gap: 1rem;
        }

        .payment-method {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .payment-method:hover {
            border-color: #01459C;
            background-color: #f8fafc;
        }

        .payment-method.selected {
            border-color: #01459C;
            background-color: #f8fafc;
            position: relative;
        }

        .payment-method.selected::after {
            content: '✓';
            position: absolute;
            right: 1.5rem;
            color: #01459C;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .payment-method-icon {
            width: 60px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-method-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .payment-method-details {
            flex: 1;
        }

        .payment-method-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .payment-method-description {
            font-size: 0.9rem;
            color: #718096;
        }

        .btn-proceed {
            background-color: #01459C;
            border: none;
            color: white;
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

        .btn-proceed:hover:not(:disabled) {
            background-color: #013579;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(1, 69, 156, 0.2);
        }

        .btn-proceed:disabled {
            background-color: #cbd5e0;
            cursor: not-allowed;
        }

        .btn-proceed i {
            font-size: 1.2rem;
        }

        @media (max-width: 992px) {
            .payment-container {
                grid-template-columns: 1fr;
                max-width: 600px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .back-button {
                position: static;
                margin-bottom: 1rem;
                display: inline-flex;
            }

            .payment-container {
                margin: 1rem auto;
            }

            .payment-method {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <a href="javascript:history.back()" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Proposal
    </a>

    <div class="payment-container">
        <div class="progress-container">
            <div class="progress-bar-custom">
                <div class="progress"></div>
            </div>
            <div class="step-circles">
                <div class="step-circle completed">
                    1
                    <div class="step-label">Quotation</div>
                </div>
                <div class="step-circle completed">
                    2
                    <div class="step-label">Proposal</div>
                </div>
                <div class="step-circle active">
                    3
                    <div class="step-label">Payment</div>
                </div>
            </div>
        </div>

        <div class="payment-summary">
            <div class="summary-title">
                <i class="fas fa-receipt me-2"></i>
                Order Summary
            </div>
            <div class="summary-row">
                <div class="summary-label">Quotation No</div>
                <div class="summary-value" id="quotationNo"></div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Vehicle Registration</div>
                <div class="summary-value" id="vehicleReg"></div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Insurance Period</div>
                <div class="summary-value" id="insurancePeriod"></div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Coverage Type</div>
                <div class="summary-value" id="coverageType"></div>
            </div>
            <div class="total-amount">
                <div class="total-amount-label">Total Premium</div>
                <div class="total-amount-value" id="totalPremium"></div>
            </div>
        </div>

        <div class="payment-methods-container">
            <div class="payment-methods-title">
                <i class="fas fa-credit-card me-2"></i>
                Select Payment Method
            </div>
            <div class="payment-methods">
                <div class="payment-method" data-method="credit_card">
                    <div class="payment-method-icon">
                        <img src="../assets/credit-card.jpg" alt="Credit Card">
                    </div>
                    <div class="payment-method-details">
                        <div class="payment-method-name">Credit Card</div>
                        <div class="payment-method-description">Pay securely with your credit card</div>
                    </div>
                </div>
                <div class="payment-method" data-method="alipay">
                    <div class="payment-method-icon">
                        <img src="../assets/alipay.jpg" alt="Alipay">
                    </div>
                    <div class="payment-method-details">
                        <div class="payment-method-name">Alipay</div>
                        <div class="payment-method-description">Pay with your Alipay account</div>
                    </div>
                </div>
                <div class="payment-method" data-method="wechat">
                    <div class="payment-method-icon">
                        <img src="../assets/wechat.jpg" alt="WeChat Pay">
                    </div>
                    <div class="payment-method-details">
                        <div class="payment-method-name">WeChat Pay</div>
                        <div class="payment-method-description">Pay with your WeChat account</div>
                    </div>
                </div>
            </div>

            <button class="btn-proceed" id="proceedButton" disabled onclick="processPayment()">
                <i class="fas fa-lock"></i>
                Proceed to Payment
            </button>
        </div>
    </div>

    <script>
        let selectedPaymentMethod = null;

        window.onload = function() {
            loadOrderDetails();
        };

        function loadOrderDetails() {
            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get('id');
            const insuranceStart = urlParams.get('insuranceStart');
            const insuranceEnd = urlParams.get('insuranceEnd');

            if (!orderId) {
                alert('Order ID not found');
                return;
            }

            fetch(`process_quotation.php?action=get_quotation&id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateOrderDetails(data.quotation, insuranceStart, insuranceEnd);
                    } else {
                        throw new Error(data.message || 'Failed to load order details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load order details. Please try again.');
                });
        }

        function populateOrderDetails(data, insuranceStart, insuranceEnd) {
            document.getElementById('quotationNo').textContent = data.id || '-';
            document.getElementById('vehicleReg').textContent = data.vehicle_id || '-';

            // 显示保险期限，优先使用URL中的参数，如果没有则使用数据中的值
            const startDate = insuranceStart || data.insurance_start || '-';
            const endDate = insuranceEnd || data.insurance_end || '-';
            document.getElementById('insurancePeriod').textContent = `${startDate} to ${endDate}`;

            // 获取并显示Coverage Type
            let coverageType = '-';
            if (data.product1_id) {
                coverageType = data.product1_name || '-';
            } else if (data.product2_id) {
                coverageType = data.product2_name || '-';
            } else if (data.product3_id) {
                coverageType = data.product3_name || '-';
            }
            document.getElementById('coverageType').textContent = coverageType;

            document.getElementById('totalPremium').textContent =
                `MOP ${new Intl.NumberFormat('zh-HK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(data.premium || 0)}`;
        }

        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            // 移除所有支付方式的选中状态
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            // 添加选中状态到所选支付方式
            document.querySelector(`.payment-method[data-method="${method}"]`).classList.add('selected');
            // 启用支付按钮
            document.getElementById('proceedButton').disabled = false;
        }

        function processPayment() {
            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get('id');
            const premium = document.getElementById('totalPremium').textContent.replace('MOP ', '').replace(',', '');

            if (!selectedPaymentMethod) {
                Swal.fire({
                    title: 'Please Select Payment Method',
                    text: 'Please select a payment method to continue',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // 根据支付方式跳转到相应的支付页面
            switch (selectedPaymentMethod) {
                case 'credit_card':
                    window.location.href = `credit_card_payment.php?id=${orderId}&amount=${premium}`;
                    break;
                case 'alipay':
                    window.location.href = `qr_payment.php?id=${orderId}&amount=${premium}&method=alipay`;
                    break;
                case 'wechat':
                    window.location.href = `qr_payment.php?id=${orderId}&amount=${premium}&method=wechat`;
                    break;
                default:
                    Swal.fire({
                        title: 'Error',
                        text: 'Unsupported payment method',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // 初始化支付按钮状态
            document.getElementById('proceedButton').disabled = true;

            // 为所有支付方式添加点击事件监听器
            document.querySelectorAll('.payment-method').forEach(method => {
                method.addEventListener('click', function() {
                    selectPaymentMethod(this.dataset.method);
                });
            });
        });
    </script>
</body>

</html>