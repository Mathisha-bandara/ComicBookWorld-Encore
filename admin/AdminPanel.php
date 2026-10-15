<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");


// ==================================================
// PAGE INFORMATION
// ==================================================

$pageTitle = "Dashboard";


// ==================================================
// DASHBOARD STATISTICS
// ==================================================


// Customers

$customerResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'customer'"
);

$customerData =
    mysqli_fetch_assoc($customerResult);

$totalCustomers =
    (int) $customerData['total'];


// Products

$productResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products"
);

$productData =
    mysqli_fetch_assoc($productResult);

$totalProducts =
    (int) $productData['total'];


// Low stock

$lowStockResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE stock <= 5"
);

$lowStockData =
    mysqli_fetch_assoc($lowStockResult);

$lowStockProducts =
    (int) $lowStockData['total'];


// Orders

$orderResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM orders"
);

$orderData =
    mysqli_fetch_assoc($orderResult);

$totalOrders =
    (int) $orderData['total'];


// Revenue

$revenueResult = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS revenue
     FROM orders
     WHERE status != 'Cancelled'"
);

$revenueData =
    mysqli_fetch_assoc($revenueResult);

$totalRevenue =
    (float) $revenueData['revenue'];


// Active promotions

$promotionResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM promotions
     WHERE active = 1"
);

$promotionData =
    mysqli_fetch_assoc($promotionResult);

$activePromotions =
    (int) $promotionData['total'];


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
                    OVERVIEW
                </p>

                <h1 class="admin-page-title">
                    Dashboard
                </h1>

                <p class="admin-page-description">
                    Welcome back. Here's what's happening
                    with your store.
                </p>

            </div>

        </div>



        <!-- ========================================== -->
        <!-- KPI CARDS -->
        <!-- ========================================== -->

        <div class="row g-3 mb-4">


            <!-- Revenue -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-stat-card">

                    <div class="admin-stat-top">

                        <div class="admin-stat-icon">

                            <i class="bi bi-cash-stack"></i>

                        </div>

                    </div>

                    <p class="admin-stat-label">
                        Total Revenue
                    </p>

                    <h2 class="admin-stat-value">
                        LKR
                        <?php echo number_format(
                            $totalRevenue,
                            2
                        ); ?>
                    </h2>

                </div>

            </div>



            <!-- Orders -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-stat-card">

                    <div class="admin-stat-top">

                        <div class="admin-stat-icon">

                            <i class="bi bi-cart-check"></i>

                        </div>

                    </div>

                    <p class="admin-stat-label">
                        Total Orders
                    </p>

                    <h2 class="admin-stat-value">
                        <?php echo $totalOrders; ?>
                    </h2>

                </div>

            </div>



            <!-- Customers -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-stat-card">

                    <div class="admin-stat-top">

                        <div class="admin-stat-icon">

                            <i class="bi bi-people"></i>

                        </div>

                    </div>

                    <p class="admin-stat-label">
                        Customers
                    </p>

                    <h2 class="admin-stat-value">
                        <?php echo $totalCustomers; ?>
                    </h2>

                </div>

            </div>



            <!-- Products -->

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="admin-stat-card">

                    <div class="admin-stat-top">

                        <div class="admin-stat-icon">

                            <i class="bi bi-box-seam"></i>

                        </div>

                    </div>

                    <p class="admin-stat-label">
                        Products
                    </p>

                    <h2 class="admin-stat-value">
                        <?php echo $totalProducts; ?>
                    </h2>

                </div>

            </div>


        </div>



        <!-- ========================================== -->
        <!-- SECONDARY STATISTICS -->
        <!-- ========================================== -->

        <div class="row g-3 mb-4">


            <div class="col-12 col-md-6">

                <div class="admin-stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="admin-stat-label">
                                Low Stock Products
                            </p>

                            <h2 class="admin-stat-value">
                                <?php echo $lowStockProducts; ?>
                            </h2>

                        </div>

                        <div class="admin-stat-icon">

                            <i class="bi bi-exclamation-triangle"></i>

                        </div>

                    </div>

                </div>

            </div>



            <div class="col-12 col-md-6">

                <div class="admin-stat-card">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <p class="admin-stat-label">
                                Active Promotions
                            </p>

                            <h2 class="admin-stat-value">
                                <?php echo $activePromotions; ?>
                            </h2>

                        </div>

                        <div class="admin-stat-icon">

                            <i class="bi bi-ticket-perforated"></i>

                        </div>

                    </div>

                </div>

            </div>


        </div>



        <!-- ========================================== -->
        <!-- ANALYTICS PREVIEW -->
        <!-- ========================================== -->

        <div class="row g-3 mb-4">


            <div class="col-12 col-xl-8">

                <div class="admin-panel">

                    <div class="admin-panel-header">

                        <div>

                            <h2 class="admin-panel-title">
                                Revenue Overview
                            </h2>

                            <p class="admin-panel-subtitle">
                                Sales performance will appear here
                            </p>

                        </div>

                        <span class="badge text-bg-light">
                            Coming soon
                        </span>

                    </div>

                    <div class="admin-panel-body">

                        <div class="admin-empty">

                            <i class="bi bi-bar-chart-line"></i>

                            <div>
                                Revenue analytics will be
                                connected in the next phase.
                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <div class="col-12 col-xl-4">

                <div class="admin-panel">

                    <div class="admin-panel-header">

                        <div>

                            <h2 class="admin-panel-title">
                                Order Status
                            </h2>

                            <p class="admin-panel-subtitle">
                                Current order distribution
                            </p>

                        </div>

                    </div>

                    <div class="admin-panel-body">

                        <div class="admin-empty">

                            <i class="bi bi-pie-chart"></i>

                            <div>
                                Order analytics will be
                                connected in the next phase.
                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>



        <!-- ========================================== -->
        <!-- MANAGEMENT -->
        <!-- ========================================== -->

        <div class="admin-panel mb-4">

            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Store Management
                    </h2>

                    <p class="admin-panel-subtitle">
                        Manage the core areas of your store.
                    </p>

                </div>

            </div>


            <div class="p-3">

                <div class="row g-3">


                    <div class="col-12 col-md-6 col-xl-3">

                        <a
                            href="ManageProducts.php"
                            class="btn btn-outline-dark w-100 py-3"
                        >

                            <i class="bi bi-box-seam me-2"></i>

                            Products

                        </a>

                    </div>


                    <div class="col-12 col-md-6 col-xl-3">

                        <a
                            href="ManageOrders.php"
                            class="btn btn-outline-dark w-100 py-3"
                        >

                            <i class="bi bi-cart-check me-2"></i>

                            Orders

                        </a>

                    </div>


                    <div class="col-12 col-md-6 col-xl-3">

                        <a
                            href="ManageUsers.php"
                            class="btn btn-outline-dark w-100 py-3"
                        >

                            <i class="bi bi-people me-2"></i>

                            Customers

                        </a>

                    </div>


                    <div class="col-12 col-md-6 col-xl-3">

                        <a
                            href="ManagePromotions.php"
                            class="btn btn-outline-dark w-100 py-3"
                        >

                            <i class="bi bi-ticket-perforated me-2"></i>

                            Promotions

                        </a>

                    </div>


                </div>

            </div>

        </div>



        <!-- ========================================== -->
        <!-- FUTURE MODULES -->
        <!-- ========================================== -->

        <div class="row g-3">


            <div class="col-12 col-md-4">

                <a
                    href="Analytics.php"
                    class="admin-panel text-decoration-none d-block h-100"
                >

                    <div class="admin-panel-body">

                        <i class="bi bi-graph-up-arrow fs-3 text-dark"></i>

                        <h3 class="admin-panel-title mt-3">
                            Analytics
                        </h3>

                        <p class="admin-panel-subtitle">
                            Explore sales and product
                            performance.
                        </p>

                    </div>

                </a>

            </div>


            <div class="col-12 col-md-4">

                <a
                    href="ManageFAQ.php"
                    class="admin-panel text-decoration-none d-block h-100"
                >

                    <div class="admin-panel-body">

                        <i class="bi bi-question-circle fs-3 text-dark"></i>

                        <h3 class="admin-panel-title mt-3">
                            FAQ
                        </h3>

                        <p class="admin-panel-subtitle">
                            Manage frequently asked
                            questions.
                        </p>

                    </div>

                </a>

            </div>


            <div class="col-12 col-md-4">

                <a
                    href="ActivityLog.php"
                    class="admin-panel text-decoration-none d-block h-100"
                >

                    <div class="admin-panel-body">

                        <i class="bi bi-clock-history fs-3 text-dark"></i>

                        <h3 class="admin-panel-title mt-3">
                            Activity Log
                        </h3>

                        <p class="admin-panel-subtitle">
                            Monitor administrator actions.
                        </p>

                    </div>

                </a>

            </div>


        </div>


    </main>

</div>


<?php

include("includes/admin_footer.php");

?>