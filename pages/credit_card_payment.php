<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Card Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .payment-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .payment-title {
            color: #1a202c;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        .payment-amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: #01459C;
            margin: 1rem 0;
            letter-spacing: -0.025em;
        }

        .card-input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .card-input-group i {
            position: absolute;
            left: 16px;
            top: 42px;
            color: #01459C;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.9375rem;
        }

        .form-control {
            height: 48px;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .form-control:focus {
            border-color: #01459C;
            box-shadow: 0 0 0 3px rgba(1, 69, 156, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        .btn-pay {
            background-color: #01459C;
            border: none;
            width: 100%;
            padding: 0.875rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 1.5rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-pay:hover {
            background-color: #013579;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(1, 69, 156, 0.1);
        }

        .btn-pay:active {
            transform: translateY(0);
        }

        .card-icon {
            font-size: 2.5rem;
            color: #01459C;
            margin-bottom: 1rem;
        }

        .input-row {
            display: flex;
            gap: 1rem;
        }

        .input-row .card-input-group {
            flex: 1;
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
            <i class="fas fa-credit-card card-icon"></i>
            <h2 class="payment-title">Credit Card Payment</h2>
            <div class="payment-amount" id="paymentAmount"></div>
        </div>

        <form id="paymentForm" onsubmit="return processPayment(event)">
            <div class="card-input-group">
                <label class="form-label">Card Number</label>
                <i class="fas fa-credit-card"></i>
                <input type="text" class="form-control" maxlength="16" placeholder="1234 5678 9012 3456" required>
            </div>

            <div class="input-row">
                <div class="card-input-group">
                    <label class="form-label">Expiry Date</label>
                    <i class="fas fa-calendar-alt"></i>
                    <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required>
                </div>
                <div class="card-input-group">
                    <label class="form-label">CVV</label>
                    <i class="fas fa-lock"></i>
                    <input type="text" class="form-control" placeholder="123" maxlength="3" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-pay">
                <i class="fas fa-lock"></i>
                Confirm Payment
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('id');
        const amount = urlParams.get('amount');

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
                        paymentMethod: 'credit_card'
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

        function processPayment(event) {
            event.preventDefault();

            fetch('process_payment_success.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        paymentMethod: 'credit_card'
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

            return false;
        }
    </script>
</body>

</html>