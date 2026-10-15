<?php

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_permission('admins.update');
require_admin_access(false);

if (!function_exists('admin_is_super_admin') || !admin_is_super_admin()) {
    http_response_code(403);
    die('Only the Super Admin can edit administrator accounts.');
}

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($userId <= 0) {
    header('Location: ManageAdministrators.php');
    exit;
}

function cbw_load_editable_administrator(mysqli $conn, int $userId): ?array
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT u.id, u.fullname, u.email, u.account_status, u.admin_role_id,
                u.created_at, r.name AS role_name
         FROM users u
         LEFT JOIN admin_roles r ON r.id = u.admin_role_id
         WHERE u.id = ? AND u.role = 'admin'
         LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $record = mysqli_fetch_assoc($result) ?: null;
    mysqli_stmt_close($stmt);
    return $record;
}

$admin = cbw_load_editable_administrator($conn, $userId);
if (!$admin) {
    header('Location: ManageAdministrators.php');
    exit;
}

if (($admin['role_name'] ?? '') === 'Super Admin') {
    http_response_code(403);
    die('The Super Admin account is protected and cannot be edited from this page.');
}

$pageTitle = 'Edit Administrator';
$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
$message = '';
$messageType = '';
$canSuspend = admin_has_permission('admins.suspend');

$rolesResult = mysqli_query(
    $conn,
    "SELECT id, name, description
     FROM admin_roles
     WHERE name <> 'Super Admin'
     ORDER BY id ASC"
);
$availableRoles = [];
if ($rolesResult) {
    while ($roleRow = mysqli_fetch_assoc($rolesResult)) {
        $availableRoles[] = $roleRow;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message = 'Your form security token is invalid. Refresh the page and try again.';
        $messageType = 'danger';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_profile') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $roleId = (int) ($_POST['admin_role_id'] ?? 0);

            if ($fullname === '' || $email === '' || $roleId <= 0) {
                $message = 'Full name, email address and role are required.';
                $messageType = 'warning';
            } elseif (mb_strlen($fullname) > 100) {
                $message = 'Full name must be no longer than 100 characters.';
                $messageType = 'warning';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
                $message = 'Enter a valid email address of no more than 100 characters.';
                $messageType = 'warning';
            } else {
                $roleStmt = mysqli_prepare(
                    $conn,
                    "SELECT id FROM admin_roles WHERE id = ? AND name <> 'Super Admin' LIMIT 1"
                );
                if (!$roleStmt) {
                    $message = 'Could not validate the selected role.';
                    $messageType = 'danger';
                } else {
                    mysqli_stmt_bind_param($roleStmt, 'i', $roleId);
                    mysqli_stmt_execute($roleStmt);
                    mysqli_stmt_store_result($roleStmt);
                    $roleValid = mysqli_stmt_num_rows($roleStmt) > 0;
                    mysqli_stmt_close($roleStmt);

                    if (!$roleValid) {
                        $message = 'Choose a valid non-Super-Admin role.';
                        $messageType = 'warning';
                    } else {
                        $emailStmt = mysqli_prepare(
                            $conn,
                            'SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1'
                        );
                        if (!$emailStmt) {
                            $message = 'Could not verify the email address.';
                            $messageType = 'danger';
                        } else {
                            mysqli_stmt_bind_param($emailStmt, 'si', $email, $userId);
                            mysqli_stmt_execute($emailStmt);
                            mysqli_stmt_store_result($emailStmt);
                            $emailExists = mysqli_stmt_num_rows($emailStmt) > 0;
                            mysqli_stmt_close($emailStmt);

                            if ($emailExists) {
                                $message = 'That email address is already being used by another account.';
                                $messageType = 'warning';
                            } else {
                                $updateStmt = mysqli_prepare(
                                    $conn,
                                    "UPDATE users
                                     SET fullname = ?, email = ?, admin_role_id = ?
                                     WHERE id = ? AND role = 'admin'"
                                );
                                if (!$updateStmt) {
                                    $message = 'Could not prepare the account update.';
                                    $messageType = 'danger';
                                } else {
                                    mysqli_stmt_bind_param(
                                        $updateStmt,
                                        'ssii',
                                        $fullname,
                                        $email,
                                        $roleId,
                                        $userId
                                    );
                                    if (mysqli_stmt_execute($updateStmt)) {
                                        $actionName = 'ADMIN_UPDATED';
                                        $targetType = 'administrator';
                                        $description = 'Updated administrator profile, email or role for user ID ' . $userId . '.';
                                        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
                                        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                                        $logStmt = mysqli_prepare(
                                            $conn,
                                            'INSERT INTO admin_activity_log
                                             (user_id, action, target_type, target_id, description, ip_address, user_agent)
                                             VALUES (?, ?, ?, ?, ?, ?, ?)'
                                        );
                                        if ($logStmt) {
                                            mysqli_stmt_bind_param(
                                                $logStmt,
                                                'ississs',
                                                $currentAdminId,
                                                $actionName,
                                                $targetType,
                                                $userId,
                                                $description,
                                                $ipAddress,
                                                $userAgent
                                            );
                                            mysqli_stmt_execute($logStmt);
                                            mysqli_stmt_close($logStmt);
                                        }
                                        $message = 'Administrator details updated successfully.';
                                        $messageType = 'success';
                                    } else {
                                        $message = 'Could not update the administrator account.';
                                        $messageType = 'danger';
                                    }
                                    mysqli_stmt_close($updateStmt);
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'suspend' || $action === 'activate') {
            if (!$canSuspend || !admin_has_permission('admins.suspend')) {
                http_response_code(403);
                die('You do not have permission to suspend or activate administrator accounts.');
            }

            $newStatus = $action === 'suspend' ? 'suspended' : 'active';
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE users SET account_status = ? WHERE id = ? AND role = 'admin' AND admin_role_id <> (
                    SELECT id FROM admin_roles WHERE name = 'Super Admin' LIMIT 1
                )"
            );

            if (!$stmt) {
                $message = 'Could not prepare the account status change.';
                $messageType = 'danger';
            } else {
                mysqli_stmt_bind_param($stmt, 'si', $newStatus, $userId);
                if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0) {
                    $actionName = $action === 'suspend' ? 'ADMIN_SUSPENDED' : 'ADMIN_ACTIVATED';
                    $targetType = 'administrator';
                    $description = ($action === 'suspend' ? 'Suspended' : 'Activated') . ' administrator account for user ID ' . $userId . '.';
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
                    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
                    $logStmt = mysqli_prepare(
                        $conn,
                        'INSERT INTO admin_activity_log
                         (user_id, action, target_type, target_id, description, ip_address, user_agent)
                         VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    if ($logStmt) {
                        mysqli_stmt_bind_param(
                            $logStmt,
                            'ississs',
                            $currentAdminId,
                            $actionName,
                            $targetType,
                            $userId,
                            $description,
                            $ipAddress,
                            $userAgent
                        );
                        mysqli_stmt_execute($logStmt);
                        mysqli_stmt_close($logStmt);
                    }
                    $message = $action === 'suspend'
                        ? 'Administrator account suspended. The next login attempt will be rejected.'
                        : 'Administrator account activated.';
                    $messageType = 'success';
                } else {
                    $message = 'Could not change the administrator account status.';
                    $messageType = 'danger';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    $admin = cbw_load_editable_administrator($conn, $userId);
    if (!$admin || ($admin['role_name'] ?? '') === 'Super Admin') {
        header('Location: ManageAdministrators.php');
        exit;
    }
}

include __DIR__ . '/includes/admin_header.php';
include __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="admin-content">
    <?php include __DIR__ . '/includes/admin_topbar.php'; ?>

    <main class="admin-page">
        <div class="admin-page-header">
            <div>
                <p class="admin-page-eyebrow">ADMINISTRATION</p>
                <h1 class="admin-page-title">Edit Administrator</h1>
                <p class="admin-page-description">Update role and contact details, or manage account activation.</p>
            </div>
            <div class="admin-page-actions">
                <a href="ManageAdministrators.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Administrators
                </a>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo e($messageType); ?> alert-dismissible fade show" role="alert">
                <?php echo e($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-xl-7">
                <div class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <h2 class="admin-panel-title">Account Details</h2>
                            <p class="admin-panel-subtitle mb-0">Super Admin accounts are protected from changes on this page.</p>
                        </div>
                    </div>
                    <div class="p-4">
                        <form method="POST" action="EditAdministrator.php?id=<?php echo (int) $userId; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                            <input type="hidden" name="action" value="save_profile">

                            <div class="mb-3">
                                <label for="fullname" class="form-label fw-semibold">Full Name</label>
                                <input type="text" id="fullname" name="fullname" class="form-control" maxlength="100" required value="<?php echo e($admin['fullname']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" maxlength="100" required value="<?php echo e($admin['email']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="admin_role_id" class="form-label fw-semibold">Role</label>
                                <select id="admin_role_id" name="admin_role_id" class="form-select" required>
                                    <?php foreach ($availableRoles as $role): ?>
                                        <option value="<?php echo (int) $role['id']; ?>" <?php echo (int) $admin['admin_role_id'] === (int) $role['id'] ? 'selected' : ''; ?>>
                                            <?php echo e($role['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Details
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="admin-panel mb-4">
                    <div class="admin-panel-header">
                        <div>
                            <h2 class="admin-panel-title">Account Status</h2>
                            <p class="admin-panel-subtitle mb-0">Suspension prevents future login.</p>
                        </div>
                    </div>
                    <div class="p-4">
                        <p class="mb-3">
                            Current status:
                            <?php if (($admin['account_status'] ?? 'active') === 'active'): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary"><?php echo e(ucfirst($admin['account_status'])); ?></span>
                            <?php endif; ?>
                        </p>

                        <?php if ($canSuspend): ?>
                            <?php if (($admin['account_status'] ?? 'active') === 'active'): ?>
                                <form method="POST" action="EditAdministrator.php?id=<?php echo (int) $userId; ?>" onsubmit="return confirm('Suspend this administrator? They will not be able to log in again until activated.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-person-lock"></i> Suspend Account
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="EditAdministrator.php?id=<?php echo (int) $userId; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-person-check"></i> Activate Account
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Your role does not have permission to suspend administrator accounts.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <h2 class="admin-panel-title">Access Rules</h2>
                            <p class="admin-panel-subtitle mb-0">Manage expiry and working hours separately.</p>
                        </div>
                    </div>
                    <div class="p-4">
                        <a href="EditAdminAccess.php?id=<?php echo (int) $userId; ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-clock-history"></i> Configure Access Period & Schedule
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
