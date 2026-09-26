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
   TOTAL STUDENTS
   ========================================================= */

$sql_total = "
    SELECT COUNT(*) AS total
    FROM students
";

$result_total = $conn->query($sql_total);

$total_students = 0;

if ($result_total) {

    $row = $result_total->fetch_assoc();

    $total_students = $row["total"] ?? 0;
}


/* =========================================================
   ACTIVE STUDENTS
   ========================================================= */

$sql_active = "
    SELECT COUNT(*) AS total
    FROM students
    WHERE status = 1
";

$result_active = $conn->query($sql_active);

$active_students = 0;

if ($result_active) {

    $row = $result_active->fetch_assoc();

    $active_students = $row["total"] ?? 0;
}


/* =========================================================
   TODAY'S IN
   ========================================================= */

$sql_in = "
    SELECT COUNT(*) AS total
    FROM access_logs
    WHERE action = 'IN'
    AND DATE(scan_time) = CURDATE()
";

$result_in = $conn->query($sql_in);

$today_in = 0;

if ($result_in) {

    $row = $result_in->fetch_assoc();

    $today_in = $row["total"] ?? 0;
}


/* =========================================================
   TODAY'S OUT
   ========================================================= */

$sql_out = "
    SELECT COUNT(*) AS total
    FROM access_logs
    WHERE action = 'OUT'
    AND DATE(scan_time) = CURDATE()
";

$result_out = $conn->query($sql_out);

$today_out = 0;

if ($result_out) {

    $row = $result_out->fetch_assoc();

    $today_out = $row["total"] ?? 0;
}


/* =========================================================
   RECENT ACCESS LOGS
   ========================================================= */

$sql_logs = "
    SELECT
        id,
        student_number,
        action,
        scan_time
    FROM access_logs
    ORDER BY scan_time DESC
    LIMIT 10
";

$result_logs = $conn->query($sql_logs);

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
        Admin Dashboard - Digital ID System
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

            gap: 10px;

            align-items: center;

            flex-wrap: wrap;

            justify-content: flex-end;
        }


        .admin-name {

            color: white;

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

            font-weight: bold;

            font-size: 14px;

            transition: 0.2s;
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

            width: 100%;

            max-width: 1200px;

            margin: 35px auto;

            padding: 20px;
        }


        /* =================================================
           PAGE TITLE
           ================================================= */

        .page-title {

            margin-bottom: 30px;
        }


        .page-title h1 {

            color: #1e3a8a;

            margin-bottom: 5px;
        }


        .page-title p {

            color: #64748b;

            margin: 0;
        }


        /* =================================================
           STATISTICS
           ================================================= */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .stat-card {

            background: white;

            padding: 25px;

            border-radius: 14px;

            box-shadow:
                0 8px 20px
                rgba(0,0,0,0.08);
        }


        .stat-label {

            color: #64748b;

            font-size: 14px;

            margin-bottom: 10px;
        }


        .stat-number {

            color: #1e3a8a;

            font-size: 32px;

            font-weight: bold;
        }


        .stat-description {

            color: #94a3b8;

            font-size: 12px;

            margin-top: 7px;
        }


        /* =================================================
           CONTENT CARD
           ================================================= */

        .card {

            background: white;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 8px 20px
                rgba(0,0,0,0.08);

            overflow-x: auto;
        }


        .card-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

            gap: 15px;
        }


        .card-title {

            color: #1e293b;

            font-size: 20px;

            font-weight: bold;
        }


        /* =================================================
           TABLE
           ================================================= */

        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 650px;
        }


        th {

            background: #1e3a8a;

            color: white;

            padding: 13px;

            text-align: left;

            font-size: 13px;
        }


        td {

            padding: 13px;

            border-bottom:
                1px solid #e2e8f0;

            color: #334155;

            font-size: 14px;
        }


        tr:hover {

            background: #f8fafc;
        }


        /* =================================================
           ACTION BADGES
           ================================================= */

        .action {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;
        }


        .action-in {

            background: #dcfce7;

            color: #166534;
        }


        .action-out {

            background: #fee2e2;

            color: #991b1b;
        }


        /* =================================================
           EMPTY
           ================================================= */

        .empty {

            text-align: center;

            padding: 30px;

            color: #64748b;
        }


        /* =================================================
           FOOTER
           ================================================= */

        .footer {

            text-align: center;

            color: #94a3b8;

            font-size: 12px;

            margin-top: 30px;

            padding-bottom: 25px;
        }


        /* =================================================
           TABLET
           ================================================= */

        @media (max-width: 900px) {

            .stats {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 600px) {

            .header {

                padding: 15px;

                flex-direction: column;

                align-items: flex-start;

                gap: 12px;
            }


            .header-title {

                font-size: 18px;
            }


            .header-right {

                width: 100%;

                justify-content: flex-start;
            }


            .container {

                margin: 20px auto;

                padding: 12px;
            }


            .stats {

                grid-template-columns: 1fr;
            }


            .card {

                padding: 15px;
            }


            .card-header {

                align-items: flex-start;

                flex-direction: column;
            }


            .admin-name {

                width: 100%;
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

        Digital ID System
        — Admin Dashboard

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


        <!-- QR SCANNER -->

        <a
            href="scanner.php"
            class="header-button"
        >

            QR Scanner

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

            Dashboard Overview

        </h1>


        <p>

            Student Access Verification System

        </p>

    </div>



    <!-- =================================================
         STATISTICS
         ================================================= -->

    <div class="stats">


        <!-- TOTAL STUDENTS -->

        <div class="stat-card">

            <div class="stat-label">

                Total Students

            </div>


            <div class="stat-number">

                <?php

                echo htmlspecialchars(
                    $total_students
                );

                ?>

            </div>


            <div class="stat-description">

                Registered students

            </div>

        </div>



        <!-- ACTIVE STUDENTS -->

        <div class="stat-card">

            <div class="stat-label">

                Active Students

            </div>


            <div class="stat-number">

                <?php

                echo htmlspecialchars(
                    $active_students
                );

                ?>

            </div>


            <div class="stat-description">

                Currently active accounts

            </div>

        </div>



        <!-- TODAY'S IN -->

        <div class="stat-card">

            <div class="stat-label">

                Today's IN

            </div>


            <div class="stat-number">

                <?php

                echo htmlspecialchars(
                    $today_in
                );

                ?>

            </div>


            <div class="stat-description">

                Student entry records

            </div>

        </div>



        <!-- TODAY'S OUT -->

        <div class="stat-card">

            <div class="stat-label">

                Today's OUT

            </div>


            <div class="stat-number">

                <?php

                echo htmlspecialchars(
                    $today_out
                );

                ?>

            </div>


            <div class="stat-description">

                Student exit records

            </div>

        </div>


    </div>



    <!-- =================================================
         RECENT ACCESS LOGS
         ================================================= -->

    <div class="card">


        <div class="card-header">


            <div class="card-title">

                Recent Access Logs

            </div>


        </div>



        <?php

        if (
            $result_logs &&
            $result_logs->num_rows > 0
        ):

        ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>


                        <th>
                            Student Number
                        </th>


                        <th>
                            Action
                        </th>


                        <th>
                            Date & Time
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php

                while (
                    $row =
                    $result_logs->fetch_assoc()
                ):

                ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["id"]
                            );

                            ?>

                        </td>


                        <!-- STUDENT NUMBER -->

                        <td>

                            <strong>

                                <?php

                                echo htmlspecialchars(
                                    $row["student_number"]
                                );

                                ?>

                            </strong>

                        </td>


                        <!-- ACTION -->

                        <td>


                            <?php

                            if (
                                $row["action"] === "IN"
                            ):

                            ?>


                                <span
                                    class="action action-in"
                                >

                                    ✓ IN

                                </span>


                            <?php

                            else:

                            ?>


                                <span
                                    class="action action-out"
                                >

                                    ↗ OUT

                                </span>


                            <?php

                            endif;

                            ?>


                        </td>


                        <!-- DATE & TIME -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                date(
                                    "F d, Y h:i A",
                                    strtotime(
                                        $row["scan_time"]
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


        <?php

        else:

        ?>


            <div class="empty">

                No access logs found.

            </div>


        <?php

        endif;

        ?>


    </div>



    <!-- =================================================
         FOOTER
         ================================================= -->

    <div class="footer">

        Digital Identification System |
        Student Access Verification

    </div>


</div>


</body>

</html>


<?php

$conn->close();

?>