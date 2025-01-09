<?php
require_once "config.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$remove_message = '';

if(isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if(isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
        $remove_message = '<div class="alert alert-success" role="alert">Item removed from cart successfully.</div>';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $quantity) {
        if (isset($_SESSION["cart"][$id])) {
            $_SESSION["cart"][$id]["quantity"] = max(1, intval($quantity));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Blooming Blossoms Flower Shop</title>
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
        <h2 class="mb-4">Your Cart</h2>

        <?php echo $remove_message; ?>

        <?php
        if(isset($_SESSION["cart"]) && count($_SESSION["cart"]) > 0){
            echo '<form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" class="animate__animated animate__fadeIn">';
            echo '<table class="table table-striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Product</th>';
            echo '<th>Price</th>';
            echo '<th>Quantity</th>';
            echo '<th>Total</th>';
            echo '<th>Action</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $total = 0;
            foreach($_SESSION["cart"] as $id => $item){
                echo '<tr>';
                echo '<td>' . htmlspecialchars($item["name"]) . '</td>';
                echo '<td>$' . number_format($item["price"], 2) . '</td>';
                echo '<td><input type="number" name="quantity[' . $id . ']" value="' . $item["quantity"] . '" min="1" class="form-control" style="width: 70px;"></td>';
                echo '<td>$' . number_format($item["price"] * $item["quantity"], 2) . '</td>';
                echo '<td><a href="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '?action=remove&id=' . $id . '" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Remove</a></td>';
                echo '</tr>';
                $total += $item["price"] * $item["quantity"];
            }

            echo '</tbody>';
            echo '</table>';

            echo '<div class="text-end">';
            echo '<h4>Total: $' . number_format($total, 2) . '</h4>';
            echo '<button type="submit" name="update_cart" class="btn btn-secondary me-2"><i class="fas fa-sync"></i> Update Cart</button>';
            echo '<a href="checkout.php" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Proceed to Checkout</a>';
            echo '</div>';
            echo '</form>';
        } else {
            echo '<div class="alert alert-info" role="alert">';
            echo 'Your cart is empty. <a href="products.php" class="alert-link">Continue shopping</a>.';
            echo '</div>';
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
