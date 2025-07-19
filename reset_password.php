<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'config.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

session_start();

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['user_type'])) {
    header("Location: forgot_password.php");
    exit();
}

$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];
    $pass = $_POST['password'];
    $repass = $_POST['repassword'];
    $email = $_SESSION['reset_email'];
    $table = $_SESSION['user_type'];

    $stmt = $conn->prepare("SELECT pass_key FROM $table WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row || $otp !== $row['pass_key']) {
        $message = "❌ Invalid OTP.";
    } elseif ($pass !== $repass) {
        $message = "🔁 Passwords do not match.";
    } else {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE $table SET password = ?, pass_key = NULL WHERE email = ?");
        if ($update->execute([$hashed, $email])) {
            // Send success email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'arpitkukadiya10@gmail.com';
                $mail->Password = 'crmscaebqyzqvist';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('arpitkukadiya10@gmail.com', 'Peanut Farm Portal');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = '✅ Your Password Has Been Updated';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; background:#f8fff5; padding:20px; border-radius:10px;'>
                        <h2 style='color:#28a745;'>🎉 Password Reset Successful</h2>
                        <p>Hello 🌿,</p>
                        <p>Your password has been successfully updated for the account linked to:</p>
                        <p><strong>$email</strong></p>
                        <p>You're all set! You can now log in using your new password.</p>
                        <hr>
                        <p style='font-size:14px; color:#555;'>If this wasn't you, please contact support immediately.</p>
                        <p style='font-size:14px;'> Team GreenHarvest 🌾<</p>
                    </div>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Log failure silently or show a small message
            }

            session_destroy();
            $success = true;
            $message = "✅ Password updated successfully! Redirecting to login...";
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 3000);</script>";
        } else {
            $message = "⚠️ Password update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | Green Harvest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0f8e0, #f7f8fa);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-box {
            width: 420px;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease-in-out;
        }

        h4 {
            color: #28a745;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            animation: slideDown 0.5s ease;
        }

        label {
            font-weight: 500;
        }

        .btn-success {
            background: linear-gradient(45deg, #28a745, #72d372);
            border: none;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #218838, #56c056);
            transform: scale(1.03);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert {
            animation: popIn 0.5s ease-in-out;
        }

        @keyframes popIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="reset-box">
    <h4>🔐 Reset Password</h4>

    <?php if ($message): ?>
        <div class='alert alert-<?php echo $success ? "success" : "danger"; ?> text-center'>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Enter OTP</label>
            <input type="text" name="otp" class="form-control" placeholder="Enter the OTP sent to your email" required>
        </div>

        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="password" class="form-control" placeholder="New password" required>
        </div>

        <div class="mb-3">
            <label>Re-enter Password</label>
            <input type="password" name="repassword" class="form-control" placeholder="Confirm password" required>
        </div>

        <button class="btn btn-success w-100">Reset Password</button>
    </form>
</div>

</body>
</html>
