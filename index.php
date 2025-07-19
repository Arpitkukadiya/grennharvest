<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check Farmer Login
    $stmt = $conn->prepare("SELECT * FROM farmers WHERE email = ?");
    $stmt->execute([$email]);
    $farmer = $stmt->fetch();

    if ($farmer && password_verify($password, $farmer['password'])) {
        $_SESSION['farmer_id'] = $farmer['id'];
        header('Location: farmer_dashboard.php');
        exit();
    }

    // Check Customer Login
    $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();

    if ($customer && password_verify($password, $customer['password'])) {
        $_SESSION['customer_id'] = $customer['id'];
        header('Location: customer_dashboard.php');
        exit();
    }

    // If no match
    $error = "Invalid email or password!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        .login-container {
            max-width: 500px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .login-container h2 {
            color: #56ab2f;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .input-group-text {
            background: #56ab2f;
            color: white;
            border: none;
        }

        .form-control {
            height: 50px;
            font-size: 16px;
            border-radius: 8px;
        }

        .btn-primary {
            background: #56ab2f;
            border: none;
            padding: 12px;
            font-size: 18px;
            border-radius: 8px;
            width: 100%;
        }

        .btn-primary:hover {
            background: #3d7720;
        }

        .form-links {
            margin-top: 20px;
        }

        .form-links a {
            color: #56ab2f;
            font-weight: bold;
            text-decoration: none;
        }

        .form-links a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2><i class="fa-solid fa-leaf"></i> Login</h2>

    <?php if (isset($error)) : ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <div class="form-links">
        <p><a href="farmer_register.php">Farmer Registration</a> | <a href="customer_register.php">Customer Registration</a></p>
    </div>

    <div class="form-links">
        <p><a href="forgot_password.php">Forgot Password?</a> | <a href="change_password.php">Reset Password?</a></p>
    </div>
</div>

</body>
</html>
