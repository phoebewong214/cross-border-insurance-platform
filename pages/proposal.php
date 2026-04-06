<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Vehicle Insurance Proposal Form</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- 添加SweetAlert2的CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- 添加PDF生成所需的库 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            padding-top: 1rem;
            position: relative;
        }

        .proposal-container {
            max-width: 900px;
            margin: 3rem auto 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            position: relative;
        }

        .progress-container {
            position: relative;
            margin: 40px auto 60px;
            width: 100%;
            max-width: 600px;
        }

        .progress-bar-custom {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            height: 2px;
            background-color: #e9ecef;
            width: 100%;
            z-index: 1;
        }

        .progress-bar-custom .progress {
            height: 100%;
            background-color: #01459C;
            width: 0;
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

        .proposal-preview {
            margin: 2rem 0;
            padding: 2rem;
            background: #fff;
            border: 1px solid #000000;
        }

        .proposal-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #000000;
        }

        .company-logo {
            width: 180px;
            margin-right: 2rem;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .company-address {
            color: #000000;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .form-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #000000;
            margin: 2rem 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-row {
            display: flex;
            margin-bottom: 1rem;
            border-bottom: 1px solid #000000;
            padding: 0.5rem 0;
        }

        .form-label {
            width: 250px;
            font-weight: 500;
            color: #000000;
        }

        .form-value {
            flex: 1;
            color: #000000;
        }

        .coverage-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            border: 1px solid #000000;
        }

        .coverage-table th,
        .coverage-table td {
            padding: 0.75rem;
            border: 1px solid #000000;
            color: #000000;
        }

        .coverage-table th {
            background: #f8f9fa;
            font-weight: 500;
            text-transform: uppercase;
        }

        .signature-section {
            margin: 2rem 0;
            padding: 2rem;
            background: #fff;
            border: 1px solid #000000;
        }

        .signature-area {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        .signature-field {
            flex: 1;
        }

        #signatureCanvas {
            border: 1px solid #000000;
            background: white;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-action {
            padding: 0.75rem 2rem;
            font-weight: 500;
            border-radius: 8px;
            min-width: 200px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-download {
            background-color: #01459C;
            border-color: #01459C;
            color: white;
        }

        .btn-download:hover {
            background-color: #013579;
            border-color: #013579;
            color: white;
        }

        .btn-proceed {
            background-color: #01459C;
            border-color: #01459C;
            color: white;
        }

        .btn-proceed:hover {
            background-color: #013579;
            border-color: #013579;
            color: white;
        }

        .declaration-text {
            font-size: 0.9rem;
            color: #000000;
            margin: 2rem 0;
            padding: 1rem;
            border: 1px solid #000000;
            line-height: 1.6;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 1rem;
            text-transform: uppercase;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
                background: white;
            }

            .proposal-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: none;
            }

            .progress-container,
            .action-buttons,
            .back-button {
                display: none !important;
            }

            .proposal-preview {
                border: none;
                padding: 0;
                margin: 0;
            }

            #signatureCanvas {
                border: 1px solid #000;
            }
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

        .insurance-period-container {
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .date-input-group {
            flex: 1;
            min-width: 200px;
        }

        .date-input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-size: 0.9rem;
        }

        .date-input-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
            color: #2d3748;
            background-color: #fff;
            transition: all 0.3s ease;
        }

        .date-input-group input:focus {
            outline: none;
            border-color: #01459C;
            box-shadow: 0 0 0 3px rgba(1, 69, 156, 0.1);
        }

        .date-input-group input:read-only {
            background-color: #f8f9fa;
            border-color: #e2e8f0;
        }

        @media (max-width: 768px) {
            .insurance-period-container {
                flex-direction: column;
                gap: 1rem;
            }

            .date-input-group {
                width: 100%;
            }
        }

        .insurance-period {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.95rem;
            color: #2d3748;
        }

        .insurance-date-input {
            position: relative;
            display: inline-block;
        }

        .insurance-date-input input {
            width: 120px;
            padding: 6px 10px;
            border: none;
            border-bottom: 2px solid #01459C;
            font-family: 'Mulish', sans-serif;
            font-size: 0.95rem;
            color: #2d3748;
            background: transparent;
            text-align: center;
        }

        .insurance-date-input input:focus {
            outline: none;
            border-bottom-color: #013579;
        }

        .insurance-date-input input:read-only {
            border-bottom: 2px solid #e2e8f0;
            color: #4a5568;
        }

        .insurance-period-text {
            font-weight: 500;
            color: #2d3748;
        }
    </style>
</head>

<body>
    <a href="javascript:history.back()" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Quotation
    </a>

    <div class="proposal-container">
        <div class="progress-container">
            <div class="progress-bar-custom">
                <div class="progress" style="width: 66.66%"></div>
            </div>
            <div class="step-circles">
                <div class="step-circle completed">1
                    <div class="step-label">Quotation</div>
                </div>
                <div class="step-circle active">2
                    <div class="step-label">Proposal</div>
                </div>
                <div class="step-circle">3
                    <div class="step-label">Payment</div>
                </div>
            </div>
        </div>

        <div class="proposal-preview" id="proposalContent">
            <div class="proposal-header">
                <img src="../assets/logo.png" alt="CrossBorder Insurance" class="company-logo">
                <div class="company-info">
                    <div class="company-name">CrossBorder Insurance COMPANY LIMITED</div>
                    <div class="company-address">
                        Alameda Dr. Carlos D'Assumpção, No. 392,<br>
                        R/C e Cave, Macau<br>
                        Tel: (853) 2878 7288 / Fax: (853) 2878 7287
                    </div>
                </div>
            </div>

            <h1 class="form-title">MOTOR VEHICLE INSURANCE PROPOSAL FORM</h1>

            <div class="form-section">
                <div class="section-title">INSURED DETAILS</div>
                <div class="form-row">
                    <div class="form-label">Insured's Name:</div>
                    <div class="form-value" id="customerName"></div>
                </div>
                <div class="form-row">
                    <div class="form-label">Contact Number:</div>
                    <div class="form-value" id="customerPhone"></div>
                </div>
                <div class="form-row">
                    <div class="form-label">Correspondence Address:</div>
                    <div class="form-value" id="customerAddress"></div>
                </div>
                <div class="form-row">
                    <div class="form-label">Nationality:</div>
                    <div class="form-value" id="customerNationality"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">VEHICLE DETAILS</div>
                <div class="form-row">
                    <div class="form-label">Registration Number:</div>
                    <div class="form-value" id="vehicleRegNo"></div>
                </div>
                <div class="form-row">
                    <div class="form-label">Make & Model:</div>
                    <div class="form-value" id="vehicleMakeModel"></div>
                </div>
                <div class="form-row">
                    <div class="form-label">Number of Seats:</div>
                    <div class="form-value" id="vehicleSeats"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">INSURANCE COVERAGE</div>
                <table class="coverage-table">
                    <thead>
                        <tr>
                            <th>Coverage Items</th>
                            <th>Sum Insured</th>
                        </tr>
                    </thead>
                    <tbody id="coverageDetails">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-label">Insurance Period:</div>
                    <div class="form-value">
                        <div class="insurance-period">
                            <span class="insurance-period-text">From</span>
                            <div class="insurance-date-input">
                                <input type="text" id="insuranceStart" placeholder="YYYY-MM-DD" onchange="updateEndDate()" required>
                            </div>
                            <span class="insurance-period-text">to</span>
                            <div class="insurance-date-input">
                                <input type="text" id="insuranceEnd" readonly>
                            </div>
                            <span class="insurance-period-text">ONLY.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="declaration-text">
                I/We hereby declare that all the particulars of this proposal are true, and I/We agree that this Proposal shall be the basis of the Contract between myself/ourselves and CrossBorder Insurance. I/We understand that any false statement or misrepresentation of facts may result in the policy being void and all benefits being forfeited.
            </div>

            <div class="signature-section">
                <div class="section-title">DECLARATION AND SIGNATURE</div>
                <div class="signature-area">
                    <div class="signature-field">
                        <div class="form-label">Proposer's Signature:</div>
                        <canvas id="signatureCanvas" width="400" height="200"></canvas>
                        <button class="btn btn-secondary" onclick="clearSignature()">Clear Signature</button>
                    </div>
                    <div class="signature-field">
                        <div class="form-label">Date:</div>
                        <div class="form-value" id="currentDate"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn btn-download btn-action" onclick="downloadProposal()">
                <i class="fas fa-download"></i> Download PDF
            </button>
            <button class="btn btn-proceed btn-action" onclick="proceedToPayment()">
                <i class="fas fa-check"></i> Proceed to Payment
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        let canvas, ctx;
        let drawing = false;
        let lastX, lastY;

        window.onload = function() {
            loadProposalData();
            initializeSignature();
            setCurrentDate();
            initializeDatePicker();
        };

        function loadProposalData() {
            const urlParams = new URLSearchParams(window.location.search);
            const orderId = urlParams.get('id');

            if (!orderId) {
                alert('Invalid proposal ID');
                window.location.href = 'order_management.php';
                return;
            }

            fetch(`process_quotation.php?action=get_quotation&id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateProposalData(data.quotation);
                        // 如果是续保，设置开始日期为前保单结束日期的第二天，并禁用输入
                        if (data.quotation.is_renewal && data.quotation.previous_end_date) {
                            const previousEndDate = new Date(data.quotation.previous_end_date);
                            const startDate = new Date(previousEndDate);
                            startDate.setDate(startDate.getDate() + 1);
                            const startDateStr = startDate.toISOString().split('T')[0];

                            const startInput = document.getElementById('insuranceStart');
                            startInput.value = startDateStr;
                            startInput.readOnly = true;
                            startInput.style.backgroundColor = '#f8f9fa';
                            startInput.style.cursor = 'not-allowed';

                            // 更新结束日期
                            updateEndDate();
                        }
                    } else {
                        throw new Error(data.message || 'Failed to load proposal data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load proposal data. Please try again.');
                });
        }

        function populateProposalData(data) {
            // 填充客户信息
            document.getElementById('customerName').textContent = data.owner_name;
            document.getElementById('customerPhone').textContent = data.phone || '';
            document.getElementById('customerAddress').textContent = data.address || '';
            document.getElementById('customerNationality').textContent = data.nationality || '';

            // 填充车辆信息
            document.getElementById('vehicleRegNo').textContent = data.vehicle_id;
            document.getElementById('vehicleMakeModel').textContent = data.car_make_model;
            document.getElementById('vehicleSeats').textContent = data.seats;

            // 填充保险期限
            document.getElementById('insuranceStart').value = data.insurance_start || '';
            document.getElementById('insuranceEnd').value = data.insurance_end || '';

            // 填充保障项目
            const coverageDetails = document.getElementById('coverageDetails');
            coverageDetails.innerHTML = '';

            // 直接使用coverage_options对象，不需要JSON.parse
            const options = data.coverage_options;
            Object.entries(options).forEach(([key, value]) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${key.replace(/_/g, ' ')}</td>
                    <td>${typeof value === 'boolean' ? (value ? '是 Yes' : '否 No') : value}</td>
                `;
                coverageDetails.appendChild(row);
            });
        }

        function initializeSignature() {
            canvas = document.getElementById('signatureCanvas');
            ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = 2;

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            // 触摸设备支持
            canvas.addEventListener('touchstart', handleTouch);
            canvas.addEventListener('touchmove', handleTouch);
            canvas.addEventListener('touchend', stopDrawing);
        }

        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(
                e.type === 'touchstart' ? 'mousedown' : 'mousemove', {
                    clientX: touch.clientX,
                    clientY: touch.clientY
                }
            );
            canvas.dispatchEvent(mouseEvent);
        }

        function startDrawing(e) {
            drawing = true;
            const rect = canvas.getBoundingClientRect();
            lastX = e.clientX - rect.left;
            lastY = e.clientY - rect.top;
        }

        function draw(e) {
            if (!drawing) return;
            const rect = canvas.getBoundingClientRect();
            const currentX = e.clientX - rect.left;
            const currentY = e.clientY - rect.top;

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.stroke();

            lastX = currentX;
            lastY = currentY;
        }

        function stopDrawing() {
            drawing = false;
        }

        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function setCurrentDate() {
            const now = new Date();
            const dateStr = now.toLocaleDateString('zh-CN');
            document.getElementById('currentDate').textContent = dateStr;
        }

        function initializeDatePicker() {
            // 设置今天的日期作为参考
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];

            // 监听起始日期输入
            const startInput = document.getElementById('insuranceStart');
            startInput.addEventListener('input', function(e) {
                // 如果是续保保单，不允许修改日期
                if (this.readOnly) {
                    return;
                }

                // 验证日期格式
                const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                if (!dateRegex.test(this.value)) {
                    return;
                }

                // 验证日期是否早于今天
                if (this.value < todayStr) {
                    alert('Start date cannot be earlier than today');
                    this.value = todayStr;
                    document.getElementById('insuranceEnd').value = '';
                    return;
                }

                updateEndDate();
            });
        }

        function updateEndDate() {
            const startInput = document.getElementById('insuranceStart');
            const endInput = document.getElementById('insuranceEnd');

            // 验证起始日期格式
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(startInput.value)) {
                endInput.value = '';
                return;
            }

            // 计算结束日期（一年后减一天）
            const startDate = new Date(startInput.value);
            const endDate = new Date(startDate);
            endDate.setFullYear(endDate.getFullYear() + 1);
            endDate.setDate(endDate.getDate() - 1);

            // 格式化日期为 YYYY-MM-DD
            endInput.value = endDate.toISOString().split('T')[0];
        }

        function downloadProposal() {
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

                // 添加公司logo
                const logo = document.querySelector('.company-logo');
                if (logo) {
                    const logoData = logo.src;
                    pdf.addImage(logoData, 'PNG', margin, margin, 40, 20);
                }

                // 添加公司信息
                pdf.setFontSize(12);
                pdf.setFont(undefined, 'bold');
                pdf.text('CrossBorder Insurance COMPANY LIMITED', margin + 45, margin + 10);
                pdf.setFontSize(10);
                pdf.setFont(undefined, 'normal');
                pdf.text('Alameda Dr. Carlos D\'Assumpção, No. 392,', margin + 45, margin + 15);
                pdf.text('R/C e Cave, Macau', margin + 45, margin + 20);
                pdf.text('Tel: (853) 2878 7288 / Fax: (853) 2878 7287', margin + 45, margin + 25);

                // 添加标题
                pdf.setFontSize(16);
                pdf.setFont(undefined, 'bold');
                pdf.text('MOTOR VEHICLE INSURANCE PROPOSAL FORM', pageWidth / 2, margin + 40, {
                    align: 'center'
                });

                // 获取所有需要的数据
                const customerName = document.getElementById('customerName').textContent;
                const customerPhone = document.getElementById('customerPhone').textContent;
                const customerAddress = document.getElementById('customerAddress').textContent;
                const customerNationality = document.getElementById('customerNationality').textContent;

                const vehicleRegNo = document.getElementById('vehicleRegNo').textContent;
                const vehicleMakeModel = document.getElementById('vehicleMakeModel').textContent;
                const vehicleSeats = document.getElementById('vehicleSeats').textContent;

                const insuranceStart = document.getElementById('insuranceStart').value;
                const insuranceEnd = document.getElementById('insuranceEnd').value;

                // 创建表格数据
                const tableData = [
                    [{
                        content: 'PROPOSER DETAILS',
                        colSpan: 4,
                        styles: {
                            fontStyle: 'bold',
                            fillColor: [240, 240, 240],
                            fontSize: 12
                        }
                    }],
                    ['Proposer\'s Name', customerName, 'Contact Number', customerPhone],
                    ['Correspondence Address', {
                        content: customerAddress,
                        colSpan: 3
                    }],
                    ['Nationality', {
                        content: customerNationality,
                        colSpan: 3
                    }],
                    [{
                        content: 'VEHICLE DETAILS',
                        colSpan: 4,
                        styles: {
                            fontStyle: 'bold',
                            fillColor: [240, 240, 240],
                            fontSize: 12
                        }
                    }],
                    ['Registration Number', vehicleRegNo, 'Make & Model', vehicleMakeModel],
                    ['Number of Seats', {
                        content: vehicleSeats,
                        colSpan: 3
                    }],
                    [{
                        content: 'INSURANCE COVERAGE',
                        colSpan: 4,
                        styles: {
                            fontStyle: 'bold',
                            fillColor: [240, 240, 240],
                            fontSize: 12
                        }
                    }]
                ];

                // 添加保障项目
                const coverageDetails = document.getElementById('coverageDetails');
                const rows = coverageDetails.getElementsByTagName('tr');
                for (let row of rows) {
                    const cells = row.getElementsByTagName('td');
                    if (cells.length === 2) {
                        tableData.push([
                            cells[0].textContent,
                            {
                                content: cells[1].textContent,
                                colSpan: 3
                            }
                        ]);
                    }
                }

                // 添加保险期限
                tableData.push([{
                    content: 'INSURANCE PERIOD',
                    colSpan: 4,
                    styles: {
                        fontStyle: 'bold',
                        fillColor: [240, 240, 240],
                        fontSize: 12
                    }
                }]);
                tableData.push([
                    'From',
                    insuranceStart,
                    'to',
                    insuranceEnd
                ]);

                // 添加声明文本
                tableData.push([{
                    content: 'DECLARATION',
                    colSpan: 4,
                    styles: {
                        fontStyle: 'bold',
                        fillColor: [240, 240, 240],
                        fontSize: 12
                    }
                }]);
                tableData.push([{
                    content: 'I/We hereby declare that all the particulars of this proposal are true, and I/We agree that this Proposal shall be the basis of the Contract between myself/ourselves and CrossBorder Insurance. I/We understand that any false statement or misrepresentation of facts may result in the policy being void and all benefits being forfeited.',
                    colSpan: 4,
                    styles: {
                        fontSize: 9
                    }
                }]);

                // 绘制表格
                pdf.autoTable({
                    startY: margin + 45,
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
                        pdf.text('CrossBorder Insurance', pageWidth - margin, pageHeight - 10, {
                            align: 'right'
                        });
                        pdf.text(`Page ${data.pageNumber} of ${data.pageCount}`, pageWidth / 2, pageHeight - 10, {
                            align: 'center'
                        });
                    }
                });

                // 保存PDF
                pdf.save('MOTOR_VEHICLE_INSURANCE_PROPOSAL_FORM.pdf');

                // 移除加载提示
                document.body.removeChild(loadingElement);
            } catch (error) {
                console.error('Error generating PDF:', error);
                alert('Failed to generate PDF: ' + error.message);
                document.body.removeChild(loadingElement);
            }
        }

        function proceedToPayment() {
            // 检查是否有签名动作
            let hasSignature = false;
            try {
                const canvas = document.getElementById('signatureCanvas');
                const ctx = canvas.getContext('2d', {
                    willReadFrequently: true
                });
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

                // 只要有任何非透明像素就认为有签名
                for (let i = 3; i < imageData.length; i += 4) {
                    if (imageData[i] > 0) {
                        hasSignature = true;
                        break;
                    }
                }
            } catch (error) {
                console.error('Error checking signature:', error);
                hasSignature = false;
            }

            if (!hasSignature) {
                alert('Please sign in the signature box before proceeding.');
                return;
            }

            const startDate = document.getElementById('insuranceStart').value;
            const endDate = document.getElementById('insuranceEnd').value;

            if (!startDate) {
                alert('Please select insurance start date');
                return;
            }

            // 获取订单ID
            const urlParams = new URLSearchParams(window.location.search);
            const quotationId = urlParams.get('id');

            if (!quotationId) {
                alert('Quotation ID not found in URL');
                return;
            }

            // 显示加载状态
            const proceedButton = document.querySelector('.btn-proceed');
            proceedButton.disabled = true;
            proceedButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            // 首先检查是否存在重复保单
            fetch(`check_duplicate_policy.php?quotation_id=${quotationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.has_duplicate) {
                        // 显示重复保单提示
                        Swal.fire({
                            title: 'Duplicate Policy Warning',
                            html: 'System detected an existing active policy for this vehicle.<br>Please contact customer service.<br><br>Redirecting to homepage in 10 seconds...',
                            icon: 'warning',
                            showConfirmButton: false,
                            timer: 10000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = '../index.php';
                        });
                        throw new Error('Duplicate policy detected');
                    }

                    // 如果没有重复保单，继续更新订单状态和保险期限
                    return fetch('process_proposal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            quotationId: quotationId,
                            action: 'update_status',
                            insuranceStart: startDate,
                            insuranceEnd: endDate
                        })
                    });
                })
                .then(response => {
                    if (!response) return; // 如果之前已经返回了，就不继续处理
                    return response.json();
                })
                .then(data => {
                    if (data && data.success) {
                        window.location.href = `payment.php?id=${quotationId}&insuranceStart=${startDate}&insuranceEnd=${endDate}`;
                    } else {
                        throw new Error(data?.message || 'Failed to proceed');
                    }
                })
                .catch(error => {
                    if (error.message === 'Duplicate policy detected') {
                        // 如果是重复保单的错误，不显示错误提示
                        return;
                    }
                    console.error('Error details:', error);
                    alert('Error proceeding to payment: ' + error.message);
                    // 恢复按钮状态
                    proceedButton.disabled = false;
                    proceedButton.innerHTML = 'Proceed to Payment';
                });
        }

        function submitProposal() {
            const formData = {
                quotation_id: quotationId,
                start_date: document.getElementById('insuranceStart').value,
                end_date: document.getElementById('insuranceEnd').value,
                coverage_options: {
                    total_coverage: document.getElementById('totalCoverage').value,
                    final_excess: document.getElementById('finalExcess').value,
                    ncd: document.getElementById('ncd').value
                },
                premium: document.getElementById('totalPremium').value
            };

            // 首先检查是否存在重复保单
            fetch('check_duplicate_policy.php?quotation_id=' + quotationId)
                .then(response => response.json())
                .then(data => {
                    if (data.has_duplicate) {
                        // 显示重复保单提示
                        Swal.fire({
                            title: 'Duplicate Policy Warning',
                            html: 'System detected an existing active policy for this vehicle.<br>Please contact customer service.<br><br>Redirecting to homepage in 10 seconds...',
                            icon: 'warning',
                            showConfirmButton: false,
                            timer: 10000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = '../index.php';
                        });
                        return;
                    }

                    // 如果没有重复保单，继续提交流程
                    return fetch('process_proposal.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '提交成功',
                            text: '您的保险提案已提交，请等待审核。',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = '../index.php';
                        });
                    } else {
                        throw new Error(data.message || '提交失败');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: '错误',
                        text: error.message,
                        icon: 'error'
                    });
                });
        }
    </script>
</body>

</html>