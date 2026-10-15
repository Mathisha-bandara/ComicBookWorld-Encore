<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('orders.update');
require_admin_access(false);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ManageOrders.php");
    exit();
}


// Check CSRF token
if (
    !isset($_POST['csrf_token'])
    || !verify_csrf_token($_POST['csrf_token'])
) {

    die("Invalid security token.");
}


// Validate order ID
if (
    !isset($_POST['id'])
    || !filter_var($_POST['id'], FILTER_VALIDATE_INT)
) {

    die("Invalid order ID.");
}

$id = (int) $_POST['id'];


// Allowed order statuses
$allowedStatuses = [
    "Pending",
    "Processing",
    "Shipped",
    "Delivered",
    "Cancelled"
];


// Get submitted status
$status = $_POST['status'] ?? "";


// Make sure status is valid
if (!in_array($status, $allowedStatuses, true)) {

    die("Invalid order status.");
}


// Update order
$sql = "UPDATE orders
        SET status = ?
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $status,
    $id
);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    header("Location: ManageOrders.php");
    exit();

} else {

    mysqli_stmt_close($stmt);

    die("Unable to update order.");
}

?>