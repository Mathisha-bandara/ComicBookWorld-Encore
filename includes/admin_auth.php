<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../HomePage.php");
    exit();
}

require_once __DIR__ . '/db.php';

$statusStmt = mysqli_prepare(
    $conn,
    "SELECT account_status
     FROM users
     WHERE id = ? AND role = 'admin'
     LIMIT 1"
);

if (!$statusStmt) {
    http_response_code(503);
    die('Unable to verify administrator account status.');
}

$sessionUserId = (int) $_SESSION['user_id'];
mysqli_stmt_bind_param($statusStmt, 'i', $sessionUserId);
mysqli_stmt_execute($statusStmt);
$statusResult = mysqli_stmt_get_result($statusStmt);
$statusRow = mysqli_fetch_assoc($statusResult);
mysqli_stmt_close($statusStmt);

if (!$statusRow || ($statusRow['account_status'] ?? 'active') !== 'active') {
    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php?status=inactive');
    exit();
}

// ==================================================
// CSRF TOKEN
// ==================================================
//
// Creates one security token for the current admin
// session. State-changing admin forms will use this
// token to prevent unauthorized cross-site requests.
//

if (empty($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));
}


/*
 * Keep the older admin_csrf_token name synchronized
 * with the main CSRF token so existing admin pages
 * continue working.
 */
$_SESSION['admin_csrf_token'] =
    $_SESSION['csrf_token'];

require_once __DIR__ . '/../admin/admin_permissions.php';

?>