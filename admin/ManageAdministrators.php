<?php

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_permission('admins.view');
require_admin_access(true);

$pageTitle = 'Manage Administrators';
$isSuperAdmin = function_exists('admin_is_super_admin')
    && admin_is_super_admin();

$successMessage = '';
if (isset($_GET['created']) && $_GET['created'] === '1') {
    $successMessage = 'Administrator account created successfully.';
} elseif (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $successMessage = 'Administrator account updated successfully.';
} elseif (isset($_GET['status']) && $_GET['status'] === 'changed') {
    $successMessage = 'Administrator account status updated successfully.';
}

$sql = "SELECT
            u.id,
            u.fullname,
            u.email,
            u.account_status,
            u.admin_role_id,
            u.created_at,
            r.name AS role_name,
            p.starts_at,
            p.expires_at,
            p.timezone,
            p.active AS access_policy_active,
            COALESCE(f.is_frozen, 0) AS individual_frozen,
            f.reason AS freeze_reason
        FROM users u
        LEFT JOIN admin_roles r ON r.id = u.admin_role_id
        LEFT JOIN admin_access_policies p ON p.user_id = u.id
        LEFT JOIN admin_individual_freezes f ON f.user_id = u.id
        WHERE u.role = 'admin'
        ORDER BY
            CASE WHEN r.name = 'Super Admin' THEN 0 ELSE 1 END,
            u.id ASC";

$adminsResult = mysqli_query($conn, $sql);
if (!$adminsResult) {
    http_response_code(500);
    die('Could not load administrator accounts. Please confirm that the V1 individual-freeze database table has been created.');
}

$globalFreezeActive = false;
$freezeResult = mysqli_query(
    $conn,
    "SELECT administrative_freeze FROM admin_system_controls WHERE id = 1 LIMIT 1"
);
if ($freezeResult && ($freezeRow = mysqli_fetch_assoc($freezeResult))) {
    $globalFreezeActive = (int) $freezeRow['administrative_freeze'] === 1;
}

include __DIR__ . '/includes/admin_header.php';
include __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="admin-content">
    <?php include __DIR__ . '/includes/admin_topbar.php'; ?>

    <main class="admin-page">
        <div class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">SECURITY</p>
                <h1 class="admin-page-title">Manage Administrators</h1>
                <p class="admin-page-description">
                    View administrator accounts, assign roles, manage account status,
                    and configure access schedules.
                </p>
            </div>

            <div class="admin-page-actions d-flex gap-2 flex-wrap">
                <?php if ($isSuperAdmin && admin_has_permission('admins.create')): ?>
                    <a href="CreateAdministrator.php" class="btn btn-primary">
                        <i class="bi bi-person-plus-fill"></i>
                        Create Administrator
                    </a>
                <?php endif; ?>
                <a href="ManageAdminAccess.php" class="btn btn-outline-secondary">
                    <i class="bi bi-clock-history"></i>
                    Access Schedules
                </a>
            </div>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo e($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($globalFreezeActive): ?>
            <div class="alert alert-warning">
                <i class="bi bi-lock-fill"></i>
                <strong>Global Administrative Freeze is active.</strong>
                Non-Super-Admin accounts are restricted from administrative changes.
            </div>
        <?php endif; ?>

        <div class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <h2 class="admin-panel-title">Administrator Accounts</h2>
                    <p class="admin-panel-subtitle mb-0">
                        Use Access to configure expiry and working hours. Freeze status is separate from account suspension.
                    </p>
                </div>
                <span class="badge text-bg-light">
                    <?php echo (int) mysqli_num_rows($adminsResult); ?> Accounts
                </span>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Administrator</th>
                            <th>Role</th>
                            <th>Account</th>
                            <th>Freeze</th>
                            <th>Access Expiry</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($adminsResult) === 0): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No administrator accounts were found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($admin = mysqli_fetch_assoc($adminsResult)): ?>
                            <?php
                            $roleName = $admin['role_name'] ?? 'Unassigned role';
                            $isProtectedSuperAdmin = $roleName === 'Super Admin';
                            $isFrozen = (int) $admin['individual_frozen'] === 1;
                            $accountStatus = $admin['account_status'] ?: 'active';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($admin['fullname']); ?></strong>
                                    <small class="d-block text-muted"><?php echo e($admin['email']); ?></small>
                                    <small class="d-block text-muted">
                                        Created: <?php echo e($admin['created_at'] ?: 'Unknown'); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?php echo e($roleName); ?></span>
                                </td>
                                <td>
                                    <?php if ($accountStatus === 'active'): ?>
                                        <span class="badge text-bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary"><?php echo e(ucfirst($accountStatus)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isProtectedSuperAdmin): ?>
                                        <span class="badge text-bg-primary">Protected</span>
                                    <?php elseif ($isFrozen): ?>
                                        <span class="badge text-bg-danger">Frozen</span>
                                        <?php if (!empty($admin['freeze_reason'])): ?>
                                            <small class="d-block text-muted mt-1"><?php echo e($admin['freeze_reason']); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge text-bg-success">Not individually frozen</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($admin['expires_at'])): ?>
                                        <?php echo e($admin['expires_at']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No expiry set</span>
                                    <?php endif; ?>
                                    <?php if (isset($admin['access_policy_active']) && (int) $admin['access_policy_active'] === 0): ?>
                                        <small class="d-block text-danger">Access policy disabled</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($isSuperAdmin && !$isProtectedSuperAdmin): ?>
                                        <?php if (admin_has_permission('admins.update')): ?>
                                            <a
                                                href="EditAdministrator.php?id=<?php echo (int) $admin['id']; ?>"
                                                class="btn btn-sm btn-outline-primary mb-1"
                                            >
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                        <a
                                            href="EditAdminAccess.php?id=<?php echo (int) $admin['id']; ?>"
                                            class="btn btn-sm btn-outline-secondary mb-1"
                                        >
                                            <i class="bi bi-clock-history"></i> Access
                                        </a>
                                    <?php elseif ($isProtectedSuperAdmin): ?>
                                        <span class="text-muted small">Protected account</span>
                                    <?php else: ?>
                                        <span class="text-muted small">View only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-muted small mt-3">
            Account suspension prevents future login. Individual freeze restricts administrative changes. Global freeze affects all non-Super-Admin accounts.
        </p>
    </main>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
