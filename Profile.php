<?php
session_start();
include("includes/db.php");

// ==================================================
// CHECK ACCOUNT STATUS
// ==================================================
//
// A blocked customer must not be allowed to
// continue using the authenticated account.
//

if (isset($_SESSION['user_id'])) {

    $loggedInUserId = (int) $_SESSION['user_id'];

    $statusStmt = mysqli_prepare(
        $conn,
        "SELECT account_status
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $statusStmt,
        "i",
        $loggedInUserId
    );

    mysqli_stmt_execute($statusStmt);

    $statusResult =
        mysqli_stmt_get_result($statusStmt);

    $account = mysqli_fetch_assoc(
        $statusResult
    );

    mysqli_stmt_close($statusStmt);


    // ----------------------------------------------
    // BLOCKED ACCOUNT
    // ----------------------------------------------

    if (
        !$account
        || $account['account_status'] === 'blocked'
    ) {

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();


        header(
            "Location: Login.html?error=blocked"
        );

        exit;
    }
}

if(!isset($_SESSION['user_id']))
{
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE id='$user_id'";
$result = mysqli_query($conn,$sql);

$user = mysqli_fetch_assoc($result);
?>
<div class="profile-container">

    <h2>Welcome, <?php echo $user['fullname']; ?>!</h2>

    <div class="profile-box">

        <p>👤 <strong>Full Name:</strong> <?php echo $user['fullname']; ?></p>
        <p>📧 <strong>Email:</strong> <?php echo $user['email']; ?></p>
        <p>📅 <strong>Member Since:</strong> <?php echo $user['created_at']; ?></p>

    </div>

    <br>

    <div class="profile-links">

        <a href="Cart.php">
            <button>My Cart</button>
        </a>

        <br><br>

        <a href="PurchaseHistory.php">
            <button>Purchase History</button>
        </a>

        <br><br>

        <a href="OrderTracking.php">
            <button>Order Tracking</button>
        </a>

        <br><br>

        <a href="Logout.php">
            <button>Logout</button>
        </a>

    </div>

</div>