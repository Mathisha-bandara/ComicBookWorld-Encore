<?php

session_start();

include("includes/db.php");
include("includes/functions.php");


// ==================================================
// 1. REQUIRE LOGIN
// ==================================================

if (!isset($_SESSION['user_id'])) {

    die("Please login before placing an order.");
}

$user_id = (int) $_SESSION['user_id'];


// ==================================================
// 2. REQUIRE POST REQUEST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: Cart.php");
    exit();
}


// ==================================================
// 3. VERIFY CSRF TOKEN
// ==================================================

if (
    !isset($_POST['csrf_token'])
    || !verify_csrf_token($_POST['csrf_token'])
) {

    die("Invalid security token.");
}


// ==================================================
// 4. GET CART DATA
// ==================================================

if (
    !isset($_POST['cart_data'])
    || empty($_POST['cart_data'])
) {

    die("Your cart is empty.");
}


$cart = json_decode(
    $_POST['cart_data'],
    true
);


if (
    !is_array($cart)
    || count($cart) === 0
) {

    die("Invalid cart data.");
}



// ==================================================
// 4A. GET PROMO CODE
// ==================================================

$promo_code = '';

if (isset($_POST['promo_code'])) {

    $promo_code =
        strtoupper(
            trim($_POST['promo_code'])
        );
}

// ==================================================
// 5. NORMALIZE CART
// ==================================================

// Combine duplicate product IDs.

$cartItems = [];

foreach ($cart as $item) {

    if (
        !isset($item['id'])
        || !filter_var(
            $item['id'],
            FILTER_VALIDATE_INT
        )
    ) {

        die("Invalid product ID.");
    }

    $product_id = (int) $item['id'];

    $quantity = 1;

    if (
        isset($item['quantity'])
        && filter_var(
            $item['quantity'],
            FILTER_VALIDATE_INT
        )
    ) {

        $quantity = (int) $item['quantity'];
    }

    if ($quantity < 1 || $quantity > 99) {

        die("Invalid product quantity.");
    }

    if (isset($cartItems[$product_id])) {

        $cartItems[$product_id] += $quantity;

    } else {

        $cartItems[$product_id] = $quantity;
    }
}


if (empty($cartItems)) {

    die("Your cart is empty.");
}


// ==================================================
// 6. START TRANSACTION
// ==================================================

mysqli_begin_transaction($conn);


try {

    $subtotal = 0;
    $discount_amount = 0;
    $final_total = 0;
    
    $items = [];

    // ==================================================
    // 7. PREPARE PRODUCT QUERY
    // ==================================================

    $productStmt = mysqli_prepare(
        $conn,
        "SELECT id, title, price, stock
         FROM products
         WHERE id = ?
         FOR UPDATE"
    );


    if (!$productStmt) {

        throw new Exception(
            "Could not prepare product query."
        );
    }


    // ==================================================
    // 8. VERIFY PRODUCTS + PRICE + STOCK
    // ==================================================

    foreach ($cartItems as $product_id => $quantity) {

        mysqli_stmt_bind_param(
            $productStmt,
            "i",
            $product_id
        );

        mysqli_stmt_execute(
            $productStmt
        );

        $result = mysqli_stmt_get_result(
            $productStmt
        );


        if (
            mysqli_num_rows($result) !== 1
        ) {

            throw new Exception(
                "One of the products no longer exists."
            );
        }


        $product =
            mysqli_fetch_assoc($result);


        $stock =
            (int) $product['stock'];

        $price =
            (float) $product['price'];


        // ----------------------------------------------
        // Check stock
        // ----------------------------------------------

        if ($stock < $quantity) {

            throw new Exception(
                $product['title']
                . " only has "
                . $stock
                . " item(s) available."
            );
        }


        // ----------------------------------------------
        // Calculate using DATABASE price
        // ----------------------------------------------

        $subtotal +=
    $price * $quantity;


        $items[] = [
            'product_id' => $product_id,
            'quantity' => $quantity
        ];
    }


    mysqli_stmt_close(
        $productStmt
    );

// ==================================================
// 9. VALIDATE PROMOTION
// ==================================================

if ($promo_code !== '') {

    $promoStmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            code,
            discount_percent,
            minimum_order,
            maximum_discount,
            starts_at,
            expires_at,
            usage_limit,
            used_count,
            active
         FROM promotions
         WHERE code = ?
         FOR UPDATE"
    );

    if (!$promoStmt) {

        throw new Exception(
            "Could not prepare promotion query."
        );
    }


    mysqli_stmt_bind_param(
        $promoStmt,
        "s",
        $promo_code
    );


    mysqli_stmt_execute(
        $promoStmt
    );


    $promoResult =
        mysqli_stmt_get_result(
            $promoStmt
        );


    if (
        mysqli_num_rows($promoResult) !== 1
    ) {

        throw new Exception(
            "This promotion code is not valid."
        );
    }


    $promotion =
        mysqli_fetch_assoc(
            $promoResult
        );


    mysqli_stmt_close(
        $promoStmt
    );


    // ----------------------------------------------
    // Check active status
    // ----------------------------------------------

    if (
        (int) $promotion['active'] !== 1
    ) {

        throw new Exception(
            "This promotion is no longer active."
        );
    }


    // ----------------------------------------------
    // Check start date
    // ----------------------------------------------

    if (
        !empty($promotion['starts_at'])
        && strtotime(
            $promotion['starts_at']
        ) > time()
    ) {

        throw new Exception(
            "This promotion has not started yet."
        );
    }


    // ----------------------------------------------
    // Check expiry
    // ----------------------------------------------

    if (
        !empty($promotion['expires_at'])
        && strtotime(
            $promotion['expires_at']
        ) < time()
    ) {

        throw new Exception(
            "This promotion has expired."
        );
    }


    // ----------------------------------------------
    // Check usage limit
    // ----------------------------------------------

    if (
        $promotion['usage_limit'] !== null
        && (int) $promotion['used_count']
            >= (int) $promotion['usage_limit']
    ) {

        throw new Exception(
            "This promotion has reached its usage limit."
        );
    }


    // ----------------------------------------------
    // Check minimum order
    // ----------------------------------------------

    $minimum_order =
        (float) $promotion['minimum_order'];


    if (
        $subtotal < $minimum_order
    ) {

        throw new Exception(
            "A minimum order of LKR "
            . number_format(
                $minimum_order,
                2
            )
            . " is required for this promotion."
        );
    }


    // ----------------------------------------------
    // Calculate discount
    // ----------------------------------------------

    $discount_percent =
        (float) $promotion['discount_percent'];


    $discount_amount =
        $subtotal
        * ($discount_percent / 100);


    // ----------------------------------------------
    // Apply maximum discount limit
    // ----------------------------------------------

    if (
        $promotion['maximum_discount'] !== null
        && $discount_amount
            > (float) $promotion['maximum_discount']
    ) {

        $discount_amount =
            (float) $promotion['maximum_discount'];
    }
}


// ==================================================
// 10. CALCULATE FINAL TOTAL
// ==================================================

$final_total =
    $subtotal - $discount_amount;

// ==================================================
// 11. CREATE ORDER
// ==================================================

    $orderStmt = mysqli_prepare(
        $conn,
        "INSERT INTO orders
        (user_id, total, promo_code, discount_amount, status)
        VALUES (?, ?, ?, ?, 'Pending')"
    );


    if (!$orderStmt) {

        throw new Exception(
            "Could not prepare order query."
        );
    }


    mysqli_stmt_bind_param(
    $orderStmt,
    "idsd",
    $user_id,
    $final_total,
    $promo_code,
    $discount_amount
);


    if (
        !mysqli_stmt_execute(
            $orderStmt
        )
    ) {

        throw new Exception(
            "Could not create order."
        );
    }


    $order_id =
        mysqli_insert_id($conn);

        
// ==================================================
// 11. INCREMENT PROMOTION USAGE
// ==================================================

if ($promo_code !== '') {

    $usageStmt = mysqli_prepare(
        $conn,
        "UPDATE promotions
         SET used_count = used_count + 1
         WHERE code = ?
         AND active = 1
         AND (
             usage_limit IS NULL
             OR used_count < usage_limit
         )"
    );


    if (!$usageStmt) {

        throw new Exception(
            "Could not prepare promotion usage update."
        );
    }


    mysqli_stmt_bind_param(
        $usageStmt,
        "s",
        $promo_code
    );


    if (
        !mysqli_stmt_execute(
            $usageStmt
        )
    ) {

        throw new Exception(
            "Could not update promotion usage."
        );
    }


    if (
        mysqli_stmt_affected_rows(
            $usageStmt
        ) !== 1
    ) {

        throw new Exception(
            "This promotion is no longer available."
        );
    }


    mysqli_stmt_close(
        $usageStmt
    );
}



    // ==================================================
    // 13. INSERT ORDER ITEMS
    // ==================================================

    $itemStmt = mysqli_prepare(
        $conn,
        "INSERT INTO order_items
        (order_id, product_id, quantity)
        VALUES (?, ?, ?)"
    );


    if (!$itemStmt) {

        throw new Exception(
            "Could not prepare order item query."
        );
    }


    foreach ($items as $item) {

        $product_id =
            $item['product_id'];

        $quantity =
            $item['quantity'];


        mysqli_stmt_bind_param(
            $itemStmt,
            "iii",
            $order_id,
            $product_id,
            $quantity
        );


        if (
            !mysqli_stmt_execute(
                $itemStmt
            )
        ) {

            throw new Exception(
                "Could not save order items."
            );
        }
    }


    mysqli_stmt_close(
        $itemStmt
    );


    // ==================================================
    // 14. REDUCE STOCK
    // ==================================================

    $stockStmt = mysqli_prepare(
        $conn,
        "UPDATE products
         SET stock = stock - ?
         WHERE id = ?
         AND stock >= ?"
    );


    if (!$stockStmt) {

        throw new Exception(
            "Could not prepare stock update."
        );
    }


    foreach ($items as $item) {

        $product_id =
            $item['product_id'];

        $quantity =
            $item['quantity'];


        mysqli_stmt_bind_param(
            $stockStmt,
            "iii",
            $quantity,
            $product_id,
            $quantity
        );


        if (
            !mysqli_stmt_execute(
                $stockStmt
            )
        ) {

            throw new Exception(
                "Could not update product stock."
            );
        }


        if (
            mysqli_stmt_affected_rows(
                $stockStmt
            ) !== 1
        ) {

            throw new Exception(
                "Stock changed while processing your order."
            );
        }
    }


    mysqli_stmt_close(
        $stockStmt
    );


    // ==================================================
    // 15. COMMIT EVERYTHING
    // ==================================================

    mysqli_commit($conn);

    


    // Generate a new CSRF token next time it is needed.

    unset($_SESSION['csrf_token']);


    // ==================================================
    // 16. SUCCESS PAGE
    // ==================================================
    ?>

    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>Order Successful - Comic Book World</title>

        <link
            rel="stylesheet"
            href="css/WebPage.css"
        >

    </head>

    <body>

        <main class="container">

            <h1>Order Placed Successfully!</h1>

            <p>
                Thank you for your purchase.
            </p>

            <p>
                Your Order ID is:
                <strong>
                    #<?php echo (int) $order_id; ?>
                </strong>
            </p>

            <p>
                Order Total:
                <strong>
                    LKR
                    <?php echo number_format(
                        $final_total,
                        2
                    ); ?>
                </strong>
            </p>

            <?php if ($promo_code !== ''): ?>

    <p>
        Promo Code:
        <strong>
            <?php echo e($promo_code); ?>
        </strong>
    </p>

    <p>
        Discount:
        <strong>
            LKR
            <?php echo number_format(
                $discount_amount,
                2
            ); ?>
        </strong>
    </p>

<?php endif; ?>

            <p>
                Your order is currently:
                <strong>Pending</strong>
            </p>

            <p>
                <a href="PurchaseHistory.php">
                    View My Orders
                </a>
            </p>

            <p>
                <a href="OrderTracking.php">
                    Track My Order
                </a>
            </p>

            <p>
                <a href="Product&ServiceDisplay.php">
                    Continue Shopping
                </a>
            </p>

            <script>
            localStorage.removeItem("cart");
        </script>

        </main>

    </body>

    </html>

    <?php


} catch (Exception $e) {

    // ==================================================
    // 17. ROLLBACK
    // ==================================================

    mysqli_rollback($conn);


    die(
        "Order could not be completed: "
        . e($e->getMessage())
    );
}

?>