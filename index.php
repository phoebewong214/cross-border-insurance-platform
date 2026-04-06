<?php
session_start();

// 檢查用戶是否已登錄
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 連接數據庫
require_once 'pages/db_config.php';

try {
    // 查詢用戶名稱
    $stmt = $pdo->prepare("SELECT User_name FROM user WHERE User_ID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_name = $user['User_name'];
    } else {
        $user_name = 'User';
    }
} catch (PDOException $e) {
    $user_name = 'User';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Center - Insurance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: #01459C !important;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #01459C !important;
        }

        .welcome-section {
            background: linear-gradient(135deg, #01459C, #0056bc);
            color: white;
            padding: 4rem 0;
            margin-bottom: 5rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%),
                linear-gradient(-45deg, rgba(255, 255, 255, 0.1) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, rgba(255, 255, 255, 0.1) 75%),
                linear-gradient(-45deg, transparent 75%, rgba(255, 255, 255, 0.1) 75%);
            background-size: 20px 20px;
            opacity: 0.1;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .stats-number {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 500;
        }

        .module-card {
            background: white;
            padding: 2.5rem 2rem;
            height: 320px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            background: linear-gradient(145deg, #ffffff, #f5f5f5);
        }

        .icon-wrapper {
            width: 90px;
            height: 90px;
            background: rgba(1, 69, 156, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.8rem;
            transition: all 0.4s ease;
            position: relative;
        }

        .icon-wrapper::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid rgba(1, 69, 156, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }

            70% {
                transform: scale(1.1);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(1, 69, 156, 0.15);
            background: linear-gradient(145deg, #ffffff, #ffffff);
        }

        .module-icon {
            font-size: 2.8rem;
            color: #01459C;
            transition: all 0.4s ease;
        }

        .module-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }

        .module-description {
            font-size: 1rem;
            color: #666;
            margin: 0;
            line-height: 1.6;
            transition: all 0.3s ease;
        }

        .module-card:hover .module-title {
            color: #01459C;
        }

        .module-card:hover .module-description {
            color: #444;
        }

        .module-card:hover .module-icon {
            color: white;
            transform: scale(1.1);
        }

        .row.g-4 {
            margin-top: 2rem;
            margin-bottom: 4rem;
        }

        .footer {
            background-color: #f1f5f9;
            padding: 2rem 0;
            margin-top: 6rem;
        }

        .contact-info {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.8;
        }

        .contact-info i {
            color: #01459C;
            width: 20px;
            margin-right: 8px;
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .business-hours {
            color: #666;
            font-size: 0.9rem;
            font-style: italic;
        }

        .product-card .card {
            border: 1px solid rgba(1, 69, 156, 0.1);
            border-radius: 20px;
            transition: all 0.4s ease;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .product-card .card:hover {
            transform: translateY(-10px);
            border-color: #01459C;
            box-shadow: 0 10px 20px rgba(1, 69, 156, 0.1);
        }

        .product-card .card-title {
            color: #01459C;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid rgba(1, 69, 156, 0.1);
            padding-bottom: 1rem;
        }

        .product-card .features p {
            margin-bottom: 0.8rem;
            color: #666;
            transition: all 0.3s ease;
        }

        .product-card .card:hover .features p {
            color: #333;
        }

        .insurance-products h2 {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
        }

        .insurance-products h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: #01459C;
            margin: 1rem auto 0;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">Personal Center</a>
            <div class="ms-auto">
                <a href="pages/changePassword.php" class="nav-link d-inline-block me-3">Settings</a>
                <a href="home.php" class="nav-link d-inline-block">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="welcome-title animate-on-scroll">Welcome Back, <?php echo $user_name; ?>!</h1>
                    <p class="welcome-subtitle animate-on-scroll">Manage your insurance portfolio and vehicle information all in one place.</p>
                </div>
                <div class="col-md-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stats-card animate-on-scroll">
                                <div class="stats-number" id="registeredPartiesCount">0</div>
                                <div class="stats-label">Registered Parties</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stats-card animate-on-scroll">
                                <div class="stats-number" id="registeredCarsCount">0</div>
                                <div class="stats-label">Registered Cars</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row g-4 justify-content-center">
            <!-- Product Selection -->
            <div class="col-lg-4">
                <div class="module-card animate-on-scroll" onclick="window.location.href='pages/select_product.php'">
                    <div class="icon-wrapper">
                        <i class="fas fa-hand-holding-dollar module-icon"></i>
                    </div>
                    <h3 class="module-title">Product Selection</h3>
                    <p class="module-description">Select and customize your cross-boundary car insurance plan.</p>
                </div>
            </div>

            <!-- Party Management -->
            <div class="col-lg-4">
                <div class="module-card animate-on-scroll" onclick="window.location.href='pages/manage_party.php'">
                    <div class="icon-wrapper">
                        <i class="fas fa-user-circle module-icon"></i>
                    </div>
                    <h3 class="module-title">Party Management</h3>
                    <p class="module-description">Manage personal and company information efficiently.</p>
                </div>
            </div>

            <!-- Car Management -->
            <div class="col-lg-4">
                <div class="module-card animate-on-scroll" onclick="window.location.href='pages/manage_car.php'">
                    <div class="icon-wrapper">
                        <i class="fas fa-car module-icon"></i>
                    </div>
                    <h3 class="module-title">Car Management</h3>
                    <p class="module-description">Manage your vehicle information and related policies.</p>
                </div>
            </div>

            <!-- Policy Management -->
            <div class="col-lg-4">
                <div class="module-card animate-on-scroll" onclick="window.location.href='pages/policy_query.php'">
                    <div class="icon-wrapper">
                        <i class="fas fa-shield-alt module-icon"></i>
                    </div>
                    <h3 class="module-title">Policy Management</h3>
                    <p class="module-description">View and manage your active insurance policies.</p>
                </div>
            </div>

            <!-- Order Management -->
            <div class="col-lg-4">
                <div class="module-card animate-on-scroll">
                    <div class="icon-wrapper">
                        <i class="fas fa-file-invoice-dollar module-icon"></i>
                    </div>
                    <h3 class="module-title">Order Management</h3>
                    <p class="module-description">Track and manage your insurance orders and payments.</p>
                </div>
            </div>

            <!-- Claims Guide -->
            <div class="col-lg-4">
                <div class="module-card animate-on-scroll" onclick="window.location.href='pages/claim_guide.php'">
                    <div class="icon-wrapper">
                        <i class="fas fa-file-medical module-icon"></i>
                    </div>
                    <h3 class="module-title">Claim Guide</h3>
                    <p class="module-description">Learn about the claim process and required documents to quickly complete the claim application.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> Phone: +853 1234 5678</p>
                        <p><i class="fas fa-envelope"></i> Email: info@yourinsurancecompany.com</p>
                        <p><i class="fas fa-map-marker-alt"></i> Address: 18/F, Insurance Tower, 88 Finance Street, Central, Hong Kong</p>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="business-hours">
                        <p class="mb-0">Business Hours:</p>
                        <p>Monday to Friday, 9:00 AM - 6:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 添加滚动动画
        function checkScroll() {
            const elements = document.querySelectorAll('.animate-on-scroll');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                if (elementTop < windowHeight * 0.9) {
                    element.classList.add('visible');
                }
            });
        }

        // 初始检查
        window.addEventListener('load', checkScroll);
        // 滚动时检查
        window.addEventListener('scroll', checkScroll);

        // 添加模块点击事件
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('.module-title').textContent;
                switch (title) {
                    case 'Product Selection':
                        window.location.href = 'pages/select_product.php';
                        break;
                    case 'Party Management':
                        window.location.href = 'pages/manage_party.php';
                        break;
                    case 'Car Management':
                        window.location.href = 'pages/manage_car.php';
                        break;
                    case 'Policy Management':
                        // 处理保单管理
                        break;
                    case 'Order Management':
                        window.location.href = 'pages/order_management.php';
                        break;
                    case '理賠指南':
                        window.location.href = 'pages/claim_guide.php';
                        break;
                }
            });
        });

        // 获取车辆数量和parties数量
        function updateCounts() {
            // 获取车辆数量
            fetch('pages/process_product_selection.php?action=get_car_count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('registeredCarsCount').textContent = data.count;
                    }
                })
                .catch(error => console.error('Error fetching car count:', error));

            // 获取parties数量
            fetch('pages/process_party.php?action=get_party_count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('registeredPartiesCount').textContent = data.count;
                    }
                })
                .catch(error => console.error('Error fetching party count:', error));
        }

        // 页面加载完成后更新数量
        document.addEventListener('DOMContentLoaded', updateCounts);
    </script>
</body>

</html>