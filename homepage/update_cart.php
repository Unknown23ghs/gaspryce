<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lpgshop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$session_id = session_id();
$product_id = $_POST['product_id'];
$quantity = max(1, intval($_POST['quantity'])); // Ensure quantity is at least 1

// Update the quantity in the cart
$sql = "UPDATE cart SET quantity = ? WHERE session_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $quantity, $session_id, $product_id);
$stmt->execute();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;