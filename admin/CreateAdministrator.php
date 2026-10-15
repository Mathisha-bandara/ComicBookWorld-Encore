<?php

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_permission('admins.create');
require_admin_access(false);

if (!function_exists('admin_is_super_admin') || !admin_is_super_admin()) {
    http_response_code(403);
    die('Only the Super Admin can create administrator accounts.');
}

$pageTitle = 'Create Administrator';
$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
$message = '';
$messageType = '';
$fullname = '';
$email = '';
$selectedRoleId = 0;

$rolesResult = mysqli_query(
    $conn,
    "SELECT id, name, description
     FROM admin_roles
     WHERE name <> 'Super Admin'
     ORDER BY id ASC"
);

if (!$rolesResult) {
    http_response_code(500);
    die('Could not load administrator roles.');
}

$availableRoles = [];
while ($roleRow = mysqli_fetch_assoc($rolesResult)) {
    $availableRoles[] = $roleRow;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $selectedRoleId = (int) ($_POST['admin_role_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $message = 'Your form security token is invalid. Refresh the page and try again.';
        $messageType = 'danger';
    } elseif ($fullname === '' || $email === '' || $selectedRoleId <= 0 || $password === '' || $confirmPassword === '') {
        $message = 'Please complete every field.';
        $messageType = 'warning';
    } elseif (mb_strlen($fullname) > 100) {
        $message = 'The full name must be no longer than 100 characters.';
        $messageType = 'warning';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $message = 'Enter a valid email address of no more than 100 characters.';
        $messageType = 'warning';
    } elseif (strlen($password) < 8) {
        $message = 'The password must contain at least 8 characters.';
        $messageType = 'warning';
    } elseif ($password !== $confirmPassword) {
        $message = 'The password and confirmation do not match.';
        $messageType = 'warning';
    } else {
        $roleStmt = mysqli_prepare(
            $conn,
            "SELECT id, name FROM admin_roles WHERE id = ? AND name <> 'Super Admin' LIMIT 1"
        );

        if (!$roleStmt) {
            $message = 'Could not validate the selected role.';
            $messageType = 'danger';
        } else {
            mysqli_stmt_bind_param($roleStmt, 'i', $selectedRoleId);
            mysqli_stmt_execute($roleStmt);
            $roleResult = mysqli_stmt_get_result($roleStmt);
            $selectedRole = mysqli_fetch_assoc($roleResult);
            mysqli_stmt_close($roleStmt);

            if (!$selectedRole) {
                $message = 'Choose a valid non-Super-Admin role.';
                $messageType = 'warning';
            } else {
                $emailStmt = mysqli_prepare(
                    $conn,
                    'SELECT id FROM users WHERE email = ? LIMIT 1'
                );

                if (!$emailStmt) {
                    $message = 'Could not check the email address.';
                    $messageType = 'danger';
                } else {
                    mysqli_stmt_bind_param($emailStmt, 's', $email);
                    mysqli_stmt_execute($emailStmt);
                    mysqli_stmt_store_result($emailStmt);
                    $emailExists = mysqli_stmt_num_rows($emailStmt) > 0;
                    mysqli_stmt_close($emailStmt);

                    if ($emailExists) {
                        $message = 'An account with this email address already exists.';
                        $messageType = 'warning';
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $accountRole = 'admin';
                        $accountStatus = 'active';

                        $insertStmt = mysqli_prepare(
                            $conn,
                            'INSERT INTO users (fullname, email, password, role, account_status, admin_role_id)
                             VALUES (?, ?, ?, ?, ?, ?)'
                        );

                        if (!$insertStmt) {
                            $message = 'Could not prepare the account creation request.';
                            $messageType = 'danger';
                        } else {
                            mysqli_stmt_bind_param(
                                $insertStmt,
                                'sssssi',
                                $fullname,
                                $email,
                                $passwordHash,
                                $accountRole,
                                $accountStatus,
                                $selectedRoleId
                            );

                            if (mysqli_stmt_execute($insertStmt)) {
                                $newUserId = (int) mysqli_insert_id($conn);
                                mysqli_stmt_close($insertStmt);

                                $action = 'ADMIN_CREATED';
                                $targetType = 'administrator';
                                $description = 'Created administrator account for ' . $fullname . ' with role ' . $selectedRole['name'] . '.';
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
                                        $action,
                                        $targetType,
                                        $newUserId,
                                        $description,
                                        $ipAddress,
                                        $userAgent
                                    );
                                    mysqli_stmt_execute($logStmt);
                                    mysqli_stmt_close($logStmt);
                                }

                                header('Location: ManageAdministrators.php?created=1');
                                exit;
                            } else {
                                $dbError = mysqli_stmt_error($insertStmt);
                                mysqli_stmt_close($insertStmt);
                                if (stripos($dbError, 'Duplicate') !== false) {
                                    $message = 'That email address is already in use.';
                                } else {
                                    $message = 'Could not create the administrator account.';
                                }
                                $messageType = 'danger';
                            }
                        }
                    }
                }
            }
        }
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
                <h1 class="admin-page-title">Create Administrator</h1>
                <p class="admin-page-description">Create an account and assign one of the existing non-Super-Admin roles.</p>
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

        <div class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <h2 class="admin-panel-title">Administrator Details</h2>
                    <p class="admin-panel-subtitle mb-0">The password will be stored as a secure password hash.</p>
                </div>
            </div>

            <div class="p-4">
                <?php if (empty($availableRoles)): ?>
                    <div class="alert alert-warning mb-0">
                        No eligible administrator roles were found. Confirm the five-role setup before creating accounts.
                    </div>
                <?php else: ?>
                    <form method="POST" action="CreateAdministrator.php" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="fullname" class="form-label fw-semibold">Full Name</label>
                                <input
                                    type="text"
                                    id="fullname"
                                    name="fullname"
                                    class="form-control"
                                    maxlength="100"
                                    required
                                    value="<?php echo e($fullname); ?>"
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    maxlength="100"
                                    required
                                    value="<?php echo e($email); ?>"
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="admin_role_id" class="form-label fw-semibold">Administrator Role</label>
                                <select id="admin_role_id" name="admin_role_id" class="form-select" required>
                                    <option value="">Choose a role</option>
                                    <?php foreach ($availableRoles as $role): ?>
                                        <option
                                            value="<?php echo (int) $role['id']; ?>"
                                            <?php echo $selectedRoleId === (int) $role['id'] ? 'selected' : ''; ?>
                                        >
                                            <?php echo e($role['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Super Admin cannot be assigned from this form.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label fw-semibold">Temporary Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                >
                                <small class="text-muted">Use at least 8 characters and share it securely with the intended account holder.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    minlength="8"
                                    required
                                    autocomplete="new-password"
                                >
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus-fill"></i> Create Administrator
                            </button>
                            <a href="ManageAdministrators.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
