<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('faq.create');
require_admin_access(false);


/*
|--------------------------------------------------------------------------
| PAGE VALUES
|--------------------------------------------------------------------------
*/

$question = "";
$answer = "";

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token'])
        || !verify_csrf_token($_POST['csrf_token'])
    ) {

        $message = "Invalid security token.";
        $messageType = "danger";

    } else {

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

        if ($question === '') {

            $message =
                "Please enter the FAQ question.";

            $messageType = "danger";

        } elseif (strlen($question) > 255) {

            $message =
                "The question cannot be longer than 255 characters.";

            $messageType = "danger";

        } elseif ($answer === '') {

            $message =
                "Please enter the FAQ answer.";

            $messageType = "danger";

        }


        /*
        |--------------------------------------------------------------------------
        | INSERT FAQ
        |--------------------------------------------------------------------------
        */

        if ($message === "") {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO faq
                (
                    question,
                    answer
                )
                VALUES (?, ?)"
            );


            if (!$stmt) {

                $message =
                    "Unable to prepare the FAQ.";

                $messageType = "danger";

            } else {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ss",
                    $question,
                    $answer
                );


                if (
                    mysqli_stmt_execute(
                        $stmt
                    )
                ) {

                    mysqli_stmt_close(
                        $stmt
                    );

                    header(
                        "Location: ManageFAQ.php"
                    );

                    exit();

                } else {

                    $message =
                        "Unable to create the FAQ.";

                    $messageType = "danger";

                    mysqli_stmt_close(
                        $stmt
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
        Add FAQ - Comic Book World
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


        <!-- ==================================================
             PAGE HEADER
        ================================================== -->

        <div class="admin-page-header">


            <div>

                <p class="admin-page-eyebrow">
                    CUSTOMER SUPPORT
                </p>


                <h1 class="admin-page-title">
                    Add FAQ
                </h1>


                <p class="admin-page-description">
                    Create a frequently asked question
                    and answer for customers.
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


        <!-- ==================================================
             ERROR / SUCCESS MESSAGE
        ================================================== -->

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


        <!-- ==================================================
             FAQ FORM PANEL
        ================================================== -->

        <div class="admin-panel">


            <div class="admin-panel-header">


                <div>

                    <h2 class="admin-panel-title">
                        FAQ Details
                    </h2>


                    <p class="admin-panel-subtitle">
                        Add a question and its answer
                        for customers.
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
                                placeholder="e.g. Do you deliver across Sri Lanka?"
                                required
                            >


                            <div class="form-text">

                                Enter the question customers
                                are most likely to ask.

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
                                placeholder="Write a clear and helpful answer for customers..."
                                required
                            ><?php

                            echo e(
                                $answer
                            );

                            ?></textarea>


                            <div class="form-text">

                                Keep the answer clear,
                                useful and easy to understand.

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

                            <i class="bi bi-check-lg"></i>

                            Create FAQ

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