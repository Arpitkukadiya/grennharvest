<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

$stmt = $conn->prepare("SELECT fv.id, fv.date, fv.description, fv.status, f.name AS farmer_name 
                        FROM farm_visits fv 
                        JOIN farmers f ON fv.farmer_id = f.id 
                        WHERE fv.customer_id = ? 
                        ORDER BY fv.date DESC");
$stmt->execute([$customer_id]);
$visits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farm Visit History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #e0f7fa, #f0f4f8);
            font-family: 'Segoe UI', sans-serif;
        }

        h2 {
            font-weight: 700;
            color: #2c3e50;
        }

        .card {
            border-radius: 16px;
            overflow: hidden;
            transition: 0.3s ease-in-out;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #00796b;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .status-pill {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.95rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-requested {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .horizontal-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            gap: 20px;
        }

        .status-step {
            text-align: center;
            color: #999;
            font-size: 14px;
        }

        .status-step .emoji {
            font-size: 28px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
        }

        .status-step.active .emoji {
            background-color: #4caf50;
            color: white;
            animation: pulse 1.8s infinite;
            box-shadow: 0 0 15px rgba(76, 175, 80, 0.6);
        }

        .status-step.rejected.active .emoji {
            background-color: #e53935;
            animation: blink 1s infinite;
            box-shadow: 0 0 15px rgba(229, 57, 53, 0.6);
        }

        .status-step.active label {
            font-weight: 600;
            color: #333;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4 px-3">
    <h2 class="text-center mb-4">🌾 Farm Visit Request History</h2>

    <?php if (count($visits) > 0): ?>
        <div class="row">
            <?php foreach ($visits as $visit): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            👨‍🌾 Farmer: <?= htmlspecialchars($visit['farmer_name']); ?>
                        </div>
                        <div class="card-body">
                            <p><strong>Visit Date:</strong> <?= htmlspecialchars($visit['date']); ?></p>
                            <p><strong>Purpose:</strong> <?= htmlspecialchars($visit['description']); ?></p>
                            <p><strong>Status:</strong>
                                <?php if ($visit['status'] === 'requested'): ?>
                                    <span class="status-pill status-requested">Requested</span>
                                <?php elseif ($visit['status'] === 'approved'): ?>
                                    <span class="status-pill status-approved">Approved</span>
                                <?php elseif ($visit['status'] === 'rejected'): ?>
                                    <span class="status-pill status-rejected">Rejected</span>
                                <?php endif; ?>
                            </p>

                            <div class="horizontal-status">
                                <div class="status-step <?= $visit['status'] === 'requested' ? 'active' : '' ?>">
                                    <div class="emoji">📩</div>
                                    <label>Requested</label>
                                </div>
                                <div class="status-step <?= $visit['status'] === 'approved' ? 'active' : '' ?>">
                                    <div class="emoji">✅</div>
                                    <label>Approved</label>
                                </div>
                                <div class="status-step <?= $visit['status'] === 'rejected' ? 'rejected active' : '' ?>">
                                    <div class="emoji">❌</div>
                                    <label>Rejected</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center text-muted">
            <h4>No farm visit requests found.</h4>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
