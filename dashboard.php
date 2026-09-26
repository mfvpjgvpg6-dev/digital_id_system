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
   CHECK STUDENT LOGIN
   ========================================================= */

if (!isset($_SESSION["student_id"])) {

    header("Location: index.php");

    exit;
}


$student_id = $_SESSION["student_id"];


/* =========================================================
   GET STUDENT INFORMATION
   ========================================================= */

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
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();


/* =========================================================
   STUDENT NOT FOUND
   ========================================================= */

if ($result->num_rows !== 1) {

    session_destroy();

    header("Location: index.php");

    exit;
}


$student = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   STUDENT DATA
   ========================================================= */

$full_name =
    $student["first_name"] . " " .
    $student["last_name"];


$conn->close();

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
        Student Dashboard - Digital ID System
    </title>


    <style>

        /* =================================================
           RESET
           ================================================= */

        * {

            box-sizing: border-box;

            margin: 0;

            padding: 0;

            font-family: Arial, sans-serif;

        }


        /* =================================================
           BODY
           ================================================= */

        body {

            min-height: 100vh;

            background-image:

                linear-gradient(
                    rgba(15, 23, 42, 0.70),
                    rgba(30, 58, 138, 0.78)
                ),

                url("background.jpg");

            background-size: cover;

            background-position: center;

            background-attachment: fixed;

            color: #1e293b;

        }


        /* =================================================
           HEADER
           ================================================= */

        .header {

            background:
                rgba(15, 23, 42, 0.90);

            backdrop-filter: blur(10px);

            -webkit-backdrop-filter: blur(10px);

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            border-bottom:
                1px solid rgba(255,255,255,0.15);

            position: sticky;

            top: 0;

            z-index: 100;

        }


        .header-title {

            font-size: 22px;

            font-weight: bold;

            letter-spacing: 0.3px;

        }


        .header-subtitle {

            font-size: 12px;

            color: #bfdbfe;

            margin-top: 3px;

        }


        .logout {

            background: #ef4444;

            color: white;

            text-decoration: none;

            padding: 10px 17px;

            border-radius: 8px;

            font-weight: bold;

            transition: 0.2s;

        }


        .logout:hover {

            background: #dc2626;

            transform: translateY(-1px);

        }


        /* =================================================
           MAIN CONTAINER
           ================================================= */

        .container {

            width: 92%;

            max-width: 1100px;

            margin: 40px auto 60px;

        }


        /* =================================================
           WELCOME CARD
           ================================================= */

        .welcome {

            background:
                rgba(255,255,255,0.94);

            backdrop-filter: blur(8px);

            -webkit-backdrop-filter: blur(8px);

            padding: 30px;

            border-radius: 20px;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.18);

            margin-bottom: 25px;

            border-left:
                6px solid #2563eb;

        }


        .welcome h1 {

            color: #1e3a8a;

            margin-bottom: 8px;

            font-size: 30px;

        }


        .welcome p {

            color: #64748b;

            font-size: 15px;

        }


        .student-name {

            color: #2563eb;

            font-weight: bold;

        }


        /* =================================================
           STUDENT INFORMATION CARD
           ================================================= */

        .student-card {

            background:
                rgba(255,255,255,0.95);

            backdrop-filter: blur(8px);

            -webkit-backdrop-filter: blur(8px);

            padding: 28px;

            border-radius: 20px;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.16);

            margin-bottom: 25px;

        }


        .student-card h2 {

            color: #1e293b;

            margin-bottom: 20px;

        }


        .info-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

        }


        .info-item {

            background:
                linear-gradient(
                    135deg,
                    #f8fafc,
                    #eff6ff
                );

            padding: 16px;

            border-radius: 10px;

            border-left:
                4px solid #3b82f6;

            transition: 0.2s;

        }


        .info-item:hover {

            transform: translateY(-2px);

            box-shadow:
                0 5px 15px
                rgba(37,99,235,0.10);

        }


        .info-label {

            color: #64748b;

            font-size: 12px;

            margin-bottom: 6px;

            text-transform: uppercase;

            letter-spacing: 0.4px;

        }


        .info-value {

            color: #1e293b;

            font-weight: bold;

            word-break: break-word;

        }


        .active-status {

            color: #16a34a;

        }


        /* =================================================
           SYSTEM MENU
           ================================================= */

        .menu-title {

            color: white;

            margin: 30px 0 15px;

            font-size: 22px;

            text-shadow:
                0 2px 5px rgba(0,0,0,0.35);

        }


        .menu-grid {

            display: grid;

            grid-template-columns: 1fr;

            gap: 20px;

        }


        /* =================================================
           DIGITAL ID CARD
           ================================================= */

        .menu-card {

            background:
                linear-gradient(
                    135deg,
                    rgba(255,255,255,0.98),
                    rgba(239,246,255,0.96)
                );

            padding: 35px;

            border-radius: 20px;

            text-align: center;

            text-decoration: none;

            box-shadow:
                0 15px 40px
                rgba(0,0,0,0.18);

            border:
                1px solid
                rgba(255,255,255,0.7);

            transition:
                transform 0.25s,
                box-shadow 0.25s;

            position: relative;

            overflow: hidden;

        }


        .menu-card::before {

            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            background:
                rgba(59,130,246,0.12);

            border-radius: 50%;

            top: -80px;

            right: -60px;

        }


        .menu-card::after {

            content: "";

            position: absolute;

            width: 130px;

            height: 130px;

            background:
                rgba(14,165,233,0.10);

            border-radius: 50%;

            bottom: -60px;

            left: -40px;

        }


        .menu-card:hover {

            transform:
                translateY(-7px);

            box-shadow:
                0 20px 45px
                rgba(0,0,0,0.25);

        }


        .icon {

            font-size: 60px;

            margin-bottom: 15px;

            position: relative;

            z-index: 2;

        }


        .menu-card h3 {

            margin: 5px 0 10px;

            color: #1e3a8a;

            font-size: 25px;

            position: relative;

            z-index: 2;

        }


        .menu-card p {

            margin: 0 auto;

            max-width: 550px;

            color: #64748b;

            font-size: 14px;

            line-height: 1.6;

            position: relative;

            z-index: 2;

        }


        .view-id {

            display: inline-block;

            margin-top: 20px;

            padding: 11px 22px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

            color: white;

            border-radius: 9px;

            font-weight: bold;

            position: relative;

            z-index: 2;

        }


        /* =================================================
           SYSTEM STATUS
           ================================================= */

        .system-status {

            margin-top: 25px;

            background:
                rgba(220,252,231,0.95);

            color: #166534;

            padding: 15px;

            border-radius: 12px;

            text-align: center;

            font-weight: bold;

            box-shadow:
                0 8px 20px
                rgba(0,0,0,0.10);

        }


        /* =================================================
           FOOTER
           ================================================= */

        .footer {

            text-align: center;

            color: rgba(255,255,255,0.80);

            font-size: 12px;

            margin-top: 25px;

        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 750px) {

            .header {

                padding: 15px;

            }


            .header-title {

                font-size: 17px;

            }


            .header-subtitle {

                font-size: 10px;

            }


            .logout {

                padding: 8px 12px;

                font-size: 13px;

            }


            .container {

                width: 94%;

                margin:
                    25px auto 40px;

            }


            .welcome {

                padding: 23px;

            }


            .welcome h1 {

                font-size: 24px;

            }


            .student-card {

                padding: 20px;

            }


            .info-grid {

                grid-template-columns: 1fr;

            }


            .menu-card {

                padding: 30px 20px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     HEADER
     ===================================================== -->

<div class="header">


    <div>

        <div class="header-title">

            Digital ID System

        </div>


        <div class="header-subtitle">

            Student Portal

        </div>

    </div>


    <a
        href="logout.php"
        class="logout"
    >

        Logout

    </a>


</div>



<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="container">


    <!-- =================================================
         WELCOME
         ================================================= -->

    <div class="welcome">

        <h1>

            Welcome, <?php
            echo htmlspecialchars($student["first_name"]);
            ?>! 👋

        </h1>


        <p>

            Welcome to your
            <span class="student-name">
                Digital ID Portal
            </span>.

            Your student information is shown below.

        </p>

    </div>



    <!-- =================================================
         STUDENT INFORMATION
         ================================================= -->

    <div class="student-card">


        <h2>

            Student Information

        </h2>


        <div class="info-grid">


            <!-- STUDENT NUMBER -->

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


            <!-- FULL NAME -->

            <div class="info-item">

                <div class="info-label">

                    Full Name

                </div>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $full_name
                    );

                    ?>

                </div>

            </div>


            <!-- COURSE -->

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


            <!-- YEAR -->

            <div class="info-item">

                <div class="info-label">

                    Year Level

                </div>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student["year_level"]
                    );

                    ?>

                </div>

            </div>


            <!-- SECTION -->

            <div class="info-item">

                <div class="info-label">

                    Section

                </div>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $student["section"]
                    );

                    ?>

                </div>

            </div>


            <!-- EMAIL -->

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


            <!-- STATUS -->

            <div class="info-item">

                <div class="info-label">

                    Account Status

                </div>

                <div class="info-value active-status">

                    ✓ ACTIVE

                </div>

            </div>


        </div>

    </div>



    <!-- =================================================
         SYSTEM MENU
         ================================================= -->

    <h2 class="menu-title">

        Your Digital ID

    </h2>


    <div class="menu-grid">


        <!-- =================================================
             DIGITAL ID ONLY
             ================================================= -->

        <a
            href="digital_id.php"
            class="menu-card"
        >

            <div class="icon">

                🪪

            </div>


            <h3>

                Digital ID

            </h3>


            <p>

                View your official digital student
                identification card and QR code.

                Use your Digital ID when your student
                identity needs to be verified.

            </p>


            <span class="view-id">

                View Digital ID →

            </span>


        </a>


    </div>



    <!-- =================================================
         SYSTEM STATUS
         ================================================= -->

    <div class="system-status">

        ✓ Your Digital ID System is Ready

    </div>


    <div class="footer">

        Digital Identification System
        © 2026

    </div>


</div>


</body>

</html>