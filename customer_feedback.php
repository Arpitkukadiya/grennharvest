<?php
session_start();
include 'config.php';

// Redirect if the customer is not logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer's orders to provide a list for feedback
$stmt = $conn->prepare("SELECT o.id, o.order_date, c.name AS crop_name FROM orders o JOIN crops c ON o.crop_id = c.id WHERE o.customer_id = ?");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll();

// Process the feedback form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['rating'], $_POST['comment'])) {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Insert the feedback into the database
    $stmt = $conn->prepare("INSERT INTO feedback (customer_id, order_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$customer_id, $order_id, $rating, $comment]);

    $message = "Thank you for your feedback!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Page</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7f8;
            font-family: 'Roboto', sans-serif;
        }

       
        .btn-danger {
            background-color: #dc3545;
        }

        .card-header {
            background-color: #007bff;
            color: white;
        }

        .crop-card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>


    <div class="container">
        <h2>Give Feedback</h2>

        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" action="customer_feedback.php">
            <div class="form-group">
                <label for="order_id">Select Order</label>
                <select name="order_id" id="order_id" class="form-control" required>
                    <option value="">Select Order</option>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?php echo $order['id']; ?>">
                            <?php echo $order['crop_name']; ?> (Ordered on: <?php echo $order['order_date']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="rating">Rating</label>
                <select name="rating" id="rating" class="form-control" required>
                    <option value="">Select Rating</option>
                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>
                </select>
            </div>

            <div class="form-group">
                <label for="comment">Comments</label>
                <textarea name="comment" id="comment" class="form-control" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Feedback</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
