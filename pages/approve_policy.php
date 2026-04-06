<?php
session_start(); // Add session start to ensure user is logged in

// Only set error handling and response headers when processing AJAX requests
if (isset($_POST['action']) && $_POST['action'] == 'send_reject_email') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    header('Content-Type: application/json');
    ob_clean();
}

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require_once 'db_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Add custom SMTP class
class CustomSMTP extends SMTP {
    public function hello($host = '')
    {
        if ($this->sendHello('EHLO', $host)) {
            return true;
        }
        if (substr($this->helo_rply, 0, 3) == '421') {
            return false;
        }
        return $this->sendHello('HELO', $host);
    }
}

// Add custom PHPMailer class
class CustomPHPMailer extends PHPMailer {
    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new CustomSMTP;
        }
        return $this->smtp;
    }
}

// Modify the function for handling rejection email sending
if (isset($_POST['action']) && $_POST['action'] == 'send_reject_email') {
    try {
        if (!isset($_POST['quotation_id']) || empty($_POST['quotation_id'])) {
            throw new Exception('Quotation ID is required');
        }

        if (!isset($_POST['reason']) || empty($_POST['reason'])) {
            throw new Exception('Rejection reason is required');
        }

        // Step 1: Confirm the received quotation_id
        $response = [
            'debug_steps' => [],
            'success' => false,
            'message' => '',
            'error_details' => []
        ];
        
        $response['debug_steps'][] = [
            'step' => 1,
            'message' => 'Received quotation ID and reason',
            'quotation_id' => $_POST['quotation_id'],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // Step 2: Check the pending_review table
        try {
            // Modify query to get more information
            $check_stmt = $pdo->prepare("
                SELECT pr.User_ID, p.Name 
                FROM pending_review pr 
                JOIN party p ON pr.Party_No = p.Party_No 
                WHERE pr.Pending_review_ID = ?
            ");
            $check_stmt->execute([$_POST['quotation_id']]);
            $user_info = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user_info) {
                // If record not found, return specific error message
                $response['success'] = false;
                $response['message'] = 'Quotation not found or already processed';
                echo json_encode($response);
                exit;
            }

            $response['debug_steps'][] = [
                'step' => 2,
                'message' => 'Found user information in pending_review and party tables',
                'user_info' => [
                    'user_id' => $user_info['User_ID'],
                    'name' => $user_info['Name']
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Get email from user table
            $email_stmt = $pdo->prepare("SELECT Email FROM user WHERE User_ID = ?");
            $email_stmt->execute([$user_info['User_ID']]);
            $userEmail = $email_stmt->fetchColumn();
            
            $response['debug_steps'][] = [
                'step' => '2.1',
                'message' => 'Querying email from user table',
                'user_id' => $user_info['User_ID'],
                'email_found' => !empty($userEmail),
                'email' => $userEmail ?? 'Not found in user table',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if (!$userEmail) {
                throw new Exception('User email not found in user table');
            }

            $response['debug_steps'][] = [
                'step' => 3,
                'message' => 'Found user email',
                'email' => $userEmail,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Step 4: Send email
            try {
                $mail = new CustomPHPMailer(true);
                $mail->SMTPDebug = 2;  // Enable verbose debug output
                $mail->Debugoutput = function($str, $level) use (&$response) {
                    $response['debug_steps'][] = [
                        'step' => '4.' . $level,
                        'message' => 'SMTP Debug: ' . $str,
                        'timestamp' => date('Y-m-d H:i:s')
                    ];
                };
                
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'dorocrh13@gmail.com';
                $mail->Password = 'cmmvzladstrdcjdn';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
                
                $mail->CharSet = 'UTF-8';
                $mail->setFrom('dorocrh13@gmail.com', 'Insurance System');
                $mail->addAddress($userEmail);
                $mail->isHTML(true);
                $mail->Subject = 'Insurance Policy Application Rejected';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; padding: 20px;'>
                        <h2 style='color: #1a237e;'>Policy Application Status Update</h2>
                        <p>Dear " . htmlspecialchars($user_info['Name']) . ",</p>
                        <p>We regret to inform you that your insurance policy application has been rejected.</p>
                        <p><strong>Reason for rejection:</strong></p>
                        <p style='padding: 15px; background-color: #f8f9fa; border-left: 4px solid #dc3545;'>
                            {$_POST['reason']}
                        </p>
                        <p>If you have any questions, please contact our support team.</p>
                        <p>Best regards,<br>Insurance System Team</p>
                    </div>";

                $mail->send();
                
                $response['success'] = true;
                $response['message'] = 'Email sent successfully';
                $response['debug_steps'][] = [
                    'step' => 4,
                    'message' => 'Email sent successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } catch (Exception $e) {
                throw new Exception('Failed to send email: ' . $e->getMessage());
            }

        } catch (PDOException $e) {
            throw new Exception('Database error: ' . $e->getMessage());
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        $response['error_details'] = [
            'error_message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Policy - Insurance Management System</title>
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
        .policy-details {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }
        .btn-confirm {
            background-color: #28a745;
            color: white;
            padding: 10px 30px;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 30px;
        }
        /* Error modal styles */
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 50px;
        }

        .timeline-badge {
            position: absolute;
            left: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            color: white;
        }

        .timeline-content {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }

        .timeline-content h6 {
            margin: 0 0 10px;
            color: #007bff;
        }

        .timeline-content p {
            margin: 0;
        }

        .timeline-content small {
            display: block;
            margin-top: 10px;
        }

        #errorDetailsModal .modal-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        #errorDetailsModal .alert {
            margin-bottom: 15px;
            border-radius: 4px;
        }

        #errorDetailsModal pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }

        #errorDetailsModal .border-start {
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../admin.php">Admin Center</a>
            <div class="ms-auto">
                <a href="validate_policy.php" class="nav-link d-inline-block">Back to Validation</a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="h2">Approve Policy</h1>
            <p class="mb-0">Review and confirm policy details</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <div class="policy-details">
            <?php
            require_once 'db_config.php';

            // Get URL parameters
            $quotationId = isset($_GET['id']) ? $_GET['id'] : '';
            $partyNo = isset($_GET['party_no']) ? $_GET['party_no'] : '';
            $registrationNo = isset($_GET['registration_no']) ? $_GET['registration_no'] : '';

            if (!empty($quotationId)) {
                try {
                    // Query policy and customer information
                    $stmt = $pdo->prepare("SELECT pr.*, 
                        p.Name, p.ID_No, p.Date_of_Birth, p.Type, p.DL_No, 
                        p.Date_of_B_class, p.Date_of_C_class, p.ID_Image_file_front, 
                        p.DL_image_file_front, p.DL_image_file_back, p.Total_Claim_Amount, 
                        p.Total_Contributed_Premium, p.User_ID, p.Party_No,
                        c.Car_Make_and_Model, c.Seats, c.Date_of_Registration, c.Chasis_No,
                        c.Owner1_ID_No, c.Owner2_ID_No, c.Owner3_ID_No,
                        c.Ownership_Registration_Certificate_Image_file,
                        c.Vehicle_Registration_Card_Image_file,
                        pol.Policy_Status as Status,
                        pr.Registration_No,
                        u.discount as Discount_Rate
                        FROM pending_review pr 
                        JOIN party p ON pr.Party_No = p.Party_No 
                        LEFT JOIN car c ON pr.Registration_No = c.Registration_No
                        LEFT JOIN policy pol ON pr.Pending_review_ID = pol.Policy_No
                        LEFT JOIN user u ON p.User_ID = u.User_ID
                        WHERE pr.Pending_review_ID = ?");
                    $stmt->execute([$quotationId]);
                    $policy = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($policy) {
                        error_log("Policy data retrieved - Party_No: " . $policy['Party_No']);
                        ?>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <h3 class="h4 mb-4">Policy Information</h3>
                                <p><strong>Review ID:</strong> <?php echo htmlspecialchars($quotationId); ?></p>
                                <p><strong>Party No:</strong> <?php echo htmlspecialchars($partyNo); ?></p>
                                <p><strong>Registration No:</strong> <?php echo htmlspecialchars($registrationNo); ?></p>
                                <p><strong>Generate Time:</strong> <?php echo htmlspecialchars($policy['Generate_Time']); ?></p>
                                <p><strong>Discount Rate:</strong> <?php echo isset($policy['Discount_Rate']) ? htmlspecialchars($policy['Discount_Rate']) . '%' : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="h4 mb-4">Customer Information</h3>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($policy['Name']); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($policy['Type']); ?></p>
                                <p><strong>ID Number:</strong> <?php echo htmlspecialchars($policy['ID_No']); ?></p>
                                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($policy['Date_of_Birth']); ?></p>
                                <p><strong>Driver License No:</strong> <?php echo htmlspecialchars($policy['DL_No']); ?></p>
                                <p><strong>B Class License Date:</strong> <?php echo htmlspecialchars($policy['Date_of_B_class']); ?></p>
                                <p><strong>C Class License Date:</strong> <?php echo htmlspecialchars($policy['Date_of_C_class']); ?></p>
                                <p><strong>Total Claim Amount:</strong> $<?php echo number_format($policy['Total_Claim_Amount'], 2); ?></p>
                                <p><strong>Total Premium:</strong> $<?php echo number_format($policy['Total_Contributed_Premium'], 2); ?></p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="h4 mb-4">Car Information</h3>
                                <p><strong>Make and Model:</strong> <?php echo htmlspecialchars($policy['Car_Make_and_Model']); ?></p>
                                <p><strong>Number of Seats:</strong> <?php echo htmlspecialchars($policy['Seats']); ?></p>
                                <p><strong>Registration Date:</strong> <?php echo htmlspecialchars($policy['Date_of_Registration']); ?></p>
                                <p><strong>Chassis Number:</strong> <?php echo htmlspecialchars($policy['Chasis_No']); ?></p>
                                <p><strong>Owner 1:</strong> <?php echo htmlspecialchars($policy['Owner1_ID_No']); ?></p>
                                <?php if ($policy['Owner2_ID_No']): ?>
                                    <p><strong>Owner 2:</strong> <?php echo htmlspecialchars($policy['Owner2_ID_No']); ?></p>
                                <?php endif; ?>
                                <?php if ($policy['Owner3_ID_No']): ?>
                                    <p><strong>Owner 3:</strong> <?php echo htmlspecialchars($policy['Owner3_ID_No']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Document Display Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="h4 mb-4">Document Information</h3>
                            </div>

                            <!-- Party Documents -->
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Party Documents</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Party No</th>
                                                        <th>Name</th>
                                                        <th>ID Card Front</th>
                                                        <th>DL Front</th>
                                                        <th>DL Back</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    try {
                                                        $stmt = $pdo->prepare("SELECT Party_No, Name, 
                                                            ID_Image_file_front, DL_image_file_front, DL_image_file_back 
                                                            FROM party 
                                                            WHERE Party_No = ?");
                                                        $stmt->execute([$policy['Party_No']]);
                                                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                                        
                                                        if ($row) {
                                                            echo "<tr>";
                                                            echo "<td>" . htmlspecialchars($row['Party_No']) . "</td>";
                                                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                                                            echo "<td>";
                                                            if ($row['ID_Image_file_front']) {
                                                                echo "<a href='display_image.php?type=id_front&id=" . htmlspecialchars($row['Party_No']) . "' 
                                                                      target='_blank' class='btn btn-sm btn-primary'>
                                                                      <i class='fas fa-image me-1'></i>View
                                                                      </a>";
                                                            } else {
                                                                echo "<span class='text-muted'>Not available</span>";
                                                            }
                                                            echo "</td>";
                                                            echo "<td>";
                                                            if ($row['DL_image_file_front']) {
                                                                echo "<a href='display_image.php?type=dl_front&id=" . htmlspecialchars($row['Party_No']) . "' 
                                                                      target='_blank' class='btn btn-sm btn-primary'>
                                                                      <i class='fas fa-image me-1'></i>View
                                                                      </a>";
                                                            } else {
                                                                echo "<span class='text-muted'>Not available</span>";
                                                            }
                                                            echo "</td>";
                                                            echo "<td>";
                                                            if ($row['DL_image_file_back']) {
                                                                echo "<a href='display_image.php?type=dl_back&id=" . htmlspecialchars($row['Party_No']) . "' 
                                                                      target='_blank' class='btn btn-sm btn-primary'>
                                                                      <i class='fas fa-image me-1'></i>View
                                                                      </a>";
                                                            } else {
                                                                echo "<span class='text-muted'>Not available</span>";
                                                            }
                                                            echo "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } catch (PDOException $e) {
                                                        echo "<tr><td colspan='5' class='text-danger'>Error loading party documents: " . $e->getMessage() . "</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Documents -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Vehicle Documents</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Registration No</th>
                                                        <th>Ownership Certificate</th>
                                                        <th>Vehicle Registration Card</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($policy['Registration_No']); ?></td>
                                                        <td>
                                                            <?php if ($policy['Ownership_Registration_Certificate_Image_file']): ?>
                                                                <a href="display_image.php?type=ownership_cert&id=<?php echo htmlspecialchars($policy['Registration_No']); ?>" 
                                                                   target="_blank" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-image me-1"></i>View
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not available</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($policy['Vehicle_Registration_Card_Image_file']): ?>
                                                                <a href="display_image.php?type=vehicle_reg&id=<?php echo htmlspecialchars($policy['Registration_No']); ?>" 
                                                                   target="_blank" 
                                                                   class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-image me-1"></i>View
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not available</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button class="btn btn-confirm me-3" onclick="confirmApproval('<?php echo htmlspecialchars($quotationId); ?>')">
                                <i class="fas fa-check me-2"></i>Confirm Approval
                            </button>
                            <button class="btn btn-danger me-3" onclick="confirmReject('<?php echo htmlspecialchars($quotationId); ?>')">
                                <i class="fas fa-times me-2"></i>Reject
                            </button>
                        </div>
                        <?php
                    } else {
                        echo '<div class="alert alert-info text-center">No pending review policy</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
                }
            } else {
                echo '<div class="alert alert-info text-center">No pending review policy</div>';
            }
            ?>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectReasonModalLabel">Rejection Reason</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejectReason" class="form-label">Please provide the reason for rejection:</label>
                        <textarea class="form-control" id="rejectReason" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitReject()">Submit Rejection</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Approval Modal -->
    <div class="modal fade" id="confirmApprovalModal" tabindex="-1" aria-labelledby="confirmApprovalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmApprovalModalLabel">Confirm Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to approve this policy?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="proceedWithApproval()">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentQuotationId = '';
        const rejectModal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
        const approvalModal = new bootstrap.Modal(document.getElementById('confirmApprovalModal'));

        function confirmApproval(quotationId) {
            currentQuotationId = quotationId;
            approvalModal.show();
        }

        function proceedWithApproval() {
            // Show loading state
            const submitButton = document.querySelector('#confirmApprovalModal .btn-success');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitButton.disabled = true;

            fetch('process_quotation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `quotation_id=${encodeURIComponent(currentQuotationId)}&action=approve`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = 'validate_policy.php';
                } else {
                    if (data.message === 'Quotation not found or already processed') {
                        window.location.href = 'validate_policy.php';
                    } else {
                        throw new Error(data.message || 'Error processing request');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.message === 'Quotation not found or already processed') {
                    window.location.href = 'validate_policy.php';
                } else {
                    alert('Error processing approval request: ' + error.message);
                }
            })
            .finally(() => {
                // Restore button state
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                approvalModal.hide();
            });
        }

        function confirmReject(quotationId) {
            currentQuotationId = quotationId;
            document.getElementById('rejectReason').value = '';
            rejectModal.show();
        }

        function submitReject() {
            const reason = document.getElementById('rejectReason').value.trim();
            
            if (!reason) {
                alert('Please provide a rejection reason');
                return;
            }

            // Show loading state
            const submitButton = document.querySelector('#rejectReasonModal .btn-danger');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitButton.disabled = true;

            // First send rejection email
            fetch('approve_policy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `quotation_id=${encodeURIComponent(currentQuotationId)}&action=send_reject_email&reason=${encodeURIComponent(reason)}`
            })
            .then(response => response.json())
            .then(emailData => {
                console.log('Email sending response:', emailData);
                
                if (!emailData.success) {
                    // If email sending fails, display error information
                    alert('Failed to send email: ' + emailData.message);
                    throw new Error(emailData.message || 'Failed to send email');
                }
                
                // After email is sent successfully, continue with rejection process
                return fetch('process_quotation.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `quotation_id=${encodeURIComponent(currentQuotationId)}&action=reject&reason=${encodeURIComponent(reason)}`
                });
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = 'validate_policy.php';
                } else {
                    if (data.message === 'Quotation not found or already processed') {
                        window.location.href = 'validate_policy.php';
                    } else {
                        throw new Error(data.message || 'Error processing request');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.message === 'Quotation not found or already processed') {
                    window.location.href = 'validate_policy.php';
                } else {
                    alert('Error processing rejection request: ' + error.message);
                }
            })
            .finally(() => {
                // Restore button state
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                rejectModal.hide();
            });
        }
    </script>
</body>
</html> 