<?php
session_start();

if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    if(isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
}

header("Location: cart.php");
exit;
?>
