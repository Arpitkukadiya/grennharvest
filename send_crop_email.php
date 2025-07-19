<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'config.php';
session_start();

if (!isset($_SESSION['farmer_id'])) {
    die("Not logged in.");
}

if (!isset($_GET['crop_id'])) {
    die("Crop ID is required.");
}

$crop_id = $_GET['crop_id'];

// Fetch crop
$stmt = $conn->prepare("SELECT * FROM crops WHERE id = ? AND farmer_id = ?");
$stmt->execute([$crop_id, $_SESSION['farmer_id']]);
$crop = $stmt->fetch();

if (!$crop) {
    die("Crop not found.");
}

// Get all customer emails
$customersStmt = $conn->prepare("SELECT email FROM customers");
$customersStmt->execute();
$customers = $customersStmt->fetchAll(PDO::FETCH_COLUMN);

// ✅ Email HTML content
$message = '
<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; background: #fffef5; padding: 25px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1);">
  <h2 style="text-align:center; color:#4CAF50;">🌿 Fresh Organic Crop Available!</h2>
  <p style="text-align:center; color:#555; font-size: 16px;">We are excited to bring you a healthy and fresh organic crop directly from your trusted farmer! 🧑‍🌾</p>

  <table style="width:100%; border-collapse: collapse; margin-top: 20px; font-size: 15px;">
    <tr><th style="background:#4CAF50;color:#fff;padding:12px;border:1px solid #ddd;">Crop Name</th><td style="padding:12px;border:1px solid #ddd;">' . htmlspecialchars($crop['name']) . ' 🌾</td></tr>
    <tr><th style="background:#4CAF50;color:#fff;padding:12px;border:1px solid #ddd;">Description</th><td style="padding:12px;border:1px solid #ddd;">' . nl2br(htmlspecialchars($crop['description'])) . '</td></tr>
    <tr><th style="background:#4CAF50;color:#fff;padding:12px;border:1px solid #ddd;">Price</th><td style="padding:12px;border:1px solid #ddd;">₹' . $crop['price_per_kg'] . ' per kg</td></tr>
    <tr><th style="background:#4CAF50;color:#fff;padding:12px;border:1px solid #ddd;">Season</th><td style="padding:12px;border:1px solid #ddd;">' . htmlspecialchars($crop['season']) . '</td></tr>
    <tr><th style="background:#4CAF50;color:#fff;padding:12px;border:1px solid #ddd;">Insurance</th><td style="padding:12px;border:1px solid #ddd;">' . ($crop['insurance_status'] ? '✅ Yes' : '❌ No') . '</td></tr>
    <tr><th style="background:#4CAF50;color:#fff;padding:12px;border:1px solid #ddd;">Certified Organic</th><td style="padding:12px;border:1px solid #ddd;">' . ($crop['certificate_available'] ? '✅ Yes' : '❌ No') . '</td></tr>
  </table>

  <div style="text-align:center; margin-top: 30px;">
    <p style="font-size: 16px; color:#333;">🍀 Don’t miss out on this healthy choice! Reserve your share now! 🍀</p>
    <a href="https://arpit-kukadiya.vercel.app/farmer/customer_dashboard.html" target="_blank" style="padding: 12px 25px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-size: 16px;">🛒 Book This Crop</a>
  </div>

  <p style="text-align:center; color:#888; font-size: 13px; margin-top: 30px;">This email was sent from YourFarm. Supporting farmer-to-customer direct organic supply 🌍.</p>
</div>
';

$subject = "🌾 Book Now: Fresh Organic " . $crop['name'];

// Send Emails
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'arpitkukadiya10@gmail.com';
    $mail->Password = 'crmscaebqyzqvist'; // ✅ Use App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('arpitkukadiya10@gmail.com', ' Team GreenHarvest 🌾');

    foreach ($customers as $email) {
        $mail->clearAddresses();
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags("New crop available: " . $crop['name'] . ". Book now from your dashboard.");

        $mail->send();
        error_log("✅ Sent to: $email");
    }

    echo "<script>alert('Emails sent to all customers successfully.'); window.location.href='farmer_dashboard.php';</script>";

} catch (Exception $e) {
    error_log("❌ Mail Error: " . $mail->ErrorInfo);
    echo "<script>alert('Failed to send email. Please check SMTP settings.'); window.location.href='farmer_dashboard.php';</script>";
}
?>
