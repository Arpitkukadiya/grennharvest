<?php
require 'config.php';
session_start();

$message = "";
$step = 1;
$email = $userType = "";

// Step 1: Verify email and current password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];

    // Check in farmers table
    $stmt = $conn->prepare("SELECT * FROM farmers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $userType = "farmers";

    if (!$user) {
        // Check in customers table
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        $userType = "customers";
    }

    if ($user && password_verify($current_password, $user['password'])) {
        $_SESSION['change_email'] = $email;
        $_SESSION['change_user_type'] = $userType;
        $step = 2; // show new password form
    } else {
        $message = "❌ Invalid email or password.";
    }
}

// Step 2: Update new password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change'])) {
    $newpass = $_POST['new_password'];
    $repass = $_POST['re_password'];
    $email = $_SESSION['change_email'];
    $userType = $_SESSION['change_user_type'];

    if ($newpass !== $repass) {
        $message = "🔁 Passwords do not match.";
        $step = 2;
    } else {
        $hashed = password_hash($newpass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE $userType SET password = ? WHERE email = ?");
        if ($stmt->execute([$hashed, $email])) {
            session_unset();
            session_destroy();
            $message = "✅ Password changed successfully! Redirecting to login...";
            echo "<script>setTimeout(() => { window.location.href = 'index.php'; }, 3000);</script>";
        } else {
            $message = "⚠️ Failed to update password.";
            $step = 2;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password | GreenHarvest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #d2f8d2, #eaf7ea);
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .box {
            width: 400px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            animation: fadeIn 0.6s ease-in-out;
        }

        h4 {
            color: #28a745;
            text-align: center;
            font-weight: bold;
            animation: slideDown 0.5s ease;
        }

        .btn-success {
            background: linear-gradient(45deg, #28a745, #72d372);
            border: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #218838, #56c056);
            transform: scale(1.03);
        }

        @keyframes fadeIn {
            from {opacity: 0; transform: scale(0.95);}
            to {opacity: 1; transform: scale(1);}
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert {
            animation: bounceIn 0.4s ease;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="box">
        <h4>🔐 Change Password</h4>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $step === 2 && $message !== '✅ Password changed successfully! Redirecting to login...' ? 'danger' : 'success'; ?> text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="POST">
                <div class="mb-3">
                    <label>Email 📧</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                </div>
                <div class="mb-3">
                    <label>Current Password 🔑</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <button type="submit" name="verify" class="btn btn-success w-100">Verify & Continue</button>
            </form>
        <?php elseif ($step === 2): ?>
            <form method="POST">
                <div class="mb-3">
                    <label>New Password 🔐</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Re-enter Password 🔁</label>
                    <input type="password" name="re_password" class="form-control" required>
                </div>
                <button type="submit" name="change" class="btn btn-success w-100">Change Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
