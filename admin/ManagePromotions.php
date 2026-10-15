<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('promotions.view');

// ==================================================
// PAGE INFORMATION
// ==================================================

$pageTitle = "Manage Promotions";


// ==================================================
// GET PROMOTIONS
// ==================================================

$result = mysqli_query(
    $conn,
    "SELECT
        id,
        code,
        discount_percent,
        minimum_order,
        maximum_discount,
        starts_at,
        expires_at,
        usage_limit,
        used_count,
        active
     FROM promotions
     ORDER BY id DESC"
);

if (!$result) {

    die("Could not load promotions.");

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
                    MARKETING
                </p>

                <h1 class="admin-page-title">
                    Manage Promotions
                </h1>

                <p class="admin-page-description">
                    Create and manage discount codes
                    available to customers.
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
                    href="AddPromotion.php"
                    class="btn admin-btn-primary"
                >

                    <i class="bi bi-plus-lg"></i>

                    Add Promotion

                </a>

            </div>

        </div>



        <!-- ========================================== -->
        <!-- PROMOTION TABLE -->
        <!-- ========================================== -->

        <div class="admin-panel">


            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Promotion Codes
                    </h2>

                    <p class="admin-panel-subtitle">
                        Monitor discounts, validity periods
                        and promotion usage.
                    </p>

                </div>


                <span class="badge text-bg-light">

                    <?php
                    echo mysqli_num_rows($result);
                    ?>

                    Promotions

                </span>

            </div>



            <div class="table-responsive">

                <table class="table admin-table align-middle">

                    <thead>

                        <tr>

                            <th>
                                Code
                            </th>

                            <th>
                                Discount
                            </th>

                            <th>
                                Minimum
                            </th>

                            <th>
                                Maximum
                            </th>

                            <th>
                                Validity
                            </th>

                            <th>
                                Usage
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


                            <!-- PROMOTION CODE -->

                            <td>

                                <span
                                    class="
                                        badge
                                        text-bg-dark
                                    "
                                >

                                    <i
                                        class="
                                            bi
                                            bi-tag
                                        "
                                    ></i>

                                    <?php
                                    echo e(
                                        $row['code']
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- DISCOUNT -->

                            <td>

                                <strong>

                                    <?php
                                    echo number_format(
                                        (float)
                                        $row[
                                            'discount_percent'
                                        ],
                                        2
                                    );
                                    ?>%

                                </strong>

                            </td>



                            <!-- MINIMUM ORDER -->

                            <td>

                                LKR

                                <?php
                                echo number_format(
                                    (float)
                                    $row[
                                        'minimum_order'
                                    ],
                                    2
                                );
                                ?>

                            </td>



                            <!-- MAXIMUM DISCOUNT -->

                            <td>

                                <?php

                                if (
                                    $row[
                                        'maximum_discount'
                                    ] !== null
                                ):

                                ?>

                                    LKR

                                    <?php
                                    echo number_format(
                                        (float)
                                        $row[
                                            'maximum_discount'
                                        ],
                                        2
                                    );
                                    ?>

                                <?php else: ?>

                                    <span class="text-muted">
                                        No limit
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- VALIDITY -->

                            <td>

                                <div
                                    class="
                                        d-flex
                                        flex-column
                                    "
                                >

                                    <?php if (
                                        !empty(
                                            $row['starts_at']
                                        )
                                    ): ?>

                                        <small>

                                            <strong>
                                                Start:
                                            </strong>

                                            <?php
                                            echo e(
                                                date(
                                                    "d M Y",
                                                    strtotime(
                                                        $row[
                                                            'starts_at'
                                                        ]
                                                    )
                                                )
                                            );
                                            ?>

                                        </small>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty(
                                            $row['expires_at']
                                        )
                                    ): ?>

                                        <small>

                                            <strong>
                                                End:
                                            </strong>

                                            <?php
                                            echo e(
                                                date(
                                                    "d M Y",
                                                    strtotime(
                                                        $row[
                                                            'expires_at'
                                                        ]
                                                    )
                                                )
                                            );
                                            ?>

                                        </small>

                                    <?php else: ?>

                                        <small
                                            class="text-muted"
                                        >

                                            No expiry

                                        </small>

                                    <?php endif; ?>

                                </div>

                            </td>



                            <!-- USAGE -->

                            <td>

                                <?php

                                $used =
                                    (int)
                                    $row['used_count'];

                                $limit =
                                    $row[
                                        'usage_limit'
                                    ];

                                ?>

                                <?php if (
                                    $limit === null
                                ): ?>

                                    <?php echo $used; ?>

                                    <span
                                        class="text-muted"
                                    >
                                        / Unlimited
                                    </span>

                                <?php else: ?>

                                    <?php echo $used; ?>

                                    <span
                                        class="text-muted"
                                    >

                                        /
                                        <?php
                                        echo (int)
                                            $limit;
                                        ?>

                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- STATUS -->

                            <td>

                                <?php if (
                                    (int)
                                    $row['active'] === 1
                                ): ?>

                                    <span
                                        class="
                                            badge
                                            text-bg-success
                                        "
                                    >

                                        Active

                                    </span>

                                <?php else: ?>

                                    <span
                                        class="
                                            badge
                                            text-bg-secondary
                                        "
                                    >

                                        Inactive

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

                                    <a
                                        href="EditPromotion.php?id=<?php
                                        echo (int)
                                            $row['id'];
                                        ?>"
                                        class="
                                            btn
                                            btn-sm
                                            btn-outline-primary
                                        "
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-pencil
                                            "
                                        ></i>

                                        Edit

                                    </a>


                                    <form
                                        method="POST"
                                        action="DeletePromotion.php"
                                        class="d-inline"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to delete this promotion?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php
                                            echo (int)
                                                $row['id'];
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
                                            class="
                                                btn
                                                btn-sm
                                                btn-outline-danger
                                            "
                                        >

                                            <i
                                                class="
                                                    bi
                                                    bi-trash
                                                "
                                            ></i>

                                            Delete

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
                                colspan="8"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="
                                            bi
                                            bi-ticket-perforated
                                            fs-2
                                        "
                                    ></i>

                                    <p class="mt-2 mb-0">

                                        No promotions found.

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