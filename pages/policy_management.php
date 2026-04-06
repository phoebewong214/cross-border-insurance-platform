<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Mulish', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 2rem;
        }

        .back-button {
            position: fixed;
            top: 1.5rem;
            left: 2rem;
            padding: 0.5rem 1rem;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            color: #01459C;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-button:hover {
            background-color: #f8f9fa;
            color: #013579;
            text-decoration: none;
        }

        .section-title {
            color: #01459C;
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
        }

        .policy-container {
            max-width: 1200px;
            margin: 4rem auto 2rem;
        }

        .policy-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .policy-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .policy-body {
            padding: 1.5rem;
        }

        .policy-footer {
            padding: 1.5rem;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-pending-renewal {
            background-color: #fff3cd;
            color: #856404;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .info-label {
            width: 150px;
            font-weight: 600;
            color: #6c757d;
        }

        .info-value {
            flex: 1;
            color: #212529;
        }

        .btn-action {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-view {
            background-color: #01459C;
            color: white;
            border: none;
        }

        .btn-view:hover {
            background-color: #013579;
            color: white;
        }

        .btn-renew {
            background-color: #28a745;
            color: white;
            border: none;
        }

        .btn-renew:hover {
            background-color: #218838;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <a href="../index.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
        Back to Personal Center
    </a>

    <div class="policy-container">
        <h1 class="section-title">Policy Management</h1>
        <div id="policyList">
            <!-- Policies will be loaded here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadPolicies();
        });

        function loadPolicies() {
            fetch('process_policy.php?action=get_policies')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('policyList');

                    if (!data.policies || data.policies.length === 0) {
                        container.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>No active policies found</p>
                            </div>
                        `;
                        return;
                    }

                    container.innerHTML = '';
                    data.policies.forEach(policy => {
                        const endDate = new Date(policy.insurance_end);
                        const today = new Date();
                        const daysUntilExpiry = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));

                        let statusClass = 'status-active';
                        let statusText = 'Active';

                        if (endDate < today) {
                            statusClass = 'status-expired';
                            statusText = 'Expired';
                        } else if (daysUntilExpiry <= 30) {
                            statusClass = 'status-pending-renewal';
                            statusText = 'Renewal Due';
                        }

                        const card = document.createElement('div');
                        card.className = 'policy-card';
                        card.innerHTML = `
                            <div class="policy-header">
                                <div>
                                    <h5 class="mb-0">Policy #${policy.id}</h5>
                                    <small class="text-muted">Issued on ${new Date(policy.created_at).toLocaleDateString()}</small>
                                </div>
                                <span class="status-badge ${statusClass}">${statusText}</span>
                            </div>
                            <div class="policy-body">
                                <div class="info-row">
                                    <div class="info-label">Product Type:</div>
                                    <div class="info-value">${policy.product_type}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Vehicle:</div>
                                    <div class="info-value">${policy.vehicle_id}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Coverage Period:</div>
                                    <div class="info-value">${new Date(policy.insurance_start).toLocaleDateString()} - ${new Date(policy.insurance_end).toLocaleDateString()}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Premium:</div>
                                    <div class="info-value">MOP ${policy.premium}</div>
                                </div>
                            </div>
                            <div class="policy-footer">
                                <div>
                                    ${daysUntilExpiry <= 30 && daysUntilExpiry > 0 ? 
                                        `<small class="text-warning">
                                            <i class="fas fa-exclamation-circle"></i> 
                                            Policy expires in ${daysUntilExpiry} days
                                        </small>` : 
                                        endDate < today ?
                                        `<small class="text-danger">
                                            <i class="fas fa-exclamation-circle"></i> 
                                            Policy expired
                                        </small>` : 
                                        `<small class="text-success">
                                            <i class="fas fa-check-circle"></i> 
                                            Policy active
                                        </small>`
                                    }
                                </div>
                                <div>
                                    <button class="btn btn-action btn-view me-2" onclick="viewPolicy(${policy.id})">
                                        View Policy
                                    </button>
                                    ${daysUntilExpiry <= 30 && daysUntilExpiry > 0 ? 
                                        `<button class="btn btn-action btn-renew" onclick="renewPolicy(${policy.id})">
                                            Renew Policy
                                        </button>` : ''
                                    }
                                </div>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('policyList').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Failed to load policies. Please try again later.</p>
                        </div>
                    `;
                });
        }

        function viewPolicy(policyId) {
            window.location.href = `view_policy.php?id=${policyId}`;
        }

        function renewPolicy(policyId) {
            window.location.href = `renew_policy.php?id=${policyId}`;
        }
    </script>
</body>

</html>