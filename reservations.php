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
    $reservation_date = $_POST['reservation_date'];
    $reservation_time = $_POST['reservation_time'];
    $service = $_POST['service'];
    $user_id = $_SESSION['id'];

    $sql = "INSERT INTO reservations (user_id, reservation_date, reservation_time, service) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $reservation_date, $reservation_time, $service);
        if (mysqli_stmt_execute($stmt)) {
            $reservation_message = '<div class="alert alert-success">Your reservation has been made successfully!</div>';
        } else {
            $reservation_message = '<div class="alert alert-danger">Oops! Something went wrong. Please try again later.</div>';
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch existing reservations
$user_id = $_SESSION['id'];
$sql = "SELECT * FROM reservations WHERE user_id = ? ORDER BY reservation_date, reservation_time";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reservations = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - Blooming Blossoms Flower Shop</title>
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
        <h2 class="mb-4">Reservations</h2>

        <?php
        if (isset($reservation_message)) {
            echo $reservation_message;
        }
        ?>

        <div class="row">
            <div class="col-md-6 animate__animated animate__fadeInLeft">
                <h3>Make a Reservation</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group mb-3">
                        <label for="reservation_date">Date</label>
                        <input type="date" class="form-control" id="reservation_date" name="reservation_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="reservation_time">Time</label>
                        <input type="time" class="form-control" id="reservation_time" name="reservation_time" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="service">Service</label>
                        <select class="form-control" id="service" name="service" required>
                            <option value="">Select a service</option>
                            <option value="Floral Consultation">Floral Consultation</option>
                            <option value="Wedding Flowers">Wedding Flowers</option>
                            <option value="Event Decoration">Event Decoration</option>
                            <option value="Floral Workshop">Floral Workshop</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-plus"></i> Make Reservation</button>
                </form>
            </div>
            <div class="col-md-6 animate__animated animate__fadeInRight">
                <h3>Your Reservations</h3>
                <?php if (!empty($reservations)): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?php echo $reservation['reservation_date']; ?></td>
                                    <td><?php echo $reservation['reservation_time']; ?></td>
                                    <td><?php echo $reservation['service']; ?></td>
                                    <td><?php echo ucfirst($reservation['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You have no reservations yet.</p>
                <?php endif; ?>
            </div>
        </div>
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
