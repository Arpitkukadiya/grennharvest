<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the status of the order in the database
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);

    // Return a response (optional, for debugging)
    echo "Status updated successfully.";
} else {
    echo "Invalid request.";
}
?>
