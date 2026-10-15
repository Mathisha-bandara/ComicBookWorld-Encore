<?php
session_start();
include("includes/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions - Comic Book World</title>
    <link rel="stylesheet" href="css/WebPage.css">
    <script src="js/WebPage.js" defer></script>
</head>
<body>
    <header>
        <h1>Terms & Conditions</h1>
        <nav>
            <ul>
                <li><a href="HomePage.php">Home</a></li>
                <li><a href="Product&ServiceDisplay.php">Products</a></li>
                <?php
                if(isset($_SESSION['fullname']))
                    {
                        echo '<li><a href="Profile.php">Profile</a></li>';
                        echo '<li><a href="logout.php">Logout</a></li>';
                    }
                    else
                    {
                        echo '<li><a href="login.php">Login</a></li>';
                    }
                ?>
                <li><a href="Register.php">Register</a></li>
                <li><a href="AboutUs.php">About Us</a></li>
                <li><a href="ContactUs.php">Contact Us</a></li>
                <li><a href="Cart.php">Cart (<span id="cart-count">0</span>)</a></li>
                <li><a href="T&C.php">Terms & Conditions</a></li>
                
            </ul>
        </nav>
    </header>

    <main class="container">
        <img src="Product/Comic_Book_World.png.png" alt="Comic Book World" class="move-left">
        <h2>Terms and Conditions</h2>
        <p>Welcome to Comic Book World! These terms and conditions outline the rules and regulations for the use of our website.</p>
        <h3>1. Acceptance of Terms</h3>
        <p>By accessing this website, you accept these terms and conditions in full. If you disagree with any part of these terms, you must not use our website.</p>
        <h3>2. Changes to Terms</h3>
        <p>We reserve the right to modify these terms at any time. Your continued use of the website after any changes constitutes acceptance of the new terms.</p>
        <h3>3. User Accounts</h3>
        <p>Users are responsible for maintaining the confidentiality of their account information and for all activities that occur under their account. If you suspect any unauthorized use of your account, you must notify us immediately.</p>
        <h3>4. Product Information</h3>
        <p>We strive to provide accurate product descriptions and pricing. However, we do not warrant that the product descriptions or other content on the website are accurate, complete, reliable, current, or error-free.</p>
        <h3>5. Limitation of Liability</h3>
        <p>In no event shall Comic Book World be liable for any direct, indirect, incidental, special, consequential, or punitive damages arising from your use of the website or any products purchased.</p>
        <h3>6. Governing Law</h3>
        <p>These terms and conditions shall be governed by and construed in accordance with the laws of Sri Lanka.</p>
        <h3>7. Contact Us</h3>
        <p>If you have any questions about these terms, please contact us at support@comicbookworld.lk.</p>
    </main>

    <footer>
        <p> 2023 Comic Book World - Sri Lanka</p>
    </footer>
</body>
</html>