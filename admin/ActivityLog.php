<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

$pageTitle = "Activity Log";


$result = mysqli_query(
    $conn,
    "SELECT
        id,
        user_id,
        action,
        target_type,
        target_id,
        description,
        created_at
     FROM admin_activity_log
     ORDER BY id DESC
     LIMIT 100"
);

if (!$result) {
    die("Could not load activity log.");
}


include("includes/admin_header.php");
include("includes/admin_sidebar.php");

?>

<div class="admin-content">

    <?php include("includes/admin_topbar.php"); ?>

    <main class="admin-page">

        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    SECURITY
                </p>

                <h1 class="admin-page-title">
                    Activity Log
                </h1>

                <p class="admin-page-description">
                    Review recent administrative actions
                    performed within the system.
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


        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Recent Administrative Activity
                    </h2>

                    <p class="admin-panel-subtitle">
                        The latest 100 recorded actions.
                    </p>

                </div>

                <span class="badge text-bg-light">
                    Audit Log
                </span>

            </div>


            <div class="table-responsive">

                <table class="table admin-table align-middle">

                    <thead>

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Administrator
                            </th>

                            <th>
                                Action
                            </th>

                            <th>
                                Details
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while (
                        $row = mysqli_fetch_assoc($result)
                    ): ?>

                        <tr>

                            <td>

                                <small>

                                    <?php
                                    echo e(
                                        $row['created_at']
                                    );
                                    ?>

                                </small>

                            </td>

                            <td>

                                <span class="badge text-bg-dark">

    User #
    <?php
    echo (int)
        $row['user_id'];
    ?>

</span>

                            </td>

                            <td>

                                <strong>
                                    <?php
                                    echo e(
                                        $row['action']
                                    );
                                    ?>
                                </strong>

                            </td>

                            <td>

                                <span class="text-muted">

                                    <?php
                                    echo e(
                                        $row['description']
                                    );
                                    ?>

                                </span>

                            </td>

                        </tr>

                    <?php endwhile; ?>


                    <?php if (
                        mysqli_num_rows($result) === 0
                    ): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="
                                            bi
                                            bi-shield-check
                                            fs-2
                                        "
                                    ></i>

                                    <p class="mt-2 mb-0">
                                        No activity recorded.
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