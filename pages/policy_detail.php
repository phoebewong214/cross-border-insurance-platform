<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- 添加 SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- 添加PDF生成所需的库 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            padding-top: 2rem;
            color: #2c3e50;
        }

        .back-button {
            position: absolute;
            top: 2rem;
            left: 2rem;
            padding: 0.75rem 1.25rem;
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            color: #01459C;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(1, 69, 156, 0.05);
        }

        .back-button:hover {
            background-color: #01459C;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(1, 69, 156, 0.15);
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
            margin-top: 1rem;
        }

        h1 {
            color: #01459C;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            font-size: 2.5rem;
            letter-spacing: -0.5px;
            position: relative;
            padding-bottom: 1rem;
        }

        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #01459C, #0056c7);
            border-radius: 2px;
        }

        .row {
            display: flex;
            margin-left: -1rem;
            margin-right: -1rem;
        }

        .col-md-6 {
            padding: 0 1rem;
            display: flex;
            flex-direction: column;
        }

        .policy-details {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02), 0 1px 3px rgba(0, 0, 0, 0.03);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(1, 69, 156, 0.05);
            height: 100%;
            /* 确保每个卡片高度相同 */
            display: flex;
            flex-direction: column;
        }

        .section-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .detail-row {
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            width: 150px;
            font-weight: 600;
            color: #6c757d;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            padding-right: 1rem;
            flex-shrink: 0;
        }

        .detail-value {
            flex: 1;
            color: #2c3e50;
            font-weight: 500;
            letter-spacing: 0.2px;
            padding: 0.25rem 0;
            min-height: 2rem;
            display: flex;
            align-items: center;
        }

        .status-badge {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status-inforce {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-expired {
            background-color: #ffebee;
            color: #c62828;
        }

        .status-renewed {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .status-waiting {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        .status-cancel {
            background-color: #fafafa;
            color: #616161;
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            gap: 1.5rem;
        }

        .spinner-border {
            width: 3.5rem;
            height: 3.5rem;
            color: #01459C;
            border-width: 0.25rem;
        }

        .loading-text {
            color: #6c757d;
            font-weight: 500;
            font-size: 1.1rem;
            letter-spacing: 0.3px;
        }

        .error-message {
            background-color: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            color: #c53030;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 2px 4px rgba(197, 48, 48, 0.1);
        }

        .insurance-period {
            font-weight: 600;
            text-align: center;
            padding: 1.25rem;
            background-color: #f8f9fa;
            border-radius: 12px;
            width: 100%;
            display: block;
            margin: 1rem 0;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
            border: 2px solid #e9ecef;
            color: #01459C;
            transition: all 0.3s ease;
        }

        .insurance-period:hover {
            background-color: #f1f3f5;
            border-color: #dee2e6;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 3rem;
            padding: 0 1rem;
        }

        .action-buttons .btn {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 200px;
            justify-content: center;
        }

        .btn-success {
            background-color: #01459C;
            border-color: #01459C;
            box-shadow: 0 4px 6px rgba(1, 69, 156, 0.1);
        }

        .btn-success:hover {
            background-color: #003a7d;
            border-color: #003a7d;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(1, 69, 156, 0.15);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
                margin-bottom: 2rem;
            }

            .row {
                flex-direction: column;
            }

            .col-md-6 {
                width: 100%;
                padding: 0;
            }

            .policy-details {
                height: auto;
                margin-bottom: 1rem;
            }

            .detail-row {
                flex-direction: column;
                margin-bottom: 1rem;
            }

            .detail-label {
                width: 100%;
                margin-bottom: 0.25rem;
                padding-right: 0;
            }

            .detail-value {
                width: 100%;
                min-height: auto;
            }

            .action-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .action-buttons .btn {
                width: 100%;
                min-width: auto;
            }

            .back-button {
                position: relative;
                top: 0;
                left: 0;
                margin: 1rem;
                width: calc(100% - 2rem);
                justify-content: center;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .container {
                padding: 1.5rem;
            }

            .policy-details {
                padding: 1.75rem;
            }
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background-color: white;
                padding: 0;
            }

            .policy-details {
                box-shadow: none;
                padding: 1rem;
                margin-bottom: 1rem;
                page-break-inside: avoid;
            }

            .section-title {
                color: #000;
                border-bottom-color: #000;
            }
        }
    </style>
</head>

<body>
    <a href="javascript:history.back()" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>

    <div class="container">
        <h1>Policy Details</h1>

        <div id="loading" class="loading-container">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-text">Loading policy details...</div>
        </div>

        <div id="policyDetails" style="display: none;">
            <!-- 保单基本信息和产品信息合并为一行 -->
            <div class="row">
                <div class="col-md-6">
                    <!-- 保单基本信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Policy Information</h3>
                        <div class="section-content">
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">Policy No</div>
                                    <div id="policyNo" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Status</div>
                                    <div id="policyStatus" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">Issue Date</div>
                                    <div id="issueDate" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Premium</div>
                                    <div id="premium" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Period</div>
                                    <div id="insurancePeriod" class="insurance-period">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Previous Policy No</div>
                                    <div id="previousPolicy" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Renewal Policy No</div>
                                    <div id="renewPolicy" class="detail-value">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- 产品信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-box-open"></i> Product Information</h3>
                        <div class="section-content">
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Product Name</div>
                                    <div id="productName" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Coverage Amount</div>
                                    <div id="coverage" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">Death Coverage</div>
                                    <div id="deathCoverage" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Medical Coverage</div>
                                    <div id="medicalCoverage" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">Material Loss Coverage</div>
                                    <div id="materialCoverage" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">No Claim Discount</div>
                                    <div id="ncd" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Excess</div>
                                    <div id="excess" class="detail-value">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 车辆信息和投保人信息合并为一行 -->
            <div class="row">
                <div class="col-md-6">
                    <!-- 车辆信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-car"></i> Vehicle Information</h3>
                        <div class="section-content">
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">Registration No</div>
                                    <div id="vehicleId" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Seats</div>
                                    <div id="seats" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Make and Model</div>
                                    <div id="carModel" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">Registration Date</div>
                                    <div id="registrationDate" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Chasis No</div>
                                    <div id="chasisNo" class="detail-value">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- 投保人信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-user"></i> Insured Information</h3>
                        <div class="section-content">
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">Party No</div>
                                    <div id="partyNo" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Type</div>
                                    <div id="ownerType" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-12">
                                    <div class="detail-label">Insured Name</div>
                                    <div id="ownerName" class="detail-value">-</div>
                                </div>
                            </div>
                            <div class="row detail-row">
                                <div class="col-md-6">
                                    <div class="detail-label">ID No</div>
                                    <div id="ownerId" class="detail-value">-</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-label">Date of Birth</div>
                                    <div id="ownerBirthDate" class="detail-value">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 操作按钮 -->
            <div class="action-buttons no-print">
                <button class="btn btn-success" onclick="generatePDF(document.getElementById('policyNo').textContent)">
                    <i class="fas fa-file-pdf me-2"></i> Download Policy
                </button>
            </div>
        </div>

        <div id="error" class="error-message" style="display: none;">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="errorText"></span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 从URL获取保单号
            const urlParams = new URLSearchParams(window.location.search);
            const policyNo = urlParams.get('policy_no');

            if (!policyNo) {
                showError('未提供保单号');
                document.getElementById('loading').style.display = 'none';
                return;
            }

            // 加载保单详情
            loadPolicyDetails(policyNo);
        });

        function loadPolicyDetails(policyNo) {
            fetch(`policy_processor.php?action=get_policy&policy_no=${policyNo}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`网络响应不正常 (${response.status}): ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('loading').style.display = 'none';

                    if (!data.success) {
                        throw new Error(data.message || '获取保单详情失败');
                    }

                    const policy = data.policy;

                    // 填充投保人資訊
                    document.getElementById('partyNo').textContent = policy.Party_No || '-';
                    document.getElementById('ownerName').textContent = policy.Customer_Name || '-';
                    document.getElementById('ownerType').textContent = policy.Type || '-';
                    document.getElementById('ownerId').textContent = policy.Customer_ID || '-';
                    document.getElementById('ownerBirthDate').textContent = policy.Date_of_Birth ? new Date(policy.Date_of_Birth).toLocaleDateString() : '-';

                    // 填充其他資訊
                    document.getElementById('policyNo').textContent = policy.Policy_No || '-';
                    document.getElementById('policyStatus').textContent = policy.Policy_Status || '-';
                    document.getElementById('issueDate').textContent = policy.Policy_Issue_Date ? new Date(policy.Policy_Issue_Date).toLocaleDateString() : '-';
                    document.getElementById('premium').textContent = policy.premium ? 'MOP ' + parseFloat(policy.premium).toLocaleString() : '-';
                    document.getElementById('insurancePeriod').textContent = policy.insurance_start && policy.insurance_end ?
                        `From ${new Date(policy.insurance_start).toLocaleDateString()} to ${new Date(policy.insurance_end).toLocaleDateString()} ONLY` : '-';
                    document.getElementById('previousPolicy').textContent = policy.Previous_Policy_No || '-';
                    document.getElementById('renewPolicy').textContent = policy.Renew_Policy_No || '-';
                    document.getElementById('productName').textContent = policy.product_name || '-';
                    document.getElementById('coverage').textContent = policy.coverage ? 'MOP ' + parseFloat(policy.coverage).toLocaleString() : '-';
                    document.getElementById('deathCoverage').textContent = policy.Death_coverage ? 'MOP ' + parseFloat(policy.Death_coverage).toLocaleString() : '-';
                    document.getElementById('medicalCoverage').textContent = policy.Medical_coverage ? 'MOP ' + parseFloat(policy.Medical_coverage).toLocaleString() : '-';
                    document.getElementById('materialCoverage').textContent = policy.Material_coverage ? 'MOP ' + parseFloat(policy.Material_coverage).toLocaleString() : '-';
                    document.getElementById('excess').textContent = policy.Excess ? 'MOP ' + parseFloat(policy.Excess).toLocaleString() : '-';
                    document.getElementById('ncd').textContent = policy.NCD ? parseFloat(policy.NCD).toFixed(2) + '%' : '-';
                    // 填充車輛信息
                    console.log('Vehicle Data:', {
                        registrationDate: policy.Date_of_Registration,
                        chasisNo: policy.Chasis_No
                    });
                    document.getElementById('vehicleId').textContent = policy.vehicle_id || '-';
                    document.getElementById('carModel').textContent = policy.car_make_model || '-';
                    document.getElementById('seats').textContent = policy.seats || '-';
                    document.getElementById('registrationDate').textContent = policy.Date_of_Registration ? new Date(policy.Date_of_Registration).toLocaleDateString() : '-';
                    document.getElementById('chasisNo').textContent = policy.Chasis_No || '-';

                    // 显示详情区域
                    document.getElementById('policyDetails').style.display = 'block';
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    showError(error.message);
                    console.error('加载保单详情时出错:', error);
                });
        }

        function showError(message) {
            const errorElement = document.getElementById('error');
            errorElement.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i> Error: ${message}`;
            errorElement.style.display = 'block';
        }

        async function generatePDF(policyNo) {
            // 顯示加載提示
            const loadingElement = document.createElement('div');
            loadingElement.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75';
            loadingElement.style.zIndex = '9999';
            loadingElement.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Generating PDF...</span></div><span class="ms-3">Generating PDF, please wait...</span>';
            document.body.appendChild(loadingElement);

            try {
                // 獲取保單詳情
                const response = await fetch(`policy_processor.php?action=get_policy&policy_no=${encodeURIComponent(policyNo)}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message || 'Failed to fetch policy details');
                }

                // 使用jsPDF直接创建PDF
                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                // 設置PDF尺寸和邊距
                const pageWidth = 210; // A4寬度，單位是mm
                const pageHeight = 297; // A4高度，單位是mm
                const margin = 15; // 頁邊距，單位是mm

                // 添加標題
                pdf.setFontSize(18);
                pdf.setFont(undefined, 'bold');
                pdf.text('Schedule', pageWidth / 2, margin, {
                    align: 'center'
                });

                // 添加保單號
                pdf.setFontSize(12);
                pdf.setFont(undefined, 'normal');
                pdf.text(`Policy No: ${policyNo}`, pageWidth / 2, margin + 8, {
                    align: 'center'
                });

                // 獲取所有需要的數據
                const status = document.getElementById('policyStatus').textContent;
                const issueDate = document.getElementById('issueDate').textContent;
                const period = document.getElementById('insurancePeriod').textContent;
                const premium = document.getElementById('premium').textContent;
                const previousPolicy = document.getElementById('previousPolicy').textContent;
                const renewPolicy = document.getElementById('renewPolicy').textContent;

                const productName = document.getElementById('productName').textContent;
                const coverage = document.getElementById('coverage').textContent;
                const deathCoverage = document.getElementById('deathCoverage').textContent;
                const medicalCoverage = document.getElementById('medicalCoverage').textContent;
                const materialCoverage = document.getElementById('materialCoverage').textContent;
                const excess = document.getElementById('excess').textContent;
                const ncd = document.getElementById('ncd').textContent;

                const vehicleId = document.getElementById('vehicleId').textContent;
                const carModel = document.getElementById('carModel').textContent;
                const seats = document.getElementById('seats').textContent;
                const registrationDate = document.getElementById('registrationDate').textContent;
                const chasisNo = document.getElementById('chasisNo').textContent;

                const partyNo = document.getElementById('partyNo').textContent;
                const ownerName = document.getElementById('ownerName').textContent;
                const ownerType = document.getElementById('ownerType').textContent;
                const ownerId = document.getElementById('ownerId').textContent;
                const ownerBirthDate = document.getElementById('ownerBirthDate').textContent;

                // 創建表格數據
                const tableData = [
                    [{
                        content: 'Basic Policy Information',
                        colSpan: 4,
                        styles: {
                            fontStyle: 'bold',
                            fillColor: [240, 240, 240],
                            fontSize: 12
                        }
                    }],
                    ['Policy No', policyNo, 'Status', status],
                    ['Issue Date', issueDate, 'Premium', premium],
                    ['Period', {
                        content: period,
                        colSpan: 3,
                        styles: {
                            fontStyle: 'bold'
                        }
                    }],
                    ['Previous Policy No', previousPolicy, 'Renewal Policy No', renewPolicy],
                    [{
                        content: 'Product Information',
                        colSpan: 4,
                        styles: {
                            fontStyle: 'bold',
                            fillColor: [240, 240, 240],
                            fontSize: 12
                        }
                    }],
                    ['Product Name', {
                        content: productName,
                        colSpan: 3
                    }],
                    ['Coverage Amount', {
                        content: coverage,
                        colSpan: 3
                    }],
                    ['Death Coverage', deathCoverage, 'Medical Coverage', medicalCoverage],
                    ['Material Loss Coverage', materialCoverage, 'No Claim Discount', ncd],
                    ['Excess', {
                        content: excess,
                        colSpan: 3
                    }],
                    [{
                        content: 'Vehicle Information',
                        colSpan: 4,
                        styles: {
                            fontStyle: 'bold',
                            fillColor: [240, 240, 240],
                            fontSize: 12
                        }
                    }],
                    ['Registration No', vehicleId, 'Seats', seats],
                    ['Make and Model', {
                        content: carModel,
                        colSpan: 3
                    }],
                    ['Registration Date', registrationDate, 'Chassis No', chasisNo],
                    [{
                        content: 'Insured Information',
                        colSpan: 4,
                        styles: {
                            fontStyle: 'bold',
                            fillColor: [240, 240, 240],
                            fontSize: 12
                        }
                    }],
                    ['Party No', partyNo, 'Type', ownerType],
                    ['Insured Name', {
                        content: ownerName,
                        colSpan: 3
                    }],
                    ['ID No', ownerId, 'Date of Birth', ownerBirthDate]
                ];

                // 繪製表格
                pdf.autoTable({
                    startY: margin + 15,
                    body: tableData,
                    theme: 'grid',
                    styles: {
                        fontSize: 10,
                        cellPadding: 3,
                        lineColor: [200, 200, 200],
                        lineWidth: 0.1
                    },
                    columnStyles: {
                        0: {
                            fontStyle: 'bold',
                            cellWidth: 40
                        },
                        1: {
                            cellWidth: 50
                        },
                        2: {
                            fontStyle: 'bold',
                            cellWidth: 40
                        },
                        3: {
                            cellWidth: 50
                        }
                    },
                    margin: {
                        left: margin,
                        right: margin
                    },
                    didDrawPage: function(data) {
                        // 添加頁腳
                        pdf.setFontSize(8);
                        pdf.setFont(undefined, 'normal');
                        const today = new Date().toLocaleDateString();
                        pdf.text(`Generated on: ${today}`, margin, pageHeight - 10);
                        pdf.text('Macau Insurance Company', pageWidth - margin, pageHeight - 10, {
                            align: 'right'
                        });
                        pdf.text(`Page ${data.pageNumber} of ${data.pageCount}`, pageWidth / 2, pageHeight - 10, {
                            align: 'center'
                        });
                    }
                });

                // 獲取產品類型
                const productType = policyNo.substring(1, 4); // 從保單號碼中提取第2-4個字符

                // 根據產品類型獲取對應的 Jecket 文件路徑
                let jecketPath;
                switch (productType.toUpperCase()) {
                    case 'MTY':
                        jecketPath = '../documents/jockets/MTY/jecket.pdf';
                        break;
                    case 'MTJ':
                        jecketPath = '../documents/jockets/MTJ/jecket.pdf';
                        break;
                    case 'MTZ':
                        jecketPath = '../documents/jockets/MTZ/jecket.pdf';
                        break;
                    default:
                        console.warn('Unknown product type:', productType);
                }

                // 添加 Jecket 文件
                if (jecketPath) {
                    try {
                        // 先保存主 PDF
                        const mainPdfBlob = pdf.output('blob');

                        // 獲取 Jecket 文件
                        const jecketResponse = await fetch(jecketPath);
                        if (jecketResponse.ok) {
                            const jecketBlob = await jecketResponse.blob();

                            // 創建 FormData 對象
                            const formData = new FormData();
                            formData.append('main_pdf', mainPdfBlob, 'main.pdf');
                            formData.append('jecket_pdf', jecketBlob, 'jecket.pdf');

                            // 發送到後端進行合併
                            const mergeResponse = await fetch('merge_pdfs.php', {
                                method: 'POST',
                                body: formData
                            });

                            if (mergeResponse.ok) {
                                const mergedPdfBlob = await mergeResponse.blob();
                                // 下載合併後的 PDF
                                const url = window.URL.createObjectURL(mergedPdfBlob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = `Policy_${policyNo}.pdf`;
                                document.body.appendChild(a);
                                a.click();
                                window.URL.revokeObjectURL(url);
                                document.body.removeChild(a);
                            } else {
                                throw new Error('Failed to merge PDFs');
                            }
                        } else {
                            console.warn('Jecket file not found for product type:', productType);
                        }
                    } catch (error) {
                        console.error('Error adding jecket:', error);
                        // 如果合併失敗，下載原始 PDF
                        pdf.save(`Policy_${policyNo}.pdf`);
                    }
                } else {
                    // 如果沒有 Jecket 文件，直接保存原始 PDF
                    pdf.save(`Policy_${policyNo}.pdf`);
                }

                // 移除加載提示
                document.body.removeChild(loadingElement);

                // 顯示成功提示
                Swal.fire({
                    icon: 'success',
                    title: 'PDF Generated Successfully',
                    text: 'Your policy document has been downloaded.',
                    confirmButtonColor: '#01459C'
                });
            } catch (error) {
                console.error('Error generating PDF:', error);
                document.body.removeChild(loadingElement);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to generate PDF',
                    confirmButtonColor: '#01459C'
                });
            }
        }
    </script>
</body>

</html>