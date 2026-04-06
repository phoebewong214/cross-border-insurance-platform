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
    <title>Manage Car Information</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
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

        .container {
            max-width: 800px;
        }

        .progress-container {
            position: relative;
            margin: 40px auto;
            width: 100%;
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
            top: 45px;
            width: 100px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            transform: translateX(-30px);
        }

        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            border-radius: 15px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e0e0e0;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #01459C;
            box-shadow: 0 0 0 0.2rem rgba(1, 69, 156, 0.25);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #01459C;
            border-color: #01459C;
        }

        .btn-primary:hover {
            background-color: #013579;
            border-color: #013579;
        }

        .form-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-page {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background-color: #e8f5e9;
            position: relative;
            animation: checkmarkBounce 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-checkmark .checkmark {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            stroke-width: 2;
            stroke: #4CAF50;
            stroke-miterlimit: 10;
            animation: checkmarkDraw 0.8s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        @keyframes checkmarkBounce {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes checkmarkDraw {
            0% {
                stroke-dashoffset: 100;
            }

            100% {
                stroke-dashoffset: 0;
            }
        }

        .success-title {
            color: #333;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards 0.4s;
        }

        .success-message {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards 0.6s;
        }

        .car-info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 300px;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards 0.8s;
        }

        .car-info-box h4 {
            margin: 0;
            color: #01459C;
        }

        .manage-another-btn {
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards 1s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: #f0f;
            position: absolute;
            top: -10px;
            animation: confetti 3s ease-in-out infinite;
        }

        @keyframes confetti {
            0% {
                transform: translateY(0) rotate(0deg);
            }

            100% {
                transform: translateY(100vh) rotate(360deg);
            }
        }

        .date-group {
            display: flex;
            gap: 10px;
        }

        .date-group input {
            text-align: center;
        }

        .date-group input:first-child {
            width: 80px;
        }

        .date-group input:nth-child(2) {
            width: 80px;
        }

        .date-group input:last-child {
            width: 100px;
        }

        .example-image-container {
            max-height: 70vh;
            overflow: hidden;
        }

        .example-image-container img {
            max-height: 100%;
            width: auto;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1050;
            visibility: hidden;
            opacity: 0;
            transition: visibility 0s linear 0.2s, opacity 0.2s linear;
        }

        .modal.show {
            visibility: visible;
            opacity: 1;
            transition-delay: 0s;
        }

        .modal-dialog {
            position: relative;
            width: auto;
            margin: 1.75rem auto;
            max-width: 800px;
            transform: none;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            border-radius: 0.3rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-body {
            padding: 0;
        }

        .carousel-item img {
            max-height: 70vh;
            object-fit: contain;
            padding: 1rem;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 50%;
            height: 40px;
            width: 40px;
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel-control-prev {
            left: 10px;
        }

        .carousel-control-next {
            right: 10px;
        }

        .btn-close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            z-index: 1;
        }

        .popover {
            max-width: 300px;
        }

        .popover-body {
            padding: 0;
        }

        .popover-body img {
            max-width: 300px;
            max-height: 300px;
            object-fit: contain;
        }

        .popover-body p {
            padding: 10px;
            margin: 0;
        }

        .carousel-indicators {
            position: relative;
            margin: 1rem 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            z-index: 15;
        }

        .carousel-indicators button {
            width: 4px;
            height: 4px;
            border-radius: 50%;
            margin: 0;
            background-color: #6c757d !important;
            border: none;
            padding: 0;
            cursor: pointer;
            opacity: 0.3 !important;
            position: relative;
            z-index: 15;
            transition: opacity 0.3s ease;
        }

        .carousel-indicators button:hover {
            opacity: 0.6 !important;
            background-color: #01459C !important;
        }

        .carousel-indicators button.active {
            background-color: #01459C !important;
            opacity: 1 !important;
        }

        #partyModal {
            z-index: 1055;
        }

        #partyModal .modal-dialog {
            max-width: 95%;
        }
    </style>
</head>

<body>
    <!-- Ownership Modal -->
    <div class="modal" id="ownershipModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ownership Registration Certificate Example</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="ownershipCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="../images/ownership_certificate_example1.jpg" class="d-block w-100" alt="Example 1">
                            </div>
                            <div class="carousel-item">
                                <img src="../images/ownership_certificate_example2.jpg" class="d-block w-100" alt="Example 2">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#ownershipCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#ownershipCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#ownershipCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#ownershipCarousel" data-bs-slide-to="1"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Modal -->
    <div class="modal" id="vehicleModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vehicle Registration Card Example</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="vehicleCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="../images/vehicle_registration_example1.jpg" class="d-block w-100" alt="Example 1">
                            </div>
                            <div class="carousel-item">
                                <img src="../images/vehicle_registration_example2.jpg" class="d-block w-100" alt="Example 2">
                            </div>
                            <div class="carousel-item">
                                <img src="../images/vehicle_registration_example3.jpg" class="d-block w-100" alt="Example 3">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#vehicleCarousel" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#vehicleCarousel" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#vehicleCarousel" data-bs-slide-to="2"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Personal Center
    </a>

    <div class="container mt-5">
        <h1 class="text-center mb-5" style="color: #01459C; font-weight: 700;">Manage Car Information</h1>

        <div class="progress-container">
            <div class="progress-bar-custom">
                <div class="progress" id="progress-bar"></div>
            </div>
            <div class="step-circles">
                <div class="step-circle active" id="step1-circle">1
                    <div class="step-label">Car Details</div>
                </div>
                <div class="step-circle" id="step2-circle">2
                    <div class="step-label">Documents</div>
                </div>
                <div class="step-circle" id="step3-circle">3
                    <div class="step-label">Ownership</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <form action="process_car.php" method="POST" enctype="multipart/form-data" class="card p-4" id="carForm">
                    <!-- Step 1: Car Details -->
                    <div class="form-step active" id="step1">
                        <h3 class="mb-4">Car Details</h3>

                        <div class="mb-4">
                            <label class="form-label">Registration Number(Car Plate Number)</label>
                            <input type="text" name="registrationNo" class="form-control" required placeholder="e.g., MA-12-34">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Car Make and Model</label>
                            <input type="text" name="carMakeAndModel" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Number of Seats</label>
                            <input type="number" name="seats" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Date of Registration</label>
                            <input type="date" name="date_of_registration" class="form-control date-picker" required max="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Chasis Number</label>
                            <div class="input-group">
                                <input type="text" name="chasisNo" class="form-control" required>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="popover" data-bs-placement="right" data-bs-html="true" data-bs-content='<div class="text-center"><img src="../images/chasis_number_example.gif" class="img-fluid" style="max-height: 300px; image-rendering: auto;"><p class="mt-2 text-muted">Please find this position on the vehicle registration certificate and enter the complete chassis number</p></div>'>
                                    <i class="fas fa-question-circle"></i> View Example
                                </button>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-primary" onclick="nextStep(1)">Continue</button>
                        </div>
                    </div>

                    <!-- Step 2: Document Upload -->
                    <div class="form-step" id="step2">
                        <h3 class="mb-4">Upload Documents</h3>

                        <div class="mb-4">
                            <label class="form-label">Ownership Registration Certificate</label>
                            <div class="input-group">
                                <input type="file" name="ownershipRegistrationCertificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#ownershipModal">
                                    <i class="fas fa-question-circle"></i> View Example
                                </button>
                            </div>
                            <small class="text-muted">Only PDF or JPG files allowed (Maximum size: 16MB)</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Vehicle Registration Card</label>
                            <div class="input-group">
                                <input type="file" name="vehicleRegistrationCard" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#vehicleModal">
                                    <i class="fas fa-question-circle"></i> View Example
                                </button>
                            </div>
                            <small class="text-muted">Only PDF or JPG files allowed (Maximum size: 16MB)</small>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="prevStep(2)">Back</button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Continue</button>
                        </div>
                    </div>

                    <!-- Step 3: Ownership Information -->
                    <div class="form-step" id="step3">
                        <h3 class="mb-4">Ownership Information</h3>
                        <div class="alert alert-info">
                            Please enter the ID numbers of the vehicle owners. The ID numbers must match with registered parties in the system.
                        </div>

                        <div class="mb-4 border border-success rounded p-3">
                            <div class="mb-2 text-end small text-success">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Owner 1 must fill in the information of the quota holder, <br>Otherwise it will affect the validity of the policy.
                            </div>
                            <label class="form-label">Owner 1 ID Number</label>
                            <input type="text" name="owner1IDNo" class="form-control" required>
                            <small class="text-muted">Parentheses in the document number are not required. e.g., 1234567(8) is the same as 12345678</small>
                        </div>

                        <div class="mb-3">
                            <label for="owner2IDNo" class="form-label">Owner 2 ID Number (Optional)</label>
                            <input type="text" class="form-control" id="owner2IDNo" name="owner2IDNo">
                            <small class="text-muted">Parentheses in the document number are not required. e.g., 1234567(8) is the same as 12345678</small>
                        </div>

                        <div class="mb-3">
                            <label for="owner3IDNo" class="form-label">Owner 3 ID Number (Optional)</label>
                            <input type="text" class="form-control" id="owner3IDNo" name="owner3IDNo">
                            <small class="text-muted">Parentheses in the document number are not required. e.g., 1234567(8) is the same as 12345678</small>
                        </div>

                        <div id="summaryInfo" class="mb-4">
                            <!-- JavaScript will populate this -->
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="prevStep(3)">Back</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>

                    <!-- Success Step -->
                    <div class="form-step" id="step4">
                        <div class="success-page">
                            <div class="success-checkmark">
                                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                                </svg>
                            </div>
                            <h2 class="success-title">Success!</h2>
                            <p class="success-message">Car information has been successfully registered.</p>
                            <div class="car-info-box">
                                <h4>Registration No: <span id="registrationNoDisplay"></span></h4>
                                <p class="mb-0">Make and Model: <span id="makeModelDisplay"></span></p>
                            </div>
                            <button type="button" class="btn btn-primary manage-another-btn" onclick="window.location.reload()">
                                Register Another Car
                            </button>
                            <button type="button" class="btn btn-secondary manage-another-btn" onclick="window.location.href='../index.php'">
                                Return to Home
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 添加Party管理彈窗 -->
    <div class="modal" id="partyModal" tabindex="-1" role="dialog" aria-labelledby="partyModalLabel">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="partyModalLabel">Register New Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe id="partyFrame" src="manage_party.php" style="width: 100%; height: 80vh; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;
        let partyModal;

        function updateProgressBar() {
            const progress = ((currentStep - 1) / 2) * 100;
            document.getElementById('progress-bar').style.width = `${progress}%`;
        }

        function nextStep(step) {
            if (validateStep(step)) {
                // 關閉所有 Popover
                const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
                popovers.forEach(popover => {
                    const bsPopover = bootstrap.Popover.getInstance(popover);
                    if (bsPopover) {
                        bsPopover.hide();
                    }
                });

                document.getElementById(`step${step}`).classList.remove('active');
                document.getElementById(`step${step+1}`).classList.add('active');
                document.getElementById(`step${step}-circle`).classList.add('completed');
                document.getElementById(`step${step+1}-circle`).classList.add('active');
                currentStep = step + 1;
                updateProgressBar();
                if (step === 2) {
                    updateSummary();
                }
            }
        }

        function prevStep(step) {
            // 關閉所有 Popover
            const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
            popovers.forEach(popover => {
                const bsPopover = bootstrap.Popover.getInstance(popover);
                if (bsPopover) {
                    bsPopover.hide();
                }
            });

            document.getElementById(`step${step}`).classList.remove('active');
            document.getElementById(`step${step}-circle`).classList.remove('active');
            document.getElementById(`step${step-1}-circle`).classList.remove('completed');
            document.getElementById(`step${step-1}`).classList.add('active');
            currentStep = step - 1;
            updateProgressBar();
        }

        function validateStep(step) {
            const currentStepDiv = document.getElementById(`step${step}`);
            let valid = true;

            if (step === 1) {
                // 第一步：驗證必填字段
                const requiredFields = currentStepDiv.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value) {
                        field.classList.add('is-invalid');
                        valid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                // 驗證日期
                const date = document.querySelector('input[name="date_of_registration"]').value;
                if (!isValidDate(date)) {
                    alert('Please enter a valid date');
                    return false;
                }
            } else if (step === 2) {
                // 第二步：驗證文件上傳
                const fileInputs = currentStepDiv.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => {
                    if (input.required && !input.files[0]) {
                        input.classList.add('is-invalid');
                        valid = false;
                    } else if (input.files[0]) {
                        const file = input.files[0];
                        const fileName = file.name.toLowerCase();
                        if (!fileName.endsWith('.pdf') && !fileName.endsWith('.jpg') && !fileName.endsWith('.jpeg') && !fileName.endsWith('.png')) {
                            alert('Please upload only PDF, JPG or PNG files');
                            input.value = '';
                            valid = false;
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    }
                });
            } else if (step === 3) {
                // 第三步：驗證Owner ID
                const ownerInputs = [
                    document.querySelector('input[name="owner1IDNo"]'),
                    document.querySelector('input[name="owner2IDNo"]'),
                    document.querySelector('input[name="owner3IDNo"]')
                ];

                ownerInputs.forEach(input => {
                    if (input && input.value.trim()) {
                        // 如果有填寫，則進行驗證
                        validateOwnerID(input);
                        if (input.classList.contains('is-invalid')) {
                            valid = false;
                        }
                    }
                });
            }

            return valid;
        }

        function isValidDate(date) {
            const parsedDate = new Date(date);
            return parsedDate instanceof Date && !isNaN(parsedDate);
        }

        function updateSummary() {
            const form = document.getElementById('carForm');
            const summary = document.getElementById('summaryInfo');

            let html = `
                <div class="card p-4 bg-light">
                    <p><strong>Registration Number:</strong> ${form.registrationNo.value}</p>
                    <p><strong>Make and Model:</strong> ${form.carMakeAndModel.value}</p>
                    <p><strong>Number of Seats:</strong> ${form.seats.value}</p>
                    <p><strong>Date of Registration:</strong> ${form.date_of_registration.value}</p>
                    <p><strong>Chassis Number:</strong> ${form.chasisNo.value}</p>
                </div>
            `;
            summary.innerHTML = html;
        }

        // Handle form submission
        document.getElementById('carForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // 显示加载状态
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
            submitBtn.disabled = true;

            // 添加调试信息
            console.log('Form Data:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            fetch('process_car.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 更新显示信息
                        document.getElementById('registrationNoDisplay').textContent = formData.get('registrationNo');
                        document.getElementById('makeModelDisplay').textContent = formData.get('carMakeAndModel');

                        // 隐藏所有步骤，显示成功页面
                        document.querySelectorAll('.form-step').forEach(step => step.classList.remove('active'));
                        document.getElementById('step4').classList.add('active');

                        // 触发彩带效果
                        createConfetti();
                    } else {
                        throw new Error(data.message || 'An error occurred while saving the data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // 显示错误信息
                    alert(error.message);
                })
                .finally(() => {
                    // 恢复按钮状态
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // 添加彩带效果函数
        function createConfetti() {
            const colors = ['#01459C', '#4CAF50', '#FFC107', '#FF5722', '#9C27B0'];
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animation = `confetti ${Math.random() * 2 + 1}s ease-in-out infinite`;
                confetti.style.opacity = Math.random();
                confetti.style.transform = `scale(${Math.random()})`;
                document.body.appendChild(confetti);

                // 3秒后移除彩带
                setTimeout(() => {
                    confetti.remove();
                }, 3000);
            }
        }

        // 限制日期输入
        document.querySelectorAll('.date-group input').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.name === 'reg_day') {
                    if (parseInt(this.value) > 31) this.value = '31';
                }
                if (this.name === 'reg_month') {
                    if (parseInt(this.value) > 12) this.value = '12';
                }
            });
        });

        // 車牌號碼驗證
        document.querySelector('input[name="registrationNo"]').addEventListener('blur', function(e) {
            let value = e.target.value.toUpperCase();
            const input = e.target;
            const feedback = input.nextElementSibling;

            // 驗證格式：單字母(M)或雙字母 + 連字符 + 兩位數字 + 連字符 + 兩位數字
            const pattern = /^([A-Z]{2}|M)-(\d{2})-(\d{2})$/;

            if (!pattern.test(value)) {
                // 如果格式不正確，顯示錯誤提示
                input.classList.add('is-invalid');
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Please enter a valid registration number (e.g., AA-12-34 or M-12-34)';
                    input.parentNode.appendChild(errorDiv);
                }
            } else {
                input.classList.remove('is-invalid');
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }

            e.target.value = value;
        });

        // 車架號碼自動大寫和移除尾部空格
        document.querySelector('input[name="chasisNo"]').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            // 移除最後的空格
            value = value.replace(/\s+$/, '');
            e.target.value = value;
        });

        // 日期驗證
        document.querySelector('input[name="date_of_registration"]').addEventListener('input', function(e) {
            const input = e.target;
            const selectedDate = new Date(input.value);
            const today = new Date();

            // 重置時間部分以便比較日期
            selectedDate.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);

            if (selectedDate > today) {
                input.classList.add('is-invalid');
                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = 'Registration date cannot exceed today';
                    input.parentNode.appendChild(errorDiv);
                }
            } else {
                input.classList.remove('is-invalid');
                if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.remove();
                }
            }
        });

        // 為所有Owner ID輸入框添加驗證
        document.querySelectorAll('input[name="owner1IDNo"], input[name="owner2IDNo"], input[name="owner3IDNo"]').forEach(input => {
            // 添加輸入事件監聽器，實現自動大寫和移除所有空格
            input.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                // 移除所有空格
                value = value.replace(/\s/g, '');
                e.target.value = value;

                // 移除所有錯誤提示
                removeAllErrorMessages(this);
            });

            // 添加失焦事件監聽器，驗證格式和存在性
            input.addEventListener('blur', function() {
                const value = this.value.trim();
                if (value) {
                    // 移除所有已存在的錯誤提示
                    removeAllErrorMessages(this);

                    // 驗證格式
                    if (!validateIDFormat(value)) {
                        showErrorMessage(this, 'Please enter a valid ID number');
                        return;
                    }

                    // 如果格式正確，立即驗證ID是否存在
                    validateOwnerID(this);
                }
            });
        });

        // 添加ID格式驗證函數
        function validateIDFormat(value) {
            // 如果以數字開頭，必須是8位
            if (/^\d/.test(value)) {
                return /^\d{8}$/.test(value);
            }
            // 如果以字母開頭，總長度必須是8位或9位
            if (/^[A-Z]/.test(value)) {
                return value.length === 8 || value.length === 9;
            }
            return false;
        }

        // 移除所有錯誤提示的輔助函數
        function removeAllErrorMessages(input) {
            const existingFeedbacks = input.parentNode.querySelectorAll('.invalid-feedback');
            existingFeedbacks.forEach(feedback => feedback.remove());
            input.classList.remove('is-invalid');
        }

        // 顯示錯誤信息的輔助函數
        function showErrorMessage(input, message) {
            input.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            input.parentNode.appendChild(errorDiv);
        }

        // Owner ID驗證函數
        function validateOwnerID(input) {
            const idNo = input.value.trim();
            if (!idNo) return;

            // 移除所有已存在的錯誤提示
            removeAllErrorMessages(input);

            // 立即發送請求驗證ID是否存在
            fetch(`check_party.php?id_no=${encodeURIComponent(idNo)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (!data.exists) {
                            input.classList.add('is-invalid');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.innerHTML = `Owner not registered, <a href="#" class="alert-link" onclick="openPartyModal(event)">Click here to register</a>`;
                            input.parentNode.appendChild(errorDiv);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage(input, 'Validation failed, please try again later');
                });
        }

        // 打開Party管理彈窗
        function openPartyModal(event) {
            event.preventDefault();
            const modal = document.getElementById('partyModal');
            if (modal) {
                // 移除 aria-hidden 屬性
                modal.removeAttribute('aria-hidden');
                // 顯示 Modal
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }
        }

        // 監聽Party管理彈窗關閉事件
        document.getElementById('partyModal').addEventListener('hide.bs.modal', function() {
            // 在 Modal 開始隱藏時，先將焦點移回觸發按鈕
            const triggerButton = document.querySelector('[data-bs-target="#partyModal"]');
            if (triggerButton) {
                triggerButton.focus();
            }
        });

        document.getElementById('partyModal').addEventListener('hidden.bs.modal', function() {
            // 當 Modal 完全隱藏後，再設置 aria-hidden
            setTimeout(() => {
                this.setAttribute('aria-hidden', 'true');
            }, 100);
        });

        // 初始化所有組件
        document.addEventListener('DOMContentLoaded', function() {
            // 初始化所有 tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // 初始化所有 Popover
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });

            // 初始化所有 Modal
            var ownershipModal = new bootstrap.Modal(document.getElementById('ownershipModal'), {
                backdrop: false,
                keyboard: false
            });
            var vehicleModal = new bootstrap.Modal(document.getElementById('vehicleModal'), {
                backdrop: false,
                keyboard: false
            });

            // 初始化 Party Modal
            partyModal = new bootstrap.Modal(document.getElementById('partyModal'), {
                backdrop: true,
                keyboard: true,
                focus: true
            });

            // 初始化輪播圖
            const ownershipCarousel = new bootstrap.Carousel(document.getElementById('ownershipCarousel'), {
                interval: false
            });
            const vehicleCarousel = new bootstrap.Carousel(document.getElementById('vehicleCarousel'), {
                interval: false
            });

            // 點擊背景關閉 Modal
            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        const bsModal = bootstrap.Modal.getInstance(this);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                });
            });

            // 監聽 Modal 顯示事件
            document.getElementById('partyModal').addEventListener('shown.bs.modal', function() {
                // 確保 iframe 可以獲得焦點
                const iframe = this.querySelector('iframe');
                if (iframe) {
                    // 使用 setTimeout 確保 iframe 已經加載完成
                    setTimeout(() => {
                        iframe.focus();
                    }, 100);
                }
            });

            // 監聽 iframe 加載完成事件
            const partyFrame = document.getElementById('partyFrame');
            if (partyFrame) {
                partyFrame.addEventListener('load', function() {
                    // 確保 iframe 內容可以獲得焦點
                    this.contentWindow.focus();
                });
            }
        });
    </script>
</body>

</html>