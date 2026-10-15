<?php
session_start();
include("includes/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Comic Book World</title>
    <link rel="stylesheet" href="css/WebPage.css">
    <script src="js/WebPage.js" defer></script>
</head>
<body>
    <header>
        <h1>About Us</h1>
        <nav>
            <ul>
                <li><a href="HomePage.php">Home</a></li>
                <li><a href="Product&ServiceDisplay.php">Products</a></li>
                <?php
                if(isset($_SESSION['fullname']))
                    {
                        if(isset($_SESSION['fullname']))
                            {
                                echo '<li><a href="Profile.php">Profile</a></li>';
                                echo '<li><a href="logout.php">Logout</a></li>';
                            }
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
        <h2>Welcome to Comic Book World!</h2>
        <p>We are dedicated to providing comic book enthusiasts with a wide range of comics, from classic to contemporary. Our mission is to create a vibrant community for comic lovers in Sri Lanka.</p>
        <img src="Product/Marvel-Comics--1536x1025.jpg" alt="About Us" class="hero-image">
        <p>Opened: Proudly serving the community since December 2024.</p>
        <p>Contact Us: Reach us at (123) 456-7890 for inquiries.</p>
        <p>Location: Visit us at 123 Comic Lane, Colombo, Sri Lanka.</p>
        <p>Company History: Founded by a group of passionate comic lovers, Comic Book World aims to provide a diverse selection of comics and graphic novels, catering to both seasoned collectors and newcomers.</p>

        <hr>
        <h2>Frequently Asked Questions</h2>
        
        <?php
        $sql = "SELECT * FROM faq";
        $result = mysqli_query($conn, $sql);
        
        while($row = mysqli_fetch_assoc($result))
        {
        ?>
        
        <div class="faq-box">

            <h3 onclick="toggleFAQ(this)">
            <?php echo $row['question']; ?>
            </h3>

            <p class="faq-answer">
            <?php echo $row['answer']; ?>
            </p>
        
        </div>
        
        <?php
        }
        ?>
    </main>

    <script>
    function toggleFAQ(question)
    {
        let answer =
        question.nextElementSibling;
        
        if(answer.style.display === "block")
            {
                answer.style.display = "none";
            }
        else
            {
                answer.style.display = "block";
            }
    }
    </script>

    <footer>
        <p> Comic Book World - Sri Lanka</p>
    </footer>
</body>
</html>