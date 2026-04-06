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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            text-align: left;
        }

        .welcome-text {
            font-size: 1.3rem;
            color: #666;
            margin-bottom: 40px;
            text-align: left;
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
            text-align: left;
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

        .change-btn {
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

        .change-btn:hover {
            background: #0d1657;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
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
    <script>
        function validateForm() {
            const username = document.getElementById("username");
            const email = document.getElementById("email");
            const newPassword = document.getElementById("new-password");
            const confirmPassword = document.getElementById("confirm-password");
            const errorMessage = document.getElementById("error-message");
            let isValid = true;

            // 重置所有错误提示
            document.querySelectorAll('.form-control').forEach(input => {
                input.classList.remove('error');
            });
            errorMessage.textContent = "";

            // 验证所有字段是否为空
            if (!username.value.trim()) {
                username.classList.add('error');
                errorMessage.textContent = "Please enter your username";
                isValid = false;
                return false;
            }
            if (!email.value.trim()) {
                email.classList.add('error');
                errorMessage.textContent = "Please enter your email";
                isValid = false;
                return false;
            }
            if (!newPassword.value) {
                newPassword.classList.add('error');
                errorMessage.textContent = "Please enter your new password";
                isValid = false;
                return false;
            }
            if (!confirmPassword.value) {
                confirmPassword.classList.add('error');
                errorMessage.textContent = "Please confirm your new password";
                isValid = false;
                return false;
            }

            // 验证密码是否匹配
            if (newPassword.value !== confirmPassword.value) {
                newPassword.classList.add('error');
                confirmPassword.classList.add('error');
                errorMessage.textContent = "Passwords do not match!";
                isValid = false;
                return false;
            }

            return isValid;
        }

        // 添加输入时清除错误提示的功能
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
                const errorMessage = document.getElementById('error-message');
                if (errorMessage) {
                    errorMessage.textContent = '';
                }
            });
        });
    </script>
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

    <!-- Change Password Container -->
    <div class="change-password-container">
        <div class="change-password-box">
            <h1>Change Password</h1>
            <p class="welcome-text">Please enter your credentials to change password</p>

            <form action="changePassword.php" method="post" onsubmit="return validateForm()">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="Username" class="form-control" placeholder="Enter your username" maxlength="12" id="username">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="Email" class="form-control" placeholder="Enter your registered email" id="email">
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="NewPassword" class="form-control" placeholder="Enter your new password" maxlength="12" id="new-password">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="ConfirmPassword" class="form-control" placeholder="Confirm your new password" maxlength="12" id="confirm-password">
                    <div class="error-message" id="error-message"></div>
                </div>

                <button type="submit" name="submit" class="change-btn">CHANGE PASSWORD</button>
            </form>
        </div>
    </div>

    <?php
    if (isset($_POST["submit"])) {
        $username = trim($_POST["Username"]);
        $email = trim($_POST["Email"]);
        $newPassword = $_POST["NewPassword"];
        $confirmPassword = $_POST["ConfirmPassword"];

        // 验证所有字段是否为空
        if (empty($username) || empty($email) || empty($newPassword) || empty($confirmPassword)) {
            echo "<script>
                document.getElementById('error-message').textContent = 'All fields are required!';
                document.querySelectorAll('.form-control').forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('error');
                    }
                });
            </script>";
            exit();
        }

        // 验证密码是否匹配
        if ($newPassword !== $confirmPassword) {
            echo "<script>
                document.getElementById('error-message').textContent = 'Passwords do not match!';
                document.getElementById('new-password').classList.add('error');
                document.getElementById('confirm-password').classList.add('error');
            </script>";
            exit();
        }

        $link = @mysqli_connect('localhost', 'root', '', 'insurance');
        if (!$link) {
            die("Connection failed: " . mysqli_connect_error());
        }

        mysqli_query($link, 'SET NAMES utf8');

        // 首先检查用户名是否存在
        $checkUser = "SELECT * FROM user WHERE User_name = ?";
        $stmt = mysqli_prepare($link, $checkUser);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $userResult = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($userResult) === 0) {
            echo "<script>
                document.getElementById('error-message').textContent = 'User not registered!';
                document.getElementById('username').classList.add('error');
            </script>";
        } else {
            // 然后检查邮箱是否匹配
            $checkEmail = "SELECT * FROM user WHERE User_name = ? AND Email = ?";
            $stmt = mysqli_prepare($link, $checkEmail);
            mysqli_stmt_bind_param($stmt, "ss", $username, $email);
            mysqli_stmt_execute($stmt);
            $emailResult = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($emailResult) === 0) {
                echo "<script>
                    document.getElementById('error-message').textContent = 'Incorrect email address!';
                    document.getElementById('email').classList.add('error');
                </script>";
            } else {
                // 用户名和邮箱都匹配，更新密码
                $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateSql = "UPDATE user SET HASH_Value = ? WHERE User_name = ? AND Email = ?";
                $stmt = mysqli_prepare($link, $updateSql);
                mysqli_stmt_bind_param($stmt, "sss", $hashed_password, $username, $email);

                if (mysqli_stmt_execute($stmt)) {
                    echo "<script>
                        window.location.href = 'successChange.php?type=password';
                    </script>";
                    exit();
                } else {
                    echo "<script>
                        document.getElementById('error-message').textContent = 'Error updating password!';
                    </script>";
                }
            }
        }

        mysqli_close($link);
    }
    ?>
</body>

</html>