<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            $link = mysqli_connect('localhost', 'root', '', 'insurance_system');
            
            if (!$link) {
                die("Connection failed: " . mysqli_connect_error());
            }

            mysqli_query($link, 'SET NAMES utf8');

            // 检查当前密码是否正确
            $check_sql = "SELECT * FROM user WHERE User_id = ?";
            $stmt = mysqli_prepare($link, $check_sql);
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($current_password, $user['HASH_Value'])) {
                    // 更新密码
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE user SET HASH_Value = ? WHERE User_id = ?";
                    $stmt = mysqli_prepare($link, $update_sql);
                    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION['user_id']);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['success_message'] = "密码修改成功!";
                        header("Location: login.php");
                        exit();
                    } else {
                        $_SESSION['password_error'] = "密码修改失败,请重试。";
                    }
                } else {
                    $_SESSION['password_error'] = "当前密码错误!";
                }
            } else {
                $_SESSION['password_error'] = "用户不存在!";
            }
            mysqli_close($link);
        } else {
            $_SESSION['password_error'] = "新密码不匹配!";
        }
    } else {
        $_SESSION['password_error'] = "所有字段都必须填写!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - CrossBorder Insurance</title>
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

        .change-password-container {
            margin-top: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 90px);
            padding: 40px 20px;
        }

        .change-password-box {
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
            margin-bottom: 30px;
            position: relative;
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

        .change-password-btn {
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

        .change-password-btn:hover {
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

        .alert {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
        }

        .alert-danger {
            background-color: #fff3f3;
            border: 1px solid #ffcdd2;
            color: #f44336;
        }

        .btn-close {
            padding: 0.5rem;
            margin: -0.5rem -0.5rem -0.5rem auto;
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
    </style>
</head>
<body>
    <!-- 导航栏 -->
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

    <!-- 修改密码容器 -->
    <div class="change-password-container">
        <div class="change-password-box">
            <?php if (isset($_SESSION['password_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['password_error'];
                        unset($_SESSION['password_error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h1>Change Password</h1>
            <p class="welcome-text">Please enter your current and new password</p>

            <form action="change_password.php" method="POST" id="changePasswordForm" onsubmit="return validateForm()">
                <div class="form-group">
                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password" id="current-password">
                    <div class="error-message" id="current-password-error"></div>
                </div>

                <div class="form-group">
                    <input type="password" name="new_password" class="form-control" placeholder="Enter new password" id="new-password">
                    <div class="error-message" id="new-password-error"></div>
                </div>

                <div class="form-group">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" id="confirm-password">
                    <div class="error-message" id="confirm-password-error"></div>
                </div>

                <button type="submit" class="change-password-btn">CHANGE PASSWORD</button>
                
                <div class="form-footer">
                    <a href="login.php" class="login-link">Back to Login</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            const currentPassword = document.getElementById('current-password');
            const newPassword = document.getElementById('new-password');
            const confirmPassword = document.getElementById('confirm-password');
            let isValid = true;

            // Reset all error messages
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));

            // Validate current password
            if (!currentPassword.value.trim()) {
                currentPassword.classList.add('error');
                document.getElementById('current-password-error').textContent = 'Please enter your current password';
                isValid = false;
            }

            // Validate new password
            if (!newPassword.value.trim()) {
                newPassword.classList.add('error');
                document.getElementById('new-password-error').textContent = 'Please enter your new password';
                isValid = false;
            }

            // Validate confirm password
            if (!confirmPassword.value.trim()) {
                confirmPassword.classList.add('error');
                document.getElementById('confirm-password-error').textContent = 'Please confirm your new password';
                isValid = false;
            } else if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('error');
                document.getElementById('confirm-password-error').textContent = 'Passwords do not match';
                isValid = false;
            }

            return isValid;
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
    </script>
</body>
</html> 