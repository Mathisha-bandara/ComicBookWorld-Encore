<?php

session_start();

include("includes/db.php");
include("includes/functions.php");

$productCatalog = [];

$sql = "
    SELECT
        id,
        image,
        stock
    FROM products
";

$result = mysqli_query($conn, $sql);

if ($result)
{
    while ($row = mysqli_fetch_assoc($result))
    {
        $productCatalog[$row['id']] = [
            'image' => $row['image'],
            'stock' => (int) $row['stock']
        ];
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

    <title>Shopping Cart - Comic Book World</title>

    <link
        rel="stylesheet"
        href="css/WebPage.css"
    >

    <script>
    window.productCatalog =
        <?php echo json_encode(
            $productCatalog,
            JSON_HEX_TAG |
            JSON_HEX_AMP |
            JSON_HEX_APOS |
            JSON_HEX_QUOT
        ); ?>;
        </script>

    <script
        src="js/WebPage.js"
        defer
    ></script>

</head>


<body>


<header>

    <h1>Your Comic Collection</h1>

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

            if (isset($_SESSION['fullname'])) {

                echo '
                <li>
                    <a href="Profile.php">
                        Profile
                    </a>
                </li>
                ';

                echo '
                <li>
                    <a href="logout.php">
                        Logout
                    </a>
                </li>
                ';

            } else {

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


<main class="cart-page">

    <div class="cart-layout">

        <!-- ==========================================
             CART ITEMS
        =========================================== -->

        <section class="cart-items-section">

            <div class="cart-heading">

                <p class="cart-label">
                    YOUR SHOPPING CART
                </p>

                <h2>
                    Items in Your Cart
                </h2>

                <p class="cart-subheading">
                    Review your comics before proceeding
                    to checkout.
                </p>

            </div>


            <div
                id="cart-items"
                class="cart-items-list"
            ></div>


            <a
                href="Product&ServiceDisplay.php"
                class="continue-shopping"
            >
                ← Continue Shopping
            </a>

        </section>


        <!-- ==========================================
             ORDER SUMMARY
        =========================================== -->

        <aside class="cart-summary">

            <h2>
                Order Summary
            </h2>


            <div class="summary-row">

                <span>
                    Items
                </span>

                <strong id="summary-item-count">
                    0
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Subtotal
                </span>

                <strong id="summary-subtotal">
                    LKR 0.00
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Shipping
                </span>

                <span class="summary-note">
                    Calculated at checkout
                </span>

            </div>

            <div class="promo-section">

    <label for="promo-code">
        Have a promo code?
    </label>

    <div class="promo-input-row">

        <input
            type="text"
            id="promo-code"
            placeholder="Enter promo code"
            maxlength="50"
            autocomplete="off"
        >

        <button
            type="button"
            id="apply-promo-button"
        >
            Apply
        </button>

    </div>

    <p
        id="promo-message"
        class="promo-message"
    ></p>

</div>


            <div class="summary-divider"></div>

            <div
    id="discount-row"
    class="summary-row discount-row"
    style="display: none;"
>

    <span>
        Discount
    </span>

    <strong id="discount-amount">
        -LKR 0.00
    </strong>

</div>


            <div class="summary-total">

                <span>
                    Total
                </span>

                <strong id="cart-total">
                    Total: LKR 0.00
                </strong>

            </div>


            <form
                action="Checkout.php"
                method="POST"
                class="checkout-form"
            >

                <input
                    type="hidden"
                    id="order-total"
                    name="total"
                >


                <input
                    type="hidden"
                    id="cart-data"
                    name="cart_data"
                >


                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e(csrf_token()); ?>"
                >

                <input
    type="hidden"
    id="promo-code-hidden"
    name="promo_code"
    value=""
>


                <button
                    type="submit"
                    class="checkout-button"
                >
                    Proceed to Checkout
                </button>

            </form>


            <button
                type="button"
                onclick="clearCart()"
                class="clear-cart-button"
            >
                Clear Cart
            </button>


            <div class="checkout-trust">

    <strong>
        🔒 Secure order processing
    </strong>

    <span>
        Your order details are validated and
        securely processed.
    </span>

</div>

        </aside>

    </div>

</main>


<footer>

    <p>
        Comic Book World - Sri Lanka
    </p>

</footer>


</body>

</html>