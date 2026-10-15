<?php

session_start();

include("includes/db.php");

$message = "";

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";

    } else {

        $sql = "SELECT id, fullname, email, password, role, account_status
                FROM users
                WHERE email = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $email);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                if (($user['account_status'] ?? 'active') !== 'active') {

                    $message = "This account is currently inactive. Please contact support if you believe this is a mistake.";

                } else {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {

                        header("Location: admin/AdminPanel.php");
                        exit();

                    } else {

                        header("Location: HomePage.php");
                        exit();
                    }
                }

            } else {

                $message = "Invalid email or password.";
            }

        } else {

            $message = "Invalid email or password.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Comic Book World</title>
<link rel="stylesheet" href="css/WebPage.css">
</head>

<body>

<header>
    <h1>Login</h1>

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

<main class="form-container">

    <h2>Login to Your Account</h2>

    <p><?php echo $message; ?></p>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Enter Email"
            required>

        <input
            type="password"
            name="password"
            placeholder="Enter Password"
            required>

        <button
            type="submit"
            name="login">
            Login
        </button>

    </form>

    <p>
        No account?
        <a href="register.php">Register</a>
    </p>

</main>

<footer>
    <p>Comic Book World - Sri Lanka</p>
</footer>

</body>
</html>