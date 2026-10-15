<?php

// ==================================================
// DATABASE & ADMIN SECURITY
// ==================================================

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('customers.view');

// ==================================================
// PAGE INFORMATION
// ==================================================

$pageTitle = "Manage Customers";


// ==================================================
// HANDLE CUSTOMER ACTIONS
// ==================================================

$message = "";
$messageType = "";

// ==================================================
// CSRF VALIDATION
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken =
        $_POST['csrf_token'] ?? '';

    $sessionToken =
        $_SESSION['admin_csrf_token'] ?? '';


    if (
        empty($sessionToken)
        || empty($submittedToken)
        || !hash_equals(
            $sessionToken,
            $submittedToken
        )
    ) {

        http_response_code(403);

        die(
            "Security verification failed."
        );
    }
}

// --------------------------------------------------
// BLOCK CUSTOMER
// --------------------------------------------------

if (
    isset($_POST['block_user'])
    && isset($_POST['user_id'])
) {

require_admin_permission('customers.block');

    $userId = filter_input(
        INPUT_POST,
        'user_id',
        FILTER_VALIDATE_INT
    );

    if ($userId) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users
             SET account_status = 'blocked'
             WHERE id = ?
             AND role = 'customer'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userId
        );

        mysqli_stmt_execute($stmt);

        if (
            mysqli_stmt_affected_rows($stmt) > 0
        ) {

            $message =
                "Customer account has been blocked.";

            $messageType = "success";

        } else {

            $message =
                "Customer could not be blocked.";

            $messageType = "warning";

        }

        mysqli_stmt_close($stmt);
    }
}


// --------------------------------------------------
// UNBLOCK CUSTOMER
// --------------------------------------------------

if (
    isset($_POST['unblock_user'])
    && isset($_POST['user_id'])
) {

require_admin_permission('customers.block');

    $userId = filter_input(
        INPUT_POST,
        'user_id',
        FILTER_VALIDATE_INT
    );

    if ($userId) {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE users
             SET account_status = 'active'
             WHERE id = ?
             AND role = 'customer'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userId
        );

        mysqli_stmt_execute($stmt);

        if (
            mysqli_stmt_affected_rows($stmt) > 0
        ) {

            $message =
                "Customer account has been unblocked.";

            $messageType = "success";

        } else {

            $message =
                "Customer could not be unblocked.";

            $messageType = "warning";

        }

        mysqli_stmt_close($stmt);
    }
}


// --------------------------------------------------
// DELETE CUSTOMER
// --------------------------------------------------

if (
    isset($_POST['delete_user'])
    && isset($_POST['user_id'])
) {

    $userId = filter_input(
        INPUT_POST,
        'user_id',
        FILTER_VALIDATE_INT
    );

    if ($userId) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM users
             WHERE id = ?
             AND role = 'customer'"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $userId
        );

        mysqli_stmt_execute($stmt);

        if (
            mysqli_stmt_affected_rows($stmt) > 0
        ) {

            $message =
                "Customer account has been deleted.";

            $messageType = "success";

        } else {

            $message =
                "Customer could not be deleted.";

            $messageType = "warning";

        }

        mysqli_stmt_close($stmt);
    }
}


// ==================================================
// LOAD CUSTOMERS
// ==================================================

$result = mysqli_query(
    $conn,
    "SELECT
        id,
        fullname,
        email,
        created_at,
        role,
        account_status
     FROM users
     WHERE role = 'customer'
     ORDER BY id DESC"
);


if (!$result) {

    die(
        "Could not load customer records."
    );

}


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
        <!-- PAGE HEADER -->
        <!-- ========================================== -->

        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    CUSTOMER MANAGEMENT
                </p>

                <h1 class="admin-page-title">
                    Manage Customers
                </h1>

                <p class="admin-page-description">
                    View and manage registered
                    Comic Book World customers.
                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="AdminPanel.php"
                    class="
                        btn
                        btn-outline-secondary
                    "
                >

                    <i class="bi bi-arrow-left"></i>

                    Dashboard

                </a>

            </div>

        </div>



        <!-- ========================================== -->
        <!-- RESULT MESSAGE -->
        <!-- ========================================== -->

        <?php if ($message !== ""): ?>

            <div
                class="
                    alert
                    alert-<?php echo e($messageType); ?>
                    alert-dismissible
                    fade
                    show
                "
                role="alert"
            >

                <?php echo e($message); ?>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>



        <!-- ========================================== -->
        <!-- CUSTOMER TABLE -->
        <!-- ========================================== -->

        <div class="admin-panel">


            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Customer Accounts
                    </h2>

                    <p class="admin-panel-subtitle">
                        Registered customers in the
                        Comic Book World system.
                    </p>

                </div>


                <span class="badge text-bg-light">

                    <?php
                    echo mysqli_num_rows($result);
                    ?>

                    Customers

                </span>

            </div>



            <div class="table-responsive">

                <table
                    class="
                        table
                        admin-table
                        align-middle
                    "
                >

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Customer</th>

                            <th>Email</th>

                            <th>Registered</th>

                            <th>Status</th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while (
                        $user = mysqli_fetch_assoc($result)
                    ): ?>

                        <tr>


                            <!-- ID -->

                            <td>

                                <span class="text-muted">

                                    #

                                    <?php
                                    echo (int)
                                        $user['id'];
                                    ?>

                                </span>

                            </td>



                            <!-- CUSTOMER -->

                            <td>

                                <div
                                    class="
                                        d-flex
                                        align-items-center
                                        gap-3
                                    "
                                >

                                    <div
                                        class="
                                            rounded-circle
                                            bg-light
                                            d-flex
                                            align-items-center
                                            justify-content-center
                                        "
                                        style="
                                            width:42px;
                                            height:42px;
                                        "
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-person
                                            "
                                        ></i>

                                    </div>


                                    <strong>

                                        <?php
                                        echo e(
                                            $user['fullname']
                                        );
                                        ?>

                                    </strong>

                                </div>

                            </td>



                            <!-- EMAIL -->

                            <td>

                                <?php
                                echo e(
                                    $user['email']
                                );
                                ?>

                            </td>



                            <!-- CREATED -->

                            <td>

                                <?php

                                echo !empty(
                                    $user['created_at']
                                )
                                    ? e(
                                        date(
                                            'd M Y',
                                            strtotime(
                                                $user[
                                                    'created_at'
                                                ]
                                            )
                                        )
                                    )
                                    : "—";

                                ?>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <?php if (
                                    $user[
                                        'account_status'
                                    ] === 'blocked'
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            text-bg-danger
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

                            </td>



                            <!-- ACTIONS -->

                            <td>

                                <div
                                    class="
                                        d-flex
                                        justify-content-end
                                        gap-2
                                    "
                                >


                                    <!-- VIEW -->

                                    <a
                                        href="
                                            ViewCustomer.php?id=<?php
                                            echo (int)
                                                $user['id'];
                                            ?>
                                        "
                                        class="
                                            btn
                                            btn-sm
                                            btn-outline-primary
                                        "
                                        title="View customer"
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-eye
                                            "
                                        ></i>

                                    </a>



                                    <!-- BLOCK -->

                                    <?php if (
                                        $user[
                                            'account_status'
                                        ] !== 'blocked'
                                    ): ?>

                                        <form
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="
                                                return confirm(
                                                    'Block this customer account?'
                                                );
                                            "
                                        >

                                            <input
    type="hidden"
    name="csrf_token"
    value="<?php
        echo e(
            $_SESSION['admin_csrf_token']
        );
    ?>"
>

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?php
                                                echo (int)
                                                    $user['id'];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="block_user"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-warning
                                                "
                                                title="Block customer"
                                            >

                                                <i
                                                    class="
                                                        bi
                                                        bi-slash-circle
                                                    "
                                                ></i>

                                            </button>

                                        </form>

                                    <?php else: ?>



                                        <!-- UNBLOCK -->

                                        <form
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="
                                                return confirm(
                                                    'Unblock this customer account?'
                                                );
                                            "
                                        >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?php
                                                echo (int)
                                                    $user['id'];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="unblock_user"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-success
                                                "
                                                title="Unblock customer"
                                            >

                                                <i
                                                    class="
                                                        bi
                                                        bi-check-circle
                                                    "
                                                ></i>

                                            </button>

                                        </form>

                                    <?php endif; ?>



                                    <!-- DELETE -->

                                    <form
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="
                                            return confirm(
                                                'Delete this customer account permanently? This action cannot be undone.'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?php
                                            echo (int)
                                                $user['id'];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_user"
                                            class="
                                                btn
                                                btn-sm
                                                btn-outline-danger
                                            "
                                            title="Delete customer"
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-trash
                                                "
                                            ></i>

                                        </button>

                                    </form>


                                </div>

                            </td>


                        </tr>

                    <?php endwhile; ?>



                    <?php if (
                        mysqli_num_rows($result) === 0
                    ): ?>

                        <tr>

                            <td
                                colspan="6"
                                class="
                                    text-center
                                    py-5
                                "
                            >

                                <div class="text-muted">

                                    <i
                                        class="
                                            bi
                                            bi-people
                                            fs-2
                                        "
                                    ></i>

                                    <p class="mt-2 mb-0">
                                        No customers
                                        registered yet.
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