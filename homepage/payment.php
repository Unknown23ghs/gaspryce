<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lpgshop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$session_id = session_id();

$sql = "SELECT c.id, c.quantity, p.name, p.price, p.image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.session_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - YZZA's LPG</title>
    <link rel="stylesheet" href="cart.css">
    <link rel="stylesheet" href="payment.css">
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
    <div class="payment-container">
        <div class="payment-title">Payment</div>
        <form method="post" action="process_payment.php" id="paymentForm">
            <table class="summary-table">
                <tr>
                    <th>Product</th><th>Image</th><th>Price</th><th>Quantity</th><th>Subtotal</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): 
                    $subtotal = $row['price'] * $row['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><img src="<?= htmlspecialchars($row['image']) ?>" width="40"></td>
                    <td>₱<?= $row['price'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>₱<?= $subtotal ?></td>
                </tr>
                <?php endwhile; ?>
                <tr>
                    <td colspan="4"><strong>Total</strong></td>
                    <td><strong>₱<?= $total ?></strong></td>
                </tr>
            </table>
            <div class="payment-options">
                <label><input type="radio" name="payment_method" value="cod" required> Cash on Delivery</label>
                <label><input type="radio" name="payment_method" value="gcash"> GCash</label>
                <label><input type="radio" name="payment_method" value="credit_card"> Credit Card</label>
            </div>
            <!-- Address field for delivery location -->
            <div class="address-section" style="margin: 20px 0;">
                <label for="address" style="font-weight:bold; color:#7a1a1a;">Delivery Address:</label><br>
                <textarea id="address" name="address" rows="3" style="width:100%; border-radius:8px; border:1px solid #ccc; padding:10px; font-size:1rem;" required placeholder="Enter your delivery address"></textarea>
            </div>
            <!-- Pay Now button placed outside .payment-options but inside the form -->
            <a href="success.php" class="pay-btn pay-btn-link">
                <svg width="24" height="24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                Pay Now
            </a>
        </form>
    </div>
</body>
</html>
