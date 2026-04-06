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
    <title>Manage Party Information</title>
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

        .success-animation {
            text-align: center;
            padding: 40px;
        }

        .success-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #e8f5e9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .success-circle i {
            color: #01459C;
            font-size: 40px;
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

        .date-picker {
            border-radius: 8px;
            padding: 12px;
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

        .party-no-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 300px;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards 0.8s;
        }

        .party-no-box h4 {
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

        /* 添加 tooltip 自定義樣式 */
        .tooltip {
            font-size: 14px;
        }

        .tooltip-inner {
            max-width: 300px;
            padding: 10px 15px;
            text-align: left;
            white-space: pre-line;
            line-height: 1.5;
        }

        .tooltip.bs-tooltip-end .tooltip-arrow {
            left: -5px;
        }

        .tooltip.bs-tooltip-start .tooltip-arrow {
            right: -5px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <a href="../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Personal Center
    </a>

    <div class="container mt-5">
        <h1 class="text-center mb-5" style="color: #01459C; font-weight: 700;">Manage Party Information</h1>

        <div class="progress-container">
            <div class="progress-bar-custom">
                <div class="progress" id="progress-bar"></div>
            </div>
            <div class="step-circles">
                <div class="step-circle active" id="step1-circle">1
                    <div class="step-label">Party Info</div>
                </div>
                <div class="step-circle" id="step2-circle">2
                    <div class="step-label">Documents</div>
                </div>
                <div class="step-circle" id="step3-circle">3
                    <div class="step-label">Confirmation</div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <form action="process_party.php" method="POST" enctype="multipart/form-data" class="card p-4" id="partyForm">
                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" id="step1">
                        <h3 class="mb-4">Basic Information</h3>
                        <div class="mb-4">
                            <label class="form-label">Party Type</label>
                            <select name="type" class="form-select" id="userType" required>
                                <option value="Individual">Individual</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" id="idLabel">ID Card No / Company Registration No <i class="fas fa-info-circle text-primary" data-bs-toggle="tooltip" data-bs-placement="right" title="Accepted document types:&#10;• Macao Identity Card&#10;• Hong Kong Identity Card&#10;• Hong Kong and Macao Travel Permit&#10;• Chinese Passport&#10;• Business Registration (Macao SAR)"></i></label>
                            <input type="text" name="id_no" class="form-control" required placeholder="e.g.: 12345678 / 123SO">
                        </div>

                        <div class="individual-only">
                            <div class="mb-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control date-picker" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Driver's License No</label>
                                <input type="text" name="dl_no" class="form-control" required>
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
                            <label class="form-label" id="idImageLabel">ID Card (Front) / Company Registration Document</label>
                            <input type="file" name="id_image_front" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Only PDF and JPG files are allowed</small>
                        </div>

                        <div class="individual-only">
                            <div class="mb-4">
                                <label class="form-label">Driver's License (Front)</label>
                                <input type="file" name="dl_image_front" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Only PDF and JPG files are allowed</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Driver's License (Back)</label>
                                <input type="file" name="dl_image_back" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Only PDF and JPG files are allowed</small>
                            </div>

                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title text-primary mb-3">
                                        <i class="fas fa-info-circle me-2"></i>License Class Dates
                                    </h6>
                                    <p class="card-text text-muted mb-3">
                                        Please provide at least one of the following license class dates:
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label">Date of B Class License</label>
                                        <input type="date" name="date_of_b_class" class="form-control date-picker" min="2008-06-21" max="2025-03-31">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date of C Class License</label>
                                        <input type="date" name="date_of_c_class" class="form-control date-picker" min="2008-06-21" max="2025-03-31">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for total claim and premium -->
                        <input type="hidden" name="total_claim_amount" value="0">
                        <input type="hidden" name="total_contributed_premium" value="0">

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="prevStep(2)">Back</button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Continue</button>
                        </div>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div class="form-step" id="step3">
                        <h3 class="mb-4">Confirm Information</h3>
                        <div class="alert alert-info">
                            Please review your information. A Party No will be generated upon submission.
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
                            <p class="success-message">Party information has been successfully saved with auto-generated Party No.</p>
                            <div class="party-no-box">
                                <h4>Party No: <span id="generatedPartyNo"></span></h4>
                            </div>
                            <button type="button" class="btn btn-primary manage-another-btn" onclick="window.location.reload()">
                                Manage Another Party
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentStep = 1;

        function updateProgressBar() {
            const progress = ((currentStep - 1) / 2) * 100;
            document.getElementById('progress-bar').style.width = `${progress}%`;
        }

        function nextStep(step) {
            if (validateStep(step)) {
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
            document.getElementById(`step${step}`).classList.remove('active');
            document.getElementById(`step${step}-circle`).classList.remove('active');
            document.getElementById(`step${step-1}-circle`).classList.remove('completed');
            document.getElementById(`step${step-1}`).classList.add('active');
            currentStep = step - 1;
            updateProgressBar();
        }

        function validateStep(step) {
            if (step === 2) {
                const form = document.getElementById('partyForm');
                if (form.type.value === 'Individual') {
                    const bClassDate = form.querySelector('input[name="date_of_b_class"]').value;
                    const cClassDate = form.querySelector('input[name="date_of_c_class"]').value;
                    if (!bClassDate && !cClassDate) {
                        alert('Please provide at least one license class date (B or C)');
                        return false;
                    }
                }

                // 验证文件类型
                const fileInputs = form.querySelectorAll('input[type="file"]');
                for (let input of fileInputs) {
                    if (input.files.length > 0) {
                        const file = input.files[0];
                        const fileType = file.type.toLowerCase();
                        const fileName = file.name.toLowerCase();

                        // 检查文件类型
                        if (!fileType.includes('pdf') && !fileType.includes('jpeg') && !fileType.includes('jpg') && !fileType.includes('png')) {
                            alert(`File "${file.name}" is not a valid PDF or JPG or PNG file.`);
                            input.classList.add('is-invalid');
                            return false;
                        }
                    }
                }
            }
            const currentStepDiv = document.getElementById(`step${step}`);
            const requiredFields = currentStepDiv.querySelectorAll('[required]');
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            return valid;
        }

        function updateSummary() {
            const form = document.getElementById('partyForm');
            const summary = document.getElementById('summaryInfo');
            const type = form.type.value;

            let html = `
                <div class="card p-4 bg-light">
                    <p><strong>Party Type:</strong> ${type}</p>
                    <p><strong>Name:</strong> ${form.name.value}</p>
                    <p><strong>${type === 'Individual' ? 'ID Card No' : 'Company Registration No'}:</strong> ${form.id_no.value}</p>
            `;

            if (type === 'Individual') {
                html += `
                    <p><strong>Date of Birth:</strong> ${form.date_of_birth.value}</p>
                    <p><strong>Driver's License No:</strong> ${form.dl_no.value}</p>
                    <p><strong>License Class Dates:</strong></p>
                    <ul>
                        ${form.date_of_b_class.value ? `<li>Class B: ${form.date_of_b_class.value}</li>` : ''}
                        ${form.date_of_c_class.value ? `<li>Class C: ${form.date_of_c_class.value}</li>` : ''}
                    </ul>
                `;
            }

            html += '</div>';
            summary.innerHTML = html;
        }

        document.getElementById('userType').addEventListener('change', function() {
            const individualFields = document.querySelectorAll('.individual-only');
            const idLabel = document.getElementById('idLabel');
            const idImageLabel = document.getElementById('idImageLabel');

            if (this.value === 'Company') {
                individualFields.forEach(field => field.style.display = 'none');
                idLabel.textContent = 'Company Registration No';
                idImageLabel.textContent = 'Company Registration Document';
            } else {
                individualFields.forEach(field => field.style.display = 'block');
                idLabel.textContent = 'ID Card No';
                idImageLabel.textContent = 'ID Card (Front)';
            }
        });

        // Handle form submission
        document.getElementById('partyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // 显示加载状态
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
            submitBtn.disabled = true;

            fetch('process_party.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // 更新Party No显示
                        document.getElementById('generatedPartyNo').textContent = data.partyNo;

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
                    // 创建一个更友好的错误提示
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                    errorDiv.innerHTML = `
                        <strong>Error!</strong> ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.getElementById('step3').insertBefore(errorDiv, document.getElementById('step3').firstChild);

                    // 5秒后自动关闭错误提示
                    setTimeout(() => {
                        errorDiv.classList.remove('show');
                        setTimeout(() => errorDiv.remove(), 150);
                    }, 5000);
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

        // 添加證件號碼驗證
        document.querySelector('input[name="id_no"]').addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase();
            // 移除所有空格
            value = value.replace(/\s/g, '');
            e.target.value = value;

            // 移除所有錯誤提示
            removeAllErrorMessages(this);
        });

        document.querySelector('input[name="id_no"]').addEventListener('blur', function() {
            const value = this.value.trim();
            if (value) {
                // 移除所有已存在的錯誤提示
                removeAllErrorMessages(this);

                // 驗證格式
                if (!validateIDFormat(value)) {
                    showErrorMessage(this, 'Please enter a valid ID number');
                }
            }
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

        // 為出生日期輸入框添加驗證
        const dateOfBirthInput = document.querySelector('input[name="date_of_birth"]');
        if (dateOfBirthInput) {
            // 設置最大日期為18年前
            const today = new Date();
            const maxDate = new Date();
            maxDate.setFullYear(today.getFullYear() - 18);
            dateOfBirthInput.max = maxDate.toISOString().split('T')[0];

            // 添加失焦事件監聽器，驗證年齡
            dateOfBirthInput.addEventListener('blur', function() {
                const selectedDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - selectedDate.getFullYear();
                const monthDiff = today.getMonth() - selectedDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < selectedDate.getDate())) {
                    age--;
                }

                if (age < 18) {
                    showErrorMessage(this, 'Must be at least 18 years old');
                } else {
                    removeAllErrorMessages(this);
                }
            });
        }

        // 為B類和C類駕照日期添加驗證
        const dateOfBClassInput = document.querySelector('input[name="date_of_b_class"]');
        const dateOfCClassInput = document.querySelector('input[name="date_of_c_class"]');

        function updateLicenseDateLimits() {
            const birthDate = new Date(dateOfBirthInput.value);
            const today = new Date();

            // 計算18歲生日日期
            const eighteenYearsAgo = new Date(birthDate);
            eighteenYearsAgo.setFullYear(birthDate.getFullYear() + 18);

            // 設置最小日期為18歲生日
            const minDate = eighteenYearsAgo.toISOString().split('T')[0];
            // 設置最大日期為今天
            const maxDate = today.toISOString().split('T')[0];

            // 更新B類和C類駕照日期的限制
            if (dateOfBClassInput) {
                dateOfBClassInput.min = minDate;
                dateOfBClassInput.max = maxDate;
            }
            if (dateOfCClassInput) {
                dateOfCClassInput.min = minDate;
                dateOfCClassInput.max = maxDate;
            }
        }

        // 當出生日期改變時更新駕照日期的限制
        if (dateOfBirthInput) {
            dateOfBirthInput.addEventListener('change', updateLicenseDateLimits);
        }

        // 為B類和C類駕照日期添加失焦驗證
        function validateLicenseDate(input) {
            if (!input.value) return;

            const birthDate = new Date(dateOfBirthInput.value);
            const selectedDate = new Date(input.value);
            const today = new Date();

            // 計算18歲生日日期
            const eighteenYearsAgo = new Date(birthDate);
            eighteenYearsAgo.setFullYear(birthDate.getFullYear() + 18);

            // 驗證日期是否在有效範圍內
            if (selectedDate < eighteenYearsAgo || selectedDate > today) {
                showErrorMessage(input, 'Date must be between 18th birthday and today');
            } else {
                removeAllErrorMessages(input);
            }
        }

        if (dateOfBClassInput) {
            dateOfBClassInput.addEventListener('blur', function() {
                validateLicenseDate(this);
            });
        }

        if (dateOfCClassInput) {
            dateOfCClassInput.addEventListener('blur', function() {
                validateLicenseDate(this);
            });
        }

        // 為用戶類型選擇框添加驗證
        const userTypeSelect = document.querySelector('select[name="type"]');
        if (userTypeSelect) {
            userTypeSelect.addEventListener('change', function() {
                // 移除所有錯誤提示
                removeAllErrorMessages(this);
            });
        }

        // 為name輸入框添加驗證
        const nameInput = document.querySelector('input[name="name"]');
        if (nameInput) {
            // 添加失焦事件監聽器，實現自動刪除最後的空格
            nameInput.addEventListener('blur', function(e) {
                let value = e.target.value;
                // 只移除最後的空格
                value = value.replace(/\s+$/, '');
                e.target.value = value;

                // 移除所有錯誤提示
                removeAllErrorMessages(this);
            });
        }

        // 初始化所有 tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>

</html>