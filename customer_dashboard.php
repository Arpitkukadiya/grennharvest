<?php
session_start();
include('config.php'); // DB connection

$customer_id = $_SESSION['customer_id'] ?? 1; // Replace with actual session in production

// Add to cart handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crop_id'])) {
    $crop_id = $_POST['crop_id'];
    $quantity = $_POST['quantity'];

    // Check crop details
    $cropStmt = $conn->prepare("SELECT * FROM crops WHERE id = ?");
    $cropStmt->execute([$crop_id]);
    $crop = $cropStmt->fetch();

    if ($crop && $quantity > 0) {
    // Check if already in cart
    $cartCheck = $conn->prepare("SELECT * FROM carts WHERE customer_id = ? AND crop_id = ?");
    $cartCheck->execute([$customer_id, $crop_id]);

    if ($cartCheck->rowCount() > 0) {
        // If exists, update quantity
        $updateCart = $conn->prepare("UPDATE carts SET quantity = quantity + ? WHERE customer_id = ? AND crop_id = ?");
        $updateCart->execute([$quantity, $customer_id, $crop_id]);
    } else {
        // Otherwise insert new
        $insert = $conn->prepare("INSERT INTO carts (customer_id, crop_id, quantity) VALUES (?, ?, ?)");
        $insert->execute([$customer_id, $crop_id, $quantity]);
    }

    $success = "Crop added to cart successfully!";
}
 else {
        $error = "Invalid crop or quantity.";
    }
}

// ✅ Fix: Check if 'city' column exists in DB
$farmerStmt = $conn->query("SELECT DISTINCT f.id, f.name FROM farmers f JOIN crops c ON f.id = c.farmer_id");
$farmers = $farmerStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        .navbar {
            background-color: #2c3e50;
            padding: 15px 20px;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffffff !important;
        }
        .navbar-nav .nav-link {
            color: #ffffff !important;
            font-size: 1rem;
            margin-right: 15px;
        }
        .navbar-nav .nav-link:hover {
            color: #f1c40f !important;
        }

        .hero-section {
            background: linear-gradient(to right, #2c3e50, #4a69bd);
            color: white;
            padding: 80px;
            text-align: center;
        }

        .card {
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            border-radius: 10px;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background: #4a69bd;
            color: white;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .btn-primary {
            background-color: #4a69bd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #1e3799;
        }

        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
            margin-top: 30px;
        }
.crop-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
    background: rgba(255, 255, 255, 0.95);
}

.crop-card:hover {
    transform: scale(1.02);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.crop-img {
    height: 220px;
    object-fit: cover;
    transition: transform 0.3s ease;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.crop-card:hover .crop-img {
    transform: scale(1.05);
}
  
    .crop-section-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 40px;
    }

    .video-card {
      border: none;
      border-radius: 20px;
      overflow: hidden;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      background: #ffffff;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .video-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .video-wrapper video, .video-card img {
      width: 100%;
      height: 230px;
      object-fit: cover;
      border-top-left-radius: 20px;
      border-top-right-radius: 20px;
    }

    .card-body {
      padding: 1rem 1.2rem;
    }

    .card-title {
      font-size: 1.25rem;
      font-weight: bold;
      color: #2c3e50;
    }

    .btn-custom {
      background: #27ae60;
      border: none;
      color: white;
    }

    .btn-custom:hover {
      background: #1e8449;
    }

    .fade-in {
      animation: fadeIn 0.6s ease-in-out both;
    }

    @keyframes fadeIn {
      0% { opacity: 0; transform: translateY(20px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    /* Section: Why Choose GreenHarvest */
.bg-success-subtle {
    background: linear-gradient(135deg, #e9f9ec, #d1f2dc);
}

section h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #14532d;
    margin-bottom: 40px;
    position: relative;
}

section h2::after {
    content: '';
    width: 60px;
    height: 4px;
    background: #198754;
    display: block;
    margin: 10px auto 0;
    border-radius: 2px;
}

.card {
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #fff;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.card i {
    background-color: #e0f3e7;
    padding: 15px;
    border-radius: 50%;
    margin-bottom: 15px;
}

.card h5 {
    color: #0f5132;
    font-size: 1.25rem;
}

/* Footer */
.footer {
    background: linear-gradient(145deg, #42a37fff, #1e293b);
    color: #ffffff;
}

.footer h5 {
    font-weight: bold;
    color: #fff;
    margin-bottom: 15px;
}

.footer p,
.footer li {
    color: #cbd5e1;
    font-size: 0.95rem;
}

.footer a:hover {
    text-decoration: underline;
    color: #84cc16;
}

.footer i {
    color: #84cc16;
}

.footer hr {
    border-color: rgba(255, 255, 255, 0.2);
}

.footer .text-center {
    color: #a3e635;
    font-weight: 500;
    font-family: 'Segoe UI', sans-serif;
    margin-top: 20px;
}

.feature-img {
    width: 350px;
    height: 190px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.video-wrapper img {
    width: 100%;
    height: 230px;
    object-fit: cover;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}
.button-row {
    display: flex;
    gap: 10px; /* Space between buttons */
    margin-top: 10px;
}

.btn-custom {
    background: linear-gradient(45deg, #28a745, #218838);
    color: #fff;
    border: none;
    padding: 10px 20px;
    font-weight: bold;
    border-radius: 5px;
    text-align: center;
    white-space: nowrap;
    transition: background 0.3s ease;
}

.btn-custom:hover {
    background: linear-gradient(45deg, #218838, #1e7e34);
    color: #fff;
}

  </style>

</head>
<body>
<?php include 'navbar.php'; ?>
<!-- Hero Section -->
<section class="hero-section text-white text-center py-5"
    style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('./k.jpg') no-repeat center center / cover;
           min-height: 62vh;
           display: flex;
           align-items: center;
           justify-content: center;">
    <div class="container">
        <h1 class="display-5 fw-bold">Welcome to GreenHarvest 🌾</h1>
        <p class="lead">Fresh, organic, and local produce delivered straight from the farms to your home.</p>
        <a href="#market" class="btn btn-warning btn-lg mt-3">Explore Crops</a>
    </div>
</section>



<!-- Features Section -->
<section class="py-5 bg-light text-center">
    <div class="container">
        <h2 class="fw-bold text-dark mb-4">Why Choose GreenHarvest?</h2>
        <div class="row">
            <div class="col-md-4">
                <img src="./frash.avif" class="feature-img" alt="Farm Fresh">
                <h5 class="mt-3">Farm-Fresh</h5>
                <p>Harvested and delivered the same day.</p>
            </div>
            <div class="col-md-4">
                <img src="./o.jpg" class="feature-img" alt="100% Organic">
                <h5 class="mt-3">100% Organic</h5>
                <p>Free from harmful chemicals and pesticides.</p>
            </div>
            <div class="col-md-4">
                <img src="./f.jpg" class="feature-img" alt="Fair to Farmers">
                <h5 class="mt-3">Fair to Farmers</h5>
                <p>Supporting local livelihoods with fair prices.</p>
            </div>
        </div>
    </div>
</section>


<!-- Crops Section -->
<div class="container my-5" id="market">
    <h2 class="text-center mb-5 text-success fw-bold display-6">🌿 Available Crops</h2>

  <div class="row g-4">
    <?php foreach ($farmers as $farmer): ?>
      <?php
        $cropStmt = $conn->prepare("SELECT * FROM crops WHERE farmer_id = ?");
        $cropStmt->execute([$farmer['id']]);
        $crops = $cropStmt->fetchAll();
      ?>

      <?php foreach ($crops as $crop): ?>
        <div class="col-md-4 fade-in">
          <div class="video-card">
            <div class="video-wrapper">
              <?php if (!empty($crop['video'])): ?>
    <video autoplay muted loop>
        <source src="<?= htmlspecialchars($crop['video']) ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
<?php else: ?>
    <img src="<?= !empty($crop['image']) ? htmlspecialchars($crop['image']) : './j1.png' ?>" alt="<?= htmlspecialchars($crop['name']) ?>">
<?php endif; ?>

            </div>
            <div class="card-body">
              <h5 class="card-title">🌾 <?= htmlspecialchars($crop['name']) ?></h5>
              <p class="text-muted"><?= htmlspecialchars($crop['description']) ?></p>
              <p><strong>Price:</strong> ₹<?= $crop['price_per_kg'] ?>/kg</p>
              <form method="POST">
                <input type="hidden" name="crop_id" value="<?= $crop['id'] ?>">
                <input type="number" name="quantity" min="1" class="form-control mb-2" placeholder="Quantity (kg)" required>
             <div class="button-row">
    <form method="post" action="add_to_cart.php" class="me-2">
        <a href="farm_visit_form.php?farmer_id=<?= $crop['farmer_id']; ?>" class="btn btn-custom">🧑‍🌾 Request Farm Visit</a>
       
    </form>

     <input type="hidden" name="crop_id" value="<?= $crop['id']; ?>">
        <button type="submit" class="btn btn-custom">🛒 Add to Cart</button>
</div>
       
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </div>
</div>

<!-- Footer -->
<footer class="footer bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>About GreenHarvest</h5>
                <p class="text-light">GreenHarvest connects Indian farmers directly with customers across cities, ensuring transparency and freshness in every deal.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="order_history.php" class="text-white text-decoration-none">Order History</a></li>
                    <li><a href="visit_history.php" class="text-white text-decoration-none">Visit History</a></li>
                    <li><a href="view_cart.php" class="text-white text-decoration-none">View Cart</a></li>
                   
                    
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Contact Us</h5>
                <p class="text-light mb-1"><i class="fas fa-envelope me-2"></i> support@greenharvest.in</p>
                <p class="text-light mb-1"><i class="fas fa-phone me-2"></i> +91 7862899655</p>
                <p class="text-light"><i class="fas fa-map-marker-alt me-2"></i> Ahmedabad, Gujarat, India</p>
            </div>
        </div>
        <hr class="border-light">
        <div class="text-center small">
    &copy; <?= date("Y") ?> GreenHarvest | Made with <span style="color: #e25555;">&#10084;</span> by Arpit Kukadiya
</div>

    </div>
</footer>

</body>
</html>
