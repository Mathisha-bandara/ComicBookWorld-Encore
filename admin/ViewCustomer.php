<?php

// ==================================================
// DATABASE & ADMIN SECURITY
// ==================================================

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('customers.view');

// ==================================================
// VALIDATE CUSTOMER ID
// ==================================================

$userId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$userId) {

    header(
        "Location: ManageUsers.php"
    );

    exit;

}


// ==================================================
// LOAD CUSTOMER
// ==================================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$user =
    mysqli_fetch_assoc($result);


if (!$user) {

    header(
        "Location: ManageUsers.php"
    );

    exit;

}


$pageTitle = "Customer Details";


// ==================================================
// ADMIN LAYOUT
// ==================================================

include("includes/admin_header.php");
include("includes/admin_sidebar.php");

?>

<div class="admin-content">

    <?php include("includes/admin_topbar.php"); ?>


    <main class="admin-page">


        <!-- ========================================== -->
        <!-- HEADER -->
        <!-- ========================================== -->

        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    CUSTOMER MANAGEMENT
                </p>

                <h1 class="admin-page-title">
                    Customer Details
                </h1>

                <p class="admin-page-description">
                    Review customer account information.
                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="ManageUsers.php"
                    class="
                        btn
                        btn-outline-secondary
                    "
                >

                    <i class="bi bi-arrow-left"></i>

                    Back to Customers

                </a>

            </div>

        </div>



        <!-- ========================================== -->
        <!-- CUSTOMER INFORMATION -->
        <!-- ========================================== -->

        <div class="row g-4">


            <!-- BASIC INFORMATION -->

            <div class="col-12 col-xl-8">

                <div class="admin-panel h-100">


                    <div class="admin-panel-header">

                        <div>

                            <h2
                                class="
                                    admin-panel-title
                                "
                            >
                                Account Information
                            </h2>

                            <p
                                class="
                                    admin-panel-subtitle
                                "
                            >
                                Customer profile information.
                            </p>

                        </div>

                    </div>


                    <div class="admin-panel-body">


                        <div class="row g-4">


                            <!-- CUSTOMER ID -->

                            <div class="col-12 col-md-6">

                                <small
                                    class="text-muted"
                                >
                                    Customer ID
                                </small>

                                <div class="mt-1">

                                    <strong>

                                        #

                                        <?php
                                        echo (int)
                                            $user['id'];
                                        ?>

                                    </strong>

                                </div>

                            </div>



                            <!-- NAME -->

                            <div class="col-12 col-md-6">

                                <small
                                    class="text-muted"
                                >
                                    Name
                                </small>

                                <div class="mt-1">

                                    <strong>

                                        <?php if (!empty($user['fullname'])): ?>

    <?php echo e($user['fullname']); ?>

<?php else: ?>

    <span class="text-muted">
        Not available
    </span>

<?php endif; ?>

                                    </strong>

                                </div>

                            </div>



                            <!-- EMAIL -->

                            <div class="col-12 col-md-6">

                                <small
                                    class="text-muted"
                                >
                                    Email
                                </small>

                                <div class="mt-1">

                                    <?php

                                    if (
                                        isset(
                                            $user['email']
                                        )
                                    ) {

                                        echo e(
                                            $user['email']
                                        );

                                    } else {

                                        echo "Not available";

                                    }

                                    ?>

                                </div>

                            </div>



                            <!-- OTHER AVAILABLE FIELDS -->

                            <?php

                            /*
                             * Display commonly available
                             * customer fields when they exist.
                             *
                             * We do not assume these columns
                             * exist in the database.
                             */

                            if (
                                isset(
                                    $user['phone']
                                )
                            ):

                            ?>

                                <div class="col-12 col-md-6">

                                    <small
                                        class="text-muted"
                                    >
                                        Phone
                                    </small>

                                    <div class="mt-1">

                                        <?php
                                        echo e(
                                            $user['phone']
                                        );
                                        ?>

                                    </div>

                                </div>

                            <?php endif; ?>


                        </div>

                    </div>

                </div>

            </div>



            <!-- ACCOUNT STATUS -->

            <div class="col-12 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-header">

                        <div>

                            <h2
                                class="
                                    admin-panel-title
                                "
                            >
                                Account Status
                            </h2>

                        </div>

                    </div>


                    <div class="admin-panel-body">


                        <div class="mb-4">

                           <?php if (
    $user['account_status'] === 'blocked'
): ?>

    <span
        class="
            badge
            text-bg-danger
            px-3
            py-2
        "
    >

        <i
            class="
                bi
                bi-slash-circle
            "
        ></i>

        Blocked

    </span>

<?php else: ?>

    <span
        class="
            badge
            text-bg-success
            px-3
            py-2
        "
    >

        <i
            class="
                bi
                bi-check-circle
            "
        ></i>

        Active

    </span>

<?php endif; ?>

                        </div>


                        <p
                            class="
                                text-muted
                                small
                            "
                        >
                            <?php if (
    $user['account_status'] === 'blocked'
): ?>

    <p class="text-danger small">

        This customer account is currently
        blocked and cannot log in.

    </p>

<?php else: ?>

    <p class="text-success small">

        This customer account is currently
        active.

    </p>

<?php endif; ?>
                        </p>


                        <a
                            href="ManageUsers.php"
                            class="
                                btn
                                btn-outline-dark
                                w-100
                            "
                        >

                            <i
                                class="
                                    bi
                                    bi-arrow-left
                                "
                            ></i>

                            Back to Customers

                        </a>

                    </div>

                </div>

            </div>


        </div>


    </main>

</div>


<?php

include("includes/admin_footer.php");

?>