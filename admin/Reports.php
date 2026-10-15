<?php

// ==================================================
// DATABASE & ADMIN SECURITY
// ==================================================

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");


// ==================================================
// PAGE INFORMATION
// ==================================================

$pageTitle = "Reports";


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
                    BUSINESS INTELLIGENCE
                </p>

                <h1 class="admin-page-title">
                    Reports
                </h1>

                <p class="admin-page-description">
                    Generate and review useful reports
                    from Comic Book World store data.
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



        <!-- ========================================== -->
        <!-- REPORT OPTIONS -->
        <!-- ========================================== -->

        <div class="row g-4">


            <!-- ====================================== -->
            <!-- SALES REPORT -->
            <!-- ====================================== -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-graph-up
                                fs-2
                            "
                        ></i>


                        <h2
                            class="
                                admin-panel-title
                                mt-3
                            "
                        >
                            Sales Report
                        </h2>


                        <p
                            class="
                                admin-panel-subtitle
                            "
                        >
                            Review sales, revenue and
                            order performance over a
                            selected period.
                        </p>


                        <button
                            type="button"
                            class="
                                btn
                                btn-outline-dark
                            "
                            disabled
                        >

                            <i
                                class="
                                    bi
                                    bi-file-earmark-bar-graph
                                "
                            ></i>

                            Generate Report

                        </button>

                    </div>

                </div>

            </div>



            <!-- ====================================== -->
            <!-- PRODUCT REPORT -->
            <!-- ====================================== -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-bar-chart
                                fs-2
                            "
                        ></i>


                        <h2
                            class="
                                admin-panel-title
                                mt-3
                            "
                        >
                            Product Report
                        </h2>


                        <p
                            class="
                                admin-panel-subtitle
                            "
                        >
                            Review product performance,
                            inventory levels and
                            best-selling comics.
                        </p>


                        <button
                            type="button"
                            class="
                                btn
                                btn-outline-dark
                            "
                            disabled
                        >

                            <i
                                class="
                                    bi
                                    bi-file-earmark-bar-graph
                                "
                            ></i>

                            Generate Report

                        </button>

                    </div>

                </div>

            </div>



            <!-- ====================================== -->
            <!-- CUSTOMER REPORT -->
            <!-- ====================================== -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-people
                                fs-2
                            "
                        ></i>


                        <h2
                            class="
                                admin-panel-title
                                mt-3
                            "
                        >
                            Customer Report
                        </h2>


                        <p
                            class="
                                admin-panel-subtitle
                            "
                        >
                            Review customer registrations,
                            activity and account growth.
                        </p>


                        <button
                            type="button"
                            class="
                                btn
                                btn-outline-dark
                            "
                            disabled
                        >

                            <i
                                class="
                                    bi
                                    bi-file-earmark-bar-graph
                                "
                            ></i>

                            Generate Report

                        </button>

                    </div>

                </div>

            </div>



            <!-- ====================================== -->
            <!-- ORDER REPORT -->
            <!-- ====================================== -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-bag-check
                                fs-2
                            "
                        ></i>


                        <h2
                            class="
                                admin-panel-title
                                mt-3
                            "
                        >
                            Order Report
                        </h2>


                        <p
                            class="
                                admin-panel-subtitle
                            "
                        >
                            Review order volume,
                            statuses and fulfilment
                            performance.
                        </p>


                        <button
                            type="button"
                            class="
                                btn
                                btn-outline-dark
                            "
                            disabled
                        >

                            <i
                                class="
                                    bi
                                    bi-file-earmark-bar-graph
                                "
                            ></i>

                            Generate Report

                        </button>

                    </div>

                </div>

            </div>



            <!-- ====================================== -->
            <!-- PROMOTION REPORT -->
            <!-- ====================================== -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-ticket-perforated
                                fs-2
                            "
                        ></i>


                        <h2
                            class="
                                admin-panel-title
                                mt-3
                            "
                        >
                            Promotion Report
                        </h2>


                        <p
                            class="
                                admin-panel-subtitle
                            "
                        >
                            Review promotion usage,
                            discount performance and
                            customer redemption.
                        </p>


                        <button
                            type="button"
                            class="
                                btn
                                btn-outline-dark
                            "
                            disabled
                        >

                            <i
                                class="
                                    bi
                                    bi-file-earmark-bar-graph
                                "
                            ></i>

                            Generate Report

                        </button>

                    </div>

                </div>

            </div>



            <!-- ====================================== -->
            <!-- SECURITY REPORT -->
            <!-- ====================================== -->

            <div class="col-12 col-md-6 col-xl-4">

                <div class="admin-panel h-100">

                    <div class="admin-panel-body">

                        <i
                            class="
                                bi
                                bi-shield-check
                                fs-2
                            "
                        ></i>


                        <h2
                            class="
                                admin-panel-title
                                mt-3
                            "
                        >
                            Security Report
                        </h2>


                        <p
                            class="
                                admin-panel-subtitle
                            "
                        >
                            Review administrative
                            activity and security
                            events.
                        </p>


                        <button
                            type="button"
                            class="
                                btn
                                btn-outline-dark
                            "
                            disabled
                        >

                            <i
                                class="
                                    bi
                                    bi-file-earmark-bar-graph
                                "
                            ></i>

                            Generate Report

                        </button>

                    </div>

                </div>

            </div>


        </div>


    </main>

</div>


<?php

// ==================================================
// ADMIN FOOTER
// ==================================================

include("includes/admin_footer.php");

?>