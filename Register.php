<?php

session_start();

include("includes/db.php");

$message = "";

if (isset($_POST['register'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Server-side validation
    if (empty($fullname) || empty($email) || empty($password)) {

        $message = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {

        $message = "Password must be at least 8 characters long.";

    } else {

        // Check whether email already exists
        $checkSql = "SELECT id FROM users WHERE email = ? LIMIT 1";

        $checkStmt = mysqli_prepare($conn, $checkSql);

        mysqli_stmt_bind_param($checkStmt, "s", $email);

        mysqli_stmt_execute($checkStmt);

        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {

            $message = "An account with this email already exists.";

        } else {

            // Convert password into a secure hash
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // New users are customers by default
            $role = "customer";

            $sql = "INSERT INTO users
                    (fullname, email, password, role)
                    VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $fullname,
                $email,
                $hashedPassword,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Registration successful! You can now log in.";

            } else {

                $message = "Registration failed. Please try again.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($checkStmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Comic Book World</title>
<link rel="stylesheet" href="css/WebPage.css">
</head>

<body>

<header>
    <h1>Register</h1>

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

    <h2>Create Your Account</h2>

    <p><?php echo $message; ?></p>

    <form method="POST">

        <input
            type="text"
            name="fullname"
            placeholder="Full Name"
            required>

        <input
            type="email"
            name="email"
            placeholder="Email"
            required>

        <input
            type="password"
            name="password"
            placeholder="Password"
            required>

        <button
            type="submit"
            name="register">
            Register
        </button>

    </form>

    <p>
        Already have an account?
        <a href="login.php">Login</a>
    </p>

</main>

<footer>
    <p>Comic Book World - Sri Lanka</p>
</footer>

</body>
</html>