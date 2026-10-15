<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('products.delete');
require_admin_access(false);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ManageProducts.php");
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

    die("Invalid product ID.");
}

$id = (int) $_POST['id'];

$sql = "DELETE FROM products WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

header("Location: ManageProducts.php");
exit();

?>