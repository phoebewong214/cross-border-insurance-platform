<?php
session_start(); // 添加session_start()來保存數據庫配置

// 處理退出請求
if (isset($_POST['logout'])) {
    // 清空所有session數據
    session_unset();
    // 銷毀session
    session_destroy();
    // 重定向到當前頁面
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// 遠程數據庫配置
$remote_db_config = [
    'host' => isset($_SESSION['db_host']) ? $_SESSION['db_host'] : '',
    'dbname' => 'insurance_system',
    'username' => isset($_SESSION['db_username']) ? $_SESSION['db_username'] : '',
    'password' => isset($_SESSION['db_password']) ? $_SESSION['db_password'] : ''
];

// 檢查是否是POST請求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['test_connection'])) {
        // 測試數據庫連接
        $remote_host = filter_var($_POST['remote_host'], FILTER_SANITIZE_STRING);
        $remote_username = filter_var($_POST['remote_username'], FILTER_SANITIZE_STRING);
        $remote_password = $_POST['remote_password'];

        try {
            $dsn = "mysql:host={$remote_host};dbname={$remote_db_config['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $remote_username, $remote_password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 如果連接成功，保存配置到session
            $_SESSION['db_host'] = $remote_host;
            $_SESSION['db_username'] = $remote_username;
            $_SESSION['db_password'] = $remote_password;

            // 更新遠程數據庫配置
            $remote_db_config['host'] = $remote_host;
            $remote_db_config['username'] = $remote_username;
            $remote_db_config['password'] = $remote_password;

            // 獲取管理員列表
            $sql = "SELECT Admin_id, Admin_name, Email FROM admin ORDER BY Admin_id";
            $stmt = $pdo->query($sql);
            $admin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 獲取用戶列表
            $sql = "SELECT User_id, User_name, Email, discount FROM user ORDER BY User_id";
            $stmt = $pdo->query($sql);
            $user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $connection_success = "Database connected successfully!";
        } catch (PDOException $e) {
            $connection_error = "Connection failed: " . $e->getMessage();
            $admin_list = [];
            $user_list = [];
        }
    } else if (isset($_POST['create_admin'])) {
        // 管理員創建邏輯
        $admin_username = filter_var($_POST['admin_username'], FILTER_SANITIZE_STRING);
        $admin_password = $_POST['admin_password'];
        $admin_email = filter_var($_POST['admin_email'], FILTER_SANITIZE_EMAIL);

        // 驗證輸入
        $errors = [];
        if (empty($admin_username)) {
            $errors[] = "Username cannot be empty";
        }
        if (empty($admin_password)) {
            $errors[] = "Password cannot be empty";
        }
        if (empty($admin_email) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email";
        }

        if (empty($errors)) {
            try {
                $dsn = "mysql:host={$_SESSION['db_host']};dbname={$remote_db_config['dbname']};charset=utf8mb4";
                $pdo = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // 生成密碼哈希
                $hash = password_hash($admin_password, PASSWORD_DEFAULT);

                // 準備SQL語句
                $sql = "INSERT INTO admin (Admin_name, HASH_Value, Email) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$admin_username, $hash, $admin_email]);

                $success = "Admin account created successfully!";

                // 重新獲取管理員列表
                $sql = "SELECT Admin_id, Admin_name, Email FROM admin ORDER BY Admin_id";
                $stmt = $pdo->query($sql);
                $admin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    } else if (isset($_POST['edit_admin'])) {
        // 管理員編輯邏輯
        $admin_id = filter_var($_POST['admin_id'], FILTER_SANITIZE_NUMBER_INT);
        $admin_username = filter_var($_POST['admin_username'], FILTER_SANITIZE_STRING);
        $admin_email = filter_var($_POST['admin_email'], FILTER_SANITIZE_EMAIL);
        $admin_password = $_POST['admin_password'];

        try {
            $dsn = "mysql:host={$_SESSION['db_host']};dbname={$remote_db_config['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!empty($admin_password)) {
                // 如果提供了新密碼，更新密碼
                $hash = password_hash($admin_password, PASSWORD_DEFAULT);
                $sql = "UPDATE admin SET Admin_name = ?, Email = ?, HASH_Value = ? WHERE Admin_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$admin_username, $admin_email, $hash, $admin_id]);
            } else {
                // 只更新用戶名和郵箱
                $sql = "UPDATE admin SET Admin_name = ?, Email = ? WHERE Admin_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$admin_username, $admin_email, $admin_id]);
            }

            $success = "Admin account updated successfully!";

            // 重新獲取管理員列表
            $sql = "SELECT Admin_id, Admin_name, Email FROM admin ORDER BY Admin_id";
            $stmt = $pdo->query($sql);
            $admin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else if (isset($_POST['delete_admin'])) {
        // 管理員刪除邏輯
        $admin_ids = $_POST['admin_ids'] ?? [];

        if (!empty($admin_ids)) {
            try {
                $dsn = "mysql:host={$_SESSION['db_host']};dbname={$remote_db_config['dbname']};charset=utf8mb4";
                $pdo = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // 準備SQL語句
                $placeholders = str_repeat('?,', count($admin_ids) - 1) . '?';
                $sql = "DELETE FROM admin WHERE Admin_id IN ($placeholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($admin_ids);

                $success = "Selected admin(s) deleted successfully!";

                // 重新獲取管理員列表
                $sql = "SELECT Admin_id, Admin_name, Email FROM admin ORDER BY Admin_id";
                $stmt = $pdo->query($sql);
                $admin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    } else if (isset($_POST['create_user'])) {
        // 用戶創建邏輯
        $user_username = filter_var($_POST['user_username'], FILTER_SANITIZE_STRING);
        $user_password = $_POST['user_password'];
        $user_email = filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL);

        try {
            $dsn = "mysql:host={$_SESSION['db_host']};dbname={$remote_db_config['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 生成密碼哈希
            $hash = password_hash($user_password, PASSWORD_DEFAULT);

            // 準備SQL語句
            $sql = "INSERT INTO user (User_name, HASH_Value, Email, discount) VALUES (?, ?, ?, 100.00)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_username, $hash, $user_email]);

            $success = "User account created successfully!";

            // 重新獲取用戶列表
            $sql = "SELECT User_id, User_name, Email, discount FROM user ORDER BY User_id";
            $stmt = $pdo->query($sql);
            $user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else if (isset($_POST['edit_user'])) {
        // 用戶編輯邏輯
        $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
        $user_username = filter_var($_POST['user_username'], FILTER_SANITIZE_STRING);
        $user_email = filter_var($_POST['user_email'], FILTER_SANITIZE_EMAIL);
        $user_password = $_POST['user_password'];
        $user_status = filter_var($_POST['user_status'], FILTER_SANITIZE_STRING);

        try {
            $dsn = "mysql:host={$_SESSION['db_host']};dbname={$remote_db_config['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if (!empty($user_password)) {
                // 如果提供了新密碼，更新密碼
                $hash = password_hash($user_password, PASSWORD_DEFAULT);
                $sql = "UPDATE user SET User_name = ?, Email = ?, HASH_Value = ? WHERE User_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_username, $user_email, $hash, $user_id]);
            } else {
                // 只更新用戶名和郵箱
                $sql = "UPDATE user SET User_name = ?, Email = ? WHERE User_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_username, $user_email, $user_id]);
            }

            $success = "User account updated successfully!";

            // 重新獲取用戶列表
            $sql = "SELECT User_id, User_name, Email, discount FROM user ORDER BY User_id";
            $stmt = $pdo->query($sql);
            $user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    } else if (isset($_POST['delete_user'])) {
        // 用戶刪除邏輯
        $user_ids = $_POST['user_ids'] ?? [];

        if (!empty($user_ids)) {
            try {
                $dsn = "mysql:host={$_SESSION['db_host']};dbname={$remote_db_config['dbname']};charset=utf8mb4";
                $pdo = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // 準備SQL語句
                $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
                $sql = "DELETE FROM user WHERE User_id IN ($placeholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($user_ids);

                $success = "Selected user(s) deleted successfully!";

                // 重新獲取用戶列表
                $sql = "SELECT User_id, User_name, Email, discount FROM user ORDER BY User_id";
                $stmt = $pdo->query($sql);
                $user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
        }
    }
}

// 如果已經有數據庫連接，獲取管理員和用戶列表
if (isset($_SESSION['db_host']) && !empty($_SESSION['db_host'])) {
    try {
        $dsn = "mysql:host={$_SESSION['db_host']};dbname={$remote_db_config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $_SESSION['db_username'], $_SESSION['db_password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 獲取管理員列表
        $sql = "SELECT Admin_id, Admin_name, Email FROM admin ORDER BY Admin_id";
        $stmt = $pdo->query($sql);
        $admin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 獲取用戶列表
        $sql = "SELECT User_id, User_name, Email, discount FROM user ORDER BY User_id";
        $stmt = $pdo->query($sql);
        $user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $connection_success = "Database connection successful!";
    } catch (PDOException $e) {
        $connection_error = "Connection failed: " . $e->getMessage();
        $admin_list = [];
        $user_list = [];
    }
} else {
    $admin_list = [];
    $user_list = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remote Account Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin-top: 50px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: #01459C;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }

        .btn-primary {
            background: #01459C;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background: #003366;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .alert {
            border-radius: 8px;
        }

        .section-title {
            color: #01459C;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #01459C;
        }

        .admin-table {
            margin-top: 20px;
        }

        .admin-table th {
            background-color: #f8f9fa;
            color: #01459C;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #dc3545;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background: #c82333;
            color: white;
        }

        .nav-tabs {
            border-bottom: 2px solid #01459C;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            color: #01459C;
            border: none;
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 8px 8px 0 0;
        }

        .nav-tabs .nav-link.active {
            background: #01459C;
            color: white;
        }

        .status-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8f9fa;
            padding: 10px 20px;
            border-top: 1px solid #dee2e6;
            font-size: 0.9em;
            color: #6c757d;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .action-buttons button {
            padding: 5px 10px;
            font-size: 0.9em;
        }

        .table th {
            background-color: #f8f9fa;
            color: #01459C;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['db_host']) && !empty($_SESSION['db_host'])): ?>
            <form method="POST" action="" style="text-align: right; margin-bottom: 20px;">
                <button type="submit" name="logout" class="btn btn-danger">Logout</button>
            </form>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Database Management System</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($connection_success)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($connection_success); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($connection_error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($connection_error); ?>
                    </div>
                <?php endif; ?>

                <!-- 數據庫連接測試表單 -->
                <form method="POST" action="">
                    <h5 class="section-title">Remote Database Configuration</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="remote_host" class="form-label">Remote Database IP Address</label>
                                <input type="text" class="form-control" id="remote_host" name="remote_host"
                                    value="<?php echo isset($_POST['remote_host']) ? htmlspecialchars($_POST['remote_host']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="remote_username" class="form-label">Remote Database Username</label>
                                <input type="text" class="form-control" id="remote_username" name="remote_username"
                                    value="<?php echo isset($_POST['remote_username']) ? htmlspecialchars($_POST['remote_username']) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="remote_password" class="form-label">Remote Database Password</label>
                        <input type="password" class="form-control" id="remote_password" name="remote_password">
                    </div>
                    <div class="mb-3">
                        <button type="submit" name="test_connection" class="btn btn-secondary">Test Connection</button>
                    </div>
                </form>

                <!-- 標籤頁導航 -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab">Admin Management</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab">User Management</button>
                    </li>
                </ul>

                <!-- 標籤頁內容 -->
                <div class="tab-content" id="myTabContent">
                    <!-- Admin 標籤頁 -->
                    <div class="tab-pane fade show active" id="admin" role="tabpanel">
                        <!-- 管理員創建表單 -->
                        <form method="POST" action="">
                            <h5 class="section-title">Admin Account Information</h5>
                            <div class="mb-3">
                                <label for="admin_username" class="form-label">Admin Username</label>
                                <input type="text" class="form-control" id="admin_username" name="admin_username"
                                    value="<?php echo isset($_POST['admin_username']) ? htmlspecialchars($_POST['admin_username']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Admin Password</label>
                                <input type="text" class="form-control" id="admin_password" name="admin_password"
                                    value="<?php echo isset($_POST['admin_password']) ? htmlspecialchars($_POST['admin_password']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email"
                                    value="<?php echo isset($_POST['admin_email']) ? htmlspecialchars($_POST['admin_email']) : ''; ?>" required>
                            </div>
                            <button type="submit" name="create_admin" class="btn btn-primary w-100" <?php echo !isset($connection_success) ? 'disabled' : ''; ?>>Generate Admin Account</button>
                        </form>

                        <h5 class="section-title">Existing Admin Accounts</h5>
                        <?php if (isset($admin_list_error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($admin_list_error); ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive admin-table">
                                <div class="action-buttons">
                                    <button type="button" class="btn btn-warning" onclick="editAdmin()">Edit Selected</button>
                                    <button type="button" class="btn btn-danger" onclick="deleteAdmin()">Delete Selected</button>
                                </div>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAllAdmin"></th>
                                            <th>Admin ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($admin_list)): ?>
                                            <?php foreach ($admin_list as $admin): ?>
                                                <tr>
                                                    <td><input type="checkbox" class="admin-select" value="<?php echo $admin['Admin_id']; ?>"></td>
                                                    <td><?php echo htmlspecialchars($admin['Admin_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($admin['Admin_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($admin['Email']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Please test the database connection first</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- User 標籤頁 -->
                    <div class="tab-pane fade" id="user" role="tabpanel">
                        <!-- 用戶創建表單 -->
                        <form method="POST" action="">
                            <h5 class="section-title">User Account Information</h5>
                            <div class="mb-3">
                                <label for="user_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="user_username" name="user_username" required>
                            </div>
                            <div class="mb-3">
                                <label for="user_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="user_password" name="user_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="user_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="user_email" name="user_email" required>
                            </div>
                            <button type="submit" name="create_user" class="btn btn-primary w-100" <?php echo !isset($connection_success) ? 'disabled' : ''; ?>>Create User Account</button>
                        </form>

                        <h5 class="section-title">Existing User Accounts</h5>
                        <div class="action-buttons">
                            <button type="button" class="btn btn-warning" onclick="editUser()">Edit Selected</button>
                            <button type="button" class="btn btn-danger" onclick="deleteUser()">Delete Selected</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllUser"></th>
                                        <th>User ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Discount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($user_list)): ?>
                                        <?php foreach ($user_list as $user): ?>
                                            <tr>
                                                <td><input type="checkbox" class="user-select" value="<?php echo $user['User_id']; ?>"></td>
                                                <td><?php echo htmlspecialchars($user['User_id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['User_name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                                <td><?php echo htmlspecialchars($user['discount']); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Please test the database connection first</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 編輯管理員模態框 -->
    <div class="modal fade" id="editAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="admin_id" id="edit_admin_id">
                        <div class="mb-3">
                            <label for="edit_admin_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_admin_username" name="admin_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_admin_password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_admin_password" name="admin_password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_admin_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_admin_email" name="admin_email" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_admin" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 編輯用戶模態框 -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label for="edit_user_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_user_username" name="user_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_user_password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_user_password" name="user_password">
                        </div>
                        <div class="mb-3">
                            <label for="edit_user_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_user_email" name="user_email" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 刪除管理員表單 -->
    <form method="POST" action="" id="deleteAdminForm" style="display: none;">
        <input type="hidden" name="admin_ids" id="delete_admin_ids">
        <button type="submit" name="delete_admin">Delete</button>
    </form>

    <!-- 刪除用戶表單 -->
    <form method="POST" action="" id="deleteUserForm" style="display: none;">
        <input type="hidden" name="user_ids" id="delete_user_ids">
        <button type="submit" name="delete_user">Delete</button>
    </form>

    <!-- 狀態欄 -->
    <div class="status-bar">
        <?php if (isset($connection_success)): ?>
            <span class="text-success">Database connected successfully at <?php echo date('Y-m-d H:i:s'); ?></span>
        <?php elseif (isset($connection_error)): ?>
            <span class="text-danger"><?php echo htmlspecialchars($connection_error); ?></span>
        <?php else: ?>
            <span class="text-muted">Not connected to database</span>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 全選功能
        document.getElementById('selectAllAdmin').addEventListener('change', function() {
            document.querySelectorAll('.admin-select').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.getElementById('selectAllUser').addEventListener('change', function() {
            document.querySelectorAll('.user-select').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // 管理員操作函數
        function editAdmin() {
            const selected = document.querySelectorAll('.admin-select:checked');
            if (selected.length !== 1) {
                alert('Please select exactly one admin to edit');
                return;
            }

            const adminId = selected[0].value;
            const row = selected[0].closest('tr');
            const username = row.cells[2].textContent;
            const email = row.cells[3].textContent;

            document.getElementById('edit_admin_id').value = adminId;
            document.getElementById('edit_admin_username').value = username;
            document.getElementById('edit_admin_email').value = email;
            document.getElementById('edit_admin_password').value = '';

            new bootstrap.Modal(document.getElementById('editAdminModal')).show();
        }

        function deleteAdmin() {
            const selected = document.querySelectorAll('.admin-select:checked');
            if (selected.length === 0) {
                alert('Please select at least one admin to delete');
                return;
            }
            if (confirm('Are you sure you want to delete the selected admin(s)?')) {
                const adminIds = Array.from(selected).map(checkbox => checkbox.value);
                document.getElementById('delete_admin_ids').value = JSON.stringify(adminIds);
                document.getElementById('deleteAdminForm').submit();
            }
        }

        // 用戶操作函數
        function editUser() {
            const selected = document.querySelectorAll('.user-select:checked');
            if (selected.length !== 1) {
                alert('Please select exactly one user to edit');
                return;
            }

            const userId = selected[0].value;
            const row = selected[0].closest('tr');
            const username = row.cells[2].textContent;
            const email = row.cells[3].textContent;

            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_user_username').value = username;
            document.getElementById('edit_user_email').value = email;
            document.getElementById('edit_user_password').value = '';

            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }

        function deleteUser() {
            const selected = document.querySelectorAll('.user-select:checked');
            if (selected.length === 0) {
                alert('Please select at least one user to delete');
                return;
            }
            if (confirm('Are you sure you want to delete the selected user(s)?')) {
                const userIds = Array.from(selected).map(checkbox => checkbox.value);
                document.getElementById('delete_user_ids').value = JSON.stringify(userIds);
                document.getElementById('deleteUserForm').submit();
            }
        }
    </script>
</body>

</html>