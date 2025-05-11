<?php
session_start();
$conn = new mysqli("localhost", "root", "", "lpgshop");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$products = $conn->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="LPG.css">
    <title>YZZA's LPG Online Shop</title>
</head>
<nav class="main-nav">
    <a href="index.php"><span class="nav-icon">&#8962;</span> Home</a>
     <a href="subscription.php"><span class="nav-icon">&#128081;</span> Subscription</a>
    <a href="cart.php"><span class="nav-icon">&#128722;</span> Cart</a>
    <a href="payment.php"><span class="nav-icon">&#128179;</span> Payment</a>
    <a href="success.php"><span class="nav-icon">&#9989;</span> Orders</a>
    <a href="index.php"><span class="nav-icon">&#8592;</span> Return</a>
</nav>
<body>
    <!-- HEADER (same as before) -->
    <header class="headerz">
        <nav class="nav-container">
            <div class="nav-left">
                <a href="#" class="menulink">
                    <div class="menuboarder">
                        <h2 class="menuword">Menu</h2>
                    </div>
                </a>
            </div>
            <div class="nav-center">
                <form class="search" role="search">
                    <label>
                        <img src="360_F_599848646_MdK7wVDJQZygObyKEahVkOuhh0IGdTt3__1_-removebg-preview.png" class="searchpic" alt="Search">
                    </label>
                    <input type="text" class="searchbox" placeholder="Search here...">
                </form>
                <a class="membership" href="/Membership/membership.html">Get Membership</a>
            </div>
            <div class="nav-right">
                <a href="#">
                    <img src="Screenshot_2025-04-27_201605-removebg-preview.png" class="notifications" title="notifications" alt="Notifications">
                </a>
                <a href="cart.php">
                    <img src="Screenshot_2025-04-27_201402-removebg-preview.png" class="cart" title="cart" alt="Cart">
                </a>
                <button class="logoutbutton">Log out</button>
                <img src="logout-icon-template-black-color-editable-log-out-icon-symbol-flat-illustration-for-graphic-and-web-design-free-vector-removebg-preview.png" class="logouticon" alt="Logout">
            </div>
        </nav>
    </header>

    <!-- HERO/BANNER (same as before) -->
    <section class="hero">
        <div class="LPGbordersign">
            <div class="banner-grid">
                <div class="banner-social-card">
                    <div class="banner-social-title">Follow us on</div>
                    <div class="banner-social-links">
                        <div class="banner-social-link">Facebook.com@JonelCuartoCruz</div>
                        <div class="banner-social-link">Instagram.com@Zntsuma.com</div>
                        <div class="banner-social-link">Tiktok.com@Daddy.tyga</div>
                    </div>
                </div>
                <div class="banner-title">
                    <h1>YZZA's LPG<br>Online Shop</h1>
                </div>
                <div class="banner-cylinder">
                    <img src="gas-bottle-vector-clipart.png" class="cylinderlogo" alt="LPG Cylinder">
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCTS SECTION (dynamic with PHP) -->
    <section class="products-section">
        <div class="products-section-title">Shop Products</div>
        <div class="products-grid">
            <?php while($row = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="product-img">
                    <div class="product-info">
                        <h2 class="product-title"><?= htmlspecialchars($row['name']) ?></h2>
                        <p class="item-price">₱<?= htmlspecialchars($row['price']) ?></p>
                        <form method="post" action="add_to_cart.php">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <input type="number" name="quantity" value="1" min="1" style="width:50px;">
                            <button type="submit" class="add-to-cart">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- FOOTER (same as before) -->
    <footer>
        <hr>
        <h2 class="followuson">Follow us on:</h2>
        <div class="allpicture">
            <a href="#"><img src="facebook-6338509_1280.webp" class="logo1" alt="Facebook"></a>
            <a href="#"><img src="Instagram-Logo-2016.png" class="logo2" alt="Instagram"></a>
            <a href="https://youtu.be/dQw4w9WgXcQ?si=YFzAYU-_4RK75q6D"><img src="yt-6273367_960_720.webp" class="logo3" alt="YouTube"></a>
        </div>
        <a href="/CustomerServicehtml/CustomerService.html"><h2 class="message">Message us</h2></a>
        <a href="#"><h2 class="FAQ">FAQs</h2></a>
        <a href="#"><h2 class="about">About us</h2></a>
    </footer>
</body>
</html>