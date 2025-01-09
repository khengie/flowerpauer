<?php
require_once "config.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_SESSION["cart"]) && count($_SESSION["cart"]) > 0){
        $user_id = $_SESSION["id"];
        $total_amount = 0;

        foreach($_SESSION["cart"] as $item){
            $total_amount += $item["price"] * $item["quantity"];
        }

        $sql = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";

        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "id", $user_id, $total_amount);

            if(mysqli_stmt_execute($stmt)){
                $order_id = mysqli_insert_id($conn);

                $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";

                if($stmt = mysqli_prepare($conn, $sql)){
                    foreach($_SESSION["cart"] as $item){
                        mysqli_stmt_bind_param($stmt, "iiid", $order_id, $item["id"], $item["quantity"], $item["price"]);
                        mysqli_stmt_execute($stmt);
                    }

                    unset($_SESSION["cart"]);

                    header("location: order_confirmation.php?id=" . $order_id);
                    exit();
                } else{
                    echo '<div class="alert alert-danger">Oops! Something went wrong. Please try again later.</div>';
                }
            } else{
                echo '<div class="alert alert-danger">Oops! Something went wrong. Please try again later.</div>';
            }
        }

        mysqli_stmt_close($stmt);
    } else {
        echo '<div class="alert alert-danger">Your cart is empty.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Blooming Blossoms Flower Shop</title>
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
        <h2 class="mb-4">Checkout</h2>

        <?php
        if(isset($_SESSION["cart"]) && count($_SESSION["cart"]) > 0){
            $total = 0;
            foreach($_SESSION["cart"] as $item){
                $total += $item["price"] * $item["quantity"];
            }

            echo '<div class="row animate__animated animate__fadeIn">';
            echo '<div class="col-md-6">';
            echo '<h4>Order Summary</h4>';
            echo '<table class="table">';
            echo '<thead><tr><th>Product</th><th>Quantity</th><th>Price</th></tr></thead>';
            echo '<tbody>';
            foreach($_SESSION["cart"] as $item){
                echo '<tr>';
                echo '<td>' . $item["name"] . '</td>';
                echo '<td>' . $item["quantity"] . '</td>';
                echo '<td>$' . number_format($item["price"] * $item["quantity"], 2) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '<tfoot><tr><th colspan="2">Total:</th><td>$' . number_format($total, 2) . '</td></tr></tfoot>';
            echo '</table>';
            echo '</div>';
            echo '<div class="col-md-6">';
            echo '<h4>Shipping Information</h4>';
            echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post">';
            echo '<div class="form-group mb-3">';
            echo '<label for="address">Shipping Address</label>';
            echo '<textarea class="form-control" id="address" name="address" rows="3" required></textarea>';
            echo '</div>';
            echo '<div class="form-group mb-3">';
            echo '<label for="payment">Payment Method</label>';
            echo '<select class="form-control" id="payment" name="payment" required>';
            echo '<option value="">Select payment method</option>';
            echo '<option value="credit_card">Credit Card</option>';
            echo '<option value="paypal">Gcash</option>';
            echo '</select>';
            echo '</div>';
            echo '<button type="submit" class="btn btn-primary"><i class="fas fa-credit-card"></i> Place Order</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">Your cart is empty. <a href="products.php">Continue shopping</a>.</div>';
        }
        ?>
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
