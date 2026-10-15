<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ManageUsers.php");
    exit();
}

if (
    !isset($_POST['csrf_token'])
    || !verify_csrf_token($_POST['csrf_token'])
) {

    die("Invalid security token.");
}

if (
    !isset($_POST['id'])
    || !filter_var($_POST['id'], FILTER_VALIDATE_INT)
) {

    die("Invalid user ID.");
}

$id = (int) $_POST['id'];

// Prevent the currently logged-in admin from deleting their own account
if ($id === (int) $_SESSION['user_id']) {

    die("You cannot delete your own admin account.");
}

$sql = "DELETE FROM users WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: ManageUsers.php");
exit();

?>