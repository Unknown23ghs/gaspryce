<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lpgshop");
$session_id = session_id();
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Check if product already in cart
$res = $conn->query("SELECT id FROM cart WHERE session_id='$session_id' AND product_id=$product_id");
if($res->num_rows > 0) {
    $conn->query("UPDATE cart SET quantity=quantity+$quantity WHERE session_id='$session_id' AND product_id=$product_id");
} else {
    $conn->query("INSERT INTO cart (session_id, product_id, quantity) VALUES ('$session_id', $product_id, $quantity)");
}
header("Location: cart.php");
exit;