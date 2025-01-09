<?php
require_once "config.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != true){
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $new_price = $_POST['new_price'];

    $sql = "UPDATE products SET price = ? WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "di", $new_price, $product_id);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Price updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error updating price"]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing statement"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

mysqli_close($conn);
