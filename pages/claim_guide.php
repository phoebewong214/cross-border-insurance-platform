<?php
session_start();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claims Guide - Insurance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .claim-guide-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            color: #01459C;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .back-button {
            background-color: #01459C;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: #0056bc;
            transform: translateY(-1px);
        }

        .claim-step {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #01459C;
        }

        .claim-step h5 {
            color: #01459C;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .claim-step p {
            color: #666;
            margin-bottom: 0.5rem;
        }

        .document-list {
            list-style-type: none;
            padding-left: 0;
        }

        .document-list li {
            padding: 0.5rem 0;
            color: #666;
            display: flex;
            align-items: center;
        }

        .document-list li i {
            color: #01459C;
            margin-right: 0.5rem;
        }

        .note-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }

        .contact-info {
            background: #e9f7fe;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }

        .contact-info h6 {
            color: #01459C;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .contact-info p {
            color: #666;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Insurance System</a>
            <div class="ms-auto">
                <a href="../index.php" class="back-button">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="claim-guide-container">
            <h2 class="section-title">Claims Guide</h2>

            <div class="claim-step">
                <h5>1. Submit a Claim</h5>
                <p>In case of an accident, please immediately call our 24/7 claims hotline: +853 1234 5678</p>
                <p>Our claims specialists will provide immediate assistance and guide you through the claims process.</p>
            </div>

            <div class="claim-step">
                <h5>2. Required Documents</h5>
                <ul class="document-list">
                    <li><i class="fas fa-check-circle"></i> Identification documents (original and copy)</li>
                    <li><i class="fas fa-check-circle"></i> Policy number and original policy document</li>
                    <li><i class="fas fa-check-circle"></i> Accident report (e.g., police report)</li>
                    <li><i class="fas fa-check-circle"></i> Medical reports (if applicable)</li>
                    <li><i class="fas fa-check-circle"></i> Repair quotation or receipt</li>
                    <li><i class="fas fa-check-circle"></i> Bank account information (for claim payment transfer)</li>
                </ul>
            </div>

            <div class="claim-step">
                <h5>3. Claims Process</h5>
                <p>1. After submitting complete claim documents, our claims team will review your case</p>
                <p>2. Upon approval, we will calculate the claim amount according to your policy terms</p>
                <p>3. The claim payment will be directly transferred to your designated bank account</p>
            </div>

            <div class="note-box">
                <h6><i class="fas fa-exclamation-circle me-2"></i>Important Notes</h6>
                <p>• Please notify us within 24 hours of the accident</p>
                <p>• All documents must be clear and legible</p>
                <p>• Claims should be submitted within 30 days of the accident</p>
                <p>• For any questions, please contact our customer service team</p>
            </div>

            <div class="contact-info">
                <h6><i class="fas fa-headset me-2"></i>Contact Us</h6>
                <p><i class="fas fa-phone me-2"></i>Claims Hotline: +853 2800 0000</p>
                <p><i class="fas fa-envelope me-2"></i>Email: claims@crossborder.com</p>
                <p><i class="fas fa-clock me-2"></i>Service Hours: 24/7</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>