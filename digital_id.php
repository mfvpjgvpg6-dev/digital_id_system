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


/* =========================================================
   QR DATA
   ========================================================= */

$qr_data = $student["student_number"];


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
        Digital ID - <?php echo htmlspecialchars($full_name); ?>
    </title>


    <!-- =====================================================
         QR CODE LIBRARY
         ===================================================== -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js">
    </script>


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
           SAME THEME AS DASHBOARD
           ================================================= */

        body {

            min-height: 100vh;

            background:

                radial-gradient(
                    circle at 15% 20%,
                    rgba(56,189,248,0.20),
                    transparent 28%
                ),

                radial-gradient(
                    circle at 85% 75%,
                    rgba(34,197,94,0.12),
                    transparent 30%
                ),

                linear-gradient(
                    135deg,
                    #0f172a,
                    #172554 45%,
                    #1e3a8a
                );

            background-attachment: fixed;

            color: #1e293b;

        }


        /* =================================================
           HEADER
           ================================================= */

        .header {

            background:
                rgba(15,23,42,0.92);

            backdrop-filter: blur(12px);

            -webkit-backdrop-filter: blur(12px);

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            border-bottom:
                1px solid rgba(255,255,255,0.14);

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

            color: #93c5fd;

            margin-top: 3px;

        }


        /* =================================================
           BACK BUTTON
           ================================================= */

        .back {

            background:
                rgba(255,255,255,0.95);

            color: #1e3a8a;

            text-decoration: none;

            padding: 10px 17px;

            border-radius: 9px;

            font-weight: bold;

            transition: 0.2s;

            border:
                1px solid rgba(255,255,255,0.5);

        }


        .back:hover {

            background: #dbeafe;

            transform: translateY(-1px);

        }


        /* =================================================
           MAIN CONTAINER
           ================================================= */

        .container {

            width: 92%;

            max-width: 950px;

            margin: 40px auto 60px;

        }


        /* =================================================
           PAGE TITLE
           ================================================= */

        .page-title {

            text-align: center;

            margin-bottom: 28px;

            color: white;

            text-shadow:
                0 2px 8px rgba(0,0,0,0.35);

        }


        .page-title h1 {

            font-size: 30px;

            margin-bottom: 7px;

            color: #dbeafe;

        }


        .page-title p {

            color: #bfdbfe;

            font-size: 14px;

        }


        /* =================================================
           DIGITAL ID CARD
           ================================================= */

        .id-card {

            max-width: 720px;

            margin: auto;

            background:
                rgba(255,255,255,0.96);

            backdrop-filter: blur(12px);

            -webkit-backdrop-filter: blur(12px);

            border-radius: 22px;

            overflow: hidden;

            box-shadow:
                0 25px 60px
                rgba(0,0,0,0.35);

            border:
                1px solid rgba(255,255,255,0.65);

        }


        /* =================================================
           ID HEADER
           ================================================= */

        .id-header {

            background:

                linear-gradient(
                    135deg,
                    #1e3a8a,
                    #2563eb,
                    #0ea5e9
                );

            color: white;

            padding: 25px 20px;

            text-align: center;

            position: relative;

            overflow: hidden;

        }


        .id-header::before {

            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            border-radius: 50%;

            background:
                rgba(255,255,255,0.10);

            top: -110px;

            right: -50px;

        }


        .id-header::after {

            content: "";

            position: absolute;

            width: 130px;

            height: 130px;

            border-radius: 50%;

            background:
                rgba(34,211,238,0.14);

            bottom: -80px;

            left: -35px;

        }


        .school-name {

            font-size: 23px;

            font-weight: bold;

            position: relative;

            z-index: 2;

        }


        .id-title {

            margin-top: 7px;

            font-size: 13px;

            color: #dbeafe;

            letter-spacing: 0.5px;

            position: relative;

            z-index: 2;

        }


        /* =================================================
           ID BODY
           ================================================= */

        .id-body {

            padding: 32px;

            background:

                linear-gradient(
                    135deg,
                    rgba(255,255,255,0.98),
                    rgba(239,246,255,0.96)
                );

        }


        /* =================================================
           PROFILE SECTION
           ================================================= */

        .profile-section {

            display: flex;

            gap: 25px;

            align-items: center;

            margin-bottom: 30px;

        }


        /* =================================================
           PROFILE PHOTO
           ================================================= */

        .profile-photo {

            width: 120px;

            height: 140px;

            background:

                linear-gradient(
                    145deg,
                    #dbeafe,
                    #e0f2fe
                );

            border:
                2px solid #93c5fd;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            text-align: center;

            color: #64748b;

            font-size: 13px;

            flex-shrink: 0;

            box-shadow:
                0 6px 15px
                rgba(37,99,235,0.10);

        }


        /* =================================================
           STUDENT NAME
           ================================================= */

        .student-name {

            font-size: 25px;

            font-weight: bold;

            color: #172554;

            margin-bottom: 8px;

        }


        .student-number {

            color: #2563eb;

            font-weight: bold;

            font-size: 15px;

        }


        /* =================================================
           INFORMATION
           ================================================= */

        .info-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 15px;

        }


        .info-item {

            padding: 15px 16px;

            background:

                linear-gradient(
                    135deg,
                    #f8fafc,
                    #eff6ff
                );

            border-radius: 10px;

            border-left:
                4px solid #3b82f6;

            box-shadow:
                0 4px 12px
                rgba(15,23,42,0.05);

            transition: 0.2s;

        }


        .info-item:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 7px 18px
                rgba(37,99,235,0.10);

        }


        .info-label {

            color: #64748b;

            font-size: 11px;

            margin-bottom: 6px;

            text-transform: uppercase;

            letter-spacing: 0.4px;

        }


        .info-value {

            color: #1e293b;

            font-weight: bold;

            word-break: break-word;

        }


        /* =================================================
           VERIFIED STATUS
           ================================================= */

        .status {

            margin-top: 25px;

            padding: 14px;

            text-align: center;

            border-radius: 10px;

            background:

                linear-gradient(
                    135deg,
                    #dcfce7,
                    #d1fae5
                );

            color: #166534;

            font-weight: bold;

            border:
                1px solid #bbf7d0;

            box-shadow:
                0 5px 15px
                rgba(22,101,52,0.08);

        }


        /* =================================================
           QR SECTION
           ================================================= */

        .qr-section {

            margin-top: 30px;

            padding-top: 27px;

            border-top:
                1px solid #dbeafe;

            text-align: center;

        }


        .qr-title {

            font-size: 18px;

            font-weight: bold;

            color: #1e3a8a;

            margin-bottom: 5px;

        }


        .qr-subtitle {

            color: #64748b;

            font-size: 13px;

        }


        /* =================================================
           QR BOX
           ================================================= */

        .qr-box {

            width: 205px;

            height: 205px;

            margin: 20px auto;

            background: white;

            border:
                2px solid #bfdbfe;

            border-radius: 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            box-shadow:
                0 8px 25px
                rgba(30,64,175,0.12);

        }


        #qrcode {

            display: flex;

            align-items: center;

            justify-content: center;

        }


        #qrcode img {

            width: 175px;

            height: 175px;

        }


        .qr-student-number {

            color: #1e3a8a;

            font-weight: bold;

            margin-top: 8px;

            font-size: 14px;

        }


        .qr-text {

            color: #64748b;

            font-size: 13px;

            line-height: 1.6;

            max-width: 500px;

            margin: 10px auto 0;

        }


        /* =================================================
           FOOTER
           ================================================= */

        .id-footer {

            background:

                linear-gradient(
                    135deg,
                    #0f172a,
                    #172554
                );

            padding: 17px;

            text-align: center;

            color: #bfdbfe;

            font-size: 12px;

            border-top:
                1px solid rgba(255,255,255,0.08);

        }


        /* =================================================
           EXTRA COLOR ACCENTS
           ================================================= */

        .accent-line {

            width: 80px;

            height: 4px;

            margin: 0 auto 20px;

            border-radius: 10px;

            background:

                linear-gradient(
                    90deg,
                    #2563eb,
                    #06b6d4,
                    #22c55e
                );

        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 700px) {

            .header {

                padding: 15px 18px;

            }


            .header-title {

                font-size: 18px;

            }


            .header-subtitle {

                font-size: 10px;

            }


            .back {

                padding: 8px 12px;

                font-size: 13px;

            }


            .container {

                width: 94%;

                margin:
                    25px auto 40px;

            }


            .page-title h1 {

                font-size: 25px;

            }


            .id-body {

                padding: 23px 18px;

            }


            .profile-section {

                flex-direction: column;

                text-align: center;

            }


            .profile-photo {

                width: 110px;

                height: 130px;

            }


            .student-name {

                font-size: 21px;

            }


            .info-grid {

                grid-template-columns: 1fr;

            }


            .school-name {

                font-size: 18px;

            }


            .id-title {

                font-size: 11px;

            }


            .qr-box {

                width: 195px;

                height: 195px;

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
        href="dashboard.php"
        class="back"
    >

        ← Back to Dashboard

    </a>


</div>



<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="container">


    <!-- =================================================
         PAGE TITLE
         ================================================= -->

    <div class="page-title">


        <h1>

            Student Digital ID

        </h1>


        <p>

            Student Access Verification

        </p>


    </div>



    <!-- =================================================
         DIGITAL ID CARD
         ================================================= -->

    <div class="id-card">


        <!-- =================================================
             ID HEADER
             ================================================= -->

        <div class="id-header">


            <div class="school-name">

                OUR LADY OF LOURDES COLLEGE

            </div>


            <div class="id-title">

                DIGITAL STUDENT IDENTIFICATION CARD

            </div>


        </div>



        <!-- =================================================
             ID BODY
             ================================================= -->

        <div class="id-body">


            <!-- =================================================
                 PROFILE
                 ================================================= -->

            <div class="profile-section">


                <div class="profile-photo">

                    STUDENT
                    <br>
                    PHOTO

                </div>


                <div>


                    <div class="student-name">

                        <?php

                        echo htmlspecialchars(
                            $full_name
                        );

                        ?>

                    </div>


                    <div class="student-number">

                        <?php

                        echo htmlspecialchars(
                            $student["student_number"]
                        );

                        ?>

                    </div>


                </div>


            </div>



            <!-- =================================================
                 INFORMATION
                 ================================================= -->

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



                <!-- YEAR LEVEL -->

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


            </div>



            <!-- =================================================
                 VERIFIED STATUS
                 ================================================= -->

            <div class="status">

                ✓ STUDENT ACCOUNT VERIFIED

            </div>



            <!-- =================================================
                 QR CODE
                 ================================================= -->

            <div class="qr-section">


                <div class="accent-line"></div>


                <div class="qr-title">

                    Student Verification QR Code

                </div>


                <div class="qr-subtitle">

                    Scan this code to verify student access

                </div>


                <div class="qr-box">

                    <div id="qrcode"></div>

                </div>


                <div class="qr-student-number">

                    Student No:

                    <?php

                    echo htmlspecialchars(
                        $student["student_number"]
                    );

                    ?>

                </div>


                <div class="qr-text">

                    This QR code contains the student's
                    student number and is used for
                    access verification through the
                    school's Digital ID System.

                </div>


            </div>


        </div>



        <!-- =================================================
             FOOTER
             ================================================= -->

        <div class="id-footer">

            Digital Identification System
            |
            Student Access Verification
            |
            © 2026

        </div>


    </div>


</div>



<!-- =====================================================
     GENERATE QR CODE
     ===================================================== -->

<script>

const studentNumber =
    <?php echo json_encode($qr_data); ?>;


new QRCode(

    document.getElementById("qrcode"),

    {

        text: studentNumber,

        width: 175,

        height: 175,

        colorDark: "#172554",

        colorLight: "#ffffff",

        correctLevel: QRCode.CorrectLevel.H

    }

);

</script>


</body>

</html>