<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'config.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $otp = rand(100000, 999999);

    // Check in farmers
    $stmt = $conn->prepare("SELECT * FROM farmers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $type = 'farmers';

    if (!$user) {
        // Check in customers
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $type = 'customers';
    }

    if ($user) {
        // Update pass_key
        $update = $conn->prepare("UPDATE $type SET pass_key = ? WHERE email = ?");
        $update->execute([$otp, $email]);

        $_SESSION['reset_email'] = $email;
        $_SESSION['user_type'] = $type;

        // Email body with inline CSS
        $emailBody = "
            <div style='font-family: Arial, sans-serif; background-color:#f4f7f8; padding:20px; border-radius:10px; color:#333;'>
                <h2 style='color:#28a745;'>🔐 Password Reset Request</h2>
                <p>Hi there! 🌿</p>
                <p>We received a request to reset the password for your account linked with <strong>$email</strong>.</p>
                <p>Please use the following OTP to reset your password:</p>
                <div style='background-color:#e6ffe6; padding:10px 20px; font-size:24px; font-weight:bold; border-left:5px solid #28a745; display:inline-block; margin:15px 0;'>
                    $otp
                </div>
                <p><strong>Instructions:</strong></p>
                <ul>
                    <li>🔢 Enter the above OTP in the reset page</li>
                    <li>🔑 Set a new strong password</li>
                    <li>📝 Re-enter the password to confirm</li>
                    <li>⏰ This OTP is valid for one-time use only</li>
                </ul>
                <p>If you didn’t request this, please ignore this email.</p>
                <br>
                <p style='font-size:14px; color:#666;'>Regards,<br><strong> Team GreenHarvest 🌾<</strong></p>
            </div>
        ";

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'arpitkukadiya10@gmail.com';
            $mail->Password = 'crmscaebqyzqvist'; // Consider using environment variable in real projects
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            $mail->setFrom('arpitkukadiya10@gmail.com', 'Peanut Farm Portal');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = '🔐 Password Reset OTP - Peanut Farm Portal';
            $mail->Body = $emailBody;

            $mail->send();
            header("Location: reset_password.php");
            exit();
        } catch (Exception $e) {
            $message = "❌ OTP sending failed: " . $mail->ErrorInfo;
        }
    } else {
        $message = "🚫 Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Green Harvest 🌾</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #d0f0c0, #f4f7f8);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }

        .forgot-box {
            width: 400px;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            animation: fadeIn 1s ease-in-out;
        }

        .forgot-box h4 {
            color: #3e8e41;
            font-weight: bold;
            margin-bottom: 20px;
            animation: popIn 0.5s ease-in-out;
        }

        .form-label {
            font-weight: 500;
        }

        .btn-success {
            background: linear-gradient(45deg, #56ab2f, #a8e063);
            border: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #3d7720, #56ab2f);
            transform: scale(1.03);
        }

        .emoji {
            font-size: 2rem;
            animation: bounce 1s infinite;
        }

        @keyframes popIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
    </style>
</head>
<body>

<div class="forgot-box">
    <div class="text-center">
        <span class="emoji">🌿</span>
        <h4>Forgot Password</h4>
    </div>

    <?php if ($message): ?>
        <div class='alert alert-danger'><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Enter your Email 📧</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. you@example.com" required>
        </div>
        <button class="btn btn-success w-100">Send OTP 🔐</button>
    </form>
</div>

</body>
</html>
