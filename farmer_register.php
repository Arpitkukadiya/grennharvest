<?php
include 'config.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $rawPassword = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);
    $location = $_POST['location'];
    $bio = $_POST['bio'];
    $crop_certification = $_POST['crop_certification'];
    $insurance_option = $_POST['insurance_option'];
    $otp = rand(100000, 999999);

    // Handle file uploads
    $certification_doc = $_FILES['certification_doc']['name'];
    $certification_tmp = $_FILES['certification_doc']['tmp_name'];
    move_uploaded_file($certification_tmp, "uploads/" . $certification_doc);

    $insurance_doc = null;
    if (!empty($_FILES['insurance_doc']['name'])) {
        $insurance_doc = $_FILES['insurance_doc']['name'];
        $insurance_tmp = $_FILES['insurance_doc']['tmp_name'];
        move_uploaded_file($insurance_tmp, "uploads/" . $insurance_doc);
    }

    // Check if email already exists in farmers or customers table
    $stmt = $conn->prepare("SELECT email FROM farmers WHERE email = ? UNION SELECT email FROM customers WHERE email = ?");
    $stmt->execute([$email, $email]);

    if ($stmt->rowCount() > 0) {
        $message = "Email already exists in our system. Please use a different email.";
    } else {
        $sql = "INSERT INTO farmers (name, email, password, location, bio, certification_status, certification_doc, insurance_doc, crop_certification, otp) 
                VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$name, $email, $password, $location, $bio, $certification_doc, $insurance_doc, $crop_certification, $otp])) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'arpitkukadiya10@gmail.com';
                $mail->Password = 'crmscaebqyzqvist'; // Use App Password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('arpitkukadiya10@gmail.com', ' Team GreenHarvest 🌾');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Farmer Account';

                $mail->Body = '
                <div style="max-width: 600px; margin: auto; font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1);">
                  <h2 style="text-align: center; color: #4CAF50;">👨‍🌾 Farmer Verification OTP</h2>
                  <p>Hello <strong>' . htmlspecialchars($name) . '</strong>,</p>
                  <p>Thank you for registering with <strong> Team GreenHarvest 🌾</strong>. Please use the OTP below to verify your email:</p>
                  <div style="margin: 25px 0; text-align: center;">
                    <span style="display: inline-block; font-size: 28px; background: #4CAF50; color: white; padding: 10px 20px; border-radius: 6px; letter-spacing: 5px;">' . $otp . '</span>
                  </div>
                  <p style="font-size: 14px; color: #888;">If you did not register, you can ignore this email.</p>
                </div>';
                
                $mail->AltBody = "Your OTP for  Team GreenHarvest 🌾 is: $otp";
                $mail->send();

                header("Location: verify_email.php?email=" . urlencode($email));
                exit();
            } catch (Exception $e) {
                $message = "Registration successful, but email sending failed: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Registration</title>
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
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        .register-container h2 {
            color: #56ab2f;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .form-control {
            font-size: 16px;
            border-radius: 8px;
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

        .form-links a {
            color: #56ab2f;
            font-weight: bold;
            text-decoration: none;
        }

        .form-links a:hover {
            color: #3d7720;
            text-decoration: underline;
        }

        .optional {
            display: none;
        }

        .message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2 class="text-center"><i class="fa-solid fa-seedling"></i> Farmer Registration</h2>
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                </div>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                <input type="text" name="location" class="form-control" placeholder="Farm Location" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" placeholder="Tell us about your farming experience" rows="2" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="crop_certification" class="form-label">Crop Certification Type</label>
                        <select name="crop_certification" id="crop_certification" class="form-control">
                            <option value="non-organic">Non-Organic</option>
                            <option value="organic">Organic</option>
                        </select>
                    </div>

                    <div class="mb-3 optional" id="certification_doc_group">
                        <label class="form-label">Upload Certification Document</label>
                        <input type="file" name="certification_doc" class="form-control">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="insurance_option" class="form-label">Do you have Insurance?</label>
                        <select name="insurance_option" id="insurance_option" class="form-control">
                            <option value="none">No Insurance</option>
                            <option value="with-insurance">Yes, I have insurance</option>
                        </select>
                    </div>

                    <div class="mb-3 optional" id="insurance_doc_group">
                        <label class="form-label">Upload Insurance Document</label>
                        <input type="file" name="insurance_doc" class="form-control">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">Register</button>
        </form>

        <div class="form-links mt-3">
            <p class="text-center">Already have an account? <a href="index.php">Login here</a></p>
        </div>
    </div>

    <script>
        document.getElementById('crop_certification').addEventListener('change', function () {
            document.getElementById('certification_doc_group').style.display = this.value === 'organic' ? 'block' : 'none';
        });

        document.getElementById('insurance_option').addEventListener('change', function () {
            document.getElementById('insurance_doc_group').style.display = this.value === 'with-insurance' ? 'block' : 'none';
        });
    </script>
</body>

</html>
