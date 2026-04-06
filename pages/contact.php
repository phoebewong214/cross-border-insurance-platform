<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - CrossBorder Insurance</title>
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

        .contact-section {
            padding: 80px 0;
        }

        .contact-info {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .contact-info h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .contact-info p {
            margin-bottom: 15px;
        }

        .contact-info i {
            color: var(--primary-color);
            margin-right: 10px;
            width: 20px;
        }

        .contact-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(1, 69, 156, 0.25);
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .map-container {
            border-radius: 15px;
            overflow: hidden;
            margin-top: 30px;
        }

        .map-container iframe {
            width: 100%;
            height: 300px;
            border: 0;
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
            <h1>Contact Us</h1>
            <p class="lead">Get in touch with our team</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="contact-info">
                        <h3>Contact Information</h3>
                        <p><i class="fas fa-map-marker-alt"></i> NAPE, Macau</p>
                        <p><i class="fas fa-phone"></i> +853 1234 5678</p>
                        <p><i class="fas fa-envelope"></i> info@crossborder.com</p>
                        <p><i class="fas fa-clock"></i> Monday - Friday: 9:00 AM - 6:00 PM</p>
                    </div>
                    <div class="contact-info">
                        <h3>Follow Us</h3>
                        <div class="social-links">
                            <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="me-3"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="contact-form">
                        <h3 class="mb-4">Visit Our Office</h3>
                        <div class="office-hours mb-4">
                            <h4>Office Hours</h4>
                            <p><i class="fas fa-clock me-2"></i> Monday - Friday: 9:00 AM - 6:00 PM</p>
                            
                        </div>
                        <div class="emergency-contact mb-4">
                            <h4>Emergency Contact</h4>
                            <p><i class="fas fa-phone-alt me-2"></i> 24/7 Hotline: +853 2800 0000</p>
                            <p class="text-muted small">For urgent claims and assistance outside office hours</p>
                        </div>
                    </div>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3691.857123201789!2d113.5583!3d22.1867!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3404007c8f2f3f3f%3A0x7c8f2f3f3f3f3f3f!2sMacau%20Cultural%20Centre!5e0!3m2!1sen!2smac!4v1645000000000!5m2!1sen!2smac" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
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