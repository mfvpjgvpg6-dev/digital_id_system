<?php
session_start();
require_once "db_connect.php";

/* =========================
   CHECK LOGIN
   ========================= */

if (!isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit;
}

/* =========================
   GET ACCESS LOGS
   ========================= */

$sql = "
    SELECT
        access_logs.id,
        access_logs.student_number,
        access_logs.action,
        access_logs.scan_time,
        students.first_name,
        students.last_name,
        students.course,
        students.year_level,
        students.section
    FROM access_logs
    LEFT JOIN students
        ON access_logs.student_id = students.id
    ORDER BY access_logs.scan_time DESC
";

$result = $conn->query($sql);

/* =========================
   COUNT IN / OUT
   ========================= */

$in_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM access_logs
    WHERE action = 'IN'
");

$in_count = $in_result->fetch_assoc()["total"];

$out_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM access_logs
    WHERE action = 'OUT'
");

$out_count = $out_result->fetch_assoc()["total"];

$total_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM access_logs
");

$total_count = $total_result->fetch_assoc()["total"];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Access Logs - Digital ID System</title>

    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #eef2f7;
            min-height: 100vh;
        }

        /* =========================
           HEADER
           ========================= */

        .header {
            background: #1e3a8a;
            color: white;
            padding: 18px 30px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            font-size: 22px;
            font-weight: bold;
        }

        .back-btn {
            background: white;
            color: #1e3a8a;
            text-decoration: none;

            padding: 9px 16px;
            border-radius: 7px;

            font-weight: bold;
        }

        .back-btn:hover {
            background: #e2e8f0;
        }

        /* =========================
           CONTAINER
           ========================= */

        .container {
            width: 95%;
            max-width: 1200px;

            margin: 35px auto;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            color: #1e3a8a;
            margin-bottom: 5px;
        }

        .page-title p {
            color: #64748b;
        }

        /* =========================
           SUMMARY CARDS
           ========================= */

        .summary {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            padding: 25px;

            border-radius: 12px;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.08);

            text-align: center;
        }

        .summary-title {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .summary-number {
            font-size: 32px;
            font-weight: bold;
            color: #1e3a8a;
        }

        .in-number {
            color: #16a34a;
        }

        .out-number {
            color: #dc2626;
        }

        /* =========================
           LOG TABLE
           ========================= */

        .logs-container {
            background: white;

            border-radius: 12px;

            padding: 25px;

            box-shadow:
                0 5px 15px
                rgba(0,0,0,0.08);

            overflow-x: auto;
        }

        .logs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 20px;
        }

        .logs-header h2 {
            margin: 0;
            color: #1e293b;
        }

        .refresh-btn {
            background: #2563eb;
            color: white;

            border: none;

            padding: 9px 15px;

            border-radius: 7px;

            cursor: pointer;

            font-weight: bold;
        }

        .refresh-btn:hover {
            background: #1d4ed8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
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

        /* =========================
           ACTION BADGES
           ========================= */

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

        /* =========================
           EMPTY
           ========================= */

        .empty {
            text-align: center;

            padding: 40px;

            color: #64748b;
        }

        /* =========================
           MOBILE
           ========================= */

        @media (max-width: 700px) {

            .header {
                padding: 15px;
            }

            .header-title {
                font-size: 17px;
            }

            .container {
                width: 94%;
                margin: 20px auto;
            }

            .summary {
                grid-template-columns: 1fr;
            }

            .logs-container {
                padding: 15px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     HEADER
     ========================= -->

<div class="header">

    <div class="header-title">
        Digital ID System
    </div>

    <a href="dashboard.php"
       class="back-btn">

        Back to Dashboard

    </a>

</div>


<!-- =========================
     MAIN
     ========================= -->

<div class="container">


    <!-- PAGE TITLE -->

    <div class="page-title">

        <h1>
            Student Access Logs
        </h1>

        <p>
            Student Entry and Exit Records
        </p>

    </div>


    <!-- =========================
         SUMMARY
         ========================= -->

    <div class="summary">


        <!-- TOTAL -->

        <div class="summary-card">

            <div class="summary-title">
                TOTAL SCANS
            </div>

            <div class="summary-number">

                <?php
                echo $total_count;
                ?>

            </div>

        </div>


        <!-- IN -->

        <div class="summary-card">

            <div class="summary-title">
                TOTAL IN
            </div>

            <div class="summary-number in-number">

                <?php
                echo $in_count;
                ?>

            </div>

        </div>


        <!-- OUT -->

        <div class="summary-card">

            <div class="summary-title">
                TOTAL OUT
            </div>

            <div class="summary-number out-number">

                <?php
                echo $out_count;
                ?>

            </div>

        </div>

    </div>


    <!-- =========================
         LOGS
         ========================= -->

    <div class="logs-container">


        <div class="logs-header">

            <h2>
                Access History
            </h2>

            <button
                class="refresh-btn"
                onclick="location.reload();">

                Refresh

            </button>

        </div>


        <?php if ($result && $result->num_rows > 0): ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Student Number
                        </th>

                        <th>
                            Student Name
                        </th>

                        <th>
                            Course
                        </th>

                        <th>
                            Year
                        </th>

                        <th>
                            Section
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

                $counter = 1;

                while ($log = $result->fetch_assoc()):

                    $student_name =
                        $log["first_name"] . " "
                        . $log["last_name"];

                ?>


                    <tr>


                        <!-- NUMBER -->

                        <td>

                            <?php
                            echo $counter++;
                            ?>

                        </td>


                        <!-- STUDENT NUMBER -->

                        <td>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $log["student_number"]
                                );
                                ?>

                            </strong>

                        </td>


                        <!-- NAME -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $student_name
                            );
                            ?>

                        </td>


                        <!-- COURSE -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $log["course"] ?? "-"
                            );
                            ?>

                        </td>


                        <!-- YEAR -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $log["year_level"] ?? "-"
                            );
                            ?>

                        </td>


                        <!-- SECTION -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $log["section"] ?? "-"
                            );
                            ?>

                        </td>


                        <!-- ACTION -->

                        <td>


                            <?php if ($log["action"] === "IN"): ?>

                                <span class="badge badge-in">

                                    ✓ IN

                                </span>

                            <?php else: ?>

                                <span class="badge badge-out">

                                    ✓ OUT

                                </span>

                            <?php endif; ?>


                        </td>


                        <!-- TIME -->

                        <td>

                            <?php

                            echo date(
                                "M d, Y h:i A",
                                strtotime(
                                    $log["scan_time"]
                                )
                            );

                            ?>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>

            </table>


        <?php else: ?>


            <div class="empty">

                No access records found.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>
