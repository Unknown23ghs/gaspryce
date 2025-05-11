<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lpgshop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$session_id = session_id();

// Handle quantity updates
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['quantity'])); // Ensure quantity is at least 1
    
    $sql = "UPDATE cart SET quantity = ? WHERE session_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $quantity, $session_id, $product_id);
    $stmt->execute();
    
    header("Location: cart.php");
    exit;
}

// Handle item removal
if (isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    
    $sql = "DELETE FROM cart WHERE session_id = ? AND product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $session_id, $product_id);
    $stmt->execute();
    
    header("Location: cart.php");
    exit;
}

// Get cart items
$sql = "SELECT c.id, c.quantity, p.name, p.price, p.image, p.id as product_id
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YZZA's LPG - Shopping Cart</title>
    <link rel="stylesheet" href="cart.css">
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
    <!-- MAIN CART CONTENT -->
    <main>
        <section class="cart-section">
            <h1 class="cart-title">Your Shopping Cart</h1>
            <div class="cart-content">
                <!-- Cart Items -->
                <div class="cart-items">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): 
                            $subtotal = $row['price'] * $row['quantity'];
                            $total += $subtotal;
                        ?>
                        <div class="cart-item-card">
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="cart-item-img">
                            <div class="cart-item-info">
                                <h2 class="cart-item-name"><?= htmlspecialchars($row['name']) ?></h2>
                                <p class="cart-item-desc"></p>
                                <form method="POST" class="quantity-form">
                                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                    <div class="cart-item-qty">
                                        <label>Qty:</label>
                                        <input type="number" 
                                               name="quantity" 
                                               value="<?= $row['quantity'] ?>" 
                                               min="1" 
                                               class="cart-qty-input">
                                        <button type="submit" name="update_quantity" class="update-btn">Update</button>
                                    </div>
                                </form>
                            </div>
                            <div class="cart-item-price">₱<?= $row['price'] ?></div>
                            <div class="cart-item-price">Subtotal: ₱<?= $subtotal ?></div>
                            <form method="POST" class="remove-form">
                                <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                                <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="padding:20px;">Your cart is empty.</p>
                    <?php endif; ?>
                </div>
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Total</span>
                        <span>₱<?= $total ?></span>
                    </div>
                    <a href="payment.php" class="checkout-btn">Proceed to Checkout</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Keep your existing footer section -->
</body>
</html>