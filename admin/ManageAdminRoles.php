<?php

require_once("../includes/admin_auth.php");
require_once("../includes/db.php");
require_once("../includes/functions.php");

require_admin_permission('roles.view');

$pageTitle = "Admin Roles";


/*
|--------------------------------------------------------------------------
| LOAD ADMIN ROLES
|--------------------------------------------------------------------------
|
| Actual database columns:
|
| id
| name
| description
| is_system
| created_at
|
*/

$result = mysqli_query(
    $conn,
    "SELECT
        id,
        name,
        description,
        is_system
     FROM admin_roles
     ORDER BY id ASC"
);


if (!$result) {

    die(
        "Could not load admin roles: "
        . mysqli_error($conn)
    );

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
        Admin Roles - Comic Book World
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
                    Admin Roles
                </h1>


                <p class="admin-page-description">
                    View and manage the administrative
                    roles configured in Comic Book World.
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


        <!-- ==================================================
             ROLES PANEL
        ================================================== -->

        <div class="admin-panel">


            <div class="admin-panel-header">


                <div>

                    <h2 class="admin-panel-title">
                        Available Roles
                    </h2>


                    <p class="admin-panel-subtitle">
                        Roles currently configured in
                        the administration system.
                    </p>

                </div>


                <span class="badge text-bg-light">

                    <?php

                    echo mysqli_num_rows($result);

                    ?>

                    Roles

                </span>


            </div>


            <div class="table-responsive">


                <table
                    class="table admin-table align-middle"
                >


                    <thead>

                        <tr>

                            <th>
                                Role
                            </th>


                            <th>
                                Description
                            </th>


                            <th>
                                Type
                            </th>


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


                            <!-- ROLE -->

                            <td>

                                <strong>

                                    <?php

                                    echo e(
                                        $row['name']
                                    );

                                    ?>

                                </strong>

                            </td>


                            <!-- DESCRIPTION -->

                            <td>

                                <?php

                                echo e(
                                    $row['description']
                                    ?? 'No description available.'
                                );

                                ?>

                            </td>


                            <!-- TYPE -->

                            <td>


                                <?php if (
                                    (int)
                                    $row['is_system']
                                    === 1
                                ): ?>

                                    <span
                                        class="badge text-bg-dark"
                                    >

                                        <i
                                            class="bi bi-shield-fill-check"
                                        ></i>

                                        System Role

                                    </span>


                                <?php else: ?>

                                    <span
                                        class="badge text-bg-light"
                                    >

                                        <i
                                            class="bi bi-person-gear"
                                        ></i>

                                        Custom Role

                                    </span>

                                <?php endif; ?>


                            </td>


                            <!-- ACTIONS -->

                            <td
                                class="text-end"
                            >


                                <a
                                    href="ManageRolePermissions.php?id=<?php echo (int) $row['id']; ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                >

                                    <i
                                        class="bi bi-shield-lock"
                                    ></i>

                                    Permissions

                                </a>


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


</body>

</html>