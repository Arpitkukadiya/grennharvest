<?php
include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    header('Location: farmer_login.php');
    exit();
}

if (isset($_GET['id'])) {
    $crop_id = $_GET['id'];

    // Delete the crop from the database
    $stmt = $conn->prepare("DELETE FROM crops WHERE id = ? AND farmer_id = ?");
    $stmt->execute([$crop_id, $_SESSION['farmer_id']]);

    header('Location: view_crops.php');
    exit();
}
?>
