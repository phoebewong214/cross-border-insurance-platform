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
    <title>Product Selection - Insurance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
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

        .section-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
        }

        .product-selection-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .product-card {
            background: white;
            border: 2px solid transparent;
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .product-card:hover {
            border-color: #01459C;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(1, 69, 156, 0.1);
        }

        .product-card.selected {
            border-color: #01459C;
            background-color: #f8f9fa;
        }

        .product-icon {
            width: 60px;
            height: 60px;
            background: #e8f0fe;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .product-icon i {
            font-size: 24px;
            color: #01459C;
        }

        .product-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #01459C;
            margin-bottom: 1rem;
            min-height: 3rem;
            display: flex;
            align-items: center;
        }

        .product-description {
            color: #6c757d;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-list li {
            margin-bottom: 0.75rem;
            padding-left: 1.5rem;
            position: relative;
            color: #495057;
        }

        .feature-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #01459C;
        }

        .coverage-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .coverage-option {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .coverage-option h5 {
            color: #01459C;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .form-select {
            border-radius: 10px;
            border-color: rgba(1, 69, 156, 0.1);
            padding: 0.75rem 1rem;
        }

        .form-select:focus {
            border-color: #01459C;
            box-shadow: 0 0 0 0.25rem rgba(1, 69, 156, 0.1);
        }

        .summary-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 2rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .total-price {
            background: #01459C;
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 1.5rem;
            text-align: center;
        }

        .btn-primary {
            background: #01459C;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #013579;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #666;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #333;
        }

        .navigation-buttons {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .navigation-buttons button {
            flex: 1;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            position: relative;
            max-width: 600px;
            margin: 0 auto 3rem;
            padding: 0 40px;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 70px;
            right: 70px;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: #01459C;
            border-color: #01459C;
            color: white;
            box-shadow: 0 0 0 3px rgba(1, 69, 156, 0.2);
        }

        .step.completed .step-circle {
            background: #01459C;
            border-color: #01459C;
            color: white;
        }

        .step.completed+.step::before {
            background: #01459C;
        }

        .step-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .step.active .step-label {
            color: #01459C;
        }

        /* 添加提示框樣式 */
        .notice-modal .modal-content {
            border-radius: 15px;
        }

        .notice-modal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 15px 15px 0 0;
        }

        .notice-modal .modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .notice-modal .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 15px 15px;
        }

        .notice-modal .form-check {
            margin-top: 15px;
        }

        .notice-modal .btn-continue {
            min-width: 100px;
        }

        .notice-modal .timer {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <a href="../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Personal Center
    </a>

    <!-- 添加提示框 -->
    <div class="modal fade notice-modal" id="noticeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noticeModalLabel">Special Notices</h5>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">Special Notices:</h6>
                    <ol class="mb-4">
                        <li class="mb-2">If there are any changes to the vehicle owner during the application process or while the policy is in effect, please contact the insurance company promptly to update the policyholder information. This is to prevent any impact on the validity of the policy. Failure to do so will result in our company not being responsible for any resulting liabilities.</li>
                        <li class="mb-2">If the policyholder or driver is under 25 years old or has less than 2 years of driving experience, it is important to inform and provide the identification and driver's license of the relevant individuals before applying for insurance. If this information is not provided during the quoting process and is discovered at the time of issuance or during the policy period, our company reserves the right to adjust the premium.</li>
                        <li class="mb-2">If the vehicle already has a valid Northbound Travel for Macau Vehicles/ Hengqin Single License Plate/ Cross-boundary Permit (Guangdong and Macau) and holds valid China Vehicle Third Party Liability Insurance, there is no need to obtain duplicate coverage.</li>
                        <li class="mb-2">The policy will be issued approximately 7 working days after all signed application forms/ confirmation statements and transfer records are received. Please allow enough time for this process.</li>
                        <li class="mb-2">If a "Hong Kong license plate number (ZM xxxx)" is issued after the policy has been issued, please provide the necessary documents for updating the policy.</li>
                        <li class="mb-2">If the final permit issuance date is earlier or later than the policy's effective date, the policy period cannot be changed. Please consider this carefully to avoid disrupting your travel plans. Thank you for your support and understanding.</li>
                    </ol>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agreeCheckbox" disabled>
                        <label class="form-check-label" for="agreeCheckbox">
                            I have read and agree to the above notices <span class="timer">(5)</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-continue" id="continueBtn" disabled>Continue</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <h1 class="section-title">Select Insurance Product</h1>

        <div class="product-selection-container">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active">
                    <div class="step-circle">1</div>
                    <div class="step-label">Select Product</div>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <div class="step-label">Coverage Options</div>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <div class="step-label">Review & Confirm</div>
                </div>
            </div>

            <!-- Step 1: Product Selection -->
            <div id="productSelection" class="step-content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="product-card" onclick="selectProduct('compulsory')">
                            <div class="product-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="product-title">Statutory Automobile Liability Insurance</h3>
                            <p class="product-description">Basic mandatory coverage for cross-boundary vehicles</p>
                            <ul class="feature-list">
                                <li>Coverage up to MOP 200,000</li>
                                <li>1-5 seats: MOP 950/year</li>
                                <li>6-9 seats: MOP 1,100/year</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="product-card" onclick="selectProduct('commercial')">
                            <div class="product-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h3 class="product-title">Commercial Insurance Package</h3>
                            <p class="product-description">Enhanced protection for your vehicle</p>
                            <ul class="feature-list">
                                <li>Third Party Liability Options</li>
                                <li>Passenger Liability Coverage</li>
                                <li>Flexible Coverage Plans</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="product-card" onclick="selectProduct('hk_private')">
                            <div class="product-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <h3 class="product-title">Hong Kong Private Car Insurance</h3>
                            <p class="product-description">Comprehensive coverage for Hong Kong private vehicles</p>
                            <ul class="feature-list">
                                <li>Base Premium: MOP 2,000</li>
                                <li>Base Excess: MOP 6,000</li>
                                <li>MIB Loading: 3.15%</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Coverage Selection -->
            <div id="coverageSelection" class="step-content" style="display: none;">
                <!-- Vehicle Selection -->
                <div class="coverage-option">
                    <h5>Select Vehicle</h5>
                    <select class="form-select" id="vehicleSelect" onchange="updateVehicleInfo()">
                        <option value="">Please select a vehicle</option>
                    </select>
                </div>

                <div id="compulsoryCoverage" class="coverage-container" style="display: none;">
                    <div class="coverage-option">
                        <h5>Vehicle Category</h5>
                        <select class="form-select" id="seatCount" disabled>
                            <option value="1-5">1-5 Seats</option>
                            <option value="6-9">6-9 Seats</option>
                        </select>
                        <small class="text-muted">Based on selected vehicle</small>
                    </div>
                    <div class="coverage-option">
                        <h5>Payment Period</h5>
                        <select class="form-select" id="paymentPeriod">
                            <option value="annual">Annual Payment (8% discount)</option>
                            <option value="short">Short-term Payment</option>
                        </select>
                    </div>
                </div>
                <div id="commercialCoverage" class="coverage-container" style="display: none;">
                    <div class="coverage-option">
                        <h5>Third Party Liability</h5>
                        <select class="form-select" id="thirdPartyLimit">
                            <option value="MTZ003">MOP 1,000,000</option>
                            <option value="MTZ005">MOP 1,500,000</option>
                            <option value="MTZ007">MOP 2,000,000</option>
                            <option value="MTZ009">MOP 3,000,000</option>
                            <option value="MTZ011">MOP 5,000,000</option>
                        </select>
                    </div>
                    <div class="coverage-option">
                        <h5>Passenger Liability (Per Person)</h5>
                        <select class="form-select" id="passengerLimit">
                            <option value="MTZ013">MOP 10,000</option>
                            <option value="MTZ014">MOP 30,000</option>
                            <option value="MTZ015">MOP 50,000</option>
                            <option value="MTZ016">MOP 100,000</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 3: Summary -->
            <div id="summary" class="step-content" style="display: none;">
                <div class="summary-card">
                    <h4 class="mb-4">Coverage Summary</h4>
                    <div id="summaryContent">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    <div class="total-price">
                        <div class="h3 mb-0">Total Premium: <span id="totalPremium">MOP 0</span></div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="navigation-buttons">
                <button class="btn btn-secondary" id="prevBtn" onclick="prevStep()" style="display: none;">Previous</button>
                <button class="btn btn-primary" id="nextBtn" onclick="nextStep()">Next</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.js"></script>
    <script>
        let currentStep = 1;
        let selectedProduct = null;
        let productPremiums = {}; // 存储产品保费信息

        // 获取产品保费信息
        function loadProductPremiums() {
            fetch('process_product_selection.php?action=get_product_premiums')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        productPremiums = data.premiums;
                    } else {
                        if (data.message === 'User not logged in') {
                            Swal.fire({
                                title: '請先登錄',
                                text: '您需要先登錄才能繼續操作',
                                icon: 'warning',
                                confirmButtonText: '確定'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = data.redirect;
                                }
                            });
                        } else {
                            throw new Error(data.message || 'Failed to load product premiums');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading product premiums:', error);
                });
        }

        function selectProduct(product) {
            selectedProduct = product;
            document.querySelectorAll('.product-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');

            // 顯示相應的表單
            if (product === 'compulsory') {
                document.getElementById('compulsoryCoverage').style.display = 'block';
                document.getElementById('commercialCoverage').style.display = 'none';
            } else if (product === 'commercial') {
                document.getElementById('compulsoryCoverage').style.display = 'none';
                document.getElementById('commercialCoverage').style.display = 'block';
            } else if (product === 'hk_private') {
                // 香港私家車保險不需要顯示額外的選項
                document.getElementById('compulsoryCoverage').style.display = 'none';
                document.getElementById('commercialCoverage').style.display = 'none';
            }
        }

        function showStep(step) {
            document.querySelectorAll('.step-content').forEach(content => {
                content.style.display = 'none';
            });

            document.querySelectorAll('.step').forEach((stepIndicator, index) => {
                if (index + 1 < step) {
                    stepIndicator.classList.add('completed');
                    stepIndicator.classList.remove('active');
                } else if (index + 1 === step) {
                    stepIndicator.classList.add('active');
                    stepIndicator.classList.remove('completed');
                } else {
                    stepIndicator.classList.remove('active', 'completed');
                }
            });

            switch (step) {
                case 1:
                    document.getElementById('productSelection').style.display = 'block';
                    document.getElementById('prevBtn').style.display = 'none';
                    break;
                case 2:
                    document.getElementById('coverageSelection').style.display = 'block';
                    document.getElementById('prevBtn').style.display = 'block';
                    // 根据选择的产品显示相应的表单
                    if (selectedProduct === 'compulsory') {
                        document.getElementById('compulsoryCoverage').style.display = 'block';
                        document.getElementById('commercialCoverage').style.display = 'none';
                    } else if (selectedProduct === 'commercial') {
                        document.getElementById('compulsoryCoverage').style.display = 'none';
                        document.getElementById('commercialCoverage').style.display = 'block';
                    }
                    // 加载车辆列表
                    if (!document.getElementById('vehicleSelect').options.length > 1) {
                        loadVehicles();
                    }
                    break;
                case 3:
                    document.getElementById('summary').style.display = 'block';
                    updateSummary();
                    break;
            }
        }

        function nextStep() {
            if (currentStep === 1 && !selectedProduct) {
                Swal.fire({
                    title: 'Please Select a Product',
                    text: 'You must select an insurance product to continue.',
                    icon: 'warning'
                });
                return;
            }

            if (currentStep === 2) {
                const vehicleSelect = document.getElementById('vehicleSelect');
                if (!vehicleSelect.value) {
                    Swal.fire({
                        title: 'Please Select a Vehicle',
                        text: 'You must select a vehicle to continue.',
                        icon: 'warning'
                    });
                    return;
                }
            }

            if (currentStep < 3) {
                currentStep++;
                showStep(currentStep);
                if (currentStep === 3) {
                    document.getElementById('nextBtn').textContent = 'Confirm';
                }
            } else {
                submitForm();
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                document.getElementById('nextBtn').textContent = 'Next';
            }
        }

        // 存储车辆数据
        let carsData = [];

        // 获取车辆列表
        function loadVehicles() {
            fetch('process_product_selection.php?action=get_cars')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        carsData = data.cars;
                        const vehicleSelect = document.getElementById('vehicleSelect');
                        vehicleSelect.innerHTML = '<option value="">Please select a vehicle</option>';

                        if (data.cars && data.cars.length > 0) {
                            data.cars.forEach((car, index) => {
                                const option = document.createElement('option');
                                option.value = car.Registration_No;
                                option.textContent = `${car.Registration_No} - ${car.Car_Make_and_Model}`;
                                option.dataset.seats = car.Seats;
                                vehicleSelect.appendChild(option);
                            });
                        } else {
                            Swal.fire({
                                title: 'No Vehicles',
                                text: 'No vehicles found in the database',
                                icon: 'info',
                                html: 'No vehicles found in the database.<br><br>Click <a href="manage_car.php" class="alert-link">here</a> to navigate to Car Management.',
                                confirmButtonText: 'OK'
                            });
                        }
                    } else {
                        if (data.message === 'User not logged in') {
                            Swal.fire({
                                title: 'Please Login',
                                text: 'You need to login to continue',
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = data.redirect;
                                }
                            });
                        } else {
                            throw new Error(data.message || 'Failed to load vehicles');
                        }
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Failed to load vehicles. Please try again later.',
                        icon: 'error'
                    });
                });
        }

        // 页面加载完成后初始化
        document.addEventListener('DOMContentLoaded', () => {
            loadVehicles();
            loadProductPremiums();
        });

        // 更新车辆信息
        function updateVehicleInfo() {
            const vehicleSelect = document.getElementById('vehicleSelect');
            const seatCount = document.getElementById('seatCount');
            const thirdPartyLimit = document.getElementById('thirdPartyLimit');

            if (vehicleSelect.value) {
                const selectedCar = carsData.find(car => car.Registration_No === vehicleSelect.value);
                if (selectedCar) {
                    seatCount.value = selectedCar.Seats <= 5 ? "1-5" : "6-9";

                    // 根据座位数更新Third Party Liability选项
                    if (selectedCar.Seats <= 5) {
                        // 1-5座位的选项
                        thirdPartyLimit.innerHTML = `
                            <option value="MTZ003">MOP 1,000,000</option>
                            <option value="MTZ005">MOP 1,500,000</option>
                            <option value="MTZ007">MOP 2,000,000</option>
                            <option value="MTZ009">MOP 3,000,000</option>
                            <option value="MTZ011">MOP 5,000,000</option>
                        `;
                    } else {
                        // 6-9座位的选项
                        thirdPartyLimit.innerHTML = `
                            <option value="MTZ004">MOP 1,000,000</option>
                            <option value="MTZ006">MOP 1,500,000</option>
                            <option value="MTZ008">MOP 2,000,000</option>
                            <option value="MTZ010">MOP 3,000,000</option>
                            <option value="MTZ012">MOP 5,000,000</option>
                        `;
                    }

                    calculatePremium();
                }
            }
        }

        function calculatePremium() {
            let premium = 0;
            let html = ''; // 添加html变量定义

            if (selectedProduct === 'compulsory') {
                const seatCount = document.getElementById('seatCount').value;
                const paymentPeriod = document.getElementById('paymentPeriod').value;

                // 调用强制保险计算API
                fetch('calculate_compulsory.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            seat_count: seatCount,
                            payment_period: paymentPeriod
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            premium = data.premium;
                            document.getElementById('totalPremium').textContent = `MOP ${premium.toFixed(2)}`;
                        }
                    });
            } else if (selectedProduct === 'commercial') {
                const thirdPartyLimit = document.getElementById('thirdPartyLimit').value;
                const passengerLimit = document.getElementById('passengerLimit').value;
                const vehicleSelect = document.getElementById('vehicleSelect');
                const selectedCar = carsData.find(car => car.Registration_No === vehicleSelect.value);
                const seats = selectedCar ? selectedCar.Seats : 0;

                // 调用商业保险计算API
                fetch('calculate_commercial.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            third_party_limit: thirdPartyLimit,
                            passenger_limit: passengerLimit,
                            seats: seats
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            html = `
                            <div class="summary-item">
                                <span>Insurance Type:</span>
                                <span>Commercial Insurance Package</span>
                            </div>
                            <div class="summary-item">
                                <span>Third Party Liability:</span>
                                <span>${data.breakdown.thirdPartyProduct.name}</span>
                            </div>
                            <div class="summary-item">
                                <span>Third Party Liability Premium:</span>
                                <span>MOP ${Number(data.breakdown.thirdPartyProduct.premium).toFixed(2)}</span>
                            </div>
                            <div class="summary-item">
                                <span>Passenger Liability:</span>
                                <span>${data.breakdown.passengerProduct.name} per person (${seats} seats)</span>
                            </div>
                            <div class="summary-item">
                                <span>Passenger Liability Premium:</span>
                                <span>MOP ${Number(data.breakdown.passengerProduct.totalPremium).toFixed(2)}</span>
                            </div>
                            <div class="summary-item">
                                <span>Total Premium:</span>
                                <span>MOP ${Number(data.breakdown.totalPremium).toFixed(2)}</span>
                            </div>
                        `;
                            document.getElementById('summaryContent').innerHTML = html;
                            document.getElementById('totalPremium').textContent = `MOP ${Number(data.breakdown.totalPremium).toFixed(2)}`;
                        } else {
                            console.error('Error calculating commercial premium:', data.message);
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to calculate commercial insurance premium',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to calculate commercial insurance premium',
                            icon: 'error'
                        });
                    });
            } else if (selectedProduct === 'hk_private') {
                const selectedCar = carsData.find(car => car.Registration_No === vehicleSelect.value);
                if (!selectedCar) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please select a vehicle first',
                        icon: 'error'
                    });
                    return;
                }

                // Check if owner information exists
                if (!selectedCar.Owner1_Birth_Date || !selectedCar.Owner1_B_License_Date) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Unable to get owner information. Please ensure the vehicle is associated with valid owner data.',
                        icon: 'error'
                    });
                    return;
                }

                // Extract years from dates
                const birthYear = new Date(selectedCar.Owner1_Birth_Date).getFullYear();
                const licenseYear = new Date(selectedCar.Owner1_B_License_Date).getFullYear();

                // Call Hong Kong car insurance calculation API
                fetch('calculate_hk_private.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            registration_year: selectedCar.Date_of_Registration,
                            birth_year: birthYear,
                            license_year: licenseYear
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            html += `
                        <div class="summary-item">
                            <span>Insurance Type:</span>
                            <span>Hong Kong Private Car Insurance (MTY001)</span>
                        </div>
                        <div class="summary-item">
                            <span>Premium Calculation:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Base Premium:</span>
                            <span>MOP ${data.breakdown.basePremium.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Additional Loadings:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Age Loading:</span>
                            <span>MOP ${data.breakdown.agePremiumLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Experience Loading:</span>
                            <span>MOP ${data.breakdown.experiencePremiumLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Vehicle Age Loading:</span>
                            <span>MOP ${data.breakdown.carAgePremiumLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>MIB Loading:</span>
                            <span>MOP ${data.breakdown.mibLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="font-weight: bold;">
                            <span>Final Premium:</span>
                            <span>MOP ${data.breakdown.finalPremium.toFixed(2)}</span>
                        </div>
                        <div class="summary-item">
                            <span>Excess Calculation:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Base Excess:</span>
                            <span>MOP ${data.breakdown.baseExcess.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Additional Excess Loadings:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Age Excess Loading:</span>
                            <span>MOP ${data.breakdown.ageExcessLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Experience Excess Loading:</span>
                            <span>MOP ${data.breakdown.experienceExcessLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="font-weight: bold;">
                            <span>Final Excess:</span>
                            <span>MOP ${data.breakdown.finalExcess.toFixed(2)}</span>
                        </div>
                        `;
                            summaryContent.innerHTML = html;
                            document.getElementById('totalPremium').textContent = `MOP ${data.breakdown.finalPremium.toFixed(2)}`;
                        } else {
                            throw new Error(data.message || 'Failed to calculate premium');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'An error occurred while calculating premium',
                            icon: 'error'
                        });
                    });
            }

            return premium;
        }

        function updateSummary() {
            const summaryContent = document.getElementById('summaryContent');
            const selectedVehicle = document.getElementById('vehicleSelect');
            const vehicleText = selectedVehicle.options[selectedVehicle.selectedIndex].text;
            let html = '';

            html += `
                <div class="summary-item">
                    <span>Selected Vehicle:</span>
                    <span>${vehicleText}</span>
                </div>
            `;

            if (selectedProduct === 'compulsory') {
                const seatCount = document.getElementById('seatCount').value;
                const paymentPeriod = document.getElementById('paymentPeriod').value;
                html += `
                    <div class="summary-item">
                        <span>Insurance Type:</span>
                        <span>Statutory Automobile Liability Insurance</span>
                    </div>
                    <div class="summary-item">
                        <span>Vehicle Category:</span>
                        <span>${seatCount} Seats</span>
                    </div>
                    <div class="summary-item">
                        <span>Payment Period:</span>
                        <span>${paymentPeriod === 'annual' ? 'Annual' : 'Short-term'}</span>
                    </div>
                `;
                summaryContent.innerHTML = html;
            } else if (selectedProduct === 'commercial') {
                const thirdPartyLimit = document.getElementById('thirdPartyLimit');
                const passengerLimit = document.getElementById('passengerLimit');
                const selectedCar = carsData.find(car => car.Registration_No === selectedVehicle.value);
                const seats = selectedCar ? selectedCar.Seats : 0;

                // 调用商业保险计算API
                fetch('calculate_commercial.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            third_party_limit: thirdPartyLimit.value,
                            passenger_limit: passengerLimit.value,
                            seats: seats
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            html += `
                            <div class="summary-item">
                                <span>Insurance Type:</span>
                                <span>Commercial Insurance Package</span>
                            </div>
                            <div class="summary-item">
                                <span>Third Party Liability:</span>
                                <span>${data.breakdown.thirdPartyProduct.name}</span>
                            </div>
                            <div class="summary-item">
                                <span>Third Party Liability Premium:</span>
                                <span>MOP ${Number(data.breakdown.thirdPartyProduct.premium).toFixed(2)}</span>
                            </div>
                            <div class="summary-item">
                                <span>Passenger Liability:</span>
                                <span>${data.breakdown.passengerProduct.name} per person (${seats} seats)</span>
                            </div>
                            <div class="summary-item">
                                <span>Passenger Liability Premium:</span>
                                <span>MOP ${Number(data.breakdown.passengerProduct.totalPremium).toFixed(2)}</span>
                            </div>
                            <div class="summary-item">
                                <span>Total Premium:</span>
                                <span>MOP ${Number(data.breakdown.totalPremium).toFixed(2)}</span>
                            </div>
                        `;
                            summaryContent.innerHTML = html;
                            document.getElementById('totalPremium').textContent = `MOP ${Number(data.breakdown.totalPremium).toFixed(2)}`;
                        } else {
                            console.error('Error calculating commercial premium:', data.message);
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to calculate commercial insurance premium',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to calculate commercial insurance premium',
                            icon: 'error'
                        });
                    });
            } else if (selectedProduct === 'hk_private') {
                const selectedCar = carsData.find(car => car.Registration_No === vehicleSelect.value);
                if (!selectedCar) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please select a vehicle first',
                        icon: 'error'
                    });
                    return;
                }

                // Check if owner information exists
                if (!selectedCar.Owner1_Birth_Date || !selectedCar.Owner1_B_License_Date) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Unable to get owner information. Please ensure the vehicle is associated with valid owner data.',
                        icon: 'error'
                    });
                    return;
                }

                // Extract years from dates
                const birthYear = new Date(selectedCar.Owner1_Birth_Date).getFullYear();
                const licenseYear = new Date(selectedCar.Owner1_B_License_Date).getFullYear();

                // Call Hong Kong car insurance calculation API
                fetch('calculate_hk_private.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            registration_year: selectedCar.Date_of_Registration,
                            birth_year: birthYear,
                            license_year: licenseYear
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            html += `
                        <div class="summary-item">
                            <span>Insurance Type:</span>
                            <span>Hong Kong Private Car Insurance (MTY001)</span>
                        </div>
                        <div class="summary-item">
                            <span>Premium Calculation:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Base Premium:</span>
                            <span>MOP ${data.breakdown.basePremium.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Additional Loadings:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Age Loading:</span>
                            <span>MOP ${data.breakdown.agePremiumLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Experience Loading:</span>
                            <span>MOP ${data.breakdown.experiencePremiumLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Vehicle Age Loading:</span>
                            <span>MOP ${data.breakdown.carAgePremiumLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>MIB Loading:</span>
                            <span>MOP ${data.breakdown.mibLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="font-weight: bold;">
                            <span>Final Premium:</span>
                            <span>MOP ${data.breakdown.finalPremium.toFixed(2)}</span>
                        </div>
                        <div class="summary-item">
                            <span>Excess Calculation:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Base Excess:</span>
                            <span>MOP ${data.breakdown.baseExcess.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 20px;">
                            <span>Additional Excess Loadings:</span>
                            <span></span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Age Excess Loading:</span>
                            <span>MOP ${data.breakdown.ageExcessLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="padding-left: 40px;">
                            <span>Experience Excess Loading:</span>
                            <span>MOP ${data.breakdown.experienceExcessLoading.toFixed(2)}</span>
                        </div>
                        <div class="summary-item" style="font-weight: bold;">
                            <span>Final Excess:</span>
                            <span>MOP ${data.breakdown.finalExcess.toFixed(2)}</span>
                        </div>
                        `;
                            summaryContent.innerHTML = html;
                            document.getElementById('totalPremium').textContent = `MOP ${data.breakdown.finalPremium.toFixed(2)}`;
                        } else {
                            throw new Error(data.message || 'Failed to calculate premium');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'An error occurred while calculating premium',
                            icon: 'error'
                        });
                    });
            }
        }

        function submitForm() {
            console.log('开始提交表单...');
            const formData = {
                product_type: selectedProduct,
                vehicle_id: document.getElementById('vehicleSelect').value,
                coverage_options: selectedProduct === 'compulsory' ? {
                    seat_count: document.getElementById('seatCount').value,
                    payment_period: document.getElementById('paymentPeriod').value
                } : selectedProduct === 'commercial' ? {
                    third_party_limit: document.getElementById('thirdPartyLimit').value,
                    passenger_limit: document.getElementById('passengerLimit').value
                } : {
                    product_id: 'MTY001' // 香港车险固定使用MTY001
                },
                premium: parseFloat(document.getElementById('totalPremium').textContent.replace('MOP ', ''))
            };

            console.log('准备提交的数据:', formData);

            // 提交报价信息
            fetch('process_quotation.php?action=collect_quotation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    console.log('收到服务器响应:', response);
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('服务器错误响应:', text);
                            throw new Error('服务器响应错误');
                        });
                    }
                    return response.text().then(text => {
                        console.log('原始响应文本:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('解析响应失败:', text);
                            throw new Error('服务器响应格式错误');
                        }
                    });
                })
                .then(data => {
                    console.log('解析后的响应数据:', data);
                    if (data.success) {
                        Swal.fire({
                            title: 'Quote Submitted',
                            text: 'Your insurance quote has been submitted for review.\nPlease wait for administrator approval.',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = '../index.php';
                        });
                    } else {
                        throw new Error(data.message || '提交报价失败');
                    }
                })
                .catch(error => {
                    console.error('提交过程中发生错误:', error);
                    if (error.message === 'User not logged in') {
                        Swal.fire({
                            title: '請先登錄',
                            text: '您需要先登錄才能繼續操作',
                            icon: 'warning',
                            confirmButtonText: '確定'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '../login.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'An unexpected error occurred',
                            icon: 'error'
                        });
                    }
                });
        }

        function calculateHKPremium() {
            const basePremium = 2000;
            const baseExcess = 6000;
            const currentYear = new Date().getFullYear();

            // 获取输入值
            const birthYear = parseInt(document.getElementById('driverBirthYear')?.value) || 0;
            const licenseYear = parseInt(document.getElementById('driverLicenseYear')?.value) || 0;
            const registrationYear = intval(substr(document.getElementById('carRegistrationYear')?.value, 0, 4)) || 0;

            // 计算各种因素
            const ageZ = Math.max(0, birthYear - 2000);
            const experienceY = Math.max(0, licenseYear - 2023);
            const carAgeX = Math.max(0, 2009 - registrationYear); // 使用2009年作为基准年份

            // 计算保费加载
            const agePremiumLoading = 0.15 * ageZ * basePremium;
            const experiencePremiumLoading = 0.25 * experienceY * basePremium;
            const carAgePremiumLoading = 0.1 * carAgeX * basePremium; // 每老一年加收基础保费的10%

            // 计算自付额加载
            const ageExcessLoading = 2000 * ageZ;
            const experienceExcessLoading = 5000 * experienceY;

            // 计算MIB加载
            const subtotalPremium = basePremium + agePremiumLoading + experiencePremiumLoading + carAgePremiumLoading;
            const mibLoading = subtotalPremium * 0.0315;

            // 计算最终保费和自付额
            const finalPremium = subtotalPremium + mibLoading;
            const finalExcess = baseExcess + ageExcessLoading + experienceExcessLoading;

            return {
                finalPremium: finalPremium,
                finalExcess: finalExcess,
                breakdowns: {
                    basePremium: basePremium,
                    baseExcess: baseExcess,
                    agePremiumLoading,
                    experiencePremiumLoading,
                    carAgePremiumLoading,
                    mibLoading,
                    ageExcessLoading,
                    experienceExcessLoading
                }
            };
        }

        // 添加提示框相關的 JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // 顯示提示框
            var noticeModal = new bootstrap.Modal(document.getElementById('noticeModal'));
            noticeModal.show();

            // 倒計時功能
            let timeLeft = 5;
            const timerSpan = document.querySelector('.timer');
            const agreeCheckbox = document.getElementById('agreeCheckbox');
            const continueBtn = document.getElementById('continueBtn');

            const timer = setInterval(() => {
                timeLeft--;
                timerSpan.textContent = `(${timeLeft})`;

                if (timeLeft <= 0) {
                    clearInterval(timer);
                    agreeCheckbox.disabled = false;
                    timerSpan.style.display = 'none';
                }
            }, 1000);

            // 監聽複選框變化
            agreeCheckbox.addEventListener('change', function() {
                continueBtn.disabled = !this.checked;
            });

            // 監聽繼續按鈕點擊
            continueBtn.addEventListener('click', function() {
                if (agreeCheckbox.checked) {
                    // 在隱藏 modal 之前移除焦點
                    this.blur();
                    noticeModal.hide();
                }
            });

            // 添加 modal 隱藏後的事件處理
            document.getElementById('noticeModal').addEventListener('hidden.bs.modal', function() {
                // 確保 modal 完全隱藏後移除 aria-hidden 屬性
                this.removeAttribute('aria-hidden');
            });
        });

        // 初始化显示第一步
        showStep(1);
    </script>
</body>

</html>