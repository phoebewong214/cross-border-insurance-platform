<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Policy</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .policy-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            position: relative;
        }

        .policy-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f0f2f5;
        }

        .policy-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .policy-number {
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
        }

        .info-label {
            width: 200px;
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            flex: 1;
        }

        .coverage-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }

        .coverage-item:last-child {
            border-bottom: none;
        }

        .total-section {
            background: #e9ecef;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            color: #01459C;
            font-size: 1.2rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-download {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-print {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .btn-print:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-top: 1rem;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-pending-renewal {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>

<body>
    <a href="policy_management.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Policy Management
    </a>

    <div class="policy-container">
        <div class="policy-header">
            <h1 class="policy-title">Insurance Policy</h1>
            <div class="policy-number" id="policyNumber"></div>
            <div id="statusBadge"></div>
        </div>

        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Customer Information
            </h3>
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div class="info-value" id="customerName"></div>
            </div>
            <div class="info-row">
                <div class="info-label">ID Number:</div>
                <div class="info-value" id="customerId"></div>
            </div>
        </div>

        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-car"></i>
                Vehicle Information
            </h3>
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

        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-shield-alt"></i>
                Coverage Details
            </h3>
            <div class="info-row">
                <div class="info-label">Insurance Period:</div>
                <div class="info-value" id="insurancePeriod"></div>
            </div>
            <div class="info-row">
                <div class="info-label">Product Type:</div>
                <div class="info-value" id="productType"></div>
            </div>
            <div id="coverageDetails">
                <!-- Coverage details will be populated here -->
            </div>
            <div class="total-section">
                <div class="total-row">
                    <span>Total Premium:</span>
                    <span id="totalPremium"></span>
                </div>
            </div>
        </div>

        <div class="action-buttons" id="actionButtons">
            <button class="btn btn-download btn-action" onclick="downloadPolicy()" id="downloadButton">
                <i class="fas fa-download"></i> Download PDF
            </button>
            <button class="btn btn-print btn-action" onclick="printPolicy()" id="printButton">
                <i class="fas fa-print"></i> Print Policy
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadPolicy();
        });

        function loadPolicy() {
            const urlParams = new URLSearchParams(window.location.search);
            const policyId = urlParams.get('id');

            if (!policyId) {
                alert('Invalid policy ID');
                window.location.href = 'policy_management.php';
                return;
            }

            fetch(`process_policy.php?action=get_policy&id=${policyId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populatePolicyData(data.policy);
                    } else {
                        throw new Error(data.message || 'Failed to load policy data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load policy data. Please try again.');
                });
        }

        function populatePolicyData(policy) {
            document.getElementById('policyNumber').textContent = `Policy #${policy.id}`;
            document.getElementById('customerName').textContent = policy.owner_name;
            document.getElementById('customerId').textContent = policy.owner_id;
            document.getElementById('registrationNo').textContent = policy.vehicle_id;
            document.getElementById('makeModel').textContent = policy.car_make_model;
            document.getElementById('seats').textContent = policy.seats;
            document.getElementById('productType').textContent = policy.product_type;

            const startDate = new Date(policy.insurance_start).toLocaleDateString();
            const endDate = new Date(policy.insurance_end).toLocaleDateString();
            document.getElementById('insurancePeriod').textContent = `${startDate} to ${endDate}`;

            document.getElementById('totalPremium').textContent = `MOP ${policy.premium}`;

            // 设置保单状态
            const policyEndDate = new Date(policy.insurance_end);
            const today = new Date();
            const daysUntilExpiry = Math.ceil((policyEndDate - today) / (1000 * 60 * 60 * 24));

            let statusClass = 'status-active';
            let statusText = 'Active';

            if (policyEndDate < today) {
                statusClass = 'status-expired';
                statusText = 'Expired';
                // 如果保单过期，隐藏下载和打印按钮
                document.getElementById('downloadButton').style.display = 'none';
                document.getElementById('printButton').style.display = 'none';
            } else if (daysUntilExpiry <= 30) {
                statusClass = 'status-pending-renewal';
                statusText = 'Renewal Due';
            }

            document.getElementById('statusBadge').innerHTML = `
                <span class="status-badge ${statusClass}">${statusText}</span>
            `;

            // 填充保险范围详情
            const coverageDetails = document.getElementById('coverageDetails');
            coverageDetails.innerHTML = '';

            if (policy.coverage_options) {
                const options = JSON.parse(policy.coverage_options);
                Object.entries(options).forEach(([key, value]) => {
                    const item = document.createElement('div');
                    item.className = 'coverage-item';
                    item.innerHTML = `
                        <span>${key}</span>
                        <span>MOP ${value}</span>
                    `;
                    coverageDetails.appendChild(item);
                });
            }
        }

        function downloadPolicy() {
            const element = document.querySelector('.policy-container');
            const opt = {
                margin: 1,
                filename: `policy_${document.getElementById('policyNumber').textContent.replace('#', '')}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opt).from(element).save();
        }

        function printPolicy() {
            window.print();
        }
    </script>
</body>

</html>