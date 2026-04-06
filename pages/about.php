<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - CrossBorder Insurance</title>
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

        .about-section {
            padding: 80px 0;
        }

        .mission-vision {
            background: var(--light-bg);
            padding: 80px 0;
        }

        .values-section {
            padding: 80px 0;
        }

        .value-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
            text-align: center;
            transition: all 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .value-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .team-section {
            margin: 60px 0;
            text-align: center;
        }

        .team-section h2 {
            color: var(--primary-color);
            margin-bottom: 40px;
        }

        .team-scroll-wrapper {
            position: relative;
            width: 100%;
            padding: 0 50px;
        }

        .team-scroll-container {
            width: 100%;
            overflow-x: auto;
            padding: 20px 0;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .team-scroll-container::-webkit-scrollbar {
            display: none;
        }

        .scroll-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .scroll-button:hover {
            background: var(--secondary-color);
            transform: translateY(-50%) scale(1.1);
        }

        .scroll-button.left {
            left: 0;
        }

        .scroll-button.right {
            right: 0;
        }

        .scroll-button i {
            font-size: 1.2rem;
        }

        .team-cards {
            display: flex;
            gap: 20px;
            padding: 10px;
            min-width: max-content;
        }

        .team-card {
            flex: 0 0 300px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-5px);
        }

        .team-card img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }

        .team-card h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .team-card p {
            color: #666;
            margin-bottom: 0;
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
            <h1>About CrossBorder Insurance</h1>
            <p class="lead">Your Trusted Partner in Cross-Border Vehicle Insurance</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="lead mb-4">Founded in 2025, CrossBorder Insurance has been at the forefront of providing specialized insurance solutions for vehicles traveling between Hong Kong and Guangdong Province.</p>
                    <p>With the opening of the Hong Kong-Zhuhai-Macau Bridge, we recognized the growing need for comprehensive insurance coverage that specifically addresses the unique challenges of cross-border travel. Our team of experienced insurance professionals is dedicated to providing tailored solutions that meet the specific needs of our clients.</p>
                </div>
                <div class="col-lg-6">
                    <img src="../assets/images/hzmb.jpg" alt="Hong Kong-Zhuhai-Macau Bridge" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-vision">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="text-primary mb-4">Our Mission</h3>
                            <p class="lead">To provide reliable and comprehensive insurance solutions that ensure safe and worry-free cross-border travel for all our clients.</p>
                            <p>We are committed to delivering exceptional service, innovative products, and peace of mind to vehicle owners traveling between Hong Kong and Guangdong Province.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="text-primary mb-4">Our Vision</h3>
                            <p class="lead">To be the leading provider of cross-border vehicle insurance solutions in the Greater Bay Area.</p>
                            <p>We aim to set the standard for excellence in cross-border insurance services, continuously innovating to meet the evolving needs of our clients and the changing landscape of cross-border travel.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values -->
    <section class="values-section">
        <div class="container">
            <h2 class="text-center mb-5">Our Core Values</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="fas fa-handshake value-icon"></i>
                        <h4>Trust</h4>
                        <p>We build long-term relationships based on trust, transparency, and reliability.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="fas fa-lightbulb value-icon"></i>
                        <h4>Innovation</h4>
                        <p>We continuously innovate to provide cutting-edge insurance solutions.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <i class="fas fa-users value-icon"></i>
                        <h4>Customer Focus</h4>
                        <p>Our customers are at the heart of everything we do.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2>Our Leadership Team</h2>
            <div class="team-scroll-wrapper">
                <button class="scroll-button left" onclick="scrollTeam('left')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="scroll-button right" onclick="scrollTeam('right')">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="team-scroll-container">
                    <div class="team-cards">
                        <div class="team-card">
                            <img src="../assets/images/1.jpg" alt="Team Member">
                            <h4>Alvin Ho</h4>
                            <p>Chief Executive Officer</p>
                        </div>
                        <div class="team-card">
                            <img src="../assets/images/2.jpg" alt="Team Member">
                            <h4>Phoebe Wong</h4>
                            <p>Chief Operations Officer</p>
                        </div>
                        <div class="team-card">
                            <img src="../assets/images/3.jpg" alt="Team Member">
                            <h4>Terrence Zhang</h4>
                            <p>Chief Technology Officer</p>
                        </div>
                        <div class="team-card">
                            <img src="../assets/images/4.jpg" alt="Team Member">
                            <h4>Dorothy Chen</h4>
                            <p>Chief Financial Officer</p>
                        </div>
                        <div class="team-card">
                            <img src="../assets/images/5.jpg" alt="Team Member">
                            <h4>Moon Zhan</h4>
                            <p>Head of Claims</p>
                        </div>
                        <div class="team-card">
                            <img src="../assets/images/6.jpg" alt="Team Member">
                            <h4>Marco Qu</h4>
                            <p>Head of Underwriting</p>
                        </div>
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
    <script>
        function scrollTeam(direction) {
            const container = document.querySelector('.team-scroll-container');
            const scrollAmount = 300; // 每次滾動的距離

            if (direction === 'left') {
                container.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            } else {
                container.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            }
        }
    </script>
</body>

</html>