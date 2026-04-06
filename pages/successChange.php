<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success - CrossBorder Insurance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <style>
        :root {
            --primary-color: #01459C;
            --secondary-color: #0056bc;
            --success-color: #4CAF50;
            --text-color: #333;
            --light-bg: #f8f9fa;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Mulish', sans-serif;
            min-height: 100vh;
            background-color: var(--light-bg);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .success-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            position: relative;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out 0.5s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            color: white;
            font-size: 50px;
        }

        h1 {
            color: var(--success-color);
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .success-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .login-btn {
            background: var(--primary-color);
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: inline-block;
            border: 2px solid var(--primary-color);
            margin-top: 30px;
        }

        .login-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>Success!</h1>
        <p class="success-message">
            <?php
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            if ($type === 'password') {
                echo "Your password has been successfully changed!<br>Please login with your new password.";
            } else {
                echo "Operation completed successfully!";
            }
            ?>
        </p>
        <a href="login.php" class="login-btn">
            <i class="fas fa-sign-in-alt me-2"></i>
            Proceed to Login
        </a>
    </div>

    <script>
        // 彩带效果
        function throwConfetti() {
            const count = 200;
            const defaults = {
                origin: { y: 0.7 },
                zIndex: 1000
            };

            function fire(particleRatio, opts) {
                confetti({
                    ...defaults,
                    ...opts,
                    particleCount: Math.floor(count * particleRatio)
                });
            }

            fire(0.25, {
                spread: 26,
                startVelocity: 55,
            });

            fire(0.2, {
                spread: 60,
            });

            fire(0.35, {
                spread: 100,
                decay: 0.91,
                scalar: 0.8
            });

            fire(0.1, {
                spread: 120,
                startVelocity: 25,
                decay: 0.92,
                scalar: 1.2
            });

            fire(0.1, {
                spread: 120,
                startVelocity: 45,
            });
        }

        // 页面加载完成后执行彩带效果
        window.addEventListener('load', function() {
            throwConfetti();
        });
    </script>
</body>
</html> 