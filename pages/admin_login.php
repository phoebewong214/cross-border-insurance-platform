<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login - CrossBorder Insurance</title>
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

        .login-container {
            margin-top: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 90px);
            padding: 40px 20px;
        }

        .login-box {
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

        .login-btn {
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

        .login-btn:hover {
            background: #0d1657;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
        }

        .form-footer {
            margin-top: 25px;
            text-align: center;
        }

        .user-login-link {
            margin-bottom: 15px;
        }

        .user-login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .user-login-link a:hover {
            color: var(--secondary-color);
        }

        .change-password-link {
            margin-top: 10px;
        }

        .change-password-link a {
            color: #666;
            text-decoration: underline;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .change-password-link a:hover {
            color: var(--primary-color);
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-control.error {
            border-color: #f44336;
        }

        .admin-badge {
            background-color: #1a237e;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-left: 10px;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../assets/logo.png" alt="CrossBorder Insurance Logo" class="brand-logo">
                CrossBorder Insurance
            </a>
            <div class="ms-auto">
                <a href="../home.php" class="back-to-home">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- 登录容器 -->
    <div class="login-container">
        <div class="login-box">
            <?php if (isset($_SESSION['admin_login_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['admin_login_error'];
                        unset($_SESSION['admin_login_error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h1>Admin Login</h1>
            <p class="welcome-text">Welcome to the administrator portal</p>

            <form method="POST" action="admin_login_process.php" id="adminLoginForm" onsubmit="return validateForm()">
                <div class="form-group">
                    <input type="text" name="admin_username" class="form-control" placeholder="Enter admin username" id="username">
                    <div class="error-message" id="username-error"></div>
                </div>

                <div class="form-group">
                    <input type="password" name="admin_password" class="form-control" placeholder="Enter admin password" id="password">
                    <div class="error-message" id="password-error"></div>
                </div>

                <button type="submit" class="login-btn">ADMIN LOGIN</button>
                
                <div class="form-footer">
                    <div class="user-login-link">
                        <a href="../login.php">User Login</a>
                    </div>
                    <div class="change-password-link">
                        <a href="change_admin_password.php">Change Password</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 验证脚本 -->
    <script>
        function validateForm() {
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            const usernameError = document.getElementById('username-error');
            const passwordError = document.getElementById('password-error');
            let isValid = true;

            // 重置错误提示
            username.classList.remove('error');
            password.classList.remove('error');
            usernameError.textContent = '';
            passwordError.textContent = '';

            // 验证用户名
            if (!username.value.trim()) {
                username.classList.add('error');
                usernameError.textContent = 'Please enter admin username';
                isValid = false;
            }

            // 验证密码
            if (!password.value.trim()) {
                password.classList.add('error');
                passwordError.textContent = 'Please enter admin password';
                isValid = false;
            }

            return isValid;
        }

        // 添加输入时清除错误提示的功能
        document.getElementById('username').addEventListener('input', function() {
            this.classList.remove('error');
            document.getElementById('username-error').textContent = '';
        });

        document.getElementById('password').addEventListener('input', function() {
            this.classList.remove('error');
            document.getElementById('password-error').textContent = '';
        });
    </script>
</body>

</html> 