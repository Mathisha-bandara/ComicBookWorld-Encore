<?php

require_once("../includes/admin_auth.php");
require_once("../includes/db.php");
require_once("../includes/functions.php");

require_admin_permission('roles.manage');
require_admin_access(false);


/*
|--------------------------------------------------------------------------
| VALIDATE ROLE ID
|--------------------------------------------------------------------------
*/

$roleId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($roleId <= 0) {

    header("Location: ManageAdminRoles.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| LOAD ROLE
|--------------------------------------------------------------------------
*/

$roleStmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        name,
        description,
        is_system
     FROM admin_roles
     WHERE id = ?
     LIMIT 1"
);


mysqli_stmt_bind_param(
    $roleStmt,
    "i",
    $roleId
);


mysqli_stmt_execute($roleStmt);


$roleResult = mysqli_stmt_get_result(
    $roleStmt
);


$role = mysqli_fetch_assoc(
    $roleResult
);


mysqli_stmt_close($roleStmt);


if (!$role) {

    header("Location: ManageAdminRoles.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| SAVE PERMISSIONS
|--------------------------------------------------------------------------
*/

$message = "";
$messageType = "";


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    isset($_POST['save_permissions'])
) {


    $selectedPermissions =
        $_POST['permissions'] ?? [];


    if (!is_array($selectedPermissions)) {

        $selectedPermissions = [];

    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE CURRENT PERMISSIONS
    |--------------------------------------------------------------------------
    */

    $deleteStmt = mysqli_prepare(
        $conn,
        "DELETE FROM admin_role_permissions
         WHERE role_id = ?"
    );


    mysqli_stmt_bind_param(
        $deleteStmt,
        "i",
        $roleId
    );


    mysqli_stmt_execute($deleteStmt);


    mysqli_stmt_close($deleteStmt);


    /*
    |--------------------------------------------------------------------------
    | INSERT NEW PERMISSIONS
    |--------------------------------------------------------------------------
    */

    if (!empty($selectedPermissions)) {


        $insertStmt = mysqli_prepare(
            $conn,
            "INSERT INTO admin_role_permissions
                (role_id, permission_id)
             VALUES
                (?, ?)"
        );


        foreach (
            $selectedPermissions
            as $permissionId
        ) {


            $permissionId =
                (int) $permissionId;


            if ($permissionId <= 0) {

                continue;

            }


            mysqli_stmt_bind_param(
                $insertStmt,
                "ii",
                $roleId,
                $permissionId
            );


            mysqli_stmt_execute(
                $insertStmt
            );

        }


        mysqli_stmt_close(
            $insertStmt
        );

    }


    $message =
        "Permissions updated successfully.";

    $messageType =
        "success";

}


/*
|--------------------------------------------------------------------------
| LOAD ALL PERMISSIONS
|--------------------------------------------------------------------------
*/

$permissionsResult = mysqli_query(
    $conn,
    "SELECT
        id,
        permission_key,
        description
     FROM admin_permissions
     ORDER BY id ASC"
);


if (!$permissionsResult) {

    die(
        "Could not load permissions: "
        . mysqli_error($conn)
    );

}


/*
|--------------------------------------------------------------------------
| LOAD CURRENT ROLE PERMISSIONS
|--------------------------------------------------------------------------
*/

$currentPermissions = [];


$currentStmt = mysqli_prepare(
    $conn,
    "SELECT permission_id
     FROM admin_role_permissions
     WHERE role_id = ?"
);


mysqli_stmt_bind_param(
    $currentStmt,
    "i",
    $roleId
);


mysqli_stmt_execute(
    $currentStmt
);


$currentResult =
    mysqli_stmt_get_result(
        $currentStmt
    );


while (
    $permissionRow =
    mysqli_fetch_assoc($currentResult)
) {

    $currentPermissions[] =
        (int) $permissionRow['permission_id'];

}


mysqli_stmt_close(
    $currentStmt
);


$currentPage =
    "ManageAdminRoles.php";

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
        Permissions - <?php echo e($role['name']); ?>
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


        <!-- ==================================================
             PAGE HEADER
        ================================================== -->

        <div class="admin-page-header">


            <div>

                <p class="admin-page-eyebrow">
                    SECURITY
                </p>


                <h1 class="admin-page-title">

                    <?php echo e($role['name']); ?>

                    Permissions

                </h1>


                <p class="admin-page-description">

                    Select the permissions available to
                    this administrative role.

                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="ManageAdminRoles.php"
                    class="btn btn-outline-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Back to Roles

                </a>

            </div>


        </div>


        <?php if ($message !== ""): ?>

            <div
                class="alert alert-<?php echo e($messageType); ?> alert-dismissible fade show"
                role="alert"
            >

                <i class="bi bi-check-circle-fill"></i>

                <?php echo e($message); ?>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             ROLE INFORMATION
        ================================================== -->

        <div class="admin-panel mb-4">


            <div class="admin-panel-header">


                <div>

                    <h2 class="admin-panel-title">

                        <?php echo e($role['name']); ?>

                    </h2>


                    <p class="admin-panel-subtitle">

                        <?php

                        echo e(
                            $role['description']
                            ?? 'No description available.'
                        );

                        ?>

                    </p>

                </div>


                <?php if (
                    (int) $role['is_system'] === 1
                ): ?>

                    <span class="badge text-bg-dark">

                        <i class="bi bi-shield-fill-check"></i>

                        System Role

                    </span>

                <?php else: ?>

                    <span class="badge text-bg-light">

                        Custom Role

                    </span>

                <?php endif; ?>


            </div>


        </div>


        <!-- ==================================================
             PERMISSIONS
        ================================================== -->

        <form
            method="POST"
            action=""
        >


            <div class="admin-panel">


                <div class="admin-panel-header">


                    <div>

                        <h2 class="admin-panel-title">

                            Permissions

                        </h2>


                        <p class="admin-panel-subtitle">

                            Enable or disable access to
                            individual administration features.

                        </p>

                    </div>


                    <span
                        class="badge text-bg-light"
                        id="permissionCount"
                    >

                        0 Selected

                    </span>


                </div>


                <div class="p-3">


                    <?php

                    $lastGroup = "";

                    ?>


                    <?php while (
                        $permission =
                        mysqli_fetch_assoc(
                            $permissionsResult
                        )
                    ): ?>


                        <?php

                        $parts =
                            explode(
                                ".",
                                $permission['permission_key']
                            );


                        $group =
                            ucfirst(
                                $parts[0]
                            );

                        ?>


                        <?php if (
                            $group !== $lastGroup
                        ): ?>


                            <?php if (
                                $lastGroup !== ""
                            ): ?>

                                </div>

                            <?php endif; ?>


                            <div class="permission-group mb-4">


                                <div
                                    class="d-flex justify-content-between align-items-center mb-3"
                                >

                                    <h3
                                        class="h6 fw-bold mb-0"
                                    >

                                        <?php
                                        echo e($group);
                                        ?>

                                    </h3>


                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary group-toggle"
                                        data-group="<?php echo e($group); ?>"
                                    >

                                        Select All

                                    </button>

                                </div>


                            <?php

                            $lastGroup =
                                $group;

                        endif;

                        ?>


                        <label
                            class="permission-option d-flex align-items-start gap-3 p-3 mb-2 border rounded"
                        >


                            <input
                                type="checkbox"
                                class="form-check-input permission-checkbox mt-1"
                                name="permissions[]"
                                value="<?php echo (int) $permission['id']; ?>"
                                data-group="<?php echo e($group); ?>"

                                <?php

                                if (
                                    in_array(
                                        (int)
                                        $permission['id'],
                                        $currentPermissions,
                                        true
                                    )
                                ) {

                                    echo "checked";

                                }

                                ?>
                            >


                            <span>

                                <strong class="d-block">

                                    <?php

                                    echo e(
                                        $permission['permission_key']
                                    );

                                    ?>

                                </strong>


                                <small
                                    class="text-muted"
                                >

                                    <?php

                                    echo e(
                                        $permission['description']
                                    );

                                    ?>

                                </small>

                            </span>


                        </label>


                    <?php endwhile; ?>


                    <?php if (
                        $lastGroup !== ""
                    ): ?>

                        </div>

                    <?php endif; ?>


                </div>


                <!-- ==================================================
                     ACTIONS
                ================================================== -->

                <div
                    class="d-flex justify-content-between align-items-center p-3 border-top"
                >


                    <a
                        href="ManageAdminRoles.php"
                        class="btn btn-outline-secondary"
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        name="save_permissions"
                        class="btn btn-dark"
                    >

                        <i class="bi bi-check2-circle"></i>

                        Save Permissions

                    </button>


                </div>


            </div>


        </form>


    </main>


</div>


<?php

include("includes/admin_footer.php");

?>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const checkboxes =
            document.querySelectorAll(
                ".permission-checkbox"
            );


        const countElement =
            document.getElementById(
                "permissionCount"
            );


        function updateCount()
        {

            const checked =
                document.querySelectorAll(
                    ".permission-checkbox:checked"
                ).length;


            countElement.textContent =
                checked + " Selected";

        }


        checkboxes.forEach(
            function (checkbox) {

                checkbox.addEventListener(
                    "change",
                    updateCount
                );

            }
        );


        document
            .querySelectorAll(".group-toggle")
            .forEach(
                function (button) {


                    button.addEventListener(
                        "click",
                        function () {


                            const group =
                                button.dataset.group;


                            const groupCheckboxes =
                                document.querySelectorAll(
                                    '.permission-checkbox[data-group="' +
                                    group +
                                    '"]'
                                );


                            let shouldCheck =
                                false;


                            groupCheckboxes.forEach(
                                function (checkbox) {

                                    if (
                                        !checkbox.checked
                                    ) {

                                        shouldCheck =
                                            true;

                                    }

                                }
                            );


                            groupCheckboxes.forEach(
                                function (checkbox) {

                                    checkbox.checked =
                                        shouldCheck;

                                }
                            );


                            button.textContent =
                                shouldCheck
                                    ? "Clear All"
                                    : "Select All";


                            updateCount();

                        }
                    );

                }
            );


        updateCount();

    }
);

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>