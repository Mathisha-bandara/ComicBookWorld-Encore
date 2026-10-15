<?php
session_start();
include("includes/db.php");

if(!isset($_SESSION['user_id']))
{
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = mysqli_query(
    $conn,
    "SELECT * FROM orders
    WHERE user_id='$user_id'
    ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Tracking</title>
    <link rel="stylesheet" href="css/WebPage.css">
    <script src="js/WebPage.js" defer></script>
</head>

<body>

<header>
    <h1>Order Tracking</h1>
</header>

<div class="cart-container">

<h2>Your Orders</h2>

<?php
while($row = mysqli_fetch_assoc($result))
{
?>
<div class="profile-box">

<p><strong>Order ID:</strong>
<?php echo $row['id']; ?></p>

<p><strong>Total:</strong>
LKR <?php echo $row['total']; ?></p>

<p><strong>Status:</strong>
<?php echo $row['status']; ?></p>

<p><strong>Date:</strong>
<?php echo $row['created_at']; ?></p>

</div>

<?php
}
?>

</div>

</body>
</html>