<?php
include 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
    $otp = rand(100000, 999999);

    // 🔍 Check email in both customers and farmers
    $stmt = $conn->prepare("SELECT email FROM customers WHERE email = ? UNION SELECT email FROM farmers WHERE email = ?");
    $stmt->execute([$email, $email]);

    if ($stmt->rowCount() > 0) {
        $error = "Email already exists in our system. Please use a different email.";
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, password, city, otp, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
        if ($stmt->execute([$name, $email, $password, $city, $otp])) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'arpitkukadiya10@gmail.com';
                $mail->Password = 'crmscaebqyzqvist'; // 🔐 Use your App Password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('arpitkukadiya10@gmail.com', ' Team GreenHarvest 🌾');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Customer Account';

                $mail->Body = '
                <div style="max-width: 600px; margin: auto; font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1);">
                  <h2 style="text-align: center; color: #4CAF50;">👨‍🌾 Customer Verification OTP</h2>
                  <p style="font-size: 16px; color: #333;">Hello <strong>' . htmlspecialchars($name) . '</strong>,</p>
                  <p style="font-size: 15px; color: #444;">
                    Thank you for registering with <strong> Team GreenHarvest 🌾</strong>. To complete your registration, please use the OTP below to verify your email address:
                  </p>
                  <div style="margin: 25px 0; text-align: center;">
                    <span style="display: inline-block; font-size: 28px; background: #4CAF50; color: white; padding: 10px 20px; border-radius: 6px; letter-spacing: 5px;">' . $otp . '</span>
                  </div>
                  <p style="font-size: 14px; color: #888; margin-top: 30px;">
                    If you did not initiate this registration, you can safely ignore this email.
                  </p>
                  <p style="font-size: 14px; color: #999; text-align: center; margin-top: 20px;">
                    —  Team GreenHarvest 🌾 Team 🌱
                  </p>
                </div>';

                $mail->send();
                header("Location: verification.php?email=" . urlencode($email));
                exit();
            } catch (Exception $e) {
                $error = "Registration successful, but email sending failed: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Registration failed.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('78.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        .register-container {
            max-width: 550px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .register-container h2 {
            color: #56ab2f;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .form-control {
            height: 50px;
            font-size: 16px;
            border-radius: 8px;
            border: 1.5px solid #ccc;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #56ab2f;
            box-shadow: 0 0 5px rgba(86, 171, 47, 0.4);
        }

        input[type="email"] {
            border: 2px solid #56ab2f;
        }

        input[type="email"]:focus {
            border-color: #3d7720;
            box-shadow: 0 0 8px rgba(86, 171, 47, 0.5);
        }

        .input-group-text {
            background: #56ab2f;
            color: white;
            border: none;
            border-radius: 8px 0px 0px 8px;
        }

        .btn-primary {
            background: #56ab2f;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 18px;
            transition: 0.3s;
            width: 100%;
            color: white;
        }

        .btn-primary:hover {
            background: #3d7720;
        }

        .form-links {
            margin-top: 15px;
        }

        .form-links a {
            color: #56ab2f;
            font-weight: bold;
            text-decoration: none;
        }

        .form-links a:hover {
            color: #3d7720;
            text-decoration: underline;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #f5c6cb;
            margin-bottom: 20px;
            font-weight: 500;
            animation: fadeIn 0.4s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2><i class="fa-solid fa-user"></i> Customer Registration</h2>

    <?php if (isset($error)) echo "<div class='error-message'>$error</div>"; ?>

    <form method="POST">
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
            <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa-solid fa-city"></i></span>
            <input type="text" name="city" class="form-control" placeholder="Enter your city" required>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <div class="form-links mt-3">
        <p>Already have an account? <a href="index.php">Login here</a></p>
    </div>
</div>

</body>
</html>
