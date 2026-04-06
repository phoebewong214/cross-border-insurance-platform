<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Generated Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .success-container {
            max-width: 500px;
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background-color: #d4edda;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .success-icon i {
            font-size: 2.5rem;
            color: #28a745;
        }

        .success-title {
            color: #01459C;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .success-message {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .policy-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .policy-detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }

        .policy-detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }

        .detail-value {
            color: #2d3748;
            font-weight: 600;
        }

        .btn-view-policy {
            background-color: #01459C;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .btn-home {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-view-policy:hover {
            background-color: #013579;
            color: white;
            transform: translateY(-1px);
        }

        .btn-home:hover {
            background-color: #5a6268;
            color: white;
            transform: translateY(-1px);
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .success-container {
                margin: 1rem auto;
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="success-title">Policy Generated Successfully!</h1>
        <p class="success-message">Your insurance policy has been created and is ready for viewing.</p>

        <div class="policy-details">
            <div class="policy-detail-row">
                <span class="detail-label">Policy Number</span>
                <span class="detail-value" id="policyNo"></span>
            </div>
            <div class="policy-detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value" id="policyStatus"></span>
            </div>
        </div>

        <div class="button-group">
            <a href="policy_query.php" class="btn-view-policy">
                <i class="fas fa-file-alt"></i>
                View Policy Details
            </a>
            <a href="../index.php" class="btn-home">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
        </div>
    </div>

    <script>
        // 获取URL参数
        const urlParams = new URLSearchParams(window.location.search);
        const policyNo = urlParams.get('policy_no');
        const policyStatus = urlParams.get('policy_status');

        // 显示保单信息
        document.getElementById('policyNo').textContent = policyNo;
        document.getElementById('policyStatus').textContent = policyStatus;
    </script>
</body>

</html>