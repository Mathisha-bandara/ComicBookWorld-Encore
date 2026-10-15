<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('promotions.create');
require_admin_access(false);

$message = "";
$messageType = "";

$code = "";
$discountPercent = "";
$minimumOrder = "0.00";
$maximumDiscount = "";
$startsAt = "";
$expiresAt = "";
$usageLimit = "";
$active = 1;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {

        $message = "Invalid security token.";
        $messageType = "danger";

    } else {

        $code = strtoupper(
            trim($_POST['code'] ?? '')
        );

        $discountPercent = trim(
            $_POST['discount_percent'] ?? ''
        );

        $minimumOrder = trim(
            $_POST['minimum_order'] ?? '0'
        );

        $maximumDiscount = trim(
            $_POST['maximum_discount'] ?? ''
        );

        $startsAt = trim(
            $_POST['starts_at'] ?? ''
        );

        $expiresAt = trim(
            $_POST['expires_at'] ?? ''
        );

        $usageLimit = trim(
            $_POST['usage_limit'] ?? ''
        );

        $active = isset($_POST['active']) ? 1 : 0;


        /*
         * ==================================================
         * BASIC VALIDATION
         * ==================================================
         */

        if ($code === '') {

            $message = "Please enter a promotion code.";
            $messageType = "danger";

        } elseif (!preg_match('/^[A-Z0-9_-]{3,50}$/', $code)) {

            $message =
                "Promotion code must contain only letters, numbers, "
                . "underscores or hyphens, and be 3-50 characters long.";

            $messageType = "danger";

        } elseif (
            $discountPercent === ''
            || !is_numeric($discountPercent)
        ) {

            $message = "Please enter a valid discount percentage.";
            $messageType = "danger";

        } elseif (
            (float)$discountPercent <= 0
            || (float)$discountPercent > 100
        ) {

            $message =
                "Discount percentage must be greater than 0 and "
                . "cannot exceed 100%.";

            $messageType = "danger";

        } elseif (
            $minimumOrder === ''
            || !is_numeric($minimumOrder)
            || (float)$minimumOrder < 0
        ) {

            $message = "Please enter a valid minimum order amount.";
            $messageType = "danger";

        } elseif (
            $maximumDiscount !== ''
            && (
                !is_numeric($maximumDiscount)
                || (float)$maximumDiscount < 0
            )
        ) {

            $message = "Please enter a valid maximum discount.";
            $messageType = "danger";

        } elseif (
            $usageLimit !== ''
            && (
                !ctype_digit($usageLimit)
                || (int)$usageLimit < 1
            )
        ) {

            $message =
                "Usage limit must be a positive whole number "
                . "or left empty for unlimited use.";

            $messageType = "danger";

        } else {

            /*
             * ==================================================
             * DATE VALIDATION
             * ==================================================
             */

            $startDateTime = null;
            $expiryDateTime = null;


            if ($startsAt !== '') {

                $timestamp = strtotime($startsAt);

                if ($timestamp === false) {

                    $message = "Invalid start date.";
                    $messageType = "danger";

                } else {

                    $startDateTime =
                        date(
                            'Y-m-d H:i:s',
                            $timestamp
                        );
                }
            }


            if ($message === "" && $expiresAt !== '') {

                $timestamp = strtotime($expiresAt);

                if ($timestamp === false) {

                    $message = "Invalid expiry date.";
                    $messageType = "danger";

                } else {

                    $expiryDateTime =
                        date(
                            'Y-m-d H:i:s',
                            $timestamp
                        );
                }
            }


            if (
                $message === ""
                && $startDateTime !== null
                && $expiryDateTime !== null
                && strtotime($expiryDateTime)
                    < strtotime($startDateTime)
            ) {

                $message =
                    "Expiry date cannot be earlier than the start date.";

                $messageType = "danger";
            }


            /*
             * ==================================================
             * CHECK DUPLICATE CODE
             * ==================================================
             */

            if ($message === "") {

                $checkSql =
                    "SELECT id
                     FROM promotions
                     WHERE code = ?
                     LIMIT 1";

                $checkStmt =
                    mysqli_prepare(
                        $conn,
                        $checkSql
                    );

                mysqli_stmt_bind_param(
                    $checkStmt,
                    "s",
                    $code
                );

                mysqli_stmt_execute(
                    $checkStmt
                );

                mysqli_stmt_store_result(
                    $checkStmt
                );


                if (
                    mysqli_stmt_num_rows(
                        $checkStmt
                    ) > 0
                ) {

                    $message =
                        "A promotion with this code already exists.";

                    $messageType = "danger";
                }


                mysqli_stmt_close(
                    $checkStmt
                );
            }


            /*
             * ==================================================
             * INSERT PROMOTION
             * ==================================================
             */

            if ($message === "") {

                $maximumDiscountValue =
                    $maximumDiscount === ''
                        ? null
                        : (float)$maximumDiscount;

                $usageLimitValue =
                    $usageLimit === ''
                        ? null
                        : (int)$usageLimit;


                $sql =
                    "INSERT INTO promotions
                    (
                        code,
                        discount_percent,
                        minimum_order,
                        maximum_discount,
                        starts_at,
                        expires_at,
                        usage_limit,
                        active
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


                $stmt =
                    mysqli_prepare(
                        $conn,
                        $sql
                    );


                mysqli_stmt_bind_param(
                    $stmt,
                    "sdddssii",
                    $code,
                    $discountPercent,
                    $minimumOrder,
                    $maximumDiscountValue,
                    $startDateTime,
                    $expiryDateTime,
                    $usageLimitValue,
                    $active
                );


                if (
                    mysqli_stmt_execute(
                        $stmt
                    )
                ) {

                    mysqli_stmt_close(
                        $stmt
                    );

                    header(
                        "Location: ManagePromotions.php"
                    );

                    exit();

                } else {

                    $message =
                        "Unable to create the promotion.";

                    $messageType = "danger";
                }


                if (isset($stmt)) {

                    mysqli_stmt_close(
                        $stmt
                    );
                }
            }
        }
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

    <title>
        Add Promotion - Comic Book World
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

</head>


<body>

<?php

include("includes/admin_header.php");
include("includes/admin_sidebar.php");

?>


<div class="admin-content">

    <?php
    include("includes/admin_topbar.php");
    ?>


    <main class="admin-page">


        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    MARKETING
                </p>

                <h1 class="admin-page-title">
                    Add Promotion
                </h1>

                <p class="admin-page-description">
                    Create a discount code for customers.
                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="ManagePromotions.php"
                    class="btn btn-outline-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Back to Promotions

                </a>

            </div>

        </div>


        <?php if ($message !== ""): ?>

            <div
                class="alert alert-<?php
                    echo e($messageType);
                ?>"
            >

                <?php
                echo e($message);
                ?>

            </div>

        <?php endif; ?>


        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Promotion Details
                    </h2>

                    <p class="admin-panel-subtitle">
                        Configure the discount and usage rules.
                    </p>

                </div>

            </div>


            <div class="p-4">


                <form
                    method="POST"
                    action=""
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


                    <div class="row g-4">


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Promotion Code
                            </label>

                            <input
                                type="text"
                                name="code"
                                class="form-control"
                                maxlength="50"
                                value="<?php
                                    echo e($code);
                                ?>"
                                placeholder="WELCOME10"
                                required
                            >

                            <div class="form-text">
                                Use letters, numbers,
                                hyphens or underscores.
                            </div>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Discount Percentage
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    name="discount_percent"
                                    class="form-control"
                                    min="0.01"
                                    max="100"
                                    step="0.01"
                                    value="<?php
                                        echo e(
                                            $discountPercent
                                        );
                                    ?>"
                                    required
                                >

                                <span class="input-group-text">
                                    %
                                </span>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Minimum Order
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    LKR
                                </span>

                                <input
                                    type="number"
                                    name="minimum_order"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    value="<?php
                                        echo e(
                                            $minimumOrder
                                        );
                                    ?>"
                                >

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Maximum Discount
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    LKR
                                </span>

                                <input
                                    type="number"
                                    name="maximum_discount"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    value="<?php
                                        echo e(
                                            $maximumDiscount
                                        );
                                    ?>"
                                    placeholder="Leave empty for no limit"
                                >

                            </div>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Starts At
                            </label>

                            <input
                                type="datetime-local"
                                name="starts_at"
                                class="form-control"
                                value="<?php
                                    echo e(
                                        $startsAt
                                    );
                                ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Expires At
                            </label>

                            <input
                                type="datetime-local"
                                name="expires_at"
                                class="form-control"
                                value="<?php
                                    echo e(
                                        $expiresAt
                                    );
                                ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Usage Limit
                            </label>

                            <input
                                type="number"
                                name="usage_limit"
                                class="form-control"
                                min="1"
                                step="1"
                                value="<?php
                                    echo e(
                                        $usageLimit
                                    );
                                ?>"
                                placeholder="Leave empty for unlimited"
                            >

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label"
                            >
                                Status
                            </label>

                            <div class="form-check mt-2">

                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    class="form-check-input"
                                    id="active"
                                    <?php
                                    echo $active
                                        ? 'checked'
                                        : '';
                                    ?>
                                >

                                <label
                                    class="form-check-label"
                                    for="active"
                                >
                                    Active promotion
                                </label>

                            </div>

                        </div>


                    </div>


                    <div
                        class="d-flex gap-2 mt-4"
                    >

                        <button
                            type="submit"
                            class="btn admin-btn-primary"
                        >

                            <i class="bi bi-check-lg"></i>

                            Create Promotion

                        </button>


                        <a
                            href="ManagePromotions.php"
                            class="btn btn-outline-secondary"
                        >

                            Cancel

                        </a>

                    </div>


                </form>


            </div>

        </div>


    </main>

</div>


<?php

include("includes/admin_footer.php");

?>

</body>

</html>