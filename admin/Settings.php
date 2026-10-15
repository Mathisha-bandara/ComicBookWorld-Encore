<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_access(true);


/*
|--------------------------------------------------------------------------
| PAGE VARIABLES
|--------------------------------------------------------------------------
*/

$freezeMessage = "";
$freezeMessageType = "";

$currentAdminId =
    (int) ($_SESSION['user_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| ADMINISTRATIVE FREEZE
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token'])
        ||
        !verify_csrf_token(
            $_POST['csrf_token']
        )
    ) {

        $freezeMessage =
            "Invalid security token.";

        $freezeMessageType =
            "danger";

    } else {

        /*
        |--------------------------------------------------------------------------
        | FREEZE
        |--------------------------------------------------------------------------
        */

        if (
            isset($_POST['freeze_admin'])
        ) {

            /*
            |--------------------------------------------------------------------------
            | Only Super Admin
            |--------------------------------------------------------------------------
            */

            if (
                !admin_is_super_admin()
                ||
                !admin_has_permission(
                    'system.freeze'
                )
            ) {

                $freezeMessage =
                    "Only the Super Admin can change the administrative freeze.";

                $freezeMessageType =
                    "danger";

            } else {

                $freezeReason =
                    trim(
                        $_POST['freeze_reason']
                        ?? ''
                    );


                if (
                    $freezeReason === ''
                ) {

                    $freezeMessage =
                        "Please provide a reason for the administrative freeze.";

                    $freezeMessageType =
                        "warning";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | ENABLE FREEZE
                    |--------------------------------------------------------------------------
                    */

                    $freezeStmt =
                        mysqli_prepare(
                            $conn,
                            "UPDATE admin_system_controls
                             SET
                                administrative_freeze = 1,
                                freeze_reason = ?,
                                frozen_at = NOW(),
                                frozen_by = ?
                             WHERE id = 1"
                        );


                    mysqli_stmt_bind_param(
                        $freezeStmt,
                        "si",
                        $freezeReason,
                        $currentAdminId
                    );


                    if (
                        mysqli_stmt_execute(
                            $freezeStmt
                        )
                    ) {

                        mysqli_stmt_close(
                            $freezeStmt
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | LOG
                        |--------------------------------------------------------------------------
                        */

                        $logAction =
                            "ADMINISTRATIVE_FREEZE";

                        $logTargetType =
                            "admin_system_controls";

                        $logTargetId =
                            1;

                        $logDescription =
                            "Administrative changes frozen. Reason: "
                            . $freezeReason;

                        $logIp =
                            $_SERVER['REMOTE_ADDR']
                            ?? null;

                        $logUserAgent =
                            $_SERVER['HTTP_USER_AGENT']
                            ?? null;


                        $logStmt =
                            mysqli_prepare(
                                $conn,
                                "INSERT INTO admin_activity_log
                                (
                                    user_id,
                                    action,
                                    target_type,
                                    target_id,
                                    description,
                                    ip_address,
                                    user_agent
                                )
                                VALUES (?, ?, ?, ?, ?, ?, ?)"
                            );


                        mysqli_stmt_bind_param(
                            $logStmt,
                            "ississs",
                            $currentAdminId,
                            $logAction,
                            $logTargetType,
                            $logTargetId,
                            $logDescription,
                            $logIp,
                            $logUserAgent
                        );


                        mysqli_stmt_execute(
                            $logStmt
                        );

                        mysqli_stmt_close(
                            $logStmt
                        );


                        $freezeMessage =
                            "Administrative changes have been frozen.";

                        $freezeMessageType =
                            "success";

                    } else {

                        mysqli_stmt_close(
                            $freezeStmt
                        );


                        $freezeMessage =
                            "Could not enable the administrative freeze.";

                        $freezeMessageType =
                            "danger";
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | UNFREEZE
        |--------------------------------------------------------------------------
        */

        if (
            isset($_POST['unfreeze_admin'])
        ) {

            /*
            |--------------------------------------------------------------------------
            | Only Super Admin
            |--------------------------------------------------------------------------
            */

            if (
                !admin_is_super_admin()
                ||
                !admin_has_permission(
                    'system.freeze'
                )
            ) {

                $freezeMessage =
                    "Only the Super Admin can remove the administrative freeze.";

                $freezeMessageType =
                    "danger";

            } else {

                $unfreezeStmt =
                    mysqli_prepare(
                        $conn,
                        "UPDATE admin_system_controls
                         SET
                            administrative_freeze = 0,
                            freeze_reason = NULL,
                            frozen_at = NULL,
                            frozen_by = NULL
                         WHERE id = 1"
                    );


                if (
                    mysqli_stmt_execute(
                        $unfreezeStmt
                    )
                ) {

                    mysqli_stmt_close(
                        $unfreezeStmt
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | LOG
                    |--------------------------------------------------------------------------
                    */

                    $logAction =
                        "ADMINISTRATIVE_UNFREEZE";

                    $logTargetType =
                        "admin_system_controls";

                    $logTargetId =
                        1;

                    $logDescription =
                        "Administrative freeze was removed.";

                    $logIp =
                        $_SERVER['REMOTE_ADDR']
                        ?? null;

                    $logUserAgent =
                        $_SERVER['HTTP_USER_AGENT']
                        ?? null;


                    $logStmt =
                        mysqli_prepare(
                            $conn,
                            "INSERT INTO admin_activity_log
                            (
                                user_id,
                                action,
                                target_type,
                                target_id,
                                description,
                                ip_address,
                                user_agent
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?)"
                        );


                    mysqli_stmt_bind_param(
                        $logStmt,
                        "ississs",
                        $currentAdminId,
                        $logAction,
                        $logTargetType,
                        $logTargetId,
                        $logDescription,
                        $logIp,
                        $logUserAgent
                    );


                    mysqli_stmt_execute(
                        $logStmt
                    );

                    mysqli_stmt_close(
                        $logStmt
                    );


                    $freezeMessage =
                        "Administrative freeze has been removed.";

                    $freezeMessageType =
                        "success";

                } else {

                    mysqli_stmt_close(
                        $unfreezeStmt
                    );


                    $freezeMessage =
                        "Could not remove the administrative freeze.";

                    $freezeMessageType =
                        "danger";
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| LOAD FREEZE STATUS
|--------------------------------------------------------------------------
*/

$freezeResult =
    mysqli_query(
        $conn,
        "SELECT
            c.administrative_freeze,
            c.freeze_reason,
            c.frozen_at,
            u.fullname AS frozen_by_name
         FROM admin_system_controls c
         LEFT JOIN users u
            ON u.id = c.frozen_by
         WHERE c.id = 1
         LIMIT 1"
    );


$freeze =
    mysqli_fetch_assoc(
        $freezeResult
    );


$pageTitle =
    "Settings";


include("includes/admin_header.php");
include("includes/admin_sidebar.php");

?>

<div class="admin-content">

    <?php include("includes/admin_topbar.php"); ?>

    <main class="admin-page">

        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    SYSTEM
                </p>

                <h1 class="admin-page-title">
                    Settings
                </h1>

                <p class="admin-page-description">
                    Configure store, security and
                    administrative system settings.
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

            </div>

        </div>


        <?php if ($freezeMessage !== ""): ?>

            <div
                class="alert alert-<?php echo e($freezeMessageType); ?> alert-dismissible fade show mb-4"
                role="alert"
            >

                <?php echo e($freezeMessage); ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <div class="row g-4">


            <!-- GENERAL -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="bi bi-gear fs-2"
                        ></i>

                        <h2 class="admin-panel-title mt-3">
                            General Settings
                        </h2>

                        <p class="admin-panel-subtitle">
                            Store information and
                            general configuration.
                        </p>

                        <button
                            type="button"
                            class="btn btn-outline-dark"
                            disabled
                        >
                            Configure
                        </button>

                    </div>

                </div>

            </div>


            <!-- SECURITY -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="bi bi-shield-lock fs-2"
                        ></i>

                        <h2 class="admin-panel-title mt-3">
                            Security
                        </h2>

                        <p class="admin-panel-subtitle">
                            Manage authentication and
                            administrative security.
                        </p>

                        <button
                            type="button"
                            class="btn btn-outline-dark"
                            disabled
                        >
                            Configure
                        </button>

                    </div>

                </div>

            </div>


            <!-- ADMIN ACCESS -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="bi bi-person-lock fs-2"
                        ></i>

                        <h2 class="admin-panel-title mt-3">
                            Admin Access
                        </h2>

                        <p class="admin-panel-subtitle">
                            Manage administrator access,
                            roles and working schedules.
                        </p>

                        <a
                            href="ManageAdminAccess.php"
                            class="btn btn-outline-dark"
                        >
                            Manage
                        </a>

                    </div>

                </div>

            </div>


            <!-- ACCESS SCHEDULE -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="bi bi-clock-history fs-2"
                        ></i>

                        <h2 class="admin-panel-title mt-3">
                            Access Schedules
                        </h2>

                        <p class="admin-panel-subtitle">
                            Configure permitted
                            administrator working periods.
                        </p>

                        <a
                            href="ManageAdminAccess.php"
                            class="btn btn-outline-dark"
                        >
                            Manage
                        </a>

                    </div>

                </div>

            </div>


            <!-- RESTRICTED ACTIONS -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="bi bi-exclamation-triangle fs-2"
                        ></i>

                        <h2 class="admin-panel-title mt-3">
                            Restricted Actions
                        </h2>

                        <p class="admin-panel-subtitle">
                            Control sensitive operations
                            available to lower-level administrators.
                        </p>

                        <button
                            type="button"
                            class="btn btn-outline-dark"
                            disabled
                        >
                            Configure
                        </button>

                    </div>

                </div>

            </div>


            <!-- SYSTEM CONTROLS -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="bi bi-sliders fs-2"
                        ></i>

                        <h2 class="admin-panel-title mt-3">
                            System Controls
                        </h2>

                        <p class="admin-panel-subtitle">
                            Manage system-wide operational
                            controls.
                        </p>

                        <button
                            type="button"
                            class="btn btn-outline-dark"
                            disabled
                        >
                            Configure
                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- ADMINISTRATIVE FREEZE -->

        <div class="admin-panel mt-4">

            <div class="admin-panel-header">

                <div>

                    <p class="admin-page-eyebrow mb-1">
                        EMERGENCY CONTROL
                    </p>

                    <h2 class="admin-panel-title">
                        Administrative Freeze
                    </h2>

                    <p class="admin-panel-subtitle">

                        Temporarily prevent
                        administrative changes for all
                        non-Super-Admin accounts.

                    </p>

                </div>


                <?php if (
                    isset(
                        $freeze['administrative_freeze']
                    )
                    &&
                    (int)
                    $freeze['administrative_freeze'] === 1
                ): ?>

                    <span
                        class="badge text-bg-danger"
                    >

                        <i
                            class="bi bi-lock-fill"
                        ></i>

                        FROZEN

                    </span>

                <?php else: ?>

                    <span
                        class="badge text-bg-success"
                    >

                        <i
                            class="bi bi-unlock-fill"
                        ></i>

                        ACTIVE

                    </span>

                <?php endif; ?>

            </div>


            <div class="p-4">


                <?php if (
                    isset(
                        $freeze['administrative_freeze']
                    )
                    &&
                    (int)
                    $freeze['administrative_freeze'] === 1
                ): ?>

                    <div
                        class="alert alert-danger"
                    >

                        <strong>

                            Administrative changes are
                            currently frozen.

                        </strong>


                        <hr>


                        <div>

                            <strong>
                                Reason:
                            </strong>

                            <?php

                            echo e(
                                $freeze['freeze_reason']
                                ??
                                'No reason provided.'
                            );

                            ?>

                        </div>


                        <div class="mt-2">

                            <strong>
                                Frozen At:
                            </strong>

                            <?php

                            echo e(
                                $freeze['frozen_at']
                                ??
                                'Unknown'
                            );

                            ?>

                        </div>


                        <div class="mt-2">

                            <strong>
                                Frozen By:
                            </strong>

                            <?php

                            echo e(
                                $freeze['frozen_by_name']
                                ??
                                'Unknown'
                            );

                            ?>

                        </div>

                    </div>


                    <?php if (
                        admin_is_super_admin()
                        &&
                        admin_has_permission(
                            'system.freeze'
                        )
                    ): ?>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo e(csrf_token()); ?>"
                            >

                            <button
                                type="submit"
                                name="unfreeze_admin"
                                class="btn btn-success"
                            >

                                <i
                                    class="bi bi-unlock-fill"
                                ></i>

                                Remove Administrative Freeze

                            </button>

                        </form>

                    <?php endif; ?>


                <?php else: ?>


                    <?php if (
                        admin_is_super_admin()
                        &&
                        admin_has_permission(
                            'system.freeze'
                        )
                    ): ?>

                        <div
                            class="alert alert-warning"
                        >

                            <i
                                class="bi bi-exclamation-triangle-fill"
                            ></i>

                            Freezing administration will
                            temporarily prevent lower-level
                            administrators from making changes.

                            <br>

                            <strong>
                                Super Admin remains fully active.
                            </strong>

                        </div>


                        <form method="POST">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo e(csrf_token()); ?>"
                            >


                            <div class="mb-3">

                                <label
                                    class="form-label fw-semibold"
                                >

                                    Freeze Reason

                                </label>


                                <textarea
                                    name="freeze_reason"
                                    class="form-control"
                                    rows="3"
                                    required
                                    maxlength="255"
                                    placeholder="Example: Emergency maintenance"
                                ></textarea>

                            </div>


                            <button
                                type="submit"
                                name="freeze_admin"
                                class="btn btn-danger"
                            >

                                <i
                                    class="bi bi-lock-fill"
                                ></i>

                                Freeze Administrative Changes

                            </button>

                        </form>


                    <?php else: ?>

                        <div
                            class="alert alert-secondary mb-0"
                        >

                            Administrative Freeze is
                            controlled by the Super Admin.

                        </div>

                    <?php endif; ?>


                <?php endif; ?>


            </div>

        </div>


    </main>

</div>


<?php

include("includes/admin_footer.php");

?>

</body>

</html>