<?php

session_start();

include("includes/db.php");
include("includes/functions.php");


// ==========================================
// SEARCH / FILTER / SORT
// ==========================================

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'default';


// Get categories
$categorySql = "
    SELECT DISTINCT category
    FROM products
    ORDER BY category ASC
";

$categoryResult = mysqli_query(
    $conn,
    $categorySql
);


// Build product query
$sql = "
    SELECT
        id,
        title,
        category,
        description,
        price,
        image,
        stock
    FROM products
    WHERE 1=1
";

$params = [];
$types = "";


if ($search !== '')
{
    $sql .= "
        AND (
            title LIKE ?
            OR description LIKE ?
            OR category LIKE ?
        )
    ";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";
}


if ($category !== '')
{
    $sql .= " AND category = ? ";

    $params[] = $category;

    $types .= "s";
}


// Sorting
switch ($sort)
{
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;

    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;

    case 'name_az':
        $sql .= " ORDER BY title ASC";
        break;

    case 'name_za':
        $sql .= " ORDER BY title DESC";
        break;

    default:
        $sql .= " ORDER BY id DESC";
        break;
}


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!empty($params))
{
    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}


mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Products - Comic Book World
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
        Our Comic Collection
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


            <?php

            if (isset($_SESSION['fullname']))
            {
                echo '
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
                ';
            }
            else
            {
                echo '
                    <li>
                        <a href="login.php">
                            Login
                        </a>
                    </li>
                ';
            }

            ?>


            <li>
                <a href="Register.php">
                    Register
                </a>
            </li>

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
                    Cart (<span id="cart-count">0</span>)
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



<main>


<section class="product-page">


    <h2>
        Available Comics
    </h2>


    <!-- SEARCH / FILTER -->

    <form
        method="GET"
        class="product-filters"
    >

        <input
            type="search"
            name="search"
            placeholder="Search comics..."
            value="<?php echo e($search); ?>"
        >


        <select name="category">

            <option value="">
                All Categories
            </option>

            <?php

            while ($cat = mysqli_fetch_assoc($categoryResult))
            {

                $selected =
                    ($category === $cat['category'])
                    ? 'selected'
                    : '';

            ?>

                <option
                    value="<?php echo e($cat['category']); ?>"
                    <?php echo $selected; ?>
                >
                    <?php echo e($cat['category']); ?>
                </option>

            <?php

            }

            ?>

        </select>


        <select name="sort">

            <option
                value="default"
                <?php echo $sort === 'default' ? 'selected' : ''; ?>
            >
                Latest
            </option>

            <option
                value="price_low"
                <?php echo $sort === 'price_low' ? 'selected' : ''; ?>
            >
                Price: Low to High
            </option>

            <option
                value="price_high"
                <?php echo $sort === 'price_high' ? 'selected' : ''; ?>
            >
                Price: High to Low
            </option>

            <option
                value="name_az"
                <?php echo $sort === 'name_az' ? 'selected' : ''; ?>
            >
                Name: A-Z
            </option>

            <option
                value="name_za"
                <?php echo $sort === 'name_za' ? 'selected' : ''; ?>
            >
                Name: Z-A
            </option>

        </select>


        <button type="submit">
            Search
        </button>


        <a
            href="Product&ServiceDisplay.php"
            class="reset-filter"
        >
            Reset
        </a>

    </form>



    <!-- PRODUCTS -->

    <div class="products-grid">


        <?php

        if (mysqli_num_rows($result) === 0)
        {

        ?>

            <div class="no-products">

                <h3>
                    No comics found
                </h3>

                <p>
                    Try a different search or category.
                </p>

            </div>

        <?php

        }


        while ($row = mysqli_fetch_assoc($result))
        {

            $stock =
                (int) $row['stock'];

        ?>


            <article
    class="product"
    data-product-url="product_details.php?id=<?php echo (int) $row['id']; ?>"
    tabindex="0"
    role="link"
    aria-label="View details for <?php echo e($row['title']); ?>"
>

    <img
        src="Product/<?php echo e($row['image']); ?>"
        alt="<?php echo e($row['title']); ?>"
    >


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
            In Stock:
            <?php echo $stock; ?>
        </p>

    <?php } else { ?>

        <p class="stock out-of-stock">
            Out of Stock
        </p>

    <?php } ?>


    <?php if ($stock > 0) { ?>

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

        <button
            type="button"
            disabled
        >
            Out of Stock
        </button>

    <?php } ?>

</article>


        <?php

        }

        mysqli_stmt_close($stmt);

        ?>


    </div>


</section>


</main>



<footer>

    <p>
        Comic Book World - Sri Lanka
    </p>

</footer>


</body>

</html>