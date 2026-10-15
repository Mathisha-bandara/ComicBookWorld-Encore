<?php

require_once("../includes/admin_auth.php");
require_once("../includes/db.php");
require_once("../includes/functions.php");


/*
|--------------------------------------------------------------------------
| FAQ UPDATE PERMISSION
|--------------------------------------------------------------------------
*/

require_admin_permission('faq.update');
require_admin_access(false);

/*
|--------------------------------------------------------------------------
| GET FAQ ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0)
{
    header("Location: ManageFAQ.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| LOAD FAQ
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        question,
        answer
     FROM faq
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$faq = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$faq)
{
    header("Location: ManageFAQ.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| FORM VALUES
|--------------------------------------------------------------------------
*/

$question = $faq['question'];
$answer   = $faq['answer'];

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| UPDATE FAQ
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    )
    {
        $message =
            "Invalid security token.";

        $messageType = "danger";
    }
    else
    {

        $question = trim(
            $_POST['question'] ?? ''
        );

        $answer = trim(
            $_POST['answer'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($question === '')
        {
            $message =
                "Please enter the FAQ question.";

            $messageType = "danger";
        }
        elseif (strlen($question) > 255)
        {
            $message =
                "The question cannot be longer than 255 characters.";

            $messageType = "danger";
        }
        elseif ($answer === '')
        {
            $message =
                "Please enter the FAQ answer.";

            $messageType = "danger";
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        if ($message === "")
        {

            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE faq
                 SET
                    question = ?,
                    answer = ?
                 WHERE id = ?"
            );


            if (!$updateStmt)
            {
                $message =
                    "Unable to prepare the FAQ update.";

                $messageType = "danger";
            }
            else
            {

                mysqli_stmt_bind_param(
                    $updateStmt,
                    "ssi",
                    $question,
                    $answer,
                    $id
                );


                if (
                    mysqli_stmt_execute(
                        $updateStmt
                    )
                )
                {

                    mysqli_stmt_close(
                        $updateStmt
                    );

                    header(
                        "Location: ManageFAQ.php"
                    );

                    exit();
                }
                else
                {

                    $message =
                        "Unable to update the FAQ.";

                    $messageType = "danger";

                    mysqli_stmt_close(
                        $updateStmt
                    );
                }
            }
        }
    }
}

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
        Edit FAQ - Comic Book World
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <!-- Admin CSS -->

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
                    CUSTOMER SUPPORT
                </p>


                <h1 class="admin-page-title">
                    Edit FAQ
                </h1>


                <p class="admin-page-description">
                    Update the selected frequently
                    asked question and answer.
                </p>

            </div>


            <div class="admin-page-actions">

                <a
                    href="ManageFAQ.php"
                    class="btn btn-outline-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Back to FAQ

                </a>

            </div>

        </div>


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div
                class="alert alert-<?php
                    echo e($messageType);
                ?>"
            >

                <?php

                echo e($message);

                ?>

            </div>

        <?php endif; ?>


        <!-- FAQ FORM -->

        <div class="admin-panel">


            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        FAQ Details
                    </h2>


                    <p class="admin-panel-subtitle">
                        Modify the question and answer
                        shown to customers.
                    </p>

                </div>

            </div>


            <div class="p-4">


                <form
                    method="POST"
                    action=""
                >


                    <!-- CSRF -->

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php
                            echo e(
                                csrf_token()
                            );
                        ?>"
                    >


                    <div class="row g-4">


                        <!-- QUESTION -->

                        <div class="col-12">

                            <label
                                for="question"
                                class="form-label"
                            >

                                Question

                            </label>


                            <input
                                type="text"
                                id="question"
                                name="question"
                                class="form-control"
                                maxlength="255"
                                value="<?php
                                    echo e(
                                        $question
                                    );
                                ?>"
                                required
                            >


                            <div class="form-text">

                                Update the question
                                customers may ask.

                            </div>

                        </div>


                        <!-- ANSWER -->

                        <div class="col-12">

                            <label
                                for="answer"
                                class="form-label"
                            >

                                Answer

                            </label>


                            <textarea
                                id="answer"
                                name="answer"
                                class="form-control"
                                rows="7"
                                required
                            ><?php

                            echo e(
                                $answer
                            );

                            ?></textarea>


                            <div class="form-text">

                                Keep the answer clear
                                and useful for customers.

                            </div>

                        </div>


                    </div>


                    <!-- BUTTONS -->

                    <div
                        class="d-flex gap-2 mt-4"
                    >

                        <button
                            type="submit"
                            class="btn admin-btn-primary"
                        >

                            <i class="bi bi-save"></i>

                            Save Changes

                        </button>


                        <a
                            href="ManageFAQ.php"
                            class="btn btn-outline-secondary"
                        >

                            Cancel

                        </a>

                    </div>


                </form>


            </div>

        </div>


    </main>


</div>


<?php

include("includes/admin_footer.php");

?>


</body>

</html>