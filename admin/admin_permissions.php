<?php

/*
|--------------------------------------------------------------------------
| ROLE PERMISSION CHECKS
|--------------------------------------------------------------------------
*/
function admin_has_permission($permission)
{
    global $conn;

    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        return false;
    }

    $userId = (int) $_SESSION['user_id'];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT 1
         FROM users u
         INNER JOIN admin_role_permissions arp ON u.admin_role_id = arp.role_id
         INNER JOIN admin_permissions ap ON arp.permission_id = ap.id
         WHERE u.id = ? AND u.role = 'admin' AND ap.permission_key = ?
         LIMIT 1"
    );

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'is', $userId, $permission);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $allowed = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $allowed;
}

function require_admin_permission($permission)
{
    if (!admin_has_permission($permission)) {
        http_response_code(403);
        die('You do not have permission to perform this action.');
    }
}

/*
|--------------------------------------------------------------------------
| SUPER ADMIN DETECTION
|--------------------------------------------------------------------------
| Identify the Super Admin by the assigned role name, not is_system.
| Multiple built-in roles may have is_system = 1.
|--------------------------------------------------------------------------
*/
function admin_is_super_admin()
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $userId = (int) $_SESSION['user_id'];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT 1
         FROM users u
         INNER JOIN admin_roles r ON r.id = u.admin_role_id
         WHERE u.id = ? AND u.role = 'admin' AND r.name = 'Super Admin'
         LIMIT 1"
    );

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $isSuperAdmin = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    return $isSuperAdmin;
}

/*
|--------------------------------------------------------------------------
| GLOBAL ADMINISTRATIVE FREEZE
|--------------------------------------------------------------------------
*/
function admin_access_is_frozen()
{
    global $conn;

    $result = mysqli_query(
        $conn,
        "SELECT administrative_freeze
         FROM admin_system_controls
         WHERE id = 1
         LIMIT 1"
    );

    if (!$result) {
        // Fail closed if the security-control state cannot be read.
        return true;
    }

    $row = mysqli_fetch_assoc($result);
    return $row ? ((int) $row['administrative_freeze'] === 1) : false;
}

/*
|--------------------------------------------------------------------------
| INDIVIDUAL ADMINISTRATOR FREEZE
|--------------------------------------------------------------------------
| This is the function missing from the current installation.
| It reads admin_individual_freezes for the signed-in administrator.
|--------------------------------------------------------------------------
*/
function admin_is_individually_frozen()
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return true;
    }

    // Super Admin is never frozen by an administrator freeze record.
    if (admin_is_super_admin()) {
        return false;
    }

    $userId = (int) $_SESSION['user_id'];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT is_frozen
         FROM admin_individual_freezes
         WHERE user_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        // Fail closed for non-Super-Admin users if this security check fails.
        return true;
    }

    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row ? ((int) $row['is_frozen'] === 1) : false;
}

/*
|--------------------------------------------------------------------------
| ACCESS PERIOD
|--------------------------------------------------------------------------
*/
function admin_is_within_access_period()
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    if (admin_is_super_admin()) {
        return true;
    }

    $userId = (int) $_SESSION['user_id'];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT starts_at, expires_at, timezone, active
         FROM admin_access_policies
         WHERE user_id = ?
         LIMIT 1"
    );

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $policy = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // No access policy means no date restriction.
    if (!$policy) {
        return true;
    }

    // A policy explicitly marked inactive denies access until corrected.
    if ((int) $policy['active'] !== 1) {
        return false;
    }

    try {
        $timezone = new DateTimeZone($policy['timezone'] ?: 'Asia/Colombo');
    } catch (Exception $e) {
        $timezone = new DateTimeZone('Asia/Colombo');
    }

    $now = new DateTime('now', $timezone);

    if (!empty($policy['starts_at'])) {
        $startsAt = new DateTime($policy['starts_at'], $timezone);
        if ($now < $startsAt) {
            return false;
        }
    }

    if (!empty($policy['expires_at'])) {
        $expiresAt = new DateTime($policy['expires_at'], $timezone);
        if ($now > $expiresAt) {
            return false;
        }
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| WEEKLY WORKING SCHEDULE
|--------------------------------------------------------------------------
| Database and PHP use 1 = Monday through 7 = Sunday.
| No enabled schedule rows means no schedule restriction.
|--------------------------------------------------------------------------
*/
function admin_is_within_schedule()
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    if (admin_is_super_admin()) {
        return true;
    }

    $userId = (int) $_SESSION['user_id'];
    $stmt = mysqli_prepare(
        $conn,
        "SELECT p.timezone, s.day_of_week, s.start_time, s.end_time
         FROM admin_access_policies p
         INNER JOIN admin_access_schedule s ON s.policy_id = p.id
         WHERE p.user_id = ? AND p.active = 1 AND s.enabled = 1"
    );

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);

    // No enabled schedule entries means no working-hours restriction.
    if (!$rows) {
        return true;
    }

    try {
        $timezone = new DateTimeZone($rows[0]['timezone'] ?: 'Asia/Colombo');
    } catch (Exception $e) {
        $timezone = new DateTimeZone('Asia/Colombo');
    }

    $now = new DateTime('now', $timezone);
    $currentDay = (int) $now->format('N');
    $currentTime = $now->format('H:i:s');

    foreach ($rows as $row) {
        if ((int) $row['day_of_week'] !== $currentDay) {
            continue;
        }

        // Supports ordinary same-day time ranges.
        if ($currentTime >= $row['start_time'] && $currentTime <= $row['end_time']) {
            return true;
        }
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| CENTRAL ADMIN ACCESS GATE
|--------------------------------------------------------------------------
*/
function require_admin_access($allowReadOnly = false)
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die('Administrator access required.');
    }

    // Preserve emergency recovery access for the Super Admin.
    if (admin_is_super_admin()) {
        return;
    }

    if (!admin_is_within_access_period()) {
        http_response_code(403);
        die('Your administrative access period is not currently active.');
    }

    if (!admin_is_within_schedule()) {
        if ($allowReadOnly) {
            return;
        }
        http_response_code(403);
        die('Administrative actions are not currently available during your scheduled access period.');
    }

    if ($allowReadOnly) {
        return;
    }

    if (admin_is_individually_frozen()) {
        http_response_code(423);
        die('Administrative actions for this account are currently frozen by the Super Admin.');
    }

    if (admin_access_is_frozen()) {
        http_response_code(423);
        die('Administrative changes are temporarily frozen by the Super Admin.');
    }
}

?>
