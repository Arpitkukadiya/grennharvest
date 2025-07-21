<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];
$stmt = $conn->prepare("
    SELECT 
        o.id AS order_id,
        o.total_price,
        o.status,
        o.payment_method,
        o.order_date,
        f.rating,
        f.comment,
        f.farmer_id,
        p.status AS payment_status,
        COALESCE(
            -- Prefer multiple items if exist
            (
                SELECT GROUP_CONCAT(CONCAT(c2.name, ' (', oi2.quantity, 'kg)') SEPARATOR ', ')
                FROM order_items oi2
                JOIN crops c2 ON oi2.crop_id = c2.id
                WHERE oi2.order_id = o.id
            ),
            -- Else fallback to single crop from orders table
            CONCAT(c.name, ' (', o.quantity, 'kg)')
        ) AS items_summary
    FROM orders o
    LEFT JOIN crops c ON o.crop_id = c.id
    LEFT JOIN feedback f ON o.id = f.order_id
    LEFT JOIN payments p ON o.id = p.order_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");

$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .order-card {
            background: #fff;
            padding: 20px;
            height: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
        }

        .badge {
            font-size: 0.85rem;
        }

        .star-display {
            color: gold;
            font-size: 1.3rem;
        }

        .btn-feedback {
            background-color: #28a745;
            color: #fff;
        }

        .btn-feedback:hover {
            background-color: #218838;
        }

        /* Updated Progress Tracker */
        .order-tracking-wrapper {
            margin-top: 20px;
            padding: 0 10px;
        }

        .order-tracking {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-tracking::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 0;
            right: 0;
            height: 6px;
            background-color: #dee2e6;
            border-radius: 10px;
            z-index: 0;
        }

        .progress-bar-fill {
            position: absolute;
            top: 24px;
            left: 0;
            height: 6px;
            background-color: #28a745;
            border-radius: 10px;
            z-index: 1;
            transition: width 0.6s ease;
        }

        .step {
            text-align: center;
            z-index: 2;
            flex: 1;
            position: relative;
        }

        .step .icon {
            width: 45px;
            height: 45px;
            line-height: 45px;
            margin: 0 auto;
            border-radius: 50%;
            background-color: #ccc;
            color: white;
            font-size: 20px;
        }

        .step.active .icon {
            background-color: #28a745;
        }

        .step small {
            display: block;
            margin-top: 6px;
            font-size: 13px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: gold;
        }

        .btn-feedback {
    background-color: rgba(25, 226, 52, 1);
    color: white;
    font-weight: bold;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    box-shadow: 0 4px 10px rgba(31, 230, 54, 0.3);
}

.btn-feedback::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(120deg, rgba(255,255,255,0.2), rgba(255,255,255,0));
    transition: left 0.5s ease;
}

.btn-feedback:hover::before {
    left: 100%;
}

.btn-feedback:hover {
    background-color: rgba(109, 189, 34, 1);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px hsla(130, 69%, 66%, 0.50);
}

    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4 text-dark">Your Order History</h2>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($orders as $order): ?>
            <div class="col">
                <div class="order-card d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold">Items:</h5>
                         <p><?= htmlspecialchars($order['items_summary']); ?></p>
                        <p><strong>Total Price:</strong> ₹<?= $order['total_price']; ?></p>
                        <p><strong>Order Date:</strong> <?= $order['order_date']; ?></p>
                        <p><strong>Payment:</strong> <?= strtoupper($order['payment_method']); ?> 
                            <?php if ($order['payment_method'] === 'online'): ?>
                                <?php if ($order['payment_status'] === 'success'): ?>
                                    <span class="badge bg-success">Success</span>
                                <?php elseif ($order['payment_status'] === 'failed'): ?>
                                    <span class="badge bg-success">success</span>
                                <?php else: ?>
                                    <span class="badge bg-success">success</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">COD</span>
                            <?php endif; ?>
                        </p>

                        <!-- Progress Tracker -->
                        <?php
                            $statuses = ['pending', 'confirmed', 'shipped', 'delivered'];
                            $currentIndex = array_search($order['status'], $statuses);
                            $stepCount = count($statuses) - 1;
                            $stepWidth = 100 / $stepCount;
                            $fillWidth = $currentIndex * $stepWidth;
                        ?>
                        <div class="order-tracking-wrapper">
                            <div class="order-tracking">
                                <div class="progress-bar-fill" style="width: <?= $fillWidth ?>%;"></div>

                                <div class="step <?= $currentIndex >= 0 ? 'active' : '' ?>">
                                    <div class="icon">🕒</div>
                                    <small>Pending</small>
                                </div>
                                <div class="step <?= $currentIndex >= 1 ? 'active' : '' ?>">
                                    <div class="icon">✅</div>
                                    <small>Confirmed</small>
                                </div>
                                <div class="step <?= $currentIndex >= 2 ? 'active' : '' ?>">
                                    <div class="icon">🚚</div>
                                    <small>Shipped</small>
                                </div>
                                <div class="step <?= $currentIndex >= 3 ? 'active' : '' ?>">
                                    <div class="icon">📦</div>
                                    <small>Delivered</small>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($order['rating'])): ?>
                            <p class="mt-3"><strong>Your Rating:</strong>
                                <span class="star-display">
                                    <?= str_repeat("★", $order['rating']) . str_repeat("☆", 5 - $order['rating']); ?>
                                </span>
                            </p>
                        <?php else: ?>
                           <button class="btn btn-feedback btn-sm mt-2"
    data-bs-toggle="modal" 
    data-bs-target="#feedbackModal" 
    data-order-id="<?= $order['order_id']; ?>"
    data-crop-name="<?= htmlspecialchars($order['items_summary']); ?>">
    Give Feedback
</button>

                        <?php endif; ?>

                      
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($orders)): ?>
            <div class="col-12 text-center">
                <p>No orders found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="submit_feedback.php">
                <div class="modal-header">
                    <h5 class="modal-title">Give Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="modalOrderId">
                    <input type="hidden" name="rating" id="selectedRating">

                    <div class="mb-3">
                        <label>Crop Name</label>
                        <input type="text" id="modalCropName" class="form-control" disabled>
                    </div>

                    <div class="mb-3 text-center">
                        <label>Rating</label>
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="star" id="star<?= $i ?>" value="<?= $i ?>">
                                <label for="star<?= $i ?>">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Comments</label>
                        <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-feedback').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('modalOrderId').value = this.dataset.orderId;
            document.getElementById('modalCropName').value = this.dataset.cropName;
        });
    });

    document.querySelectorAll('.star-rating input').forEach(star => {
        star.addEventListener('change', function () {
            document.getElementById('selectedRating').value = this.value;
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
