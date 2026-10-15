<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('promotions.delete');
require_admin_access(false);

/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST REQUESTS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ManagePromotions.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| CSRF PROTECTION
|--------------------------------------------------------------------------
*/

if (
    !isset($_POST['csrf_token'])
    || !verify_csrf_token($_POST['csrf_token'])
) {

    $_SESSION['error_message'] =
        "Invalid security token.";

    header("Location: ManagePromotions.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| GET PROMOTION ID
|--------------------------------------------------------------------------
*/

$id = isset($_POST['id'])
    ? (int)$_POST['id']
    : 0;


if ($id <= 0) {

    $_SESSION['error_message'] =
        "Invalid promotion.";

    header("Location: ManagePromotions.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| CHECK THAT PROMOTION EXISTS
|--------------------------------------------------------------------------
*/

$checkStmt = mysqli_prepare(
    $conn,
    "SELECT id, code
     FROM promotions
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $checkStmt,
    "i",
    $id
);

mysqli_stmt_execute($checkStmt);

$result = mysqli_stmt_get_result($checkStmt);

$promotion = mysqli_fetch_assoc($result);

mysqli_stmt_close($checkStmt);


if (!$promotion) {

    $_SESSION['error_message'] =
        "Promotion not found.";

    header("Location: ManagePromotions.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| DELETE PROMOTION
|--------------------------------------------------------------------------
*/

$deleteStmt = mysqli_prepare(
    $conn,
    "DELETE FROM promotions
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $deleteStmt,
    "i",
    $id
);


if (mysqli_stmt_execute($deleteStmt)) {

    $_SESSION['success_message'] =
        "Promotion "
        . $promotion['code']
        . " was deleted successfully.";

} else {

    $_SESSION['error_message'] =
        "Unable to delete the promotion.";
}


mysqli_stmt_close($deleteStmt);


/*
|--------------------------------------------------------------------------
| RETURN TO PROMOTIONS
|--------------------------------------------------------------------------
*/

header("Location: ManagePromotions.php");
exit();

?>