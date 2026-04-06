<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross-Border Insurance Solutions - Your Trusted Partner</title>
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
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .brand-logo {
            height: 40px;
            margin-right: 10px;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
        }

        .btn-get-quote {
            background: var(--primary-color);
            color: white !important;
            padding: 0.5rem 1.5rem !important;
            border-radius: 25px;
            margin-left: 1rem;
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

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
                url('assets/images/hzmb.jpg') no-repeat center center;
            background-size: cover;
            background-position: center 40%;
            display: flex;
            align-items: center;
            padding-top: 80px;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(1, 69, 156, 0.2);
            z-index: 1;
        }

        .hero-content {
            color: white;
            max-width: 600px;
            position: relative;
            z-index: 2;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .btn-hero {
            background: white;
            color: var(--primary-color);
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            color: var(--primary-color);
        }

        /* Why Us Section */
        .why-us {
            padding: 80px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
            color: var(--primary-color);
            font-weight: 700;
        }

        .feature-card {
            text-align: center;
            padding: 30px;
            margin-bottom: 30px;
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
        }

        .feature-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .feature-text {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 80px 0;
            background: var(--light-bg);
            overflow: hidden;
        }

        .testimonial-slider {
            position: relative;
            width: 100%;
        }

        .testimonial-slide {
            display: none;
            animation: fadeEffect 1s;
        }

        .testimonial-slide.active {
            display: block;
        }

        @keyframes fadeEffect {
            from {
                opacity: 0.4
            }

            to {
                opacity: 1
            }
        }

        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .author-info {
            flex: 1;
        }

        .author-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--primary-color);
        }

        .author-title {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
        }

        .testimonial-dots {
            text-align: center;
            margin-top: 30px;
        }

        .dot {
            height: 10px;
            width: 10px;
            margin: 0 5px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .dot.active {
            background-color: var(--primary-color);
        }

        /* Footer */
        .footer {
            background: white;
            padding: 60px 0 30px;
            border-top: 1px solid #eee;
        }

        .footer-logo {
            height: 40px;
            margin-bottom: 20px;
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
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/logo.png" alt="CrossBorder Insurance Logo" class="brand-logo">
                CrossBorder Insurance
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/product.php">Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-login" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Cross-Border Insurance Made Simple</h1>
                <p class="hero-subtitle">Get peace of mind knowing you're covered for your journey between Hong Kong and Guangdong Province.</p>
                <a href="./index.php" class="btn-hero">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Why Us Section -->
    <section class="why-us">
        <div class="container">
            <h2 class="section-title">Why CrossBorder Insurance?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <img src="assets/icon-protection.svg" alt="Financial Protection" class="feature-icon">
                        <h3 class="feature-title">Financial Protection</h3>
                        <p class="feature-text">Comprehensive coverage up to MOP 2,000,000 for your cross-border travels.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <img src="assets/icon-customize.svg" alt="Customizable Plans" class="feature-icon">
                        <h3 class="feature-title">Customizable Plans</h3>
                        <p class="feature-text">Choose the coverage that works best for you and your vehicle's needs.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <img src="assets/icon-quick.svg" alt="Quick Process" class="feature-icon">
                        <h3 class="feature-title">Quick Process</h3>
                        <p class="feature-text">Easy application and fast claims processing for your convenience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="testimonial-slider">
                <!-- Slide 1 -->
                <div class="testimonial-slide active">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="testimonial-card">
                                <p class="testimonial-text">"跨境保险的服务非常专业，让我在往返广东的路途中倍感安心。他们的团队反应迅速，理赔过程也很顺畅。"</p>
                                <div class="testimonial-author">
                                    <img src="assets/images/2.jpg" alt="John Wong" class="author-avatar">
                                    <div class="author-info">
                                        <p class="author-name">王志明</p>
                                        <p class="author-title">香港商人</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="testimonial-card">
                                <p class="testimonial-text">"保险方案非常灵活，可以根据自己的需求选择合适的保障。客服团队也很耐心地解答了我的所有问题。"</p>
                                <div class="testimonial-author">
                                    <img src="assets/images/1.jpg" alt="Sarah Chen" class="author-avatar">
                                    <div class="author-info">
                                        <p class="author-name">陈美玲</p>
                                        <p class="author-title">跨境物流经理</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="testimonial-slide">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="testimonial-card">
                                <p class="testimonial-text">"作为一个经常往返大湾区的商人，这个保险给了我很大的保障。特别是他们的24小时支援服务，让人非常放心。"</p>
                                <div class="testimonial-author">
                                    <img src="assets/images/3.jpg" alt="Michael Zhang" class="author-avatar">
                                    <div class="author-info">
                                        <p class="author-name">张伟明</p>
                                        <p class="author-title">科技公司CEO</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="testimonial-card">
                                <p class="testimonial-text">"价格合理，保障全面，最重要的是理赔速度快。遇到问题时，他们的处理效率令人印象深刻。"</p>
                                <div class="testimonial-author">
                                    <img src="assets/images/5.jpg" alt="Linda Li" class="author-avatar">
                                    <div class="author-info">
                                        <p class="author-name">李婷婷</p>
                                        <p class="author-title">教育顾问</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="testimonial-slide">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="testimonial-card">
                                <p class="testimonial-text">"网上投保流程非常简单，几分钟就能完成。客服团队的专业程度和服务态度都值得表扬。"</p>
                                <div class="testimonial-author">
                                    <img src="assets/images/4.jpg" alt="David Lau" class="author-avatar">
                                    <div class="author-info">
                                        <p class="author-name">刘大卫</p>
                                        <p class="author-title">金融分析师</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="testimonial-card">
                                <p class="testimonial-text">"这是我用过的最好的跨境保险服务，没有之一。他们真正理解客户的需求，提供的解决方案都很到位。"</p>
                                <div class="testimonial-author">
                                    <img src="assets/images/6.jpg" alt="Emily Wong" class="author-avatar">
                                    <div class="author-info">
                                        <p class="author-name">黄小梅</p>
                                        <p class="author-title">零售连锁店</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dots -->
                <div class="testimonial-dots">
                    <span class="dot active" onclick="currentSlide(1)"></span>
                    <span class="dot" onclick="currentSlide(2)"></span>
                    <span class="dot" onclick="currentSlide(3)"></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <img src="assets/logo.png" alt="CrossBorder Insurance Logo" class="footer-logo">
                    <p class="footer-text">Your trusted partner for cross-border vehicle insurance between Hong Kong and Guangdong Province.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="pages/about.php">About Us</a></li>
                        <li><a href="pages/product.php">Products</a></li>
                        <li><a href="pages/contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Our Services</h5>
                    <ul>
                        <li><a href="pages/product.php">Compulsory Insurance</a></li>
                        <li><a href="pages/product.php">Commercial Insurance</a></li>
                        <li><a href="pages/claim_guide.php">Claim Guide</a></li>
                        <li><a href="pages/policy_query.php">Policy Management</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <ul>
                        <li><i class="fas fa-phone me-2"></i> +853 1234 5678</li>
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
                    <a href="pages/privacy.php" class="text-decoration-none text-muted me-3">Privacy Policy</a>
                    <a href="pages/terms.php" class="text-decoration-none text-muted">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let slideIndex = 1;
        let timeoutHandle;

        // 自动轮播
        function autoSlide() {
            showSlides(slideIndex += 1);
            timeoutHandle = setTimeout(autoSlide, 10000); // 10秒后切换
        }

        // 手动切换到指定slide
        function currentSlide(n) {
            clearTimeout(timeoutHandle); // 清除之前的定时器
            showSlides(slideIndex = n);
            timeoutHandle = setTimeout(autoSlide, 10000); // 重新开始自动轮播
        }

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("testimonial-slide");
            let dots = document.getElementsByClassName("dot");

            if (n > slides.length) {
                slideIndex = 1
            }
            if (n < 1) {
                slideIndex = slides.length
            }

            // 隐藏所有slides
            for (i = 0; i < slides.length; i++) {
                slides[i].classList.remove("active");
                dots[i].classList.remove("active");
            }

            // 显示当前slide
            slides[slideIndex - 1].classList.add("active");
            dots[slideIndex - 1].classList.add("active");
        }

        // 页面加载完成后启动自动轮播
        document.addEventListener('DOMContentLoaded', function() {
            showSlides(slideIndex);
            timeoutHandle = setTimeout(autoSlide, 10000);
        });
    </script>
</body>

</html>