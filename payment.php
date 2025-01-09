<?php
require_once "config.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("location: index.php");
    exit;
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION["id"];

// Fetch order details
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if (!$order) {
    header("location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process GCash payment
    // In a real-world scenario, you would integrate with the GCash API here
    // For this example, we'll just simulate a successful payment
    $payment_successful = true;

    if ($payment_successful) {
        // Update order status
        $sql = "UPDATE orders SET status = 'paid' WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $order_id);
            mysqli_stmt_execute($stmt);
        }
        $payment_message = '<div class="alert alert-success">Payment successful! Your order has been confirmed.</div>';
    } else {
        $payment_message = '<div class="alert alert-danger">Payment failed. Please try again.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Blooming Blossoms Flower Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff5f5;
            font-family: 'Arial', sans-serif;
        }
        .navbar {
            background-color: #ff9a9e;
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background-color: #ff9a9e;
            border-color: #ff9a9e;
        }
        .btn-primary:hover {
            background-color: #ff8087;
            border-color: #ff8087;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-flower"></i> Blooming Blossoms</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php"><i class="fas fa-shopping-bag"></i> Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i> Reservations</a>
                        </li>
                        <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == true): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="pos.php"><i class="fas fa-cash-register"></i> POS</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Payment</h2>

        <?php
        if (isset($payment_message)) {
            echo $payment_message;
        }
        ?>

        <div class="card animate__animated animate__fadeIn">
            <div class="card-body">
                <h5 class="card-title">Order Summary</h5>
                <p class="card-text">Order ID: <?php echo $order['id']; ?></p>
                <p class="card-text">Total Amount: $<?php echo number_format($order['total_amount'], 2); ?></p>
                <p class="card-text">Pickup Method: <?php echo ucfirst($order['pickup_method']); ?></p>
            </div>
        </div>

        <h3 class="mt-4">Pay with GCash</h3>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?order_id=" . $order_id); ?>" class="animate__animated animate__fadeIn">
            <div class="form-group">
                <label for="gcash_number">GCash Number:</label>
                <input type="text" name="gcash_number" id="gcash_number" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Pay Now</button>
        </form>
    </div>

    <footer class="bg-light text-center text-lg-start mt-4">
        <div class="text-center p-3" style="background-color: #ff9a9e; color: white;">
            © 2023 Blooming Blossoms Flower Shop
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.card').addClass('animate__animated animate__fadeIn');
        });
    </script>
</body>
</html>
