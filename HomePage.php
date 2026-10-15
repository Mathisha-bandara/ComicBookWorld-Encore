<?php

session_start();

include("includes/db.php");
include("includes/functions.php");


// --------------------------------------------------
// Check whether the logged-in user is an administrator
// --------------------------------------------------

$isAdmin = false;

if (isset($_SESSION['user_id'])) {

    $userId = (int) $_SESSION['user_id'];

    $sql = "
        SELECT role
        FROM users
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userId
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {

            if ($user['role'] === 'admin') {
                $isAdmin = true;
            }
        }

        mysqli_stmt_close($stmt);
    }
}


// --------------------------------------------------
// Store statistics
// --------------------------------------------------

$productCount = 0;
$categoryCount = 0;
$customerCount = 0;
$orderCount = 0;


// Total products

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $productCount = (int) $row['total'];
}


// Total categories

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT category) AS total FROM products"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $categoryCount = (int) $row['total'];
}


// Total customers

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $customerCount = (int) $row['total'];
}


// Total orders

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM orders"
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $orderCount = (int) $row['total'];
}


// --------------------------------------------------
// Get latest featured products
// --------------------------------------------------

$featuredProducts = [];

$sql = "
    SELECT
        id,
        title,
        category,
        price,
        image,
        stock
    FROM products
    ORDER BY id DESC
    LIMIT 8
";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $featuredProducts[] = $row;
    }
}


// --------------------------------------------------
// Get product categories
// --------------------------------------------------

$categories = [];

$sql = "
    SELECT DISTINCT category
    FROM products
    WHERE category <> ''
    ORDER BY category ASC
";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $categories[] = $row['category'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Comic Book World Sri Lanka - Browse and shop comic books, manga and graphic novels."
    >

    <title>
        Comic Book World - Sri Lanka
    </title>

    <link
        rel="stylesheet"
        href="css/WebPage.css"
    >

    <script
        src="js/WebPage.js"
        defer
    ></script>

</head>


<body>


<header>

    <h1>
        Comic Book World - Sri Lanka
    </h1>


    <nav>

        <ul>

            <li>
                <a href="HomePage.php">
                    Home
                </a>
            </li>


            <li>
                <a href="Product&ServiceDisplay.php">
                    Products
                </a>
            </li>


            <?php if (isset($_SESSION['user_id'])) { ?>

                <li>
                    <a href="Profile.php">
                        Profile
                    </a>
                </li>

                <li>
                    <a href="logout.php">
                        Logout
                    </a>
                </li>

            <?php } else { ?>

                <li>
                    <a href="login.php">
                        Login
                    </a>
                </li>

                <li>
                    <a href="Register.php">
                        Register
                    </a>
                </li>

            <?php } ?>


            <?php if ($isAdmin) { ?>

                <li>
                    <a href="admin/AdminPanel.php">
                        Admin Panel
                    </a>
                </li>

            <?php } ?>


            <li>
                <a href="AboutUs.php">
                    About Us
                </a>
            </li>


            <li>
                <a href="ContactUs.php">
                    Contact Us
                </a>
            </li>


            <li>
                <a href="Cart.php">
                    Cart
                    (<span id="cart-count">0</span>)
                </a>
            </li>


            <li>
                <a href="T&C.php">
                    Terms & Conditions
                </a>
            </li>

        </ul>

    </nav>

</header>


<?php if (isset($_SESSION['fullname'])) { ?>

    <div class="home-welcome">

        Welcome,
        <?php echo e($_SESSION['fullname']); ?>!

    </div>

<?php } ?>


<main>


    <!-- ==========================================
         HERO SECTION
    =========================================== -->

    <section class="home-hero">

        <div class="home-hero-content">

            <p class="home-hero-label">
                COMIC BOOK WORLD
            </p>


            <h2>
                Discover Your Next Great Adventure
            </h2>


            <p>
                Explore our collection of comic books,
                manga and graphic novels available
                right here in Sri Lanka.
            </p>


            <div class="home-hero-buttons">

                <a
                    href="Product&ServiceDisplay.php"
                    class="home-primary-btn"
                >
                    Browse Comics
                </a>


                <a
                    href="Cart.php"
                    class="home-secondary-btn"
                >
                    View Cart
                </a>

            </div>

        </div>


        <div class="home-hero-image">

            <img
                src="Product/Comic_Book_World.png.png"
                alt="Comic Book World"
            >

        </div>

    </section>


   <!-- ==========================================
     WHY COMIC BOOK WORLD
=========================================== -->

<section class="home-benefits">

    <div class="home-section-heading">

        <p>
            WHY COMIC BOOK WORLD?
        </p>

        <h2>
            Everything You Need to Enjoy Your Next Comic
        </h2>

        <span>
            A simple and enjoyable way to discover,
            shop for, and keep track of your comics.
        </span>

    </div>


    <div class="home-benefit-grid">


        <article class="home-benefit-card">

            <div class="home-benefit-icon">
                📚
            </div>

            <h3>
                Growing Collection
            </h3>

            <p>
                Discover comics across different characters,
                publishers, stories, and categories.
            </p>

        </article>


        <article class="home-benefit-card">

            <div class="home-benefit-icon">
                🔎
            </div>

            <h3>
                Easy to Explore
            </h3>

            <p>
                Search, filter, and sort our collection
                to quickly find the comics you're looking for.
            </p>

        </article>


        <article class="home-benefit-card">

            <div class="home-benefit-icon">
                🛒
            </div>

            <h3>
                Simple Shopping
            </h3>

            <p>
                Add comics to your cart, adjust quantities,
                and complete your order with ease.
            </p>

        </article>


        <article class="home-benefit-card">

            <div class="home-benefit-icon">
                📦
            </div>

            <h3>
                Order Tracking
            </h3>

            <p>
                Keep track of your purchases and follow
                your order status from your account.
            </p>

        </article>


    </div>

</section>

   <!-- ==========================================
     COMIC SPOTLIGHT
=========================================== -->

<section class="home-featured">

    <div class="home-section-heading">

        <p>
            EXPLORE OUR COLLECTION
        </p>

        <h2>
            Comic Spotlight
        </h2>

        <span>
            Discover some of our latest and most exciting comics.
        </span>

    </div>


    <?php if (count($featuredProducts) > 0) { ?>

        <div class="spotlight-wrapper">

            <button
                type="button"
                class="spotlight-arrow spotlight-prev"
                aria-label="Previous comics"
            >
                &#10094;
            </button>


            <div class="spotlight-viewport">

                <div class="spotlight-track">

                    <?php foreach ($featuredProducts as $row) { ?>

                        <?php
                        $stock = (int) $row['stock'];
                        ?>

                        <article
                            class="spotlight-card"
                            data-product-url="product_details.php?id=<?php echo (int) $row['id']; ?>"
                            tabindex="0"
                            role="link"
                            aria-label="View details for <?php echo e($row['title']); ?>"
                        >

                            <div class="spotlight-image-wrap">

                                <img
                                    src="Product/<?php echo e($row['image']); ?>"
                                    alt="<?php echo e($row['title']); ?>"
                                >

                            </div>


                            <div class="spotlight-info">

                                <p class="product-category">
                                    <?php echo e($row['category']); ?>
                                </p>


                                <h3>
                                    <?php echo e($row['title']); ?>
                                </h3>


                                <p class="product-price">
                                    LKR
                                    <?php echo number_format(
                                        (float) $row['price'],
                                        2
                                    ); ?>
                                </p>


                                <?php if ($stock > 0) { ?>

                                    <p class="stock available">
                                        In Stock: <?php echo $stock; ?>
                                    </p>


                                    <button
                                        type="button"
                                        onclick="addToCart(
                                            <?php echo (int) $row['id']; ?>,
                                            <?php echo htmlspecialchars(
                                                json_encode($row['title']),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>,
                                            <?php echo (float) $row['price']; ?>
                                        )"
                                    >
                                        Add to Cart
                                    </button>

                                <?php } else { ?>

                                    <p class="stock out-of-stock">
                                        Out of Stock
                                    </p>


                                    <button
                                        type="button"
                                        disabled
                                    >
                                        Out of Stock
                                    </button>

                                <?php } ?>

                            </div>

                        </article>

                    <?php } ?>

                </div>

            </div>


            <button
                type="button"
                class="spotlight-arrow spotlight-next"
                aria-label="Next comics"
            >
                &#10095;
            </button>

        </div>


        <div class="spotlight-dots" aria-label="Comic spotlight navigation">
        </div>


    <?php } else { ?>

        <p class="home-no-products">
            No products are currently available.
        </p>

    <?php } ?>


    <div class="home-view-all">

        <a
            href="Product&ServiceDisplay.php"
            class="home-primary-btn"
        >
            View All Products
        </a>

    </div>

</section>
    <!-- ==========================================
         CATEGORIES
    =========================================== -->

    <section class="home-categories">

        <div class="home-section-heading">

            <p>
                FIND YOUR FAVORITES
            </p>

            <h2>
                Browse by Category
            </h2>

        </div>


        <div class="home-category-grid">

            <?php foreach ($categories as $category) { ?>

                <a
                    href="Product&ServiceDisplay.php?category=<?php echo urlencode($category); ?>"
                    class="home-category-card"
                >

                    <span>
                        <?php echo e($category); ?>
                    </span>

                    <strong>
                        View Collection →
                    </strong>

                </a>

            <?php } ?>

        </div>

    </section>


    <!-- ==========================================
         CALL TO ACTION
    =========================================== -->

    <section class="home-cta">

        <h2>
            Ready to Start Your Collection?
        </h2>

        <p>
            Find your next favorite comic and
            add it to your collection today.
        </p>

        <a
            href="Product&ServiceDisplay.php"
            class="home-primary-btn"
        >
            Explore Comics
        </a>

    </section>


</main>


<footer>

    <p>
        &copy; 2026 Comic Book World - Sri Lanka
    </p>

</footer>


</body>

</html>