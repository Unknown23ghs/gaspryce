<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "lpgshop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get the last order ID from session
$order_id = $_SESSION['last_order_id'] ?? 0;

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

$order_items = [];
if ($order) {
    // Fetch order items
    $stmt2 = $conn->prepare("SELECT oi.quantity, oi.price, p.name 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ?");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    while ($row = $result2->fetch_assoc()) {
        $order_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful - YZZA's LPG</title>
    <link rel="stylesheet" href="cart.css">
    <link rel="stylesheet" href="success.css">
</head>
<body>
 <nav class="main-nav">
    <a href="index.php"><span class="nav-icon">&#8962;</span> Home</a>
     <a href="subscription.php"><span class="nav-icon">&#128081;</span> Subscription</a>
    <a href="cart.php"><span class="nav-icon">&#128722;</span> Cart</a>
    <a href="payment.php"><span class="nav-icon">&#128179;</span> Payment</a>
    <a href="success.php"><span class="nav-icon">&#9989;</span> Orders</a>
    <a href="index.php"><span class="nav-icon">&#8592;</span> Return</a>
</nav>
    <div class="success-container">
        <div class="success-icon">
            <svg width="80" height="80" viewBox="0 0 80 80">
                <circle cx="40" cy="40" r="38" fill="#fff" stroke="#ff9f1c" stroke-width="4"/>
                <polyline points="24,44 38,58 58,28" fill="none" stroke="#ff6b6b" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1 class="success-title">Payment Successful!</h1>
        <p class="success-message">Thank you for your order.<br>Your payment has been processed successfully.</p>
        <?php if ($order && !empty($order_items)): ?>
        <div class="order-summary">
            <h2>Order Summary</h2>
            <ul>
                <?php foreach ($order_items as $item): ?>
                <li><?= htmlspecialchars($item['name']) ?> &mdash; <?= $item['quantity'] ?> x ₱<?= $item['price'] ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="order-total">Total Paid: <span>₱<?= $order['total'] ?></span></div>
            <div class="payment-method">Payment Method: <span><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></span></div>
        </div>
        <?php endif; ?>
        <a href="index.php" class="order-again-btn">Order Again</a>
    </div>
</body>
</html>