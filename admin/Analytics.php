<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

$pageTitle = "Analytics";


// ==================================================
// BASIC ANALYTICS DATA
// ==================================================

$totalProducts = 0;
$totalUsers = 0;
$totalOrders = 0;
$totalRevenue = 0;


// Products

$productResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products"
);

if ($productResult) {

    $row = mysqli_fetch_assoc(
        $productResult
    );

    $totalProducts =
        (int) $row['total'];
}


// Users

$userResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users"
);

if ($userResult) {

    $row = mysqli_fetch_assoc(
        $userResult
    );

    $totalUsers =
        (int) $row['total'];
}


// Orders

$orderResult = mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(total), 0) AS revenue
     FROM orders"
);

if ($orderResult) {

    $row = mysqli_fetch_assoc(
        $orderResult
    );

    $totalOrders =
        (int) $row['total'];

    $totalRevenue =
        (float) $row['revenue'];
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


        <!-- PAGE HEADER -->

        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    BUSINESS INTELLIGENCE
                </p>

                <h1 class="admin-page-title">
                    Analytics
                </h1>

                <p class="admin-page-description">
                    Monitor store performance,
                    customers and sales activity.
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
             KPI CARDS
             ================================================== -->

        <div class="row g-4 mb-4">


            <!-- PRODUCTS -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-book
                                fs-2
                            "
                        ></i>

                        <p class="text-muted mb-1 mt-3">
                            Products
                        </p>

                        <h2 class="mb-0">

                            <?php
                            echo number_format(
                                $totalProducts
                            );
                            ?>

                        </h2>

                    </div>

                </div>

            </div>



            <!-- USERS -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-people
                                fs-2
                            "
                        ></i>

                        <p class="text-muted mb-1 mt-3">
                            Customers
                        </p>

                        <h2 class="mb-0">

                            <?php
                            echo number_format(
                                $totalUsers
                            );
                            ?>

                        </h2>

                    </div>

                </div>

            </div>



            <!-- ORDERS -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-bag-check
                                fs-2
                            "
                        ></i>

                        <p class="text-muted mb-1 mt-3">
                            Orders
                        </p>

                        <h2 class="mb-0">

                            <?php
                            echo number_format(
                                $totalOrders
                            );
                            ?>

                        </h2>

                    </div>

                </div>

            </div>



            <!-- REVENUE -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-currency-exchange
                                fs-2
                            "
                        ></i>

                        <p class="text-muted mb-1 mt-3">
                            Revenue
                        </p>

                        <h2 class="mb-0">

                            LKR
                            <?php
                            echo number_format(
                                $totalRevenue,
                                2
                            );
                            ?>

                        </h2>

                    </div>

                </div>

            </div>

        </div>



        <!-- ==================================================
             CHART AREA
             ================================================== -->

        <div class="row g-4">


            <!-- REVENUE CHART -->

            <div class="col-12 col-xl-8">

                <div class="admin-panel h-100">

                    <div class="admin-panel-header">

                        <div>

                            <h2 class="admin-panel-title">
                                Revenue Overview
                            </h2>

                            <p class="admin-panel-subtitle">
                                Revenue performance over time.
                            </p>

                        </div>

                    </div>


                    <div class="admin-panel-body">

                        <canvas
                            id="revenueChart"
                            height="120"
                        ></canvas>

                    </div>

                </div>

            </div>



            <!-- ORDER STATUS -->

            <div class="col-12 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-header">

                        <div>

                            <h2 class="admin-panel-title">
                                Order Status
                            </h2>

                            <p class="admin-panel-subtitle">
                                Current order distribution.
                            </p>

                        </div>

                    </div>


                    <div class="admin-panel-body">

                        <canvas
                            id="orderStatusChart"
                        ></canvas>

                    </div>

                </div>

            </div>



            <!-- BEST SELLERS -->

            <div class="col-12">

                <div class="admin-panel">

                    <div class="admin-panel-header">

                        <div>

                            <h2 class="admin-panel-title">
                                Best-Selling Products
                            </h2>

                            <p class="admin-panel-subtitle">
                                Products generating the most
                                sales.
                            </p>

                        </div>

                    </div>


                    <div class="admin-panel-body">

                        <canvas
                            id="bestSellerChart"
                            height="90"
                        ></canvas>

                    </div>

                </div>

            </div>


        </div>


    </main>

</div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /*
         * Revenue Chart
         *
         * Temporary visual data.
         * We will replace this with real
         * database data later.
         */

        const revenueCanvas =
            document.getElementById(
                "revenueChart"
            );


        if (revenueCanvas) {

            new Chart(
                revenueCanvas,
                {

                    type: "line",

                    data: {

                        labels: [
                            "Jan",
                            "Feb",
                            "Mar",
                            "Apr",
                            "May",
                            "Jun"
                        ],

                        datasets: [

                            {

                                label:
                                    "Revenue (LKR)",

                                data: [
                                    12000,
                                    18000,
                                    15000,
                                    24000,
                                    21000,
                                    32000
                                ],

                                tension: 0.35,

                                fill: true

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio:
                            false,

                        plugins: {

                            legend: {
                                display: false
                            }

                        }

                    }

                }
            );

        }



        /*
         * Order Status Chart
         */

        const statusCanvas =
            document.getElementById(
                "orderStatusChart"
            );


        if (statusCanvas) {

            new Chart(
                statusCanvas,
                {

                    type: "doughnut",

                    data: {

                        labels: [
                            "Pending",
                            "Processing",
                            "Shipped",
                            "Delivered",
                            "Cancelled"
                        ],

                        datasets: [

                            {

                                data: [
                                    5,
                                    7,
                                    4,
                                    12,
                                    2
                                ]

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        plugins: {

                            legend: {
                                position: "bottom"
                            }

                        }

                    }

                }
            );

        }



        /*
         * Best Sellers
         */

        const sellerCanvas =
            document.getElementById(
                "bestSellerChart"
            );


        if (sellerCanvas) {

            new Chart(
                sellerCanvas,
                {

                    type: "bar",

                    data: {

                        labels: [
                            "Avengers",
                            "X-Men",
                            "Spider-Man",
                            "Batman",
                            "Justice League"
                        ],

                        datasets: [

                            {

                                label:
                                    "Units Sold",

                                data: [
                                    32,
                                    27,
                                    24,
                                    19,
                                    15
                                ]

                            }

                        ]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio:
                            false,

                        plugins: {

                            legend: {
                                display: false
                            }

                        }

                    }

                }
            );

        }

    }
);

</script>


<?php

include("includes/admin_footer.php");

?>