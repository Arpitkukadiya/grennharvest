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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #e6f0ff, #f0f4f8);
            font-family: 'Roboto', sans-serif;
        }

        .card {
            border-radius: 16px;
            transition: 0.4s ease;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(60deg, #00ff99, #00ccff, #ff99cc, #00ff99);
            animation: shine 3s linear infinite;
            z-index: 0;
            opacity: 0.1;
        }

        @keyframes shine {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .card-body, .card-header {
            position: relative;
            z-index: 2;
        }

        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
            background-color: #2c3e50;
            color: #fff;
            border-radius: 16px 16px 0 0;
        }

        .status-pill {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: bold;
            display: inline-block;
            text-transform: capitalize;
        }

        .status-requested {
            color: #ffc107;
            background-color: #fff3cd;
        }

        .status-approved {
            color: #28a745;
            background-color: #d4edda;
        }

        .status-rejected {
            color: #dc3545;
            background-color: #f8d7da;
        }

        .vertical-status {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 25px;
            gap: 16px;
        }

        .status-step {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .status-step .emoji {
            font-size: 42px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
            box-shadow: none;
        }

        .status-step.active .emoji {
            background-color: #28a745;
            color: white;
            box-shadow: 0 0 20px 6px rgba(40, 167, 69, 0.7);
            animation: pulse 1.8s infinite;
        }

        .status-step.rejected .emoji {
            background-color: #dc3545;
            color: white;
            box-shadow: 0 0 20px 6px rgba(220, 53, 69, 0.7);
            animation: blink 1s infinite;
        }

        .status-step label {
            font-size: 15px;
            margin-top: 5px;
            font-weight: 600;
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

        .text-center h2 {
            font-weight: 700;
            color: #333;
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-4 px-4">
    <h2 class="text-center mb-4">🌾 Farm Visit Request History</h2>

    <?php if (count($visits) > 0): ?>
        <div class="row">
            <?php foreach ($visits as $visit): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
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

                            <!-- Enhanced Vertical Emoji Tracker -->
                            <div class="vertical-status">
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
        <div class="text-center">
            <h4 class="text-muted">No farm visit requests found.</h4>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
