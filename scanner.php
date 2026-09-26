<?php

session_start();

require_once "db_connect.php";


/* =========================================================
   PREVENT BROWSER CACHE AFTER LOGOUT
   ========================================================= */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


/* =========================================================
   CHECK ADMIN LOGIN
   ========================================================= */

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");

    exit;
}


/* =========================================================
   ADMIN INFORMATION
   ========================================================= */

$admin_name =
    $_SESSION["admin_name"] ??
    $_SESSION["admin_username"] ??
    "Administrator";


/* =========================================================
   VARIABLES
   ========================================================= */

$message = "";
$message_type = "";

$student = null;


/* =========================================================
   PROCESS QR SCAN
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_number =
        trim($_POST["student_number"] ?? "");


    /* =====================================================
       CHECK EMPTY SCAN
       ===================================================== */

    if ($student_number === "") {

        $message =
            "Please scan or enter a student number.";

        $message_type = "error";

    } else {


        /* =================================================
           FIND STUDENT
           ================================================= */

        $stmt = $conn->prepare("
            SELECT
                id,
                student_number,
                first_name,
                last_name,
                course,
                year_level,
                section,
                email,
                status
            FROM students
            WHERE student_number = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "s",
            $student_number
        );

        $stmt->execute();

        $result = $stmt->get_result();


        /* =================================================
           STUDENT NOT FOUND
           ================================================= */

        if ($result->num_rows !== 1) {

            $message =
                "Student number not found.";

            $message_type = "error";

        } else {

            $student =
                $result->fetch_assoc();


            /* =============================================
               CHECK STUDENT STATUS
               ============================================= */

            if ((int)$student["status"] !== 1) {

                $message =
                    "This student account is inactive.";

                $message_type = "error";

            } else {


                /* =========================================
                   GET LAST ACCESS RECORD
                   ========================================= */

                $last_stmt = $conn->prepare("
                    SELECT
                        action
                    FROM access_logs
                    WHERE student_id = ?
                    ORDER BY scan_time DESC
                    LIMIT 1
                ");

                $last_stmt->bind_param(
                    "i",
                    $student["id"]
                );

                $last_stmt->execute();

                $last_result =
                    $last_stmt->get_result();


                /* =========================================
                   DETERMINE IN / OUT
                   ========================================= */

                if ($last_result->num_rows === 0) {

                    $action = "IN";

                } else {

                    $last_record =
                        $last_result->fetch_assoc();

                    if (
                        $last_record["action"] === "IN"
                    ) {

                        $action = "OUT";

                    } else {

                        $action = "IN";

                    }

                }


                $last_stmt->close();


                /* =========================================
                   INSERT ACCESS LOG
                   ========================================= */

                $insert_stmt = $conn->prepare("
                    INSERT INTO access_logs
                    (
                        student_id,
                        student_number,
                        action,
                        scan_time
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        NOW()
                    )
                ");

                $insert_stmt->bind_param(
                    "iss",
                    $student["id"],
                    $student["student_number"],
                    $action
                );


                if ($insert_stmt->execute()) {


                    /* =====================================
                       SUCCESS MESSAGE
                       ===================================== */

                    if ($action === "IN") {

                        $message =
                            "Student successfully recorded as IN.";

                        $message_type = "success";

                    } else {

                        $message =
                            "Student successfully recorded as OUT.";

                        $message_type = "out";

                    }


                } else {

                    $message =
                        "Failed to save access record.";

                    $message_type = "error";

                }


                $insert_stmt->close();

            }

        }


        $stmt->close();

    }

}


/* =========================================================
   RECENT ACCESS LOGS
   ========================================================= */

$recent_logs = $conn->query("
    SELECT
        access_logs.id,
        access_logs.student_number,
        access_logs.action,
        access_logs.scan_time,
        students.first_name,
        students.last_name
    FROM access_logs
    LEFT JOIN students
        ON access_logs.student_id = students.id
    ORDER BY access_logs.scan_time DESC
    LIMIT 10
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        http-equiv="Cache-Control"
        content="no-cache, no-store, must-revalidate"
    >

    <meta
        http-equiv="Pragma"
        content="no-cache"
    >

    <meta
        http-equiv="Expires"
        content="0"
    >

    <title>
        QR Scanner - Admin
    </title>


    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }


        body {

            margin: 0;

            min-height: 100vh;

            background: #eef2f7;

        }


        /* =================================================
           HEADER
           ================================================= */

        .header {

            background: #1e3a8a;

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

        }


        .header-title {

            font-size: 22px;

            font-weight: bold;

        }


        .header-right {

            display: flex;

            align-items: center;

            gap: 10px;

            flex-wrap: wrap;

        }


        .admin-name {

            font-size: 14px;

            font-weight: bold;

            margin-right: 5px;

        }


        .header-button {

            background: white;

            color: #1e3a8a;

            text-decoration: none;

            padding: 9px 15px;

            border-radius: 7px;

            font-size: 14px;

            font-weight: bold;

        }


        .header-button:hover {

            background: #e2e8f0;

        }


        .logout-button {

            background: #dc2626;

            color: white;

        }


        .logout-button:hover {

            background: #b91c1c;

        }


        /* =================================================
           CONTAINER
           ================================================= */

        .container {

            width: 95%;

            max-width: 1100px;

            margin: 35px auto;

        }


        /* =================================================
           PAGE TITLE
           ================================================= */

        .page-title {

            margin-bottom: 25px;

        }


        .page-title h1 {

            margin: 0 0 7px;

            color: #1e3a8a;

        }


        .page-title p {

            margin: 0;

            color: #64748b;

        }


        /* =================================================
           SCANNER CARD
           ================================================= */

        .scanner-card {

            background: white;

            padding: 35px;

            border-radius: 15px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,0.08);

            margin-bottom: 25px;

            text-align: center;

        }


        .scanner-icon {

            font-size: 60px;

            margin-bottom: 15px;

        }


        .scanner-card h2 {

            margin: 0 0 10px;

            color: #1e293b;

        }


        .scanner-description {

            color: #64748b;

            margin-bottom: 25px;

        }


        /* =================================================
           FORM
           ================================================= */

        .scan-form {

            width: 100%;

            max-width: 600px;

            margin: auto;

        }


        .scan-input {

            width: 100%;

            padding: 17px;

            border: 2px solid #cbd5e1;

            border-radius: 10px;

            font-size: 18px;

            text-align: center;

            outline: none;

        }


        .scan-input:focus {

            border-color: #2563eb;

        }


        .scan-button {

            width: 100%;

            margin-top: 12px;

            padding: 15px;

            background: #2563eb;

            color: white;

            border: none;

            border-radius: 9px;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

        }


        .scan-button:hover {

            background: #1d4ed8;

        }


        .scanner-note {

            margin-top: 15px;

            color: #94a3b8;

            font-size: 13px;

        }


        /* =================================================
           MESSAGE
           ================================================= */

        .message {

            margin: 20px auto;

            max-width: 600px;

            padding: 15px;

            border-radius: 9px;

            font-weight: bold;

        }


        .message-success {

            background: #dcfce7;

            color: #166534;

        }


        .message-out {

            background: #fee2e2;

            color: #991b1b;

        }


        .message-error {

            background: #fef2f2;

            color: #b91c1c;

        }


        /* =================================================
           STUDENT RESULT
           ================================================= */

        .student-result {

            margin: 25px auto 0;

            max-width: 600px;

            background: #f8fafc;

            border-radius: 12px;

            padding: 25px;

            text-align: left;

            border-left: 5px solid #2563eb;

        }


        .student-result h3 {

            margin-top: 0;

            color: #1e3a8a;

        }


        .student-info {

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 12px;

        }


        .info-item {

            background: white;

            padding: 12px;

            border-radius: 7px;

        }


        .info-label {

            font-size: 12px;

            color: #64748b;

            margin-bottom: 4px;

        }


        .info-value {

            font-weight: bold;

            color: #1e293b;

        }


        /* =================================================
           RECENT SCANS
           ================================================= */

        .logs-card {

            background: white;

            padding: 25px;

            border-radius: 15px;

            box-shadow:
                0 8px 25px
                rgba(0,0,0,0.08);

            overflow-x: auto;

        }


        .logs-card h2 {

            margin-top: 0;

            color: #1e293b;

        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 650px;

        }


        th {

            background: #1e3a8a;

            color: white;

            padding: 12px;

            text-align: left;

            font-size: 13px;

        }


        td {

            padding: 12px;

            border-bottom:
                1px solid #e2e8f0;

            color: #334155;

            font-size: 14px;

        }


        tr:hover {

            background: #f8fafc;

        }


        /* =================================================
           BADGES
           ================================================= */

        .badge {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .badge-in {

            background: #dcfce7;

            color: #166534;

        }


        .badge-out {

            background: #fee2e2;

            color: #991b1b;

        }


        /* =================================================
           EMPTY
           ================================================= */

        .empty {

            text-align: center;

            color: #64748b;

            padding: 30px;

        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 700px) {

            .header {

                padding: 15px;

                flex-direction: column;

                align-items: flex-start;

            }


            .header-right {

                width: 100%;

            }


            .container {

                width: 94%;

                margin: 20px auto;

            }


            .scanner-card {

                padding: 25px 18px;

            }


            .student-info {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
     ===================================================== -->

<div class="header">


    <div class="header-title">

        Digital ID System — QR Scanner

    </div>


    <div class="header-right">


        <div class="admin-name">

            👤

            <?php

            echo htmlspecialchars(
                $admin_name
            );

            ?>

        </div>


        <!-- DASHBOARD -->

        <a
            href="admin_dashboard.php"
            class="header-button"
        >

            Dashboard

        </a>


        <!-- LOGOUT -->

        <a
            href="admin_logout.php"
            class="header-button logout-button"
        >

            Logout

        </a>

    </div>

</div>



<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="container">


    <!-- PAGE TITLE -->

    <div class="page-title">

        <h1>

            QR Code Scanner

        </h1>


        <p>

            Scan a student's Digital ID to record
            their entry or exit.

        </p>

    </div>



    <!-- =================================================
         SCANNER
         ================================================= -->

    <div class="scanner-card">


        <div class="scanner-icon">

            📱

        </div>


        <h2>

            Scan Student QR Code

        </h2>


        <p class="scanner-description">

            Use the USB QR scanner or manually enter
            the student's number.

        </p>


        <?php if ($message !== ""): ?>


            <div
                class="message

                <?php

                if (
                    $message_type === "success"
                ) {

                    echo " message-success";

                } elseif (
                    $message_type === "out"
                ) {

                    echo " message-out";

                } else {

                    echo " message-error";

                }

                ?>"
            >

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>


        <?php endif; ?>



        <!-- =================================================
             SCAN FORM
             ================================================= -->

        <form
            method="POST"
            action="scanner.php"
            class="scan-form"
        >


            <input
                type="text"
                name="student_number"
                id="student_number"
                class="scan-input"
                placeholder="Scan or enter student number"
                autocomplete="off"
                autofocus
            >


            <button
                type="submit"
                class="scan-button"
            >

                Scan / Verify

            </button>


        </form>


        <div class="scanner-note">

            💡 USB QR scanners automatically type the
            student number into the field.

        </div>



        <!-- =================================================
             STUDENT RESULT
             ================================================= -->

        <?php if ($student !== null): ?>


            <div class="student-result">


                <h3>

                    Student Information

                </h3>


                <div class="student-info">


                    <div class="info-item">

                        <div class="info-label">

                            Student Number

                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $student["student_number"]
                            );

                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">

                            Name

                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $student["first_name"]
                                . " "
                                . $student["last_name"]
                            );

                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">

                            Course

                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $student["course"]
                            );

                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">

                            Year / Section

                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $student["year_level"]
                                . " - "
                                . $student["section"]
                            );

                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">

                            Email

                        </div>

                        <div class="info-value">

                            <?php

                            echo htmlspecialchars(
                                $student["email"]
                            );

                            ?>

                        </div>

                    </div>


                    <div class="info-item">

                        <div class="info-label">

                            Account Status

                        </div>

                        <div
                            class="info-value"
                            style="color:#16a34a;"
                        >

                            ✓ ACTIVE

                        </div>

                    </div>


                </div>

            </div>


        <?php endif; ?>


    </div>



    <!-- =================================================
         RECENT SCANS
         ================================================= -->

    <div class="logs-card">


        <h2>

            Recent Scans

        </h2>


        <?php

        if (
            $recent_logs &&
            $recent_logs->num_rows > 0
        ):

        ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            Student Number
                        </th>

                        <th>
                            Student Name
                        </th>

                        <th>
                            Action
                        </th>

                        <th>
                            Scan Time
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                while (
                    $log =
                    $recent_logs->fetch_assoc()
                ):

                ?>


                    <tr>


                        <td>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $log["student_number"]
                                );

                                ?>

                            </strong>

                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                ($log["first_name"] ?? "")
                                . " "
                                . ($log["last_name"] ?? "")
                            );

                            ?>

                        </td>


                        <td>


                            <?php

                            if (
                                $log["action"] === "IN"
                            ):

                            ?>

                                <span
                                    class="badge badge-in"
                                >

                                    ✓ IN

                                </span>

                            <?php

                            else:

                            ?>

                                <span
                                    class="badge badge-out"
                                >

                                    ✓ OUT

                                </span>

                            <?php endif; ?>


                        </td>


                        <td>

                            <?php

                            echo htmlspecialchars(
                                date(
                                    "M d, Y h:i A",
                                    strtotime(
                                        $log["scan_time"]
                                    )
                                )
                            );

                            ?>

                        </td>


                    </tr>


                <?php

                endwhile;

                ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="empty">

                No recent scans found.

            </div>


        <?php endif; ?>


    </div>


</div>



<script>

/*
 * Keep the scanner input focused.
 * Useful for USB QR scanners.
 */

const scannerInput =
    document.getElementById(
        "student_number"
    );

if (scannerInput) {

    scannerInput.focus();


    document.addEventListener(
        "click",
        function () {

            scannerInput.focus();

        }
    );

}

</script>


</body>

</html>


<?php

$conn->close();

?>