<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - CrossBorder Insurance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #01459C;
            --secondary-color: #0056bc;
            --text-color: #333;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Mulish', sans-serif;
            color: var(--text-color);
        }

        .navbar {
            background: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .brand-logo {
            height: 40px;
            margin-right: 10px;
            object-fit: contain;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
        }

        .btn-login {
            background: var(--primary-color);
            color: white !important;
            padding: 0.5rem 1.5rem !important;
            border-radius: 25px;
            margin-left: 1rem;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            background: linear-gradient(rgba(1, 69, 156, 0.9), rgba(1, 69, 156, 0.9)),
                url('../assets/images/hzmb.jpg') no-repeat center center;
            background-size: cover;
            padding: 120px 0 60px;
            color: white;
            text-align: center;
        }

        .privacy-section {
            padding: 80px 0;
        }

        .privacy-content {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .privacy-content h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
        }

        .privacy-content h3 {
            color: var(--primary-color);
            margin: 40px 0 20px;
        }

        .privacy-content p {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .privacy-content ul {
            margin-bottom: 20px;
            padding-left: 20px;
        }

        .privacy-content li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .footer {
            background: white;
            padding: 60px 0 30px;
            border-top: 1px solid #eee;
        }

        .footer-logo {
            height: 40px;
            margin-bottom: 20px;
            object-fit: contain;
        }

        .footer-text {
            color: #666;
            margin-bottom: 20px;
        }

        .footer h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .footer ul {
            list-style: none;
            padding: 0;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer ul li a:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../home.php">
                <img src="../assets/logo.png" alt="CrossBorder Insurance" class="brand-logo">
                <span class="d-none d-md-inline">CrossBorder Insurance</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="../home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-login" href="../login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Privacy Policy</h1>
            <p class="lead">Last updated: March 2025</p>
        </div>
    </section>

    <!-- Privacy Content -->
    <section class="privacy-section">
        <div class="container">
            <div class="privacy-content">
                <h2>Introduction</h2>
                <p>At CrossBorder Insurance, we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, and safeguard your data when you use our services.</p>

                <h3>Information We Collect</h3>
                <p>We collect various types of information to provide and improve our services:</p>
                <ul>
                    <li>Personal identification information (name, email address, phone number)</li>
                    <li>Vehicle information (registration number, make, model)</li>
                    <li>Insurance policy details</li>
                    <li>Payment information</li>
                    <li>Usage data and cookies</li>
                </ul>

                <h3>How We Use Your Information</h3>
                <p>We use the collected information for various purposes:</p>
                <ul>
                    <li>To provide and maintain our services</li>
                    <li>To process your insurance applications and claims</li>
                    <li>To communicate with you about your policy</li>
                    <li>To improve our services and customer experience</li>
                    <li>To comply with legal obligations</li>
                </ul>

                <h3>Data Security</h3>
                <p>We implement appropriate security measures to protect your personal information:</p>
                <ul>
                    <li>Encryption of sensitive data</li>
                    <li>Regular security assessments</li>
                    <li>Access controls and authentication</li>
                    <li>Secure data storage and transmission</li>
                </ul>

                <h3>Data Sharing</h3>
                <p>We may share your information with:</p>
                <ul>
                    <li>Insurance partners and underwriters</li>
                    <li>Service providers and contractors</li>
                    <li>Legal authorities when required by law</li>
                </ul>

                <h3>Your Rights</h3>
                <p>You have the right to:</p>
                <ul>
                    <li>Access your personal information</li>
                    <li>Correct inaccurate data</li>
                    <li>Request deletion of your data</li>
                    <li>Opt-out of marketing communications</li>
                </ul>

                <h3>Cookies and Tracking</h3>
                <p>We use cookies and similar tracking technologies to:</p>
                <ul>
                    <li>Remember your preferences</li>
                    <li>Analyze website usage</li>
                    <li>Improve our services</li>
                </ul>

                <h3>Changes to This Policy</h3>
                <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date.</p>

                <h3>Contact Us</h3>
                <p>If you have any questions about this Privacy Policy, please contact us:</p>
                <ul>
                    <li>Email: info@crossborder.com</li>
                    <li>Phone: +853 1234 5678</li>
                    <li>Address: NAPE, Macau</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <img src="../assets/logo.png" alt="CrossBorder Insurance Logo" class="footer-logo">
                    <p class="footer-text">Your trusted partner for cross-border vehicle insurance between Hong Kong and Guangdong Province.</p>
                </div>
                <div class="col-md-2">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="../home.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="product.php">Products</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Our Services</h5>
                    <ul>
                        <li><a href="product.php">Compulsory Insurance</a></li>
                        <li><a href="product.php">Commercial Insurance</a></li>
                        <li><a href="claim_guide.php">Make a Claim</a></li>
                        <li><a href="policy_query.php">Policy Management</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <ul>
                        
                        <li><i class="fas fa-phone me-2"></i> Macau: +853 1234 5678</li>
                        <li><i class="fas fa-envelope me-2"></i> info@crossborder.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> NAPE, Macau</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 CrossBorder Insurance. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="privacy.php" class="text-decoration-none text-muted me-3">Privacy Policy</a>
                    <a href="terms.php" class="text-decoration-none text-muted">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>