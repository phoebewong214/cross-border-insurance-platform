<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Products - CrossBorder Insurance</title>
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
                url('assets/images/hzmb.jpg') no-repeat center center;
            background-size: cover;
            padding: 120px 0 60px;
            color: white;
            text-align: center;
        }

        .product-section {
            padding: 80px 0;
            background: var(--light-bg);
        }

        .product-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            min-height: 60px;
            display: flex;
            align-items: center;
        }

        .product-features {
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
            min-height: 160px;
        }

        .product-features li {
            margin-bottom: 15px;
            padding-left: 25px;
            position: relative;
            line-height: 1.5;
        }

        .product-features li:before {
            content: "✓";
            color: var(--primary-color);
            position: absolute;
            left: 0;
        }

        .premium-table {
            width: 100%;
            margin-top: auto;
            margin-bottom: 30px;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }

        .premium-table th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
        }

        .premium-table td {
            padding: 15px;
            border: 1px solid #eee;
        }

        .premium-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .faq-section {
            padding: 80px 0;
            background: white;
        }

        .faq-item {
            margin-bottom: 20px;
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
        }

        .faq-question {
            background: white;
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background: #f8f9fa;
        }

        .faq-answer.active {
            padding: 20px;
            max-height: 500px;
        }

        .faq-question:after {
            content: '+';
            font-size: 20px;
            color: var(--primary-color);
        }

        .faq-item.active .faq-question:after {
            content: '-';
        }

        .btn-quote {
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }

        .btn-quote:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

        .social-links a {
            color: var(--primary-color);
            margin-right: 15px;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .footer hr {
            margin: 30px 0;
            border-color: #eee;
        }

        .footer .bottom-links a {
            color: #666;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer .bottom-links a:hover {
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
                        <a class="nav-link" href="../pages/product.php">Product</a>
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
            <h1>Cross-Border Auto Insurance Solutions</h1>
            <p class="lead">Comprehensive Protection for Your Cross-Border Journey</p>
        </div>
    </section>

    <!-- Product Section -->
    <section class="product-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="product-card">
                        <h3>Compulsory Insurance for Cross-Border Vehicles</h3>
                        <ul class="product-features">
                            <li>Suitable for vehicles traveling via Hong Kong-Zhuhai-Macau Bridge</li>
                            <li>Coverage up to ¥200,000</li>
                            <li>20% discount for annual policies</li>
                            <li>Fast claims processing service</li>
                        </ul>
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Vehicle Type</th>
                                    <th>Base Premium</th>
                                    <th>Discounted Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1-5 Seats</td>
                                    <td>¥950</td>
                                    <td>¥760</td>
                                </tr>
                                <tr>
                                    <td>6-9 Seats</td>
                                    <td>¥1,100</td>
                                    <td>¥880</td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="../login.php" class="btn-quote">Get Insured Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card">
                        <h3>Commercial Insurance Add-on Plans</h3>
                        <ul class="product-features">
                            <li>Third-party liability insurance (Additional coverage)</li>
                            <li>Passenger liability insurance</li>
                            <li>Flexible coverage options</li>
                            <li>24/7 emergency assistance service</li>
                        </ul>
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Coverage Type</th>
                                    <th>Sum Insured Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Third-party Liability</td>
                                    <td>¥1,000,000 - ¥5,000,000</td>
                                </tr>
                                <tr>
                                    <td>Per Passenger Coverage</td>
                                    <td>¥10,000 - ¥100,000</td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="../login.php" class="btn-quote">Get Insured Now</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card">
                        <h3>Hong Kong Private Car Insurance</h3>
                        <ul class="product-features">
                            <li>Comprehensive coverage for Hong Kong private vehicles</li>
                            <li>Base Premium: HK$2,000</li>
                            <li>Base Excess: HK$6,000</li>
                            <li>Flexible loading options based on driver profile</li>
                        </ul>
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>Loading Type</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Age Loading</td>
                                    <td>15% per year</td>
                                </tr>
                                <tr>
                                    <td>Experience Loading</td>
                                    <td>25% per year</td>
                                </tr>
                                <tr>
                                    <td>MIB Loading</td>
                                    <td>3.15%</td>
                                </tr>
                            </tbody>
                        </table>
                        <a href="../login.php" class="btn-quote">Get Insured Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2 class="text-center mb-5">Frequently Asked Questions</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">What's the difference between Compulsory Insurance and Commercial Insurance?</div>
                    <div class="faq-answer">
                        Compulsory Insurance is a mandatory basic insurance with coverage up to ¥200,000. Commercial Insurance is optional additional coverage that offers higher sum insured and broader protection, including third-party liability and passenger liability insurance.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">What are the policy period and coverage period?</div>
                    <div class="faq-answer">
                        You can choose between annual or short-term policies. Annual policies come with discounted rates, while short-term policies are calculated on a daily basis. Coverage begins from the effective date until the policy expiration date.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How do I make a claim?</div>
                    <div class="faq-answer">
                        In the event of an accident, immediately call our 24-hour service hotline. Our professional team will guide you through the on-site procedures and subsequent claims process. You'll need to provide accident documentation and loss details, and we'll process your claim promptly.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Can I cancel my insurance policy?</div>
                    <div class="faq-answer">
                        Yes, you can request to cancel your policy. However, please note that cancellation may affect your benefits. We recommend careful consideration. Contact our customer service for specific cancellation rules and refund standards.
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">How do I choose the right insurance plan?</div>
                    <div class="faq-answer">
                        We recommend selecting based on your vehicle usage frequency, cross-border routes, and risk preferences. Our insurance advisors can provide professional recommendations based on your specific situation.
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
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="#" title="WeChat"><i class="fab fa-weixin"></i></a>
                    </div>
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
                        <li><a href="claim_guide.php">Make a claim</a></li>
                        <li><a href="policy_query.php">Policy Management</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <ul>
                        <li><i class="fas fa-phone me-2"></i> Macau: +853 1234 5678</li>
                        <li><i class="fas fa-envelope me-2"></i> info@crossborder.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> NAPE, Macau</li>
                        <li><i class="fas fa-clock me-2"></i> Mon-Fri: 9:00-18:00</li>
                        <li><i class="fas fa-phone-volume me-2"></i> 24/7 Claims Hotline</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 CrossBorder Insurance. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end bottom-links">
                    <a href="privacy.php" class="me-3">Privacy Policy</a>
                    <a href="terms.php" class="me-3">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                const answer = question.nextElementSibling;

                if (item.classList.contains('active')) {
                    item.classList.remove('active');
                    answer.classList.remove('active');
                } else {
                    document.querySelectorAll('.faq-item').forEach(item => {
                        item.classList.remove('active');
                        item.querySelector('.faq-answer').classList.remove('active');
                    });
                    item.classList.add('active');
                    answer.classList.add('active');
                }
            });
        });
    </script>
</body>

</html>