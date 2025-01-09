<?php
require_once "config.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != true){
    header("Location: login.php");
    exit;
}

// Process order status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    $payment_status = $_POST['payment_status'];

    $sql = "UPDATE orders SET status = ?, payment_status = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssi", $new_status, $payment_status, $order_id);
        mysqli_stmt_execute($stmt);
    }
}

// Process reservation status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $new_status = $_POST['reservation_status'];

    $sql = "UPDATE reservations SET status = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $new_status, $reservation_id);
        mysqli_stmt_execute($stmt);
    }
}

// Fetch all orders with user information
$sql = "SELECT o.*, u.username, u.email FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $sql);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch all reservations with user information
$sql = "SELECT r.*, u.username, u.email FROM reservations r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.reservation_date DESC, r.reservation_time DESC";
$result = mysqli_query($conn, $sql);
$reservations = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch products for quick sale
$sql = "SELECT * FROM products";
$result = mysqli_query($conn, $sql);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Blooming Blossoms Flower Shop</title>
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
            font-weight: 500;
        }
        .nav-link:hover {
            color: #f8f9fa !important;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease-in-out;
            background-color: #ffffff;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background-color: #ff9a9e;
            border-color: #ff9a9e;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #ff8087;
            border-color: #ff8087;
            color: #ffffff;
        }
        .nav-tabs .nav-link {
            color: #000000;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #000000;
            border-color: #ff6b6b #ff6b6b #fff;
            font-weight: 600;
        }
        .table {
            color: #000000;
        }
        .table thead th {
            background-color: #ffefef;
            color: #000000;
            font-weight: 600;
        }
        .modal-title {
            color: #000000;
        }
        .form-label {
            color: #000000;
            font-weight: 500;
        }
        .text-muted {
            color: #666666 !important;
        }
        .alert {
            color: #000000;
        }
        h2, h3, h4, h5, h6 {
            color: #000000;
        }
        .modal-content {
            background-color: #ffffff;
        }
        .form-control, .form-select {
            color: #000000;
            background-color: #ffffff;
        }
        .form-control:focus, .form-select:focus {
            color: #000000;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 154, 158, 0.05);
        }
        .footer {
            background-color: #ff9a9e;
            color: #ffffff !important;
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
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == true): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="pos.php"><i class="fas fa-cash-register"></i> POS</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" id="orders-tab" data-bs-toggle="tab" href="#orders">Orders</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="reservations-tab" data-bs-toggle="tab" href="#reservations">Reservations</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Orders Tab -->
            <div class="tab-pane fade show active" id="orders">
                <div class="row">
                    <!-- Orders List -->
                    <div class="col-md-8">
                        <h2 class="mb-4">Customer Orders</h2>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td>
                                                <?php echo $order['username']; ?><br>
                                                <small class="text-muted"><?php echo $order['email']; ?></small>
                                            </td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><?php echo ucfirst($order['status']); ?></td>
                                            <td><?php echo ucfirst($order['payment_status']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                                    Manage
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Sale Section -->
                    <div class="col-md-4">
                        <h2 class="mb-4">Quick Sale</h2>
                        <form method="post" action="" id="quickSaleForm">
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="product" class="form-label">Product</label>
                                        <select class="form-select" id="product" name="product_id" required>
                                            <option value="">Select a product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>"
                                                        data-price="<?php echo $product['price']; ?>">
                                                    <?php echo $product['name']; ?> - $<?php echo number_format($product['price'], 2); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="total" class="form-label">Total</label>
                                        <input type="text" class="form-control" id="total" readonly>
                                    </div>
                                    <button type="submit" name="quick_sale" class="btn btn-primary w-100">Complete Sale</button>
                                    <button type="button" id="updatePrice" class="btn btn-secondary w-100 mt-2">Update Product Price</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reservations Tab -->
            <div class="tab-pane fade" id="reservations">
                <div class="row">
                    <div class="col-12">
                        <h2 class="mb-4">Customer Reservations</h2>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <tr>
                                            <td>#<?php echo $reservation['id']; ?></td>
                                            <td>
                                                <?php echo $reservation['username']; ?><br>
                                                <small class="text-muted"><?php echo $reservation['email']; ?></small>
                                            </td>
                                            <td><?php echo $reservation['service']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></td>
                                            <td><?php echo ucfirst($reservation['status']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#reservationModal<?php echo $reservation['id']; ?>">
                                                    Manage
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Management Modals -->
    <?php foreach ($orders as $order): ?>
        <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1" aria-labelledby="orderModalLabel<?php echo $order['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="orderModalLabel<?php echo $order['id']; ?>">
                            Order #<?php echo $order['id']; ?> Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        // Fetch order items
                        $sql = "SELECT oi.*, p.name FROM order_items oi
                                JOIN products p ON oi.product_id = p.id
                                WHERE oi.order_id = ?";
                        if ($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "i", $order['id']);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            $order_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
                        }
                        ?>
                        <div class="mb-4">
                            <h6>Order Items:</h6>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['name']; ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="new_status<?php echo $order['id']; ?>" class="form-label">Order Status</label>
                                        <select name="new_status" id="new_status<?php echo $order['id']; ?>" class="form-select">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_status<?php echo $order['id']; ?>" class="form-label">Payment Status</label>
                                        <select name="payment_status" id="payment_status<?php echo $order['id']; ?>" class="form-select">
                                            <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="update_order" class="btn btn-primary">Update Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Reservation Management Modals -->
    <?php foreach ($reservations as $reservation): ?>
        <div class="modal fade" id="reservationModal<?php echo $reservation['id']; ?>" tabindex="-1" aria-labelledby="reservationModalLabel<?php echo $reservation['id']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reservationModalLabel<?php echo $reservation['id']; ?>">
                            Reservation #<?php echo $reservation['id']; ?> Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="">
                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                            <div class="mb-3">
                                <label for="reservation_status<?php echo $reservation['id']; ?>" class="form-label">Reservation Status</label>
                                <select name="reservation_status" id="reservation_status<?php echo $reservation['id']; ?>" class="form-select">
                                    <option value="pending" <?php echo $reservation['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $reservation['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="completed" <?php echo $reservation['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $reservation['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <h6>Reservation Details:</h6>
                                <p><strong>Customer:</strong> <?php echo $reservation['username']; ?></p>
                                <p><strong>Email:</strong> <?php echo $reservation['email']; ?></p>
                                <p><strong>Service:</strong> <?php echo $reservation['service']; ?></p>
                                <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></p>
                                <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($reservation['reservation_time'])); ?></p>
                            </div>
                            <button type="submit" name="update_reservation" class="btn btn-primary">Update Reservation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <footer class="bg-light text-center text-lg-start mt-4">
        <div class="text-center p-3" style="background-color: #ff9a9e; color: white;">
            © 2023 Blooming Blossoms Flower Shop
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('product');
        const quantityInput = document.getElementById('quantity');
        const priceInput = document.getElementById('price');
        const totalInput = document.getElementById('total');
        const updatePriceBtn = document.getElementById('updatePrice');

        function calculateTotal() {
            const quantity = parseInt(quantityInput.value);
            const price = parseFloat(priceInput.value);
            const total = quantity * price;
            totalInput.value = '$' + total.toFixed(2);
        }

        function updatePriceField() {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            if (selectedOption.value) {
                priceInput.value = selectedOption.dataset.price;
                calculateTotal();
            } else {
                priceInput.value = '';
                totalInput.value = '';
            }
        }

        productSelect.addEventListener('change', updatePriceField);
        quantityInput.addEventListener('input', calculateTotal);
        priceInput.addEventListener('input', calculateTotal);

        updatePriceBtn.addEventListener('click', function() {
            const productId = productSelect.value;
            const newPrice = priceInput.value;

            if (!productId || !newPrice) {
                alert('Please select a product and enter a price.');
                return;
            }

            fetch('update_price.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&new_price=${newPrice}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    const selectedOption = productSelect.options[productSelect.selectedIndex];
                    selectedOption.dataset.price = newPrice;
                    selectedOption.textContent = `${selectedOption.textContent.split(' - ')[0]} - $${parseFloat(newPrice).toFixed(2)}`;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the price.');
            });
        });

        // Quick sale form submission
        document.getElementById('quickSaleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your quick sale processing logic here
            alert('Sale completed successfully!');
            this.reset();
            totalInput.value = '';
        });

        // Initialize price field on page load
        updatePriceField();
    });
    </script>
</body>
</html>
