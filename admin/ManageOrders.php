<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('orders.view');

// ==================================================
// PAGE INFORMATION
// ==================================================

$pageTitle = "Manage Orders";


// ==================================================
// GET ORDERS
// ==================================================

$result = mysqli_query(
    $conn,
    "SELECT
        id,
        user_id,
        total,
        promo_code,
        discount_amount,
        status
     FROM orders
     ORDER BY id DESC"
);

if (!$result) {

    die("Could not load orders.");
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
                    Manage Orders
                </h1>

                <p class="admin-page-description">
                    Review customer orders and update
                    their current status.
                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="AdminPanel.php"
                    class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Dashboard

                </a>

            </div>

        </div>



        <!-- ========================================== -->
        <!-- ORDER TABLE -->
        <!-- ========================================== -->

        <div class="admin-panel">


            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Order Management
                    </h2>

                    <p class="admin-panel-subtitle">
                        View orders, totals, promotions
                        and order status.
                    </p>

                </div>


                <span class="badge text-bg-light">

                    <?php
                    echo mysqli_num_rows($result);
                    ?>

                    Orders

                </span>

            </div>



            <div class="table-responsive">

                <table class="table admin-table align-middle">

                    <thead>

                        <tr>

                            <th>
                                Order
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Promotion
                            </th>

                            <th>
                                Status
                            </th>

                            <th class="text-end">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php while (
                            $row = mysqli_fetch_assoc($result)
                        ): ?>


                            <tr>


                                <!-- ORDER ID -->

                                <td>

                                    <strong>
                                        #<?php
                                            echo (int) $row['id'];
                                            ?>
                                    </strong>

                                </td>



                                <!-- CUSTOMER -->

                                <td>

                                    <span class="text-muted">

                                        User #

                                        <?php
                                        echo (int) $row['user_id'];
                                        ?>

                                    </span>

                                </td>



                                <!-- TOTAL -->

                                <td>

                                    <strong>

                                        LKR

                                        <?php
                                        echo number_format(
                                            (float) $row['total'],
                                            2
                                        );
                                        ?>

                                    </strong>

                                </td>



                                <!-- PROMOTION -->

                                <td>

                                    <?php

                                    $promoCode =
                                        trim(
                                            (string)
                                            $row['promo_code']
                                        );

                                    $discount =
                                        (float)
                                        $row['discount_amount'];

                                    ?>


                                    <?php if (
                                        $promoCode !== ''
                                    ): ?>

                                        <div>

                                            <span
                                                class="
                                                badge
                                                text-bg-success
                                            ">

                                                <?php
                                                echo e(
                                                    $promoCode
                                                );
                                                ?>

                                            </span>

                                        </div>


                                        <?php if (
                                            $discount > 0
                                        ): ?>

                                            <small
                                                class="
                                                text-muted
                                            ">

                                                Discount:
                                                LKR
                                                <?php
                                                echo number_format(
                                                    $discount,
                                                    2
                                                );
                                                ?>

                                            </small>

                                        <?php endif; ?>


                                    <?php else: ?>

                                        <span class="text-muted">

                                            None

                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <?php

                                    $status =
                                        $row['status'];

                                    switch ($status) {

                                        case 'Pending':

                                            $statusClass =
                                                'text-bg-warning';

                                            break;


                                        case 'Processing':

                                            $statusClass =
                                                'text-bg-info';

                                            break;


                                        case 'Shipped':

                                            $statusClass =
                                                'text-bg-primary';

                                            break;


                                        case 'Delivered':

                                            $statusClass =
                                                'text-bg-success';

                                            break;


                                        case 'Cancelled':

                                            $statusClass =
                                                'text-bg-danger';

                                            break;


                                        default:

                                            $statusClass =
                                                'text-bg-secondary';
                                    }

                                    ?>

                                    <span
                                        class="
                                        badge
                                        <?php
                                        echo $statusClass;
                                        ?>
                                    ">

                                        <?php
                                        echo e($status);
                                        ?>

                                    </span>

                                </td>



                                <!-- UPDATE STATUS -->

                                <td>

                                    <form
                                        method="POST"
                                        action="UpdateOrder.php"
                                        class="
                                        d-flex
                                        justify-content-end
                                        gap-2
                                    ">


                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php
                                                    echo (int)
                                                    $row['id'];
                                                    ?>">


                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php
                                                    echo e(
                                                        csrf_token()
                                                    );
                                                    ?>">


                                        <select
                                            name="status"
                                            class="form-select
                                               form-select-sm"
                                            style="
                                            max-width: 150px;
                                        ">

                                            <option
                                                value="Pending"
                                                <?php
                                                echo $status ===
                                                    'Pending'
                                                    ? 'selected'
                                                    : '';
                                                ?>>
                                                Pending
                                            </option>


                                            <option
                                                value="Processing"
                                                <?php
                                                echo $status ===
                                                    'Processing'
                                                    ? 'selected'
                                                    : '';
                                                ?>>
                                                Processing
                                            </option>


                                            <option
                                                value="Shipped"
                                                <?php
                                                echo $status ===
                                                    'Shipped'
                                                    ? 'selected'
                                                    : '';
                                                ?>>
                                                Shipped
                                            </option>


                                            <option
                                                value="Delivered"
                                                <?php
                                                echo $status ===
                                                    'Delivered'
                                                    ? 'selected'
                                                    : '';
                                                ?>>
                                                Delivered
                                            </option>


                                            <option
                                                value="Cancelled"
                                                <?php
                                                echo $status ===
                                                    'Cancelled'
                                                    ? 'selected'
                                                    : '';
                                                ?>>
                                                Cancelled
                                            </option>

                                        </select>


                                        <button
                                            type="submit"
                                            class="
                                            btn
                                            btn-sm
                                            btn-outline-dark
                                        ">

                                            <i
                                                class="
                                                bi
                                                bi-check-lg
                                            "></i>

                                            Update

                                        </button>

                                    </form>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        <?php if (
                            mysqli_num_rows($result) === 0
                        ): ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center py-5">

                                    <div
                                        class="
                                        text-muted
                                    ">

                                        <i
                                            class="
                                            bi
                                            bi-inbox
                                            fs-2
                                        "></i>

                                        <p class="mt-2 mb-0">

                                            No orders found.

                                        </p>

                                    </div>

                                </td>

                            </tr>

                        <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>


    </main>

</div>


<?php

include("includes/admin_footer.php");

?>