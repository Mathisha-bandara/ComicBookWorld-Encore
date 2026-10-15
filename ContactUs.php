<?php
session_start();
include("includes/db.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <link rel="stylesheet" href="css/WebPage.css">
    <script src="js/WebPage.js" defer></script>
</head>

<body>

<header>
    <h1>Contact Us</h1>

    <nav>
        <ul>
            <li><a href="HomePage.php">Home</a></li>
            <li><a href="Product&ServiceDisplay.php">Products</a></li>

            <?php
            if(isset($_SESSION['fullname']))
            {
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
        </ul>
    </nav>
</header>

<main class="form-container">

<h1>Send Us a Message</h1>

<form>
    <input
    type="text"
    placeholder="Your Name"
    required>
    
    <input
    type="email"
    placeholder="Your Email"
    required>
    
    <textarea
    placeholder="Your Message"
    required></textarea>
    
    <br><br>
    
    <button type="submit">
        Send Message
    </button>

</form>

</main>

<footer>
    <p>Comic Book World - Sri Lanka</p>
</footer>

</body>
</html>