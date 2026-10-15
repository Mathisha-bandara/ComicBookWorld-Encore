<?php

session_start();

include("includes/db.php");
include("includes/functions.php");

header("Content-Type: application/json");


/*
==================================================
AUTHENTICATION
==================================================
*/

if (!isset($_SESSION['user_id']))
{
    echo json_encode([
        "success" => false,
        "message" => "Please log in before using a promo code."
    ]);

    exit();
}


/*
==================================================
REQUEST METHOD
==================================================
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit();
}


/*
==================================================
CSRF VALIDATION
==================================================
*/

if (
    !isset($_POST['csrf_token']) ||
    !verify_csrf_token($_POST['csrf_token'])
)
{
    echo json_encode([
        "success" => false,
        "message" => "Invalid security token."
    ]);

    exit();
}


/*
==================================================
PROMO CODE
==================================================
*/

$promoCode = strtoupper(
    trim($_POST['promo_code'] ?? '')
);

if ($promoCode === '')
{
    echo json_encode([
        "success" => false,
        "message" => "Please enter a promo code."
    ]);

    exit();
}


/*
==================================================
CART DATA
==================================================
*/

$cartData = $_POST['cart_data'] ?? '';

$cart = json_decode(
    $cartData,
    true
);

if (
    !is_array($cart) ||
    count($cart) === 0
)
{
    echo json_encode([
        "success" => false,
        "message" => "Your cart is empty."
    ]);

    exit();
}


/*
==================================================
NORMALIZE CART
==================================================
*/

$cartItems = [];

foreach ($cart as $item)
{
    if (
        !isset($item['id']) ||
        !filter_var(
            $item['id'],
            FILTER_VALIDATE_INT
        )
    )
    {
        continue;
    }

    $productId = (int) $item['id'];

    $quantity =
        isset($item['quantity'])
            ? (int) $item['quantity']
            : 0;

    if (
        $quantity < 1 ||
        $quantity > 99
    )
    {
        continue;
    }

    if (isset($cartItems[$productId]))
    {
        $cartItems[$productId] += $quantity;
    }
    else
    {
        $cartItems[$productId] = $quantity;
    }
}

if (count($cartItems) === 0)
{
    echo json_encode([
        "success" => false,
        "message" => "Your cart contains invalid items."
    ]);

    exit();
}


/*
==================================================
CALCULATE SUBTOTAL FROM DATABASE
==================================================
*/

$subtotal = 0.00;

$productStmt = mysqli_prepare(
    $conn,
    "SELECT id, price, stock
     FROM products
     WHERE id = ?"
);

if (!$productStmt)
{
    echo json_encode([
        "success" => false,
        "message" => "Unable to validate your cart."
    ]);

    exit();
}


foreach ($cartItems as $productId => $quantity)
{
    mysqli_stmt_bind_param(
        $productStmt,
        "i",
        $productId
    );

    mysqli_stmt_execute(
        $productStmt
    );

    $productResult =
        mysqli_stmt_get_result(
            $productStmt
        );

    $product =
        mysqli_fetch_assoc(
            $productResult
        );

    if (!$product)
    {
        mysqli_stmt_close($productStmt);

        echo json_encode([
            "success" => false,
            "message" => "One of the products in your cart no longer exists."
        ]);

        exit();
    }

    $stock =
        (int) $product['stock'];

    if ($quantity > $stock)
    {
        mysqli_stmt_close($productStmt);

        echo json_encode([
            "success" => false,
            "message" =>
                "Not enough stock available for one of the products."
        ]);

        exit();
    }

    $price =
        (float) $product['price'];

    $subtotal +=
        $price * $quantity;
}

mysqli_stmt_close($productStmt);



/*
==================================================
FIND PROMOTION
==================================================
*/

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
     LIMIT 1"
);

if (!$promoStmt)
{
    echo json_encode([
        "success" => false,
        "message" => "Unable to validate the promo code."
    ]);

    exit();
}

mysqli_stmt_bind_param(
    $promoStmt,
    "s",
    $promoCode
);

mysqli_stmt_execute(
    $promoStmt
);

$promoResult =
    mysqli_stmt_get_result(
        $promoStmt
    );

$promotion =
    mysqli_fetch_assoc(
        $promoResult
    );

mysqli_stmt_close($promoStmt);


/*
==================================================
PROMOTION EXISTS
==================================================
*/

if (!$promotion)
{
    echo json_encode([
        "success" => false,
        "message" => "Invalid promo code."
    ]);

    exit();
}


/*
==================================================
ACTIVE CHECK
==================================================
*/

if ((int) $promotion['active'] !== 1)
{
    echo json_encode([
        "success" => false,
        "message" => "This promo code is currently inactive."
    ]);

    exit();
}


/*
==================================================
START DATE
==================================================
*/

if (
    !empty($promotion['starts_at']) &&
    strtotime($promotion['starts_at']) > time()
)
{
    echo json_encode([
        "success" => false,
        "message" => "This promo code is not active yet."
    ]);

    exit();
}


/*
==================================================
EXPIRY DATE
==================================================
*/

if (
    !empty($promotion['expires_at']) &&
    strtotime($promotion['expires_at']) < time()
)
{
    echo json_encode([
        "success" => false,
        "message" => "This promo code has expired."
    ]);

    exit();
}


/*
==================================================
USAGE LIMIT
==================================================
*/

if (
    $promotion['usage_limit'] !== null &&
    (int) $promotion['used_count'] >=
    (int) $promotion['usage_limit']
)
{
    echo json_encode([
        "success" => false,
        "message" => "This promo code has reached its usage limit."
    ]);

    exit();
}


/*
==================================================
MINIMUM ORDER
==================================================
*/

$minimumOrder =
    (float) $promotion['minimum_order'];

if ($subtotal < $minimumOrder)
{
    echo json_encode([
        "success" => false,
        "message" =>
            "A minimum order of LKR " .
            number_format(
                $minimumOrder,
                2
            ) .
            " is required for this promo code."
    ]);

    exit();
}


/*
==================================================
CALCULATE DISCOUNT
==================================================
*/

$discountPercent =
    (float) $promotion['discount_percent'];

$discount =
    $subtotal *
    ($discountPercent / 100);


/*
==================================================
MAXIMUM DISCOUNT
==================================================
*/

if (
    $promotion['maximum_discount'] !== null
)
{
    $maximumDiscount =
        (float) $promotion['maximum_discount'];

    if ($discount > $maximumDiscount)
    {
        $discount =
            $maximumDiscount;
    }
}


/*
==================================================
FINAL TOTAL
==================================================
*/

$total =
    $subtotal - $discount;

if ($total < 0)
{
    $total = 0;
}


/*
==================================================
SUCCESS
==================================================
*/

echo json_encode([
    "success" => true,
    "code" => $promotion['code'],
    "discount" => round($discount, 2),
    "subtotal" => round($subtotal, 2),
    "total" => round($total, 2),
    "message" =>
        "Promo code " .
        $promotion['code'] .
        " applied successfully."
]);

exit();

?>