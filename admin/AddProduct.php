<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('products.create');
require_admin_access(false);

$message = "";


if (isset($_POST['add']))
{

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    )
    {
        $message = "Invalid security token.";
    }
    else
    {

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


        if (
            $title === ''
            || $category === ''
            || $description === ''
            || $price === ''
            || $image === ''
            || $stock === ''
        )
        {
            $message =
                "Please fill in all fields.";
        }
        elseif (
            !is_numeric($price)
            || $price < 0
        )
        {
            $message =
                "Please enter a valid price.";
        }
        elseif (
            !filter_var(
                $stock,
                FILTER_VALIDATE_INT
            )
            || (int) $stock < 0
        )
        {
            $message =
                "Please enter a valid stock quantity.";
        }
        else
        {

            $price =
                (float) $price;

            $stock =
                (int) $stock;


            $sql = "
                INSERT INTO products
                (
                    title,
                    category,
                    description,
                    price,
                    image,
                    stock
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ";


            $stmt =
                mysqli_prepare(
                    $conn,
                    $sql
                );


            mysqli_stmt_bind_param(
                $stmt,
                "sssdsi",
                $title,
                $category,
                $description,
                $price,
                $image,
                $stock
            );


            if (
                mysqli_stmt_execute($stmt)
            )
            {
                mysqli_stmt_close($stmt);

                header(
                    "Location: ManageProducts.php"
                );

                exit();
            }


            $message =
                "Unable to add product.";

            mysqli_stmt_close($stmt);
        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Add Product
    </title>

    <link
        rel="stylesheet"
        href="../css/WebPage.css"
    >

</head>


<body>


<div class="form-container">


<h2>
    Add Product
</h2>


<?php if ($message !== "") { ?>

    <p>
        <?php echo e($message); ?>
    </p>

<?php } ?>


<form method="POST">


<input
    type="hidden"
    name="csrf_token"
    value="<?php echo e(csrf_token()); ?>"
>


<label>
    Product Title
</label>

<input
    type="text"
    name="title"
    required
>


<label>
    Category
</label>

<input
    type="text"
    name="category"
    required
>


<label>
    Description
</label>

<textarea
    name="description"
    required
></textarea>


<label>
    Price (LKR)
</label>

<input
    type="number"
    name="price"
    min="0"
    step="0.01"
    required
>


<label>
    Stock Quantity
</label>

<input
    type="number"
    name="stock"
    min="0"
    value="10"
    required
>


<label>
    Image Filename
</label>

<input
    type="text"
    name="image"
    placeholder="comic1.jpg"
    required
>


<br>


<button
    type="submit"
    name="add"
>
    Add Product
</button>


</form>


</div>


</body>

</html>