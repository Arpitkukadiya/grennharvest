<?php
include 'config.php';

if (isset($_GET['email'])) {
    $email = $_GET['email'];
} else {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $otp_digits = $_POST['otp']; // array of 6 digits
    $entered_otp = implode('', $otp_digits); // combine into single string

    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ? AND otp = ?");
    $stmt->execute([$email, $entered_otp]);

    if ($stmt->rowCount() > 0) {
        $update = $conn->prepare("UPDATE customers SET otp = NULL, is_verified = 1 WHERE email = ?");
        $update->execute([$email]);
        $message = "✅ OTP Verified! Your account is now active.";
        header("refresh:3;url=index.php");
    } else {
        $message = "❌ Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('78.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verify-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            max-width: 500px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #56ab2f;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .otp-input {
            width: 40px;
            height: 50px;
            font-size: 24px;
            text-align: center;
            margin: 5px;
            border: 2px solid #56ab2f;
            border-radius: 6px;
            outline: none;
        }

        .otp-input:focus {
            border-color: #3d7720;
            box-shadow: 0 0 5px #56ab2f;
        }

        .otp-box {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .btn-primary {
            background: #56ab2f;
            border: none;
            padding: 12px;
            font-size: 18px;
            border-radius: 8px;
            width: 100%;
            transition: 0.3s;
            color: white;
        }

        .btn-primary:hover {
            background: #3d7720;
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

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<body>
<div class="verify-container">
    <h2 class="text-center"><i class="fa-solid fa-shield-halved"></i> Email Verification</h2>

    <?php if ($message): ?>
        <p class="message <?php echo (str_contains($message, 'Verified') ? 'success' : ''); ?>">
            <?= $message ?>
        </p>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

        <label class="form-label text-center w-100 mb-2">Enter 6-digit OTP sent to your email</label>
        <div class="otp-box">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <input type="text" name="otp[]" maxlength="1" class="otp-input" required oninput="moveToNext(this, <?= $i ?>)">
            <?php endfor; ?>
        </div>

        <button type="submit" class="btn-primary">Verify OTP</button>
    </form>
</div>

<script>
    const inputs = document.querySelectorAll('.otp-input');
    function moveToNext(el, index) {
        if (el.value.length === 1 && index < 5) {
            inputs[index + 1].focus();
        } else if (el.value.length === 0 && index > 0) {
            inputs[index - 1].focus();
        }
    }

    inputs[0].focus();
</script>
</body>
</html>
