<?php

include("../includes/db.php");
include("../includes/admin_auth.php");
include("../includes/functions.php");

require_admin_permission('faq.view');

$pageTitle = "Manage FAQ";

$result = mysqli_query(
    $conn,
    "SELECT id, question, answer
     FROM faq
     ORDER BY id DESC"
);

if (!$result) {
    die("Could not load FAQ records.");
}

include("includes/admin_header.php");
include("includes/admin_sidebar.php");

?>

<div class="admin-content">

    <?php include("includes/admin_topbar.php"); ?>

    <main class="admin-page">

        <div class="admin-page-header">

            <div>

                <p class="admin-page-eyebrow">
                    STORE SUPPORT
                </p>

                <h1 class="admin-page-title">
                    Manage FAQ
                </h1>

                <p class="admin-page-description">
                    Manage frequently asked questions
                    displayed to customers.
                </p>

            </div>

            <div class="admin-page-actions">

                <a
                    href="AdminPanel.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Dashboard
                </a>

                <a
                    href="AddFAQ.php"
                    class="btn admin-btn-primary"
                >
                    <i class="bi bi-plus-lg"></i>
                    Add FAQ
                </a>

            </div>

        </div>


        <div class="admin-panel">

            <div class="admin-panel-header">

                <div>

                    <h2 class="admin-panel-title">
                        Frequently Asked Questions
                    </h2>

                    <p class="admin-panel-subtitle">
                        Keep customer information
                        accurate and up to date.
                    </p>

                </div>

                <span class="badge text-bg-light">
                    <?php echo mysqli_num_rows($result); ?>
                    Questions
                </span>

            </div>


            <div class="table-responsive">

                <table class="table admin-table align-middle">

                    <thead>

                        <tr>

                            <th style="width: 80px;">
                                ID
                            </th>

                            <th>
                                Question
                            </th>

                            <th>
                                Answer
                            </th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while (
                        $row = mysqli_fetch_assoc($result)
                    ): ?>

                        <tr>

                            <td>
                                #<?php
                                echo (int) $row['id'];
                                ?>
                            </td>

                            <td>

                                <strong>
                                    <?php
                                    echo e(
                                        $row['question']
                                    );
                                    ?>
                                </strong>

                            </td>

                            <td>

                                <span class="text-muted">

                                    <?php
                                    $answer =
                                        trim(
                                            $row['answer']
                                        );

                                    echo e(
                                        strlen($answer) > 100
                                            ? substr(
                                                $answer,
                                                0,
                                                100
                                            ) . "..."
                                            : $answer
                                    );
                                    ?>

                                </span>

                            </td>

                            <td>

                                <div
                                    class="
                                        d-flex
                                        justify-content-end
                                        gap-2
                                    "
                                >

                                    <a
                                        href="EditFAQ.php?id=<?php
                                        echo (int)
                                            $row['id'];
                                        ?>"
                                        class="
                                            btn
                                            btn-sm
                                            btn-outline-primary
                                        "
                                    >
                                        <i
                                            class="
                                                bi bi-pencil
                                            "
                                        ></i>
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="DeleteFAQ.php"
                                        class="d-inline"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to delete this FAQ?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php
                                            echo (int)
                                                $row['id'];
                                            ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php
                                            echo e(
                                                csrf_token()
                                            );
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="
                                                btn
                                                btn-sm
                                                btn-outline-danger
                                            "
                                        >
                                            <i
                                                class="
                                                    bi bi-trash
                                                "
                                            ></i>
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>


                    <?php if (
                        mysqli_num_rows($result) === 0
                    ): ?>

                        <tr>

                            <td
                                colspan="4"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="
                                            bi
                                            bi-question-circle
                                            fs-2
                                        "
                                    ></i>

                                    <p class="mt-2 mb-0">
                                        No FAQ records found.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>

<?php

include("includes/admin_footer.php");

?>