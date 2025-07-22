<style>
        /* Global Theme */
        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', sans-serif;
        }

        /* Navbar */
        .navbar {
           background: linear-gradient(145deg, rgba(46, 221, 104, 1), rgba(29, 107, 45, 1));
            padding: 8px 20px;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffffff !important;
        }
        .navbar-nav .nav-link {
            color: #ffffff !important;
            font-size: 1rem;
            margin-right: 2px;
        }
        .navbar-nav .nav-link:hover {
            color: #f1c40f !important;
        }
</style>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="customer_dashboard.php">GreenHarvest</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon">☰</span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="customer_profile.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="order_history.php">Order History</a></li>
                <li class="nav-item"><a class="nav-link" href="visit_history.php">Visit History</a></li>
                <li class="nav-item"><a class="nav-link" href="view_cart.php">View Cart</a></li>
           

                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
