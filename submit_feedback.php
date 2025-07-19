<?php
session_start();
include 'config.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $customer_id = $_SESSION['customer_id'];

    // Fetch the farmer_id from the orders table
    $stmt = $conn->prepare("SELECT crop_id FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if ($order) {
        // Fetch the farmer_id from the crops table
        $stmt = $conn->prepare("SELECT farmer_id FROM crops WHERE id = ?");
        $stmt->execute([$order['crop_id']]);
        $crop = $stmt->fetch();

        if ($crop) {
            $farmer_id = $crop['farmer_id'];

            // Insert feedback into the database
            $stmt = $conn->prepare("INSERT INTO feedback (order_id, customer_id, farmer_id, rating, comment) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $customer_id, $farmer_id, $rating, $comment]);

            header("Location: order_history.php?feedback_success=true");
            exit();
        }
    }
    header("Location: order_history.php?feedback_error=true");
    exit();
}
?>
