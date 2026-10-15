<?php

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_permission('admins.update');
require_admin_access(true);

$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
$message = '';
$messageType = '';

/* Log security actions using the existing admin_activity_log schema (user_id). */
function cbw_log_admin_freeze_event($actorId, $action, $targetId, $description)
{
    global $conn;

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO admin_activity_log
            (user_id, action, target_type, target_id, description, ip_address, user_agent)
         VALUES (?, ?, 'administrator', ?, ?, ?, ?)"
    );

    if (!$stmt) {
        return;
    }

    mysqli_stmt_bind_param($stmt, 'isisss', $actorId, $action, $targetId, $description, $ip, $agent);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message = 'Your form security token is invalid. Refresh the page and try again.';
        $messageType = 'danger';
    } elseif (!admin_is_super_admin() || !admin_has_permission('admins.update')) {
        http_response_code(403);
        die('Only the Super Admin can freeze or unfreeze an individual administrator.');
    } else {
        $targetId = (int) ($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if ($targetId <= 0) {
            $message = 'Choose a valid administrator.';
            $messageType = 'danger';
        } else {
            $targetStmt = mysqli_prepare(
                $conn,
                "SELECT u.fullname, r.name AS role_name
                 FROM users u
                 LEFT JOIN admin_roles r ON r.id = u.admin_role_id
                 WHERE u.id = ? AND u.role = 'admin'
                 LIMIT 1"
            );

            if (!$targetStmt) {
                $message = 'Could not verify the selected administrator.';
                $messageType = 'danger';
            } else {
                mysqli_stmt_bind_param($targetStmt, 'i', $targetId);
                mysqli_stmt_execute($targetStmt);
                $targetResult = mysqli_stmt_get_result($targetStmt);
                $target = mysqli_fetch_assoc($targetResult);
                mysqli_stmt_close($targetStmt);

                if (!$target) {
                    $message = 'The selected administrator was not found.';
                    $messageType = 'danger';
                } elseif (($target['role_name'] ?? '') === 'Super Admin') {
                    $message = 'The Super Admin account cannot be individually frozen.';
                    $messageType = 'danger';
                } elseif ($action === 'freeze') {
                    $reason = trim($_POST['reason'] ?? '');

                    if ($reason === '') {
                        $message = 'Enter a reason before freezing this administrator.';
                        $messageType = 'warning';
                    } elseif (mb_strlen($reason) > 255) {
                        $message = 'The freeze reason must be 255 characters or fewer.';
                        $messageType = 'warning';
                    } else {
                        $stmt = mysqli_prepare(
                            $conn,
                            "INSERT INTO admin_individual_freezes
                                (user_id, is_frozen, reason, frozen_by, frozen_at, unfrozen_at)
                             VALUES (?, 1, ?, ?, NOW(), NULL)
                             ON DUPLICATE KEY UPDATE
                                is_frozen = 1,
                                reason = VALUES(reason),
                                frozen_by = VALUES(frozen_by),
                                frozen_at = NOW(),
                                unfrozen_at = NULL"
                        );

                        if (!$stmt) {
                            $message = 'Could not prepare the individual freeze. Confirm the admin_individual_freezes table exists.';
                            $messageType = 'danger';
                        } else {
                            mysqli_stmt_bind_param($stmt, 'isi', $targetId, $reason, $currentAdminId);
                            if (mysqli_stmt_execute($stmt)) {
                                cbw_log_admin_freeze_event(
                                    $currentAdminId,
                                    'ADMIN_FREEZE',
                                    $targetId,
                                    'Individually froze ' . $target['fullname'] . '. Reason: ' . $reason
                                );
                                $message = 'Administrator ' . $target['fullname'] . ' has been frozen.';
                                $messageType = 'success';
                            } else {
                                $message = 'Could not freeze this administrator.';
                                $messageType = 'danger';
                            }
                            mysqli_stmt_close($stmt);
                        }
                    }
                } elseif ($action === 'unfreeze') {
                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE admin_individual_freezes
                         SET is_frozen = 0, unfrozen_at = NOW()
                         WHERE user_id = ? AND is_frozen = 1"
                    );

                    if (!$stmt) {
                        $message = 'Could not prepare the unfreeze request.';
                        $messageType = 'danger';
                    } else {
                        mysqli_stmt_bind_param($stmt, 'i', $targetId);
                        if (mysqli_stmt_execute($stmt)) {
                            cbw_log_admin_freeze_event(
                                $currentAdminId,
                                'ADMIN_UNFREEZE',
                                $targetId,
                                'Individually unfroze ' . $target['fullname'] . '.'
                            );
                            $message = 'Administrator ' . $target['fullname'] . ' has been unfrozen.';
                            $messageType = 'success';
                        } else {
                            $message = 'Could not unfreeze this administrator.';
                            $messageType = 'danger';
                        }
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    $message = 'Unknown freeze action.';
                    $messageType = 'danger';
                }
            }
        }
    }
}

$adminsResult = mysqli_query(
    $conn,
    "SELECT
        u.id,
        u.fullname,
        u.email,
        u.account_status,
        r.name AS role_name,
        p.id AS policy_id,
        p.starts_at,
        p.expires_at,
        p.timezone,
        p.active AS access_policy_active,
        COALESCE(f.is_frozen, 0) AS individual_frozen,
        f.reason AS freeze_reason,
        f.frozen_at,
        freezer.fullname AS frozen_by_name
     FROM users u
     LEFT JOIN admin_roles r ON r.id = u.admin_role_id
     LEFT JOIN admin_access_policies p ON p.user_id = u.id
     LEFT JOIN admin_individual_freezes f ON f.user_id = u.id
     LEFT JOIN users freezer ON freezer.id = f.frozen_by
     WHERE u.role = 'admin'
     ORDER BY CASE WHEN r.name = 'Super Admin' THEN 0 ELSE 1 END, u.id ASC"
);

if (!$adminsResult) {
    http_response_code(500);
    die('Could not load administrators. Verify that the admin_individual_freezes table exists.');
}

$globalFreezeActive = false;
$globalResult = mysqli_query($conn, 'SELECT administrative_freeze FROM admin_system_controls WHERE id = 1 LIMIT 1');
if ($globalResult && ($globalRow = mysqli_fetch_assoc($globalResult))) {
    $globalFreezeActive = (int) $globalRow['administrative_freeze'] === 1;
}

$pageTitle = 'Admin Access';
include __DIR__ . '/includes/admin_header.php';
include __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="admin-content">
    <?php include __DIR__ . '/includes/admin_topbar.php'; ?>

    <main class="admin-page">
        <div class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">SECURITY</p>
                <h1 class="admin-page-title">Admin Access</h1>
                <p class="admin-page-description">Manage working schedules, access periods and individual administrator freezes.</p>
            </div>
            <div class="admin-page-actions d-flex gap-2 flex-wrap">
                <a href="ManageAdministrators.php" class="btn btn-outline-secondary">
                    <i class="bi bi-person-gear"></i> Manage Administrators
                </a>
                <a href="Settings.php" class="btn btn-outline-secondary">
                    <i class="bi bi-sliders"></i> Settings
                </a>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo e($messageType); ?> alert-dismissible fade show" role="alert">
                <?php echo e($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($globalFreezeActive): ?>
            <div class="alert alert-warning">
                <i class="bi bi-lock-fill"></i>
                <strong>Global Administrative Freeze is active.</strong>
                All non-Super-Admin accounts are restricted from administrative changes.
                Individual freeze status is still shown separately below.
            </div>
        <?php endif; ?>

        <div class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <h2 class="admin-panel-title">Administrators</h2>
                    <p class="admin-panel-subtitle mb-0">Suspension blocks login; individual freeze blocks administrative changes; access schedules control permitted hours.</p>
                </div>
                <span class="badge text-bg-light"><?php echo (int) mysqli_num_rows($adminsResult); ?> Accounts</span>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>Administrator</th>
                            <th>Role</th>
                            <th>Account</th>
                            <th>Individual Freeze</th>
                            <th>Access Period</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($adminsResult) === 0): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No administrator accounts were found.</td></tr>
                    <?php else: ?>
                        <?php while ($admin = mysqli_fetch_assoc($adminsResult)): ?>
                            <?php
                            $roleName = $admin['role_name'] ?? 'Unassigned role';
                            $isSuperAccount = $roleName === 'Super Admin';
                            $isFrozen = (int) $admin['individual_frozen'] === 1;
                            $accountStatus = $admin['account_status'] ?: 'active';
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($admin['fullname']); ?></strong>
                                    <small class="d-block text-muted"><?php echo e($admin['email']); ?></small>
                                </td>
                                <td><?php echo e($roleName); ?></td>
                                <td>
                                    <?php if ($accountStatus === 'active'): ?>
                                        <span class="badge text-bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary"><?php echo e($accountStatus); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isSuperAccount): ?>
                                        <span class="badge text-bg-primary">Exempt</span>
                                    <?php elseif ($isFrozen): ?>
                                        <span class="badge text-bg-danger"><i class="bi bi-lock-fill"></i> Frozen</span>
                                        <?php if (!empty($admin['freeze_reason'])): ?>
                                            <small class="d-block text-muted mt-1">Reason: <?php echo e($admin['freeze_reason']); ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($admin['frozen_at'])): ?>
                                            <small class="d-block text-muted"><?php echo e($admin['frozen_at']); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge text-bg-success">Not frozen</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($admin['policy_id']): ?>
                                        <small class="d-block">From: <?php echo e($admin['starts_at'] ?: 'Immediately'); ?></small>
                                        <small class="d-block text-muted">Until: <?php echo e($admin['expires_at'] ?: 'No expiry'); ?></small>
                                        <small class="d-block text-muted"><?php echo e($admin['timezone'] ?: 'Asia/Colombo'); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">No policy configured</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <a href="EditAdminAccess.php?id=<?php echo (int) $admin['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-clock-history"></i> Access
                                        </a>

                                        <?php if (admin_is_super_admin() && !$isSuperAccount): ?>
                                            <?php if ($isFrozen): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                                    <input type="hidden" name="user_id" value="<?php echo (int) $admin['id']; ?>">
                                                    <input type="hidden" name="action" value="unfreeze">
                                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Unfreeze this administrator?');">
                                                        <i class="bi bi-unlock-fill"></i> Unfreeze
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <details class="text-start">
                                                    <summary class="btn btn-sm btn-outline-danger"><i class="bi bi-lock-fill"></i> Freeze</summary>
                                                    <form method="POST" class="border rounded p-3 mt-2" style="min-width: 250px; max-width: 320px;">
                                                        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                                        <input type="hidden" name="user_id" value="<?php echo (int) $admin['id']; ?>">
                                                        <input type="hidden" name="action" value="freeze">
                                                        <label class="form-label" for="freeze-reason-<?php echo (int) $admin['id']; ?>">Reason for freezing</label>
                                                        <textarea id="freeze-reason-<?php echo (int) $admin['id']; ?>" name="reason" class="form-control form-control-sm mb-2" rows="3" maxlength="255" required placeholder="Enter a reason"></textarea>
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Freeze this administrator\'s administrative actions?');">Confirm Freeze</button>
                                                    </form>
                                                </details>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
</body>
</html>
