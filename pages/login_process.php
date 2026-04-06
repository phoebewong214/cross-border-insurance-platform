<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
    $password = $_POST["password"];

    if (!empty($username) && !empty($password)) {
        $link = mysqli_connect('localhost', 'root', '', 'insurance_system');

        if (!$link) {
            die("Connection failed: " . mysqli_connect_error());
        }

        mysqli_query($link, 'SET NAMES utf8');

        // 查询用户
        $sql = "SELECT User_ID, User_name, HASH_Value FROM user WHERE User_name = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // 验证密码
            if (password_verify($password, $row['HASH_Value'])) {
                // 登录成功
                $_SESSION['user_id'] = $row['User_ID'];
                $_SESSION['user_name'] = $row['User_name'];
                header("Location: ../index.php"); // 登录成功后跳转到管理页面
                exit();
            } else {
                $_SESSION['login_error'] = "Incorrect password!";
            }
        } else {
            $_SESSION['login_error'] = "Username not found!";
        }

        mysqli_close($link);
    } else {
        $_SESSION['login_error'] = "Please enter both username and password!";
    }

    header("Location: ../login.php");
    exit();
}
