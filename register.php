<?php
session_start();
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 添加自定义 SMTP 类
class CustomSMTP extends SMTP {
    public function hello($host = '')
    {
        // Try extended hello first (RFC 2821)
        if ($this->sendHello('EHLO', $host))
        {
            return true;
        }

        // Some servers shut down the SMTP service here (RFC 5321)
        if (substr($this->helo_rply, 0, 3) == '421')
        {
            return false;
        }

        return $this->sendHello('HELO', $host);
    }
}

// 添加自定义 PHPMailer 类
class CustomPHPMailer extends PHPMailer {
    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new CustomSMTP;
        }
        return $this->smtp;
    }
}

// 添加发送验证码的处理逻辑
if (isset($_POST['action']) && $_POST['action'] == 'send_code') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $verificationCode = sprintf("%06d", rand(0, 999999)); // 生成6位验证码
    $_SESSION['verification_code'] = $verificationCode;
    $_SESSION['verification_email'] = $email;
    $_SESSION['code_expire_time'] = time() + 300; // 5分钟有效期

    try {
        $mail = new CustomPHPMailer(true); // 使用自定义的 PHPMailer 类
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // 生产环境关闭调试
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dorocrh13@gmail.com';
        $mail->Password = 'cmmvzladstrdcjdn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // 添加 SSL 选项
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('dorocrh13@gmail.com', 'Insurance System');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h2 style='color: #1a237e;'>Email Verification</h2>
                <p>Your verification code is: <strong style='font-size: 24px; color: #1a237e;'>{$verificationCode}</strong></p>
                <p>This code will expire in 5 minutes.</p>
            </div>";

        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $mail->ErrorInfo]);
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_var($_POST["Username"], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST["Email"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["Password"];
    $confirm = $_POST["Confirm"];

    if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm)) {
        if ($password === $confirm) {
            $link = mysqli_connect('localhost', 'root', '', 'insurance_system');
            
            if (!$link) {
                die("Connection failed: " . mysqli_connect_error());
            }

            mysqli_query($link, 'SET NAMES utf8');

            // Check if username already exists
            $check_sql = "SELECT * FROM user WHERE User_name = ?";
            $stmt = mysqli_prepare($link, $check_sql);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $_SESSION['register_error'] = "用户名已存在!";
            } else {
                // Check if email already exists
                $check_email_sql = "SELECT * FROM user WHERE Email = ?";
                $stmt = mysqli_prepare($link, $check_email_sql);
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    $_SESSION['register_error'] = "该邮箱已被注册!";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $sql = "INSERT INTO user (User_name, Email, HASH_Value) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($link, $sql);
                    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        header("Location: pages/successRegister.php?type=register");
                        exit();
                    } else {
                        $_SESSION['register_error'] = "注册失败,请重试。";
                    }
                }
            }
            mysqli_close($link);
        } else {
            $_SESSION['register_error'] = "Passwords do not match!";
        }
    } else {
        $_SESSION['register_error'] = "All fields are required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - CrossBorder Insurance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #01459C;
            --secondary-color: #0056bc;
            --text-color: #333;
            --light-bg: #f8f9fa;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Mulish', sans-serif;
            min-height: 100vh;
            background-color: var(--light-bg);
            color: var(--text-color);
        }

        .navbar {
            background: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            color: var(--text-color) !important;
            text-decoration: none;
            font-weight: 500;
        }

        .brand-logo {
            height: 40px;
            margin-right: 10px;
        }

        .back-to-home {
            color: white !important;
            padding: 0.5rem 1.5rem !important;
            border-radius: 25px;
            margin-left: 1rem;
            transition: all 0.3s ease;
            background: var(--primary-color);
            text-decoration: none;
        }

        .back-to-home:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .register-container {
            margin-top: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 90px);
            padding: 40px 20px;
        }

        .register-box {
            width: 100%;
            max-width: 800px;
            padding: 50px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        }

        h1 {
            font-size: 2.8rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 10px;
        }
        
        .welcome-text {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #eee;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
            outline: none;
        }

        .register-btn {
            width: 100%;
            padding: 15px;
            background: #1a237e;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .register-btn:hover {
            background: #0d1657;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
        }

        .form-footer {
            margin-top: 25px;
            text-align: center;
        }

        .login-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-link:hover {
            color: var(--secondary-color);
        }

        .error-message {
            color: #f44336;
            font-size: 0.9rem;
            margin-top: 5px;
            min-height: 20px;
        }

        .form-control.error {
            border-color: #f44336;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        #sendVerificationBtn {
            white-space: nowrap;
            min-width: 120px;
            background-color: #1a237e;
            border: none;
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #sendVerificationBtn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        #sendVerificationBtn:not(:disabled):hover {
            background-color: #0d1657;
            transform: translateY(-1px);
        }

        .verification-code-input {
            letter-spacing: 4px;
            font-size: 1.2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/logo.png" alt="CrossBorder Insurance Logo" class="brand-logo">
                CrossBorder Insurance
            </a>
            <div class="ms-auto">
                <a href="home.php" class="back-to-home">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Registration Container -->
    <div class="register-container">
        <div class="register-box">
            <?php if (isset($_SESSION['register_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['register_error'];
                        unset($_SESSION['register_error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <h1>Welcome!</h1>
            <p class="welcome-text">Create your account to get started</p>

            <form action="register.php" method="POST" id="registerForm" onsubmit="return validateForm()">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="Username" class="form-control" placeholder="Enter your username" maxlength="12" id="username">
                    <div class="error-message" id="username-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <input type="email" name="Email" class="form-control" placeholder="Enter your email" maxlength="64" id="email">
                        <button type="button" id="sendVerificationBtn">Send Code</button>
                    </div>
                    <div class="error-message" id="email-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Verification Code</label>
                    <input type="text" name="verification_code" class="form-control verification-code-input" 
                           placeholder="Enter 6-digit code" maxlength="6" id="verification-code" 
                           pattern="[0-9]*" inputmode="numeric">
                    <div class="error-message" id="verification-code-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="Password" class="form-control" placeholder="Enter your password" maxlength="12" id="password">
                    <div class="error-message" id="password-error"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="Confirm" class="form-control" placeholder="Confirm your password" maxlength="12" id="confirm-password">
                    <div class="error-message" id="confirm-password-error"></div>
                </div>

                <button type="submit" class="register-btn">REGISTER</button>
                
                <div class="form-footer">
                    Already have an account? <a href="login.php" class="login-link">Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            const username = document.getElementById('username');
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm-password');
            const verificationCode = document.getElementById('verification-code');
            let isValid = true;

            // Reset all error messages
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));

            // Validate username
            if (!username.value.trim()) {
                username.classList.add('error');
                document.getElementById('username-error').textContent = 'Please enter your username';
                isValid = false;
            }

            // Validate email
            if (!email.value.trim()) {
                email.classList.add('error');
                document.getElementById('email-error').textContent = 'Please enter your email';
                isValid = false;
            } else if (!isValidEmail(email.value.trim())) {
                email.classList.add('error');
                document.getElementById('email-error').textContent = 'Please enter a valid email address';
                isValid = false;
            }

            // Validate password
            if (!password.value) {
                password.classList.add('error');
                document.getElementById('password-error').textContent = 'Please enter your password';
                isValid = false;
            }

            // Validate confirm password
            if (!confirmPassword.value) {
                confirmPassword.classList.add('error');
                document.getElementById('confirm-password-error').textContent = 'Please confirm your password';
                isValid = false;
            } else if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('error');
                document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
                isValid = false;
            }

            // Validate verification code
            if (!verificationCode.value.trim()) {
                verificationCode.classList.add('error');
                document.getElementById('verification-code-error').textContent = 'Please enter verification code';
                isValid = false;
            } else if (verificationCode.value.length !== 6) {
                verificationCode.classList.add('error');
                document.getElementById('verification-code-error').textContent = 'Verification code must be 6 digits';
                isValid = false;
            }

            return isValid;
        }

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Add functionality to clear error messages on input
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorElement = document.getElementById(this.id + '-error');
                if (errorElement) {
                    errorElement.textContent = '';
                }
            });
        });

        document.getElementById('sendVerificationBtn').addEventListener('click', function() {
            const email = document.getElementById('email').value.trim();
            const btn = this;
            
            if (!email || !isValidEmail(email)) {
                document.getElementById('email-error').textContent = 'Please enter a valid email address';
                return;
            }

            // Disable button and show countdown
            btn.disabled = true;
            let countdown = 60;
            const originalText = btn.textContent;
            
            const timer = setInterval(() => {
                btn.textContent = `Resend (${countdown}s)`;
                countdown--;
                
                if (countdown < 0) {
                    clearInterval(timer);
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            }, 1000);

            // Send verification code request
            fetch('register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_code&email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Failed to send verification code: ' + data.message);
                    clearInterval(timer);
                    btn.disabled = false;
                    btn.textContent = originalText;
                } else {
                    // Show success message
                    const successMessage = document.createElement('div');
                    successMessage.className = 'alert alert-success alert-dismissible fade show mt-2';
                    successMessage.innerHTML = `
                        Verification code has been sent to your email
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    btn.parentElement.parentElement.appendChild(successMessage);
                }
            })
            .catch(error => {
                alert('Error sending verification code');
                clearInterval(timer);
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });

        // Only allow numeric input for verification code
        document.getElementById('verification-code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>