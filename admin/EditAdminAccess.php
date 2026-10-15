<?php

require_once("../includes/admin_auth.php");
require_once("../includes/db.php");
require_once("../includes/functions.php");

require_admin_permission('admins.update');


/*
|--------------------------------------------------------------------------
| GET ADMIN USER ID
|--------------------------------------------------------------------------
*/

$userId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($userId <= 0) {

    header("Location: ManageAdminAccess.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| LOAD ADMIN USER
|--------------------------------------------------------------------------
*/

$userStmt = mysqli_prepare(
    $conn,
    "SELECT
        u.id,
        u.fullname,
        u.email,
        u.account_status,
        u.admin_role_id,
        r.name AS role_name
     FROM users u
     LEFT JOIN admin_roles r
        ON r.id = u.admin_role_id
     WHERE u.id = ?
       AND u.role = 'admin'
     LIMIT 1"
);


mysqli_stmt_bind_param(
    $userStmt,
    "i",
    $userId
);


mysqli_stmt_execute($userStmt);


$userResult =
    mysqli_stmt_get_result($userStmt);


$admin =
    mysqli_fetch_assoc($userResult);


mysqli_stmt_close($userStmt);


if (!$admin) {

    header("Location: ManageAdminAccess.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| LOAD ACCESS POLICY
|--------------------------------------------------------------------------
*/

$policyStmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        starts_at,
        expires_at,
        timezone,
        active
     FROM admin_access_policies
     WHERE user_id = ?
     LIMIT 1"
);


mysqli_stmt_bind_param(
    $policyStmt,
    "i",
    $userId
);


mysqli_stmt_execute($policyStmt);


$policyResult =
    mysqli_stmt_get_result($policyStmt);


$policy =
    mysqli_fetch_assoc($policyResult);


mysqli_stmt_close($policyStmt);


/*
|--------------------------------------------------------------------------
| DEFAULT POLICY VALUES
|--------------------------------------------------------------------------
*/

$policyId =
    $policy['id'] ?? 0;

$startsAt =
    $policy['starts_at'] ?? '';

$expiresAt =
    $policy['expires_at'] ?? '';

$timezone =
    $policy['timezone'] ?? 'Asia/Colombo';

$active =
    isset($policy['active'])
        ? (int) $policy['active']
        : 1;


/*
|--------------------------------------------------------------------------
| LOAD WEEKLY SCHEDULE
|--------------------------------------------------------------------------
*/

$schedule = [];


for ($day = 1; $day <= 7; $day++) {

    $schedule[$day] = [
        'enabled' => 0,
        'start_time' => '09:00',
        'end_time' => '17:00'
    ];

}


if ($policyId > 0) {

    $scheduleStmt = mysqli_prepare(
        $conn,
        "SELECT
            day_of_week,
            start_time,
            end_time,
            enabled
         FROM admin_access_schedule
         WHERE policy_id = ?
         ORDER BY day_of_week ASC"
    );


    mysqli_stmt_bind_param(
        $scheduleStmt,
        "i",
        $policyId
    );


    mysqli_stmt_execute(
        $scheduleStmt
    );


    $scheduleResult =
        mysqli_stmt_get_result(
            $scheduleStmt
        );


    while (
        $scheduleRow =
        mysqli_fetch_assoc(
            $scheduleResult
        )
    ) {

        $day =
            (int) $scheduleRow['day_of_week'];


        if (
    $day >= 1 &&
    $day <= 7
) {

            $schedule[$day] = [

                'enabled' =>
                    (int)
                    $scheduleRow['enabled'],

                'start_time' =>
                    substr(
                        $scheduleRow['start_time'],
                        0,
                        5
                    ),

                'end_time' =>
                    substr(
                        $scheduleRow['end_time'],
                        0,
                        5
                    )

            ];

        }

    }


    mysqli_stmt_close(
        $scheduleStmt
    );

}


/*
|--------------------------------------------------------------------------
| SAVE POLICY
|--------------------------------------------------------------------------
*/

$message = "";
$messageType = "";


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {


    $startsAt =
        trim(
            $_POST['starts_at'] ?? ''
        );


    $expiresAt =
        trim(
            $_POST['expires_at'] ?? ''
        );


    $timezone =
        trim(
            $_POST['timezone'] ?? 'Asia/Colombo'
        );


    $active =
        isset($_POST['active'])
            ? 1
            : 0;


    /*
    |--------------------------------------------------------------------------
    | SAVE / UPDATE POLICY
    |--------------------------------------------------------------------------
    */

    if ($policyId > 0) {


        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE admin_access_policies
             SET
                starts_at = NULLIF(?, ''),
                expires_at = NULLIF(?, ''),
                timezone = ?,
                active = ?,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = ?
               AND user_id = ?"
        );


        mysqli_stmt_bind_param(
            $updateStmt,
            "sssiii",
            $startsAt,
            $expiresAt,
            $timezone,
            $active,
            $policyId,
            $userId
        );


        mysqli_stmt_execute(
            $updateStmt
        );


        mysqli_stmt_close(
            $updateStmt
        );


    } else {


        $insertStmt = mysqli_prepare(
            $conn,
            "INSERT INTO admin_access_policies
                (
                    user_id,
                    starts_at,
                    expires_at,
                    timezone,
                    active
                )
             VALUES
                (
                    ?,
                    NULLIF(?, ''),
                    NULLIF(?, ''),
                    ?,
                    ?
                )"
        );


        mysqli_stmt_bind_param(
            $insertStmt,
            "isssi",
            $userId,
            $startsAt,
            $expiresAt,
            $timezone,
            $active
        );


        mysqli_stmt_execute(
            $insertStmt
        );


        $policyId =
            mysqli_insert_id($conn);


        mysqli_stmt_close(
            $insertStmt
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SAVE WEEKLY SCHEDULE
    |--------------------------------------------------------------------------
    */

    $deleteScheduleStmt =
        mysqli_prepare(
            $conn,
            "DELETE FROM admin_access_schedule
             WHERE policy_id = ?"
        );


    mysqli_stmt_bind_param(
        $deleteScheduleStmt,
        "i",
        $policyId
    );


    mysqli_stmt_execute(
        $deleteScheduleStmt
    );


    mysqli_stmt_close(
        $deleteScheduleStmt
    );


    $insertScheduleStmt =
        mysqli_prepare(
            $conn,
            "INSERT INTO admin_access_schedule
                (
                    policy_id,
                    day_of_week,
                    start_time,
                    end_time,
                    enabled
                )
             VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )"
        );


    for ($day = 1; $day <= 7; $day++) {


        $enabled =
            isset(
                $_POST['days'][$day]['enabled']
            )
                ? 1
                : 0;


        $startTime =
            $_POST['days'][$day]['start_time']
            ?? '09:00';


        $endTime =
            $_POST['days'][$day]['end_time']
            ?? '17:00';


        mysqli_stmt_bind_param(
            $insertScheduleStmt,
            "iissi",
            $policyId,
            $day,
            $startTime,
            $endTime,
            $enabled
        );


        mysqli_stmt_execute(
            $insertScheduleStmt
        );

    }


    mysqli_stmt_close(
        $insertScheduleStmt
    );


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    $message =
        "Access policy updated successfully.";

    $messageType =
        "success";


    /*
    |--------------------------------------------------------------------------
    | RELOAD VALUES
    |--------------------------------------------------------------------------
    */

    $policyStmt = mysqli_prepare(
        $conn,
        "SELECT
            id,
            starts_at,
            expires_at,
            timezone,
            active
         FROM admin_access_policies
         WHERE user_id = ?
         LIMIT 1"
    );


    mysqli_stmt_bind_param(
        $policyStmt,
        "i",
        $userId
    );


    mysqli_stmt_execute(
        $policyStmt
    );


    $policyResult =
        mysqli_stmt_get_result(
            $policyStmt
        );


    $policy =
        mysqli_fetch_assoc(
            $policyResult
        );


    mysqli_stmt_close(
        $policyStmt
    );


    $policyId =
        $policy['id'] ?? 0;

}


/*
|--------------------------------------------------------------------------
| DISPLAY VALUES
|--------------------------------------------------------------------------
*/

$displayStartsAt =
    !empty($policy['starts_at'])
        ? date(
            'Y-m-d\TH:i',
            strtotime($policy['starts_at'])
        )
        : '';

$displayExpiresAt =
    !empty($policy['expires_at'])
        ? date(
            'Y-m-d\TH:i',
            strtotime($policy['expires_at'])
        )
        : '';

$displayTimezone =
    $policy['timezone']
    ?? 'Asia/Colombo';

$displayActive =
    isset($policy['active'])
        ? (int) $policy['active']
        : 1;


/*
|--------------------------------------------------------------------------
| RELOAD SCHEDULE AFTER SAVE
|--------------------------------------------------------------------------
*/

if ($policyId > 0) {

    $scheduleStmt = mysqli_prepare(
        $conn,
        "SELECT
            day_of_week,
            start_time,
            end_time,
            enabled
         FROM admin_access_schedule
         WHERE policy_id = ?
         ORDER BY day_of_week ASC"
    );


    mysqli_stmt_bind_param(
        $scheduleStmt,
        "i",
        $policyId
    );


    mysqli_stmt_execute(
        $scheduleStmt
    );


    $scheduleResult =
        mysqli_stmt_get_result(
            $scheduleStmt
        );


    while (
        $scheduleRow =
        mysqli_fetch_assoc(
            $scheduleResult
        )
    ) {

        $day =
            (int) $scheduleRow['day_of_week'];


        if (
    $day >= 1 &&
    $day <= 7
) {

            $schedule[$day] = [

                'enabled' =>
                    (int)
                    $scheduleRow['enabled'],

                'start_time' =>
                    substr(
                        $scheduleRow['start_time'],
                        0,
                        5
                    ),

                'end_time' =>
                    substr(
                        $scheduleRow['end_time'],
                        0,
                        5
                    )

            ];

        }

    }


    mysqli_stmt_close(
        $scheduleStmt
    );

}


$dayNames = [

    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'

];

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Edit Admin Access - Comic Book World
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="css/admin.css"
    >

</head>


<body>


<?php

include("includes/admin_header.php");

include("includes/admin_sidebar.php");

?>


<div class="admin-content">


    <?php

    include("includes/admin_topbar.php");

    ?>


    <main class="admin-page">


        <!-- PAGE HEADER -->

        <div class="admin-page-header">


            <div>

                <p class="admin-page-eyebrow">
                    SECURITY
                </p>


                <h1 class="admin-page-title">
                    Admin Access
                </h1>


                <p class="admin-page-description">

                    Configure access dates, timezone
                    and permitted working hours.

                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="ManageAdminAccess.php"
                    class="btn btn-outline-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Back

                </a>

            </div>


        </div>


        <?php if ($message !== ""): ?>

            <div
                class="alert alert-<?php echo e($messageType); ?> alert-dismissible fade show"
                role="alert"
            >

                <i class="bi bi-check-circle-fill"></i>

                <?php echo e($message); ?>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <!-- ADMIN INFORMATION -->

        <div class="admin-panel mb-4">


            <div class="admin-panel-header">


                <div>

                    <h2 class="admin-panel-title">

                        <?php

                        echo e(
                            $admin['fullname']
                        );

                        ?>

                    </h2>


                    <p class="admin-panel-subtitle">

                        <?php

                        echo e(
                            $admin['email']
                        );

                        ?>

                    </p>

                </div>


                <span class="badge text-bg-light">

                    <?php

                    echo e(
                        $admin['role_name']
                        ?? 'No Role'
                    );

                    ?>

                </span>


            </div>


        </div>


        <form
            method="POST"
            action=""
        >


            <!-- ACCESS PERIOD -->

            <div class="admin-panel mb-4">


                <div class="admin-panel-header">

                    <div>

                        <h2 class="admin-panel-title">

                            Access Period

                        </h2>


                        <p class="admin-panel-subtitle">

                            Control when this administrator
                            is allowed to access the system.

                        </p>

                    </div>

                </div>


                <div class="p-4">


                    <div class="row g-4">


                        <div class="col-md-6">

                            <label
                                class="form-label fw-semibold"
                            >

                                Start Date & Time

                            </label>


                            <input
                                type="datetime-local"
                                name="starts_at"
                                class="form-control"
                                value="<?php echo e($displayStartsAt); ?>"
                            >


                            <small class="text-muted">

                                Leave empty to allow access
                                immediately.

                            </small>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label fw-semibold"
                            >

                                Expiry Date & Time

                            </label>


                            <input
                                type="datetime-local"
                                name="expires_at"
                                class="form-control"
                                value="<?php echo e($displayExpiresAt); ?>"
                            >


                            <small class="text-muted">

                                Leave empty for no expiry date.

                            </small>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label fw-semibold"
                            >

                                Timezone

                            </label>


                            <select
                                name="timezone"
                                class="form-select"
                            >

                                <?php

                                $timezones = [

                                    'Asia/Colombo'
                                        => 'Asia/Colombo',

                                    'Asia/Kolkata'
                                        => 'Asia/Kolkata',

                                    'Asia/Dubai'
                                        => 'Asia/Dubai',

                                    'Asia/Singapore'
                                        => 'Asia/Singapore',

                                    'Europe/London'
                                        => 'Europe/London',

                                    'America/New_York'
                                        => 'America/New_York',

                                    'America/Los_Angeles'
                                        => 'America/Los_Angeles',

                                    'UTC'
                                        => 'UTC'

                                ];

                                ?>


                                <?php foreach (
                                    $timezones
                                    as $value => $label
                                ): ?>

                                    <option
                                        value="<?php echo e($value); ?>"
                                        <?php

                                        echo
                                            $displayTimezone ===
                                            $value
                                                ? 'selected'
                                                : '';

                                        ?>
                                    >

                                        <?php echo e($label); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="col-md-6">


                            <div
                                class="form-check form-switch mt-4"
                            >

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="active"
                                    id="active"
                                    <?php

                                    echo
                                        $displayActive === 1
                                            ? 'checked'
                                            : '';

                                    ?>
                                >


                                <label
                                    class="form-check-label fw-semibold"
                                    for="active"
                                >

                                    Access Policy Active

                                </label>

                            </div>


                            <small class="text-muted">

                                Disable this to temporarily
                                deactivate the access policy.

                            </small>


                        </div>


                    </div>


                </div>


            </div>


            <!-- WORKING HOURS -->

            <div class="admin-panel mb-4">


                <div class="admin-panel-header">


                    <div>

                        <h2 class="admin-panel-title">

                            Working Hours

                        </h2>


                        <p class="admin-panel-subtitle">

                            Define the times when this
                            administrator may work.

                        </p>

                    </div>


                </div>


                <div class="p-4">


                    <?php foreach (
                        $dayNames
                        as $dayNumber => $dayName
                    ): ?>


                        <div
                            class="row align-items-center g-3 py-3 border-bottom"
                        >


                            <div
                                class="col-md-3"
                            >

                                <div
                                    class="form-check"
                                >

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="days[<?php echo $dayNumber; ?>][enabled]"
                                        id="day_<?php echo $dayNumber; ?>"
                                        <?php

                                        echo
                                            $schedule[$dayNumber]['enabled']
                                            === 1
                                                ? 'checked'
                                                : '';

                                        ?>
                                    >


                                    <label
                                        class="form-check-label fw-semibold"
                                        for="day_<?php echo $dayNumber; ?>"
                                    >

                                        <?php

                                        echo e(
                                            $dayName
                                        );

                                        ?>

                                    </label>

                                </div>

                            </div>


                            <div
                                class="col-md-4"
                            >

                                <label
                                    class="form-label small text-muted"
                                >

                                    Start

                                </label>


                                <input
                                    type="time"
                                    class="form-control"
                                    name="days[<?php echo $dayNumber; ?>][start_time]"
                                    value="<?php echo e($schedule[$dayNumber]['start_time']); ?>"
                                >

                            </div>


                            <div
                                class="col-md-4"
                            >

                                <label
                                    class="form-label small text-muted"
                                >

                                    End

                                </label>


                                <input
                                    type="time"
                                    class="form-control"
                                    name="days[<?php echo $dayNumber; ?>][end_time]"
                                    value="<?php echo e($schedule[$dayNumber]['end_time']); ?>"
                                >

                            </div>


                        </div>


                    <?php endforeach; ?>


                </div>


            </div>


            <!-- ACTIONS -->

            <div
                class="d-flex justify-content-between align-items-center mb-5"
            >


                <a
                    href="ManageAdminAccess.php"
                    class="btn btn-outline-secondary"
                >

                    Cancel

                </a>


                <button
                    type="submit"
                    class="btn btn-dark px-4"
                >

                    <i class="bi bi-check2-circle"></i>

                    Save Access Policy

                </button>


            </div>


        </form>


    </main>


</div>


<?php

include("includes/admin_footer.php");

?>


</body>

</html>