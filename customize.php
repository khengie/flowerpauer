<?php
require_once "config.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$customization_message = '';

// Fetch all products
$all_products = [];
$sql = "SELECT * FROM products ORDER BY name";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $all_products[] = $row;
    }
    mysqli_free_result($result);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_products = isset($_POST['selected_products']) ? $_POST['selected_products'] : [];
    $customization_details = $_POST['customization_details'];
    $user_id = $_SESSION['id'];

    if (!empty($selected_products)) {
        $success = true;
        foreach ($selected_products as $product_id) {
            $sql = "INSERT INTO customizations (user_id, product_id, customization_details) VALUES (?, ?, ?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "iis", $user_id, $product_id, $customization_details);
                if (!mysqli_stmt_execute($stmt)) {
                    $success = false;
                    break;
                }
                mysqli_stmt_close($stmt);
            } else {
                $success = false;
                break;
            }
        }
        if ($success) {
            $customization_message = '<div class="alert alert-success">Your customization has been saved!</div>';
        } else {
            $customization_message = '<div class="alert alert-danger">Oops! Something went wrong. Please try again later.</div>';
        }
    } else {
        $customization_message = '<div class="alert alert-warning">Please select at least one product to customize.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Products - Blooming Blossoms Flower Shop</title>
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
        .product-list {
            max-height: 600px;
            overflow-y: auto;
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
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-shopping-bag"></i> Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i> Reservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customizations.php"><i class="fas fa-paint-brush"></i> My Customizations</a>
                    </li>
                    <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="pos.php"><i class="fas fa-cash-register"></i> POS</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2 class="mb-4">Customize Products</h2>

        <?php echo $customization_message; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="row">
                <div class="col-md-6">
                    <h3>Available Products</h3>
                    <div class="product-list">
                        <?php foreach ($all_products as $prod): ?>
                            <div class="card mb-3 animate__animated animate__fadeIn">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <img src="<?php echo htmlspecialchars($prod['image']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h5>
                                            <p class="card-text"><strong>Price: $<?php echo number_format($prod['price'], 2); ?></strong></p>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="selected_products[]" value="<?php echo $prod['id']; ?>" id="product_<?php echo $prod['id']; ?>">
                                                <label class="form-check-label" for="product_<?php echo $prod['id']; ?>">
                                                    Select for customization
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card animate__animated animate__fadeIn">
                        <div class="card-body">
                            <h3>Customization Details</h3>
                            <div class="mb-3">
                                <label for="customization_details" class="form-label">Enter your customization details:</label>
                                <textarea class="form-control" id="customization_details" name="customization_details" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Customization</button>
                        </div>
                    </div>
                </div>
            </div>
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
