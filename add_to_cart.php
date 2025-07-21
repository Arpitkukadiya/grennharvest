<?php
session_start();

if (!isset($_POST['crop_id']) || !isset($_POST['qty'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$crop_id = $_POST['crop_id'];
$qty = $_POST['qty'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][] = ['crop_id' => $crop_id, 'qty' => $qty];
header("Location: view_cart.php");
exit();
