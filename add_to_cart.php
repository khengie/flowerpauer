<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    require_once "config.php";

    $sql = "SELECT * FROM products WHERE id = ?";

    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $param_id);

        $param_id = trim($_GET["id"]);

        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);

            if(mysqli_num_rows($result) == 1){
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                if(!isset($_SESSION["cart"])){
                    $_SESSION["cart"] = array();
                }

                $product_exists = false;
                foreach($_SESSION["cart"] as &$item){
                    if($item["id"] == $row["id"]){
                        $item["quantity"]++;
                        $product_exists = true;
                        break;
                    }
                }

                if(!$product_exists){
                    $row["quantity"] = 1;
                    array_push($_SESSION["cart"], $row);
                }

                header("location: cart.php");
                exit();
            } else{
                header("location: error.php");
                exit();
            }

        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else{
    header("location: error.php");
    exit();
}
?>
