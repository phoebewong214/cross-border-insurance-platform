<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Payment</title>
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

        .payment-container {
            max-width: 500px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
        }

        .payment-header {
            margin-bottom: 2rem;
        }

        .payment-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .payment-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #01459C;
            margin-bottom: 2rem;
        }

        .qr-container {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: inline-block;
        }

        .payment-instructions {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .payment-status {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            background: #fff3cd;
            color: #856404;
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

        .payment-method-icon {
            font-size: 2rem;
            color: #01459C;
            margin-bottom: 1rem;
        }

        #qrCode {
            width: 200px;
            height: 200px;
            margin: 0 auto;
        }

        .btn-test-payment {
            background-color: #01459C;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-test-payment:hover {
            background-color: #013579;
        }
    </style>
</head>

<body>
    <a href="javascript:history.back()" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>

    <div class="payment-container">
        <div class="payment-header">
            <i class="fas fa-qrcode payment-method-icon"></i>
            <h1 class="payment-title">QR Code Payment</h1>
            <div class="payment-amount" id="paymentAmount"></div>
        </div>

        <div class="qr-container">
            <div id="qrCode">
                <!-- QR code will be displayed here -->
                <img id="qrCodeImage" style="width: 200px; height: 200px;" alt="Payment QR Code">
            </div>
        </div>

        <div class="payment-instructions">
            <p>Please scan the QR code with your mobile device</p>
            <p>The page will automatically redirect after payment</p>
        </div>

        <div class="payment-status" id="paymentStatus">
            Waiting for payment...
        </div>

        <!-- Test button - for demonstration only -->
        <button class="btn btn-test-payment" onclick="simulatePayment()">
            Pay
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // 添加调试信息
        console.log('开始生成二维码...');

        // 获取URL参数
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('id');
        const amount = urlParams.get('amount');
        const paymentMethod = urlParams.get('method');

        // 调试参数
        console.log('订单ID:', orderId);
        console.log('金额:', amount);
        console.log('支付方式:', paymentMethod);

        // 显示支付金额
        document.getElementById('paymentAmount').textContent = `MOP ${parseFloat(amount).toFixed(2)}`;

        // 页面加载时创建交易记录
        window.onload = function() {
            fetch('create_transaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        paymentMethod: paymentMethod
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to create transaction:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error creating transaction:', error);
                });
        };

        // 生成二维码
        const qrCodeText = `${window.location.origin}/pay?orderId=${orderId}&amount=${amount}&method=${paymentMethod}`;
        // 使用 QRServer API
        const qrServerApi = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrCodeText)}&color=01459C`;
        document.getElementById('qrCodeImage').src = qrServerApi;

        // 添加错误处理
        document.getElementById('qrCodeImage').onerror = function() {
            // 如果加载失败，显示错误信息
            document.getElementById('qrCode').innerHTML = `
                <div style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; border: 2px dashed #01459C; color: #01459C; text-align: center; padding: 20px;">
                    测试二维码<br>Test QR Code
                </div>
            `;
        };

        // 根据支付方式设置标题
        const paymentTitle = paymentMethod === 'alipay' ? 'Alipay Payment' : 'WeChat Pay Payment';
        document.querySelector('.payment-title').textContent = paymentTitle;

        // 更新支付状态文本
        document.getElementById('paymentStatus').textContent = `Waiting for ${paymentMethod === 'alipay' ? 'Alipay' : 'WeChat Pay'} payment...`;

        // 更新按钮文本
        document.querySelector('.btn-test-payment').textContent = 'Pay';

        // 模拟支付
        function simulatePayment() {
            // Show loading animation
            Swal.fire({
                title: 'Processing...',
                text: 'Confirming payment status',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simulate payment processing
            setTimeout(() => {
                fetch('process_payment_success.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            orderId: orderId,
                            paymentMethod: paymentMethod
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Payment Successful!',
                                text: 'Your payment has been processed successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = 'generate_policy.php?id=' + orderId;
                            });
                        } else {
                            throw new Error(data.message || 'Payment processing failed');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: error.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            }, 2000);
        }
    </script>
</body>

</html>