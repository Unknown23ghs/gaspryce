<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "lpgshop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$session_id = session_id();
$payment_method = $_POST['payment_method'] ?? '';

// Debug information
error_log("Payment method: " . $payment_method);
error_log("Session ID: " . $session_id);

// Fetch cart items
$sql = "SELECT c.product_id, c.quantity, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$items = [];
while ($row = $result->fetch_assoc()) {
    $subtotal = $row['price'] * $row['quantity'];
    $total += $subtotal;
    $items[] = $row;
}

error_log("Total: " . $total);
error_log("Number of items: " . count($items));

if ($total > 0 && $payment_method) {
    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders (session_id, total, payment_method, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("sds", $session_id, $total, $payment_method);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert order items
    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt2->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $stmt2->execute();
    }

    // Clear cart
    $stmt3 = $conn->prepare("DELETE FROM cart WHERE session_id = ?");
    $stmt3->bind_param("s", $session_id);
    $stmt3->execute();

    // Store order ID in session
    $_SESSION['last_order_id'] = $order_id;
    error_log("Redirecting to success.php with order ID: " . $order_id);

    // Redirect to success page
    header("Location: success.php");
    exit;
} else {
    error_log("No items or no payment method");
    header("Location: payment.php");
    exit;
}
?>