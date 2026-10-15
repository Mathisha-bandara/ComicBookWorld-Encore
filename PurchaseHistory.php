<?php
session_start();
include("includes/db.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders
        WHERE user_id='$user_id'
        ORDER BY created_at DESC";

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Purchase History</title>

    <link rel="stylesheet"
          href="css/WebPage.css">

    <script src="js/WebPage.js" defer></script>
</head>

<body>

<header>
<h1>Purchase History</h1>

<nav>
<ul>
<li><a href="HomePage.php">Home</a></li>
<li><a href="Product&ServiceDisplay.php">Products</a></li>
<li><a href="Profile.php">Profile</a></li>
<li><a href="Cart.php">Cart (<span id="cart-count">0</span>)</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</nav>

</header>

<div class="profile-container">

<h2>Your Orders</h2>

<?php

if(mysqli_num_rows($result) > 0)
{
    while($row = mysqli_fetch_assoc($result))
    {
?>

<div class="profile-box">

<p>
<b>Order ID:</b>
<?php echo $row['id']; ?>
</p>

<p>
<b>Total:</b>
LKR <?php echo $row['total']; ?>
</p>

<p>
<b>Status:</b>
<?php echo $row['status']; ?>
</p>

<p>
<b>Date:</b>
<?php echo $row['created_at']; ?>
</p>

</div>

<?php
    }
}
else
{
    echo "<p>No orders found.</p>";
}
?>

</div>

<footer>
<p>Comic Book World - Sri Lanka</p>
</footer>

</body>
</html>