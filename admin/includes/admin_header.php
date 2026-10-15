<?php

$currentPage = basename($_SERVER['PHP_SELF']);

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
        <?php echo isset($pageTitle)
            ? e($pageTitle)
            : 'Admin Dashboard'; ?>
        - Comic Book World
    </title>


    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="css/admin.css"
    >

    <script src="https://cdn.jsdelivr.net/npm/chart.js">
        
    </script>

</head>


<body class="admin-body">


<div class="admin-layout">