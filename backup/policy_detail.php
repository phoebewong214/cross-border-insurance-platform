<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>保单详情</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- 添加PDF生成所需的库 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
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
            background: linear-gradient(135deg, #01459C 0%, #0077CC 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .page-subtitle {
            opacity: 0.9;
            margin-bottom: 0;
        }

        .container {
            max-width: 1200px;
        }

        .policy-details {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #01459C;
            margin-bottom: 0.8rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row {
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.2rem;
        }

        .detail-value {
            font-weight: 500;
            color: #212529;
        }

        .status-inforce {
            color: #28a745;
        }

        .status-expired {
            color: #dc3545;
        }

        .status-renewed {
            color: #17a2b8;
        }

        .status-waiting {
            color: #ffc107;
        }

        .status-cancel {
            color: #6c757d;
        }

        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
        }

        .loading-text {
            margin-top: 1rem;
            color: #6c757d;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            margin-bottom: 2rem;
        }

        .action-buttons .btn {
            margin: 0 0.5rem;
            padding: 0.5rem 1.5rem;
        }

        .footer {
            background-color: white;
            padding: 1.5rem 0;
            margin-top: 2rem;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
        }

        /* 修改保险期间的特殊样式 - 减小一些间距 */
        .insurance-period {
            font-weight: bold;
            text-align: center;
            padding: 8px 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            width: 100%;
            display: block;
            margin: 5px 0;
            font-size: 1.05em;
            letter-spacing: 0.3px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            border: 1px solid #e9ecef;
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
                padding: 0;
                margin-bottom: 0.5rem;
            }

            .page-header {
                background: none;
                color: #000;
                padding: 0.5rem 0;
                margin-bottom: 0.5rem;
            }
        }

        /* 添加PDF专用样式 */
        @media print {
            body {
                font-size: 10px;
                line-height: 1.2;
            }

            .policy-details {
                margin-bottom: 8px;
                padding: 8px;
            }

            .section-title {
                font-size: 12px;
                margin-bottom: 5px;
                padding-bottom: 3px;
            }

            .detail-row {
                margin-bottom: 3px;
            }

            .detail-label {
                font-size: 9px;
                margin-bottom: 1px;
            }

            .detail-value {
                font-size: 10px;
            }

            .insurance-period {
                padding: 5px;
                margin: 3px 0;
                font-size: 11px;
            }

            .page-header {
                padding: 10px 0;
                margin-bottom: 10px;
            }

            .page-title {
                font-size: 16px;
            }

            .page-subtitle {
                font-size: 12px;
            }
        }

        .pdf-mode {
            body {
                font-size: 10px;
                line-height: 1.2;
            }

            .policy-details {
                margin-bottom: 8px;
                padding: 8px;
            }

            .section-title {
                font-size: 12px;
                margin-bottom: 5px;
                padding-bottom: 3px;
            }

            .detail-row {
                margin-bottom: 3px;
            }

            .detail-label {
                font-size: 9px;
                margin-bottom: 1px;
            }

            .detail-value {
                font-size: 10px;
            }

            .insurance-period {
                padding: 5px;
                margin: 3px 0;
                font-size: 11px;
            }

            .page-header {
                padding: 10px 0;
                margin-bottom: 10px;
            }

            .page-title {
                font-size: 16px;
            }

            .page-subtitle {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Insurance Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="policy_query.php"><i class="fas fa-file-contract me-1"></i> Policy Query</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 页面标题 -->
    <header class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">Policy Details</h1>
                    <p class="page-subtitle" id="policyNumber">Policy No: Loading...</p>
                </div>
                <div class="col-md-4 text-md-end no-print">
                    <a href="policy_query.php" class="btn btn-light"><i class="fas fa-arrow-left me-2"></i> Back to Policy List</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- 加载提示 -->
        <div id="loading" class="loading-container">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-text">Loading policy details, please wait...</div>
        </div>

        <!-- 错误提示 -->
        <div id="error" class="error-message" style="display: none;"></div>

        <!-- 保单详情 -->
        <div id="policyDetails" style="display: none;">
            <!-- 保单基本信息和产品信息合并为一行 -->
            <div class="row">
                <div class="col-md-6">
                    <!-- 保单基本信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Policy Information</h3>
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

                <div class="col-md-6">
                    <!-- 产品信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-box-open"></i> Product Information</h3>
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

            <!-- 车辆信息和投保人信息合并为一行 -->
            <div class="row">
                <div class="col-md-6">
                    <!-- 车辆信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-car"></i> Vehicle Information</h3>
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
                                <div class="detail-label">Chassis No</div>
                                <div id="chasisNo" class="detail-value">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- 投保人信息 -->
                    <div class="policy-details">
                        <h3 class="section-title"><i class="fas fa-user"></i> Insured Information</h3>
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

            <!-- 操作按钮 -->
            <div class="action-buttons no-print">
                <button class="btn btn-success" onclick="generatePDF(document.getElementById('policyNo').textContent)">
                    <i class="fas fa-file-pdf me-2"></i> Download Policy
                </button>
            </div>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="footer no-print">
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
            // 从URL获取保单号
            const urlParams = new URLSearchParams(window.location.search);
            const policyNo = urlParams.get('policy_no');

            if (!policyNo) {
                showError('未提供保单号');
                document.getElementById('loading').style.display = 'none';
                return;
            }

            // 更新页面标题中的保单号
            document.getElementById('policyNumber').textContent = 'Policy No: ' + policyNo;

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

                    // 填充保单基本信息
                    document.getElementById('policyNo').textContent = policy.Policy_No || '-';

                    // 设置状态样式和文本
                    let statusText = policy.Policy_Status || '-';
                    let statusClass = '';

                    if (policy.Policy_Status === 'Inforce') {
                        statusClass = 'status-inforce';
                        statusText = 'Active';
                    } else if (policy.Policy_Status === 'Expired') {
                        statusClass = 'status-expired';
                        statusText = 'Expired';
                    } else if (policy.Policy_Status === 'Renewed') {
                        statusClass = 'status-renewed';
                        statusText = 'Renewed';
                    } else if (policy.Policy_Status === 'Waiting') {
                        statusClass = 'status-waiting';
                        statusText = 'Pending';
                    } else if (policy.Policy_Status === 'Cancel') {
                        statusClass = 'status-cancel';
                        statusText = 'Cancelled';
                    }

                    const statusElement = document.getElementById('policyStatus');
                    statusElement.textContent = statusText;
                    statusElement.className = `detail-value ${statusClass}`;

                    // 格式化日期
                    const issueDate = policy.Policy_Issue_Date ? new Date(policy.Policy_Issue_Date).toLocaleDateString() : '-';
                    const startDate = policy.insurance_start ? new Date(policy.insurance_start).toLocaleDateString() : '-';
                    const endDate = policy.insurance_end ? new Date(policy.insurance_end).toLocaleDateString() : '-';

                    document.getElementById('issueDate').textContent = issueDate;

                    // 设置保险期间格式为 "From [Start_Date] to [End_Date] ONLY"，增加日期前后的空格
                    if (startDate !== '-' && endDate !== '-') {
                        document.getElementById('insurancePeriod').textContent = `From   ${startDate}   to   ${endDate}   ONLY`;
                    } else {
                        document.getElementById('insurancePeriod').textContent = '-';
                    }

                    // 填充其他保单信息
                    document.getElementById('premium').textContent = policy.premium ? 'MOP ' + parseFloat(policy.premium).toLocaleString() : '-';
                    document.getElementById('previousPolicy').textContent = policy.Previous_Policy_No || '-';
                    document.getElementById('renewPolicy').textContent = policy.Renew_Policy_No || '-';

                    // 填充产品信息
                    document.getElementById('productName').textContent = policy.product_name || '-';
                    document.getElementById('coverage').textContent = policy.coverage ? 'MOP ' + parseFloat(policy.coverage).toLocaleString() : '-';

                    // 如果有产品详细信息
                    if (policy.Death_coverage) {
                        document.getElementById('deathCoverage').textContent = 'MOP ' + parseFloat(policy.Death_coverage).toLocaleString();
                    }
                    if (policy.Medical_coverage) {
                        document.getElementById('medicalCoverage').textContent = 'MOP ' + parseFloat(policy.Medical_coverage).toLocaleString();
                    }
                    if (policy.Material_coverage) {
                        document.getElementById('materialCoverage').textContent = 'MOP ' + parseFloat(policy.Material_coverage).toLocaleString();
                    }
                    if (policy.Excess) {
                        document.getElementById('excess').textContent = 'MOP ' + parseFloat(policy.Excess).toLocaleString();
                    }
                    if (policy.NCD) {
                        document.getElementById('ncd').textContent = parseFloat(policy.NCD).toFixed(2) + '%';
                    }

                    // 填充车辆信息
                    document.getElementById('vehicleId').textContent = policy.vehicle_id || '-';
                    document.getElementById('carModel').textContent = policy.car_make_model || '-';
                    document.getElementById('seats').textContent = policy.seats || '-';
                    document.getElementById('registrationDate').textContent = policy.Date_of_Registration ? new Date(policy.Date_of_Registration).toLocaleDateString() : '-';
                    document.getElementById('chasisNo').textContent = policy.Chasis_No || '-';

                    // 填充投保人信息
                    document.getElementById('partyNo').textContent = policy.Party_No || '-';
                    document.getElementById('ownerName').textContent = policy.owner_name || '-';
                    document.getElementById('ownerType').textContent = policy.Type === 'Individual' ? 'Individual' : (policy.Type === 'Company' ? 'Company' : '-');
                    document.getElementById('ownerId').textContent = policy.owner_id || '-';
                    document.getElementById('ownerBirthDate').textContent = policy.Date_of_Birth ? new Date(policy.Date_of_Birth).toLocaleDateString() : '-';

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

        function generatePDF(policyNo) {
            // 显示加载提示
            const loadingElement = document.createElement('div');
            loadingElement.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75';
            loadingElement.style.zIndex = '9999';
            loadingElement.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Generating PDF...</span></div><span class="ms-3">Generating PDF, please wait...</span>';
            document.body.appendChild(loadingElement);

            try {
                // 使用jsPDF直接创建PDF
                const {
                    jsPDF
                } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                // 设置PDF尺寸和边距
                const pageWidth = 210; // A4宽度，单位是mm
                const pageHeight = 297; // A4高度，单位是mm
                const margin = 15; // 页边距，单位是mm

                // 添加标题
                pdf.setFontSize(18);
                pdf.setFont(undefined, 'bold');
                pdf.text('Schedule', pageWidth / 2, margin, {
                    align: 'center'
                });

                // 添加保单号
                pdf.setFontSize(12);
                pdf.setFont(undefined, 'normal');
                pdf.text(`Policy No: ${policyNo}`, pageWidth / 2, margin + 8, {
                    align: 'center'
                });

                // 获取所有需要的数据
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

                // 创建表格数据
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

                // 绘制表格
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
                        // 添加页脚
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

                // 保存PDF
                pdf.save(`Policy_${policyNo}.pdf`);

                // 移除加载提示
                document.body.removeChild(loadingElement);
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Failed to generate PDF: ' + error.message);
                document.body.removeChild(loadingElement);
            }
        }
    </script>
</body>

</html>