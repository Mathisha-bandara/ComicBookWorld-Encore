<?php

session_start();

include("includes/db.php");
include("includes/functions.php");


// ==========================================
// VALIDATE PRODUCT ID
// ==========================================

if (
    !isset($_GET['id'])
    || !filter_var(
        $_GET['id'],
        FILTER_VALIDATE_INT
    )
) {
    die("Product not found.");
}


$id = (int) $_GET['id'];


// ==========================================
// GET PRODUCT
// ==========================================

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
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);


if (mysqli_num_rows($result) !== 1)
{
    mysqli_stmt_close($stmt);

    die("Product not found.");
}


$product =
    mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

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
        <?php echo e($product['title']); ?>
        - Comic Book World
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
        Comic Details
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

            <li>
                <a href="Cart.php">
                    Cart (<span id="cart-count">0</span>)
                </a>
            </li>

        </ul>

    </nav>

</header>



<main class="product-detail">


    <div class="product-detail-image">

        <img
            src="Product/<?php echo e($product['image']); ?>"
            alt="<?php echo e($product['title']); ?>"
        >

    </div>



    <div class="product-detail-info">


        <p class="product-category">
            <?php echo e($product['category']); ?>
        </p>


        <h2>
            <?php echo e($product['title']); ?>
        </h2>


        <p class="product-detail-description">
            <?php echo e($product['description']); ?>
        </p>


        <h3 class="product-detail-price">

            LKR
            <?php echo number_format(
                (float) $product['price'],
                2
            ); ?>

        </h3>


        <?php if ((int) $product['stock'] > 0) { ?>


            <p class="stock available">

                <?php echo (int) $product['stock']; ?>

                copies available

            </p>


            <label for="product-quantity">
    Quantity
</label>

<div class="quantity-selector">

    <button
        type="button"
        class="quantity-button"
        id="quantity-decrease"
        aria-label="Decrease quantity"
    >
        −
    </button>

    <input
        id="product-quantity"
        type="number"
        min="1"
        max="<?php echo (int) $product['stock']; ?>"
        value="1"
        aria-label="Product quantity"
    >

    <button
        type="button"
        class="quantity-button"
        id="quantity-increase"
        aria-label="Increase quantity"
    >
        +
    </button>

</div>

<button
    type="button"
    onclick="addProductDetailToCart(
        <?php echo (int) $product['id']; ?>,
        <?php echo htmlspecialchars(
            json_encode($product['title']),
            ENT_QUOTES,
            'UTF-8'
        ); ?>,
        <?php echo (float) $product['price']; ?>,
        <?php echo (int) $product['stock']; ?>
    )"
>
    Add to Cart
</button>


        <?php } else { ?>


            <p class="stock out-of-stock">
                This comic is currently out of stock.
            </p>


        <?php } ?>


        <br><br>


        <a
            href="Product&ServiceDisplay.php"
        >
            ← Back to Products
        </a>


    </div>


</main>



<footer>

    <p>
        Comic Book World - Sri Lanka
    </p>

</footer>

<script>

/* ==========================================
   PRODUCT DETAIL QUANTITY CONTROLS
========================================== */

document.addEventListener(
    'DOMContentLoaded',
    function()
    {
        const quantityInput =
            document.getElementById(
                'product-quantity'
            );

        const decreaseButton =
            document.getElementById(
                'quantity-decrease'
            );

        const increaseButton =
            document.getElementById(
                'quantity-increase'
            );


        if (
            !quantityInput ||
            !decreaseButton ||
            !increaseButton
        )
        {
            return;
        }


        const maxQuantity =
            Number(quantityInput.max);


        function updateQuantityButtons()
        {
            const quantity =
                Number(quantityInput.value) || 1;


            decreaseButton.disabled =
                quantity <= 1;


            increaseButton.disabled =
                quantity >= maxQuantity;
        }


        /* --------------------------------------
           DECREASE QUANTITY
        -------------------------------------- */

        decreaseButton.addEventListener(
            'click',
            function()
            {
                let quantity =
                    Number(quantityInput.value) || 1;


                if (quantity > 1)
                {
                    quantity--;

                    quantityInput.value =
                        quantity;

                    updateQuantityButtons();
                }
            }
        );


        /* --------------------------------------
           INCREASE QUANTITY
        -------------------------------------- */

        increaseButton.addEventListener(
            'click',
            function()
            {
                let quantity =
                    Number(quantityInput.value) || 1;


                if (quantity < maxQuantity)
                {
                    quantity++;

                    quantityInput.value =
                        quantity;

                    updateQuantityButtons();
                }
            }
        );


        /* --------------------------------------
           MANUAL QUANTITY INPUT
        -------------------------------------- */

      quantityInput.addEventListener(
    'change',
    function()
    {
        let quantity =
            Number(quantityInput.value);


        if (
            !Number.isInteger(quantity) ||
            quantity < 1
        )
        {
            quantityInput.value = 1;
        }
        else if (
            quantity > maxQuantity
        )
        {
            quantityInput.value =
                maxQuantity;

            alert(
                "Only " +
                maxQuantity +
                " copies are available."
            );
        }


        updateQuantityButtons();
    }
);


        /* --------------------------------------
           INITIAL BUTTON STATE
        -------------------------------------- */

        updateQuantityButtons();
    }
);


/* ==========================================
   ADD PRODUCT DETAIL TO CART
========================================== */

function addProductDetailToCart(
    productId,
    itemName,
    itemPrice,
    stock
)
{
    const quantityInput =
        document.getElementById(
            'product-quantity'
        );


    if (!quantityInput)
    {
        return;
    }


    const requestedQuantity =
        Number(quantityInput.value);


    /*
        Validate the requested quantity.
    */

    if (
        !Number.isInteger(requestedQuantity) ||
        requestedQuantity < 1
    )
    {
        alert(
            "Please enter a valid quantity."
        );

        quantityInput.value = 1;

        return;
    }


    /*
        Find the product in the cart.
    */

    const cartItems =
        JSON.parse(
            localStorage.getItem('cart')
        ) || [];


    const existingItem =
        cartItems.find(function(item)
        {
            return Number(item.id) ===
                Number(productId);
        });


    const currentCartQuantity =
        existingItem
            ? Number(existingItem.quantity) || 0
            : 0;


    /*
        Work out how many copies can
        actually be added.
    */

    const remainingStock =
        Number(stock) -
        currentCartQuantity;


    if (remainingStock <= 0)
    {
        alert(
            "You already have the maximum " +
            stock +
            " copies in your cart."
        );

        return;
    }


    /*
        The requested quantity must fit
        inside the remaining stock.
    */

    if (
        requestedQuantity >
        remainingStock
    )
    {
        alert(
            "You can only add " +
            remainingStock +
            " more cop" +
            (remainingStock === 1
                ? "y"
                : "ies") +
            ". " +
            "You already have " +
            currentCartQuantity +
            " in your cart."
        );

        return;
    }


    /*
        Add the selected quantity
        without showing individual
        popups.
    */

    for (
        let i = 0;
        i < requestedQuantity;
        i++
    )
    {
        addToCart(
            productId,
            itemName,
            itemPrice,
            stock,
            false
        );
    }


    /*
        ONE confirmation message.
    */

    alert(
        requestedQuantity +
        " item" +
        (requestedQuantity === 1
            ? ""
            : "s") +
        " added to your cart!"
    );
}
</script>
</body>

</html>