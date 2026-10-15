<aside class="admin-sidebar">

    <div class="admin-brand">

        <div class="admin-brand-mark">
            <i class="bi bi-book-half"></i>
        </div>

        <div>

            <div class="admin-brand-name">
                Comic Book World
            </div>

            <div class="admin-brand-label">
                ADMINISTRATION
            </div>

        </div>

    </div>


    <nav class="admin-nav">


        <!-- MAIN -->

        <div class="admin-nav-label">
            MAIN
        </div>


        <?php if (admin_has_permission('dashboard.view')): ?>

<a 
    href="AdminPanel.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'AdminPanel.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-grid-1x2-fill"></i>

    <span>
        Dashboard
    </span>

</a>

<?php endif; ?>


        <?php if (admin_has_permission('analytics.view')): ?>

<a 
    href="Analytics.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'Analytics.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-bar-chart-line-fill"></i>

    <span>
        Analytics
    </span>

</a>

<?php endif; ?>


        <?php if (admin_has_permission('orders.view')): ?>

<a 
    href="ManageOrders.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'ManageOrders.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-cart-check-fill"></i>

    <span>
        Orders
    </span>

</a>

<?php endif; ?>


        <?php if (admin_has_permission('products.view')): ?>

<a 
    href="ManageProducts.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'ManageProducts.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-box-seam-fill"></i>

    <span>
        Products
    </span>

</a>

<?php endif; ?>


        <?php if (admin_has_permission('customers.view')): ?>

<a 
    href="ManageUsers.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'ManageUsers.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-people-fill"></i>

    <span>
        Customers
    </span>

</a>

<?php endif; ?>



        <?php if (admin_has_permission('admins.view')): ?>

    <div class="admin-nav-label mt-4">
        ADMINISTRATION
    </div>

    <a
        href="ManageAdministrators.php"
        class="admin-nav-link <?php echo $currentPage === 'ManageAdministrators.php' ? 'active' : ''; ?>"
    >
        <i class="bi bi-person-gear"></i>
        <span>Manage Administrators</span>
    </a>

<?php endif; ?>

<!-- MARKETING -->

        <div class="admin-nav-label mt-4">
            MARKETING
        </div>


        <?php if (admin_has_permission('promotions.view')): ?>

<a 
    href="ManagePromotions.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'ManagePromotions.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-ticket-perforated-fill"></i>

    <span>
        Promotions
    </span>

</a>

<?php endif; ?>


        <?php if (admin_has_permission('faq.view')): ?>

<a 
    href="ManageFAQ.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'ManageFAQ.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-question-circle-fill"></i>

    <span>
        FAQ
    </span>

</a>

<?php endif; ?>



    <!-- BUSINESS -->
     
    <div class="admin-nav-label mt-4">
    BUSINESS
</div>


<?php if (admin_has_permission('reports.view')): ?>

<a 
    href="Reports.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'Reports.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-graph-up-arrow"></i>

    <span>
        Reports
    </span>

</a>

<?php endif; ?>


<?php if (admin_has_permission('activity.view')): ?>

<a 
    href="ActivityLog.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'ActivityLog.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-clock-history"></i>

    <span>
        Activity Log
    </span>

</a>

<?php endif; ?>

<a
    href="ManageAdminRoles.php"
    class="admin-nav-link
    <?php echo $currentPage === 'ManageAdminRoles.php'
        ? 'active'
        : ''; ?>"
>

    <i class="bi bi-shield-lock-fill"></i>

    <span>
        Admin Roles
    </span>

</a>

<a
    href="ManageAdminAccess.php"
    class="admin-nav-link
    <?php echo $currentPage === 'ManageAdminAccess.php'
        ? 'active'
        : ''; ?>"
>

    <i class="bi bi-clock-history"></i>

    <span>
        Admin Access
    </span>

</a>

<?php if (admin_has_permission('settings.view')): ?>

<a 
    href="Settings.php" 
    class="admin-nav-link 
    <?php echo $currentPage === 'Settings.php' 
        ? 'active' 
        : ''; ?>" 
>

    <i class="bi bi-gear-fill"></i>

    <span>
        Settings
    </span>

</a>

<?php endif; ?>


    </nav>


    <!-- SIDEBAR FOOTER -->

    <div class="admin-sidebar-footer">

        <a
            href="../HomePage.php"
            class="admin-store-link"
        >

            <i class="bi bi-shop"></i>

            <span>
                View Store
            </span>

        </a>


        <a
            href="../logout.php"
            class="admin-logout-link"
        >

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>

    </div>

</aside>