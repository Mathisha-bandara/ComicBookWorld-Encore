<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('products.update');
require_admin_access(false);

/*
|--------------------------------------------------------------------------
| Validate Product ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id'])
    || !filter_var($_GET['id'], FILTER_VALIDATE_INT)
) {
    die("Invalid product ID.");
}

$id = (int) $_GET['id'];

$message = "";


/*
|--------------------------------------------------------------------------
| Update Product
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {

        $message = "Invalid security token.";

    } else {

        $title =
            trim($_POST['title'] ?? '');

        $category =
            trim($_POST['category'] ?? '');

        $description =
            trim($_POST['description'] ?? '');

        $price =
            $_POST['price'] ?? '';

        $image =
            trim($_POST['image'] ?? '');

        $stock =
            $_POST['stock'] ?? '';


        /*
        |--------------------------------------------------------------------------
        | Validate Required Fields
        |--------------------------------------------------------------------------
        */

        if (
            $title === ''
            || $category === ''
            || $description === ''
            || $price === ''
            || $image === ''
            || $stock === ''
        ) {

            $message =
                "Please fill in all fields.";

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Price
        |--------------------------------------------------------------------------
        */

        elseif (
            !is_numeric($price)
            || (float) $price < 0
        ) {

            $message =
                "Please enter a valid price.";

        }


        /*
        |--------------------------------------------------------------------------
        | Validate Stock
        |--------------------------------------------------------------------------
        */

        elseif (
            filter_var(
                $stock,
                FILTER_VALIDATE_INT
            ) === false
            || (int) $stock < 0
        ) {

            $message =
                "Please enter a valid stock quantity.";

        }


        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        else {

            $price =
                (float) $price;

            $stock =
                (int) $stock;


            $sql = "
                UPDATE products
                SET
                    title = ?,
                    category = ?,
                    description = ?,
                    price = ?,
                    image = ?,
                    stock = ?
                WHERE id = ?
            ";


            $stmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );


            if (!$stmt) {

                $message =
                    "Unable to prepare product update.";

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssdsii",
                    $title,
                    $category,
                    $description,
                    $price,
                    $image,
                    $stock,
                    $id
                );


                if (
                    mysqli_stmt_execute($stmt)
                ) {

                    mysqli_stmt_close($stmt);

                    header(
                        "Location: ManageProducts.php"
                    );

                    exit();

                } else {

                    $message =
                        "Unable to update product.";

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Get Existing Product
|--------------------------------------------------------------------------
*/

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


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if (!$stmt) {
    die("Unable to load product.");
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


if (
    mysqli_num_rows($result) !== 1
) {

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
        Edit Product
    </title>

    <link
        rel="stylesheet"
        href="../css/WebPage.css"
    >

</head>


<body>


<div class="form-container">


    <h2>
        Edit Product
    </h2>


    <?php if ($message !== "") { ?>

        <p>
            <?php echo e($message); ?>
        </p>

    <?php } ?>


    <form method="POST">


        <!-- CSRF protection -->

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo e(csrf_token()); ?>"
        >


        <!-- Product Title -->

        <label>
            Product Title
        </label>

        <input
            type="text"
            name="title"
            value="<?php echo e($product['title']); ?>"
            required
        >


        <br><br>


        <!-- Category -->

        <label>
            Category
        </label>

        <input
            type="text"
            name="category"
            value="<?php echo e($product['category']); ?>"
            required
        >


        <br><br>


        <!-- Description -->

        <label>
            Description
        </label>

        <textarea
            name="description"
            required
        ><?php echo e($product['description']); ?></textarea>


        <br><br>


        <!-- Price -->

        <label>
            Price (LKR)
        </label>

        <input
            type="number"
            step="0.01"
            min="0"
            name="price"
            value="<?php echo e($product['price']); ?>"
            required
        >


        <br><br>


        <!-- Stock -->

        <label>
            Stock Quantity
        </label>

        <input
            type="number"
            name="stock"
            min="0"
            value="<?php echo (int) $product['stock']; ?>"
            required
        >


        <br><br>


        <!-- Image -->

        <label>
            Image Filename
        </label>

        <input
            type="text"
            name="image"
            value="<?php echo e($product['image']); ?>"
            required
        >


        <br><br>


        <button
            type="submit"
            name="update"
        >
            Update Product
        </button>


        <a
            href="ManageProducts.php"
        >
            Cancel
        </a>


    </form>


</div>


</body>

</html>