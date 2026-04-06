<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate Quotations - Insurance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: 700;
            color: #01459C !important;
        }
        .page-header {
            background: linear-gradient(135deg, #01459C, #0056bc);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .quotation-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #01459C;
            font-weight: 600;
        }
        .navbar .admin-text {
            color: #1a237e;  /* 深蓝色 */
            font-weight: 700;
        }

        .navbar .btn-light {
            color: #2c3e50;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .navbar .btn-light:hover {
            background-color: #f8f9fa;
            color: #1a237e;
            transform: translateY(-1px);
        }

        .navbar .btn-light i {
            margin-right: 0.5rem;
        }

        .page-header .btn-light {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .page-header .btn-light:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-2px);
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
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <div class="navbar-brand admin-text">Admin Center</div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="h2">Validate Quotations</h1>
            <p class="mb-0">Review and process pending insurance quotations</p>
            <a href="../admin.php" class="btn btn-light mt-3 animate-on-scroll">
                <i class="fas fa-arrow-left me-2"></i>Back to Admin Center
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <div class="quotation-table p-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Review ID</th>
                            <th>Party No</th>
                            <th>Registration No</th>
                            <th>Generate Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="quotationTableBody">
                        <?php
                        require_once 'db_config.php';

                        try {
                            $stmt = $pdo->query("SELECT Pending_review_ID, Party_No, Registration_No, Generate_Time, Admin_Comment FROM pending_review ORDER BY Generate_Time ASC");
                            $hasData = false;
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $hasData = true;
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['Pending_review_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Party_No']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Registration_No']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Generate_Time']) . "</td>";
                                echo "<td>";
                                // Add status badge
                                if ($row['Admin_Comment'] === null) {
                                    echo "<span class='badge bg-warning text-dark me-2'>Waiting for Approve</span>";
                                    echo "<button class='btn btn-sm' style='background-color: #4e73df; color: white;' 
                                        data-bs-toggle='tooltip' 
                                        data-bs-placement='top' 
                                        title='Click to review this policy'
                                        onclick='approveQuotation(\"" . 
                                        htmlspecialchars($row['Pending_review_ID']) . "\", \"" . 
                                        htmlspecialchars($row['Party_No']) . "\", \"" . 
                                        htmlspecialchars($row['Registration_No']) . "\")'>";
                                    echo "Approve/Reject</button>";
                                } else {
                                    echo "<span class='badge bg-danger me-2'>Rejected</span>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            
                            if (!$hasData) {
                                echo "<tr><td colspan='5' class='text-center p-4'><h4 class='text-muted'>No pending review policy</h4></td></tr>";
                            }
                            
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='5' class='text-center text-danger'>Error: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function approveQuotation(quotationId, partyNo, registrationNo) {
            window.location.href = `approve_policy.php?id=${quotationId}&party_no=${partyNo}&registration_no=${registrationNo}`;
        }

        function rejectQuotation(quotationId) {
            if (confirm('Are you sure you want to reject this quotation?')) {
                processQuotation(quotationId, 'reject');
            }
        }

        function processQuotation(quotationId, action) {
            fetch('process_quotation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `quotation_id=${quotationId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the request.');
            });
        }

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

        window.addEventListener('load', checkScroll);
        window.addEventListener('scroll', checkScroll);

        // 初始化所有的 tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html> 