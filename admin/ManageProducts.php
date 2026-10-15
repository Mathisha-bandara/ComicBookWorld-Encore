<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('products.view');


// ==================================================
// PAGE INFORMATION
// ==================================================

$pageTitle = "Manage Products";


// ==================================================
// GET PRODUCTS
// ==================================================

$result = mysqli_query(
    $conn,
    "SELECT id, title, price, stock
     FROM products
     ORDER BY id ASC"
);

if (!$result) {

    die("Could not load products.");

}


// ==================================================
// ADMIN SHELL
// ==================================================

include("includes/admin_header.php");

include("includes/admin_sidebar.php");

?>

<div class="admin-content">

    <?php include("includes/admin_topbar.php"); ?>


    <main class="admin-page">


        <!-- ========================================== -->
        <!-- PAGE HEADER -->
        <!-- ========================================== -->

        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    STORE MANAGEMENT
                </p>

                <h1 class="admin-page-title">
                    Manage Products
                </h1>

                <p class="admin-page-description">
                    Manage your comic book products,
                    prices and inventory.
                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="AdminPanel.php"
                    class="btn btn-outline-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Dashboard

                </a>


                <a
                    href="AddProduct.php"
                    class="btn admin-btn-primary"
                >

                    <i class="bi bi-plus-lg"></i>

                    Add Product

                </a>

            </div>

        </div>


        <!-- ========================================== -->
        <!-- PRODUCT TABLE -->
        <!-- ========================================== -->

        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Product Inventory
                    </h2>

                    <p class="admin-panel-subtitle">
                        View and manage products currently
                        available in the store.
                    </p>

                </div>

            </div>


            <div class="table-responsive">

                <table class="table admin-table align-middle">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Product</th>

                            <th>Price</th>

                            <th>Stock</th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while (
                        $row = mysqli_fetch_assoc($result)
                    ): ?>

                        <tr>

                            <td>
                                #<?php
                                echo (int) $row['id'];
                                ?>
                            </td>


                            <td>

                                <div class="admin-product-name">

                                    <strong>
                                        <?php
                                        echo e(
                                            $row['title']
                                        );
                                        ?>
                                    </strong>

                                    <small>
                                        Comic Book
                                    </small>

                                </div>

                            </td>


                            <td>

                                <strong>
                                    LKR
                                    <?php
                                    echo number_format(
                                        (float) $row['price'],
                                        2
                                    );
                                    ?>
                                </strong>

                            </td>


                            <td>

                                <?php

                                $stock =
                                    (int) $row['stock'];

                                if ($stock <= 0):

                                ?>

                                    <span class="badge text-bg-danger">
                                        Out of Stock
                                    </span>

                                <?php
                                elseif ($stock <= 5):
                                ?>

                                    <span class="badge text-bg-warning">
                                        Low Stock:
                                        <?php echo $stock; ?>
                                    </span>

                                <?php else: ?>

                                    <span class="badge text-bg-success">
                                        <?php echo $stock; ?>
                                        Available
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <div
                                    class="d-flex
                                           justify-content-end
                                           gap-2"
                                >

                                    <a
                                        href="EditProduct.php?id=<?php
                                        echo (int) $row['id'];
                                        ?>"
                                        class="btn
                                               btn-sm
                                               btn-outline-primary"
                                    >

                                        <i class="bi bi-pencil"></i>

                                        Edit

                                    </a>


                                    <form
                                        method="POST"
                                        action="DeleteProduct.php"
                                        class="d-inline"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to delete this product?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php
                                            echo (int) $row['id'];
                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php
                                            echo e(
                                                csrf_token()
                                            );
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            class="btn
                                                   btn-sm
                                                   btn-outline-danger"
                                        >

                                            <i
                                                class="bi bi-trash"
                                            ></i>

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>


    </main>

</div>


<?php

include("includes/admin_footer.php");

?>