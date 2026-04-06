<?php
session_start();
require_once('db_config.php');

// 如果是 AJAX 請求，返回 JSON 數據
if (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
) {
    header('Content-Type: application/json');

    try {
        $quotation_id = $_GET['id'];
        error_log("Processing quotation request - Quotation ID: " . $quotation_id);

        // 檢查報價單ID是否為空
        if (empty($quotation_id)) {
            throw new Exception('Quotation ID cannot be empty');
        }

        // 從 quotation 表獲取完整信息
        $sql = "SELECT 
                q.*,
                p.Name as Customer_Name,
                p.ID_No as Customer_ID,
                c.Car_Make_and_Model,
                c.Registration_No,
                c.Chasis_No,
                c.Seats,
                c.Date_of_Registration,
                p1.Product_Name as Product1_Name,
                p1.Death_coverage as Death_Coverage1,
                p1.Medical_coverage as Medical_Coverage1,
                p1.Material_coverage as Material_Coverage1,
                p1.Excess as Excess1,
                p1.Basic_premium as Basic_Premium1,
                p2.Product_Name as Product2_Name,
                p2.Death_coverage as Death_Coverage2,
                p2.Medical_coverage as Medical_Coverage2,
                p2.Material_coverage as Material_Coverage2,
                p2.Excess as Excess2,
                p2.Basic_premium as Basic_Premium2,
                p3.Product_Name as Product3_Name,
                p3.Death_coverage as Death_Coverage3,
                p3.Medical_coverage as Medical_Coverage3,
                p3.Material_coverage as Material_Coverage3,
                p3.Excess as Excess3,
                p3.Basic_premium as Basic_Premium3,
                q.Total_Coverage,
                q.Final_Excess,
                q.NCD,
                q.Total_Premium,
                q.User_ID
                FROM quotation q
                LEFT JOIN party p ON q.Party_No = p.Party_No
                LEFT JOIN car c ON q.Registration_No = c.Registration_No
                LEFT JOIN product p1 ON q.Product1_ID = p1.Product_ID
                LEFT JOIN product p2 ON q.Product2_ID = p2.Product_ID
                LEFT JOIN product p3 ON q.Product3_ID = p3.Product_ID
                WHERE q.Quotation_ID = ?";

        error_log("Executing quotation query: " . $sql);
        error_log("Query parameters (quotation_id): " . $quotation_id);

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$quotation_id]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$quotation) {
            error_log("No quotation record found");
            throw new Exception('No quotation record found');
        }

        error_log("Quotation record found: " . print_r($quotation, true));

        // 構建返回數據
        $response = [
            'success' => true,
            'data' => [
                'Quotation_ID' => $quotation['Quotation_ID'],
                'Customer_Name' => $quotation['Customer_Name'],
                'Customer_ID' => $quotation['Customer_ID'],
                'Registration_No' => $quotation['Registration_No'],
                'Car_Make_and_Model' => $quotation['Car_Make_and_Model'],
                'Seats' => $quotation['Seats'],
                'Total_Coverage' => $quotation['Total_Coverage'],
                'Final_Excess' => $quotation['Final_Excess'],
                'NCD' => $quotation['NCD'],
                'Total_Premium' => $quotation['Total_Premium'],
                'User_ID' => $quotation['User_ID'],
                'User_Discount' => 100.00, // 默認折扣率
                'Product1_Name' => $quotation['Product1_Name'],
                'Death_Coverage1' => $quotation['Death_Coverage1'],
                'Medical_Coverage1' => $quotation['Medical_Coverage1'],
                'Material_Coverage1' => $quotation['Material_Coverage1'],
                'Excess1' => $quotation['Excess1'],
                'Basic_Premium1' => $quotation['Basic_Premium1'],
                'Product2_Name' => $quotation['Product2_Name'],
                'Death_Coverage2' => $quotation['Death_Coverage2'],
                'Medical_Coverage2' => $quotation['Medical_Coverage2'],
                'Material_Coverage2' => $quotation['Material_Coverage2'],
                'Excess2' => $quotation['Excess2'],
                'Basic_Premium2' => $quotation['Basic_Premium2'],
                'Product3_Name' => $quotation['Product3_Name'],
                'Death_Coverage3' => $quotation['Death_Coverage3'],
                'Medical_Coverage3' => $quotation['Medical_Coverage3'],
                'Material_Coverage3' => $quotation['Material_Coverage3'],
                'Excess3' => $quotation['Excess3'],
                'Basic_Premium3' => $quotation['Basic_Premium3']
            ]
        ];

        // 獲取用戶折扣
        if (isset($quotation['User_ID'])) {
            $discountSql = "SELECT discount FROM user WHERE User_ID = ?";
            $discountStmt = $pdo->prepare($discountSql);
            $discountStmt->execute([$quotation['User_ID']]);
            $discountResult = $discountStmt->fetch(PDO::FETCH_ASSOC);
            if ($discountResult) {
                $response['data']['User_Discount'] = $discountResult['discount'];
            }
        }

        error_log("Final response data: " . print_r($response, true));
        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        error_log("Error processing quotation: " . $e->getMessage());
        error_log("Error stack: " . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'debug' => $e->getMessage()
        ]);
        exit;
    }
}

// 如果不是 AJAX 請求，顯示頁面
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Quotation</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .quotation-container {
            max-width: 800px;
            margin: 2rem auto;
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

        .quotation-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
        }

        .quotation-logo {
            max-width: 200px;
            margin-bottom: 1rem;
        }

        .quotation-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .quotation-number {
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
            color: white;
        }

        .preview-wrapper {
            position: relative;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .preview-header {
            position: absolute;
            top: -30px;
            left: 0;
            background: #01459C;
            color: white;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 14px;
        }

        .document-preview {
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .btn-confirm {
            background-color: #01459C;
            border-color: #01459C;
            color: white;
            padding: 0.75rem 3rem;
            font-size: 1.1rem;
            border-radius: 8px;
        }

        .btn-confirm:hover {
            background-color: #013579;
            border-color: #013579;
            color: white;
        }

        @media print {

            .back-button,
            .action-buttons {
                display: none;
            }

            body {
                background-color: white;
                padding: 0;
                margin: 0;
            }

            .quotation-container {
                box-shadow: none;
                margin: 0;
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <?php
    error_log("Debugging view_quotation.php");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("GET parameters: " . print_r($_GET, true));
    ?>
    <a href="order_management.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Order Management
    </a>

    <div class="quotation-container">
        <div class="progress-container">
            <div class="progress-bar-custom">
                <div class="progress" style="width: 0"></div>
            </div>
            <div class="step-circles">
                <div class="step-circle active">1
                    <div class="step-label">Quotation</div>
                </div>
                <div class="step-circle">2
                    <div class="step-label">Proposal</div>
                </div>
                <div class="step-circle">3
                    <div class="step-label">Payment</div>
                </div>
            </div>
        </div>

        <div class="quotation-header">
            <h1 class="quotation-title">Insurance Quotation</h1>
            <div class="quotation-number" id="quotationNumber"></div>
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
            <div class="row">
                <div class="col-md-6">
                    <div class="coverage-item">
                        <div>Total Coverage</div>
                        <div id="totalCoverage"></div>
                    </div>
                    <div class="coverage-item">
                        <div>Final Excess</div>
                        <div id="finalExcess"></div>
                    </div>
                    <div class="coverage-item">
                        <div>NCD</div>
                        <div id="ncd"></div>
                    </div>
                    <div class="coverage-item" id="userDiscountContainer" style="display: none;">
                        <div>User Discount</div>
                        <div id="userDiscount"></div>
                    </div>
                    <div class="coverage-item">
                        <div>Total Premium</div>
                        <div id="totalPremium"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="coverage-item">
                        <div>Product 1</div>
                        <div id="product1Name"></div>
                    </div>
                    <div class="coverage-item">
                        <div>Death Coverage 1</div>
                        <div id="deathCoverage1"></div>
                    </div>
                    <div class="coverage-item">
                        <div>Medical Coverage 1</div>
                        <div id="medicalCoverage1"></div>
                    </div>
                    <div class="coverage-item">
                        <div>Material Coverage 1</div>
                        <div id="materialCoverage1"></div>
                    </div>
                    <div class="coverage-item">
                        <div>Excess 1</div>
                        <div id="excess1"></div>
                    </div>
                    <div id="product2Container" style="display: none;">
                        <div class="coverage-item">
                            <div>Product 2</div>
                            <div id="product2Name"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Death Coverage 2</div>
                            <div id="deathCoverage2"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Medical Coverage 2</div>
                            <div id="medicalCoverage2"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Material Coverage 2</div>
                            <div id="materialCoverage2"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Excess 2</div>
                            <div id="excess2"></div>
                        </div>
                    </div>
                    <div id="product3Container" style="display: none;">
                        <div class="coverage-item">
                            <div>Product 3</div>
                            <div id="product3Name"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Death Coverage 3</div>
                            <div id="deathCoverage3"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Medical Coverage 3</div>
                            <div id="medicalCoverage3"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Material Coverage 3</div>
                            <div id="materialCoverage3"></div>
                        </div>
                        <div class="coverage-item">
                            <div>Excess 3</div>
                            <div id="excess3"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="total-section">
                <div class="total-row">
                    <span>Total Premium:</span>
                    <span id="totalPremiumBottom"></span>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn btn-confirm btn-download" onclick="proceedToProposal()">
                <i class="fas fa-check"></i>
                Confirm
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        async function loadQuotation() {
            const urlParams = new URLSearchParams(window.location.search);
            const quotationId = urlParams.get('id');

            console.log('URL parameters:', urlParams.toString());
            console.log('Quotation ID:', quotationId);

            // 檢查報價單ID是否有效
            if (!quotationId || quotationId === '0') {
                console.error('Invalid quotation ID');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid quotation ID',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'order_management.php';
                });
                return;
            }

            try {
                console.log('Starting to get quotation details...');
                console.log('Sending request to:', `view_quotation.php?id=${quotationId}`);

                // 獲取報價單詳細信息
                const detailResponse = await fetch(`view_quotation.php?id=${quotationId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                console.log('Received response status:', detailResponse.status);
                console.log('Response headers:', Object.fromEntries(detailResponse.headers.entries()));

                if (!detailResponse.ok) {
                    throw new Error(`Failed to get quotation details: ${detailResponse.status}`);
                }

                const detailResult = await detailResponse.json();
                console.log('Quotation details:', detailResult);

                if (!detailResult.success) {
                    console.error('Quotation details request failed:', detailResult.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: detailResult.message || 'Failed to get quotation details',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'order_management.php';
                    });
                    return;
                }

                // 填充頁面數據
                console.log('Starting to fill page data...');
                const data = detailResult.data;
                console.log('Filled data:', data);

                // 填充基本信息
                document.getElementById('quotationNumber').textContent = `Quotation ID #${data.Quotation_ID}`;
                document.getElementById('customerName').textContent = data.Customer_Name;
                document.getElementById('customerId').textContent = data.Customer_ID;
                document.getElementById('registrationNo').textContent = data.Registration_No;
                document.getElementById('makeModel').textContent = data.Car_Make_and_Model;
                document.getElementById('seats').textContent = data.Seats;

                // 填充保障信息
                console.log('Original Total Coverage:', data.Total_Coverage);
                console.log('Original Final Excess:', data.Final_Excess);
                console.log('Original NCD:', data.NCD);
                console.log('Original Total Premium:', data.Total_Premium);

                document.getElementById('totalCoverage').textContent = `MOP ${Number(data.Total_Coverage || 0).toFixed(2)}`;
                document.getElementById('finalExcess').textContent = `MOP ${Number(data.Final_Excess || 0).toFixed(2)}`;
                document.getElementById('ncd').textContent = `${(Number(data.NCD || 0) * 100).toFixed(0)}%`;

                // 處理用戶折扣顯示
                const userDiscountContainer = document.getElementById('userDiscountContainer');
                const userDiscountElement = document.getElementById('userDiscount');
                if (userDiscountContainer && userDiscountElement) {
                    const userDiscount = Number(data.User_Discount || 100);
                    if (userDiscount !== 100) {
                        userDiscountContainer.style.display = 'flex';
                        userDiscountElement.textContent = `${userDiscount.toFixed(2)}%`;
                    } else {
                        userDiscountContainer.style.display = 'none';
                    }
                }

                document.getElementById('totalPremium').textContent = `MOP ${Number(data.Total_Premium || 0).toFixed(2)}`;
                document.getElementById('totalPremiumBottom').textContent = `MOP ${Number(data.Total_Premium || 0).toFixed(2)}`;

                // 填充產品1信息
                console.log('Product 1 coverage:', {
                    death: data.Death_Coverage1,
                    medical: data.Medical_Coverage1,
                    material: data.Material_Coverage1
                });
                document.getElementById('product1Name').textContent = data.Product1_Name;
                document.getElementById('deathCoverage1').textContent = `MOP ${Number(data.Death_Coverage1 || 0).toFixed(2)}`;
                document.getElementById('medicalCoverage1').textContent = `MOP ${Number(data.Medical_Coverage1 || 0).toFixed(2)}`;
                document.getElementById('materialCoverage1').textContent = `MOP ${Number(data.Material_Coverage1 || 0).toFixed(2)}`;
                document.getElementById('excess1').textContent = `MOP ${Number(data.Excess1 || 0).toFixed(2)}`;

                // 處理產品2
                if (data.Product2_Name) {
                    console.log('Product 2 coverage:', {
                        death: data.Death_Coverage2,
                        medical: data.Medical_Coverage2,
                        material: data.Material_Coverage2
                    });
                    document.getElementById('product2Container').style.display = 'block';
                    document.getElementById('product2Name').textContent = data.Product2_Name;
                    document.getElementById('deathCoverage2').textContent = `MOP ${Number(data.Death_Coverage2 || 0).toFixed(2)}`;
                    document.getElementById('medicalCoverage2').textContent = `MOP ${Number(data.Medical_Coverage2 || 0).toFixed(2)}`;
                    document.getElementById('materialCoverage2').textContent = `MOP ${Number(data.Material_Coverage2 || 0).toFixed(2)}`;
                    document.getElementById('excess2').textContent = `MOP ${Number(data.Excess2 || 0).toFixed(2)}`;
                }

                // 處理產品3
                if (data.Product3_Name) {
                    console.log('Product 3 coverage:', {
                        death: data.Death_Coverage3,
                        medical: data.Medical_Coverage3,
                        material: data.Material_Coverage3
                    });
                    document.getElementById('product3Container').style.display = 'block';
                    document.getElementById('product3Name').textContent = data.Product3_Name;
                    document.getElementById('deathCoverage3').textContent = `MOP ${Number(data.Death_Coverage3 || 0).toFixed(2)}`;
                    document.getElementById('medicalCoverage3').textContent = `MOP ${Number(data.Medical_Coverage3 || 0).toFixed(2)}`;
                    document.getElementById('materialCoverage3').textContent = `MOP ${Number(data.Material_Coverage3 || 0).toFixed(2)}`;
                    document.getElementById('excess3').textContent = `MOP ${Number(data.Excess3 || 0).toFixed(2)}`;
                }

                console.log('Page data filled');

            } catch (error) {
                console.error('Failed to load quotation:', error);
                console.error('Error stack:', error.stack);
                alert('Failed to load quotation: ' + error.message);
            }
        }

        function addCoverageItem(container, label, value) {
            const item = document.createElement('div');
            item.className = 'coverage-item';
            item.innerHTML = `
            <div>${label}</div>
            <div>${value}</div>
        `;
            container.appendChild(item);
        }

        function downloadQuotation() {
            const element = document.querySelector('.quotation-container');
            const opt = {
                margin: 1,
                filename: `quotation_${new Date().getTime()}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            const buttons = document.querySelector('.action-buttons');
            const backButton = document.querySelector('.back-button');
            buttons.style.display = 'none';
            backButton.style.display = 'none';

            html2pdf().set(opt).from(element).save().then(() => {
                buttons.style.display = 'flex';
                backButton.style.display = 'flex';
            });
        }

        function proceedToProposal() {
            const quotationId = document.getElementById('quotationNumber').textContent.replace('Quotation ID #', '');
            window.location.href = `proposal.php?id=${quotationId}`;
        }

        document.addEventListener('DOMContentLoaded', loadQuotation);
    </script>
</body>

</html>