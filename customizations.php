<?php
require_once "config.php";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$remove_message = '';

// Handle remove action
if(isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $customization_id = $_GET['id'];
    $sql = "DELETE FROM customizations WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $customization_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $remove_message = '<div class="alert alert-success">Customization removed successfully.</div>';
        } else {
            $remove_message = '<div class="alert alert-danger">Error removing customization. Please try again.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

$sql = "SELECT c.id, c.customization_details, c.created_at, p.name AS product_name, p.image AS product_image
        FROM customizations c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC";

$customizations = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $customizations[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Customizations - Blooming Blossoms Flower Shop</title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-shopping-bag"></i> Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="order_history.php"><i class="fas fa-history"></i> Order History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-alt"></i> Reservations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="customizations.php"><i class="fas fa-paint-brush"></i> Customizations</a>
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
        <h2 class="mb-4">My Customizations</h2>
        <?php echo $remove_message; ?>
        <?php if (empty($customizations)): ?>
            <div class="alert alert-info">You haven't made any customizations yet.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($customizations as $customization): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card animate__animated animate__fadeIn">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="<?php echo htmlspecialchars($customization['product_image']); ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($customization['product_name']); ?>">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($customization['product_name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($customization['customization_details']); ?></p>
                                        <p class="card-text"><small class="text-muted">Created on: <?php echo date('F j, Y, g:i a', strtotime($customization['created_at'])); ?></small></p>
                                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?action=remove&id=' . $customization['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this customization?');">
                                            <i class="fas fa-trash"></i> Remove
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
