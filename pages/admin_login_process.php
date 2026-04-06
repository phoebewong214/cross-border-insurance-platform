<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = filter_var($_POST["admin_username"], FILTER_SANITIZE_STRING);
    $admin_password = $_POST["admin_password"];

    if (!empty($admin_username) && !empty($admin_password)) {
        $link = mysqli_connect('localhost', 'root', '', 'insurance_system');

        if (!$link) {
            die("Connection failed: " . mysqli_connect_error());
        }

        mysqli_query($link, 'SET NAMES utf8');

        // 检查管理员账号
        $sql = "SELECT Admin_id, Admin_name, HASH_Value FROM admin WHERE Admin_name = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $admin_username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify($admin_password, $admin['HASH_Value'])) {
                // 登录成功
                $_SESSION['admin_id'] = $admin['Admin_id'];
                $_SESSION['admin_name'] = $admin['Admin_name'];
                $_SESSION['is_admin'] = true;

                header("Location: ../admin.php");
                exit();
            } else {
                $_SESSION['admin_login_error'] = "Wrong password!";
            }
        } else {
            $_SESSION['admin_login_error'] = "Admin account does not exist!";
        }
        mysqli_close($link);
    } else {
        $_SESSION['admin_login_error'] = "Please fill in all fields!";
    }
}

// 如果登录失败，重定向回管理员登录页面
header("Location: admin_login.php");
exit();
