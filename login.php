<?php

session_start();

require_once "db_connect.php";

$message = "";


/* =========================================================
   LOGIN PROCESS
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_number = trim($_POST["student_number"]);
    $password = $_POST["password"];


    if (empty($student_number) || empty($password)) {

        $message =
            "Please enter your student number and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                id,
                student_number,
                first_name,
                last_name,
                course,
                year_level,
                section,
                email,
                password,
                status
             FROM students
             WHERE student_number = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "s",
            $student_number
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows == 1) {

            $student = $result->fetch_assoc();


            /* =================================================
               CHECK ACCOUNT STATUS
               ================================================= */

            if ($student["status"] != 1) {

                $message =
                    "Your account is inactive.";


            /* =================================================
               VERIFY PASSWORD
               ================================================= */

            } elseif (
                password_verify(
                    $password,
                    $student["password"]
                )
            ) {

                $_SESSION["student_id"] =
                    $student["id"];

                $_SESSION["student_number"] =
                    $student["student_number"];

                $_SESSION["first_name"] =
                    $student["first_name"];

                $_SESSION["last_name"] =
                    $student["last_name"];

                $_SESSION["course"] =
                    $student["course"];

                $_SESSION["year_level"] =
                    $student["year_level"];

                $_SESSION["section"] =
                    $student["section"];

                $_SESSION["email"] =
                    $student["email"];


                header(
                    "Location: dashboard.php"
                );

                exit;


            } else {

                $message =
                    "Invalid student number or password.";

            }


        } else {

            $message =
                "Invalid student number or password.";

        }


        $stmt->close();
    }
}


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
        Login - Digital ID System
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
           SAME THEME AS DASHBOARD
           ================================================= */

        body {

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 20px;


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
           BACKGROUND LIGHT EFFECT
           ================================================= */

        body::before {

            content: "";

            position: fixed;

            width: 420px;

            height: 420px;

            background:
                rgba(59,130,246,0.18);

            border-radius: 50%;

            filter: blur(80px);

            top: -150px;

            left: -120px;

            pointer-events: none;

        }


        body::after {

            content: "";

            position: fixed;

            width: 350px;

            height: 350px;

            background:
                rgba(14,165,233,0.16);

            border-radius: 50%;

            filter: blur(80px);

            bottom: -130px;

            right: -100px;

            pointer-events: none;

        }


        /* =================================================
           LOGIN CONTAINER
           ================================================= */

        .login-box {

            width: 100%;

            max-width: 430px;

            position: relative;

            z-index: 2;


            background:
                rgba(255,255,255,0.93);


            backdrop-filter: blur(14px);

            -webkit-backdrop-filter: blur(14px);


            padding: 38px;


            border-radius: 22px;


            border:
                1px solid
                rgba(255,255,255,0.75);


            box-shadow:

                0 25px 60px
                rgba(0,0,0,0.30);


            overflow: hidden;

        }


        /* =================================================
           BLUE TOP ACCENT
           ================================================= */

        .login-box::before {

            content: "";

            position: absolute;

            top: 0;

            left: 0;

            width: 100%;

            height: 6px;


            background:
                linear-gradient(
                    90deg,
                    #2563eb,
                    #38bdf8,
                    #1d4ed8
                );

        }


        /* =================================================
           LOGO / ICON
           ================================================= */

        .logo {

            width: 70px;

            height: 70px;

            margin: 0 auto 18px;


            display: flex;

            align-items: center;

            justify-content: center;


            border-radius: 18px;


            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );


            color: white;

            font-size: 34px;


            box-shadow:
                0 10px 25px
                rgba(37,99,235,0.30);

        }


        /* =================================================
           TITLE
           ================================================= */

        h1 {

            text-align: center;

            color: #1e3a8a;

            margin-bottom: 7px;

            font-size: 28px;

            font-weight: bold;

        }


        .subtitle {

            text-align: center;

            color: #64748b;

            margin-bottom: 28px;

            font-size: 14px;

        }


        /* =================================================
           MESSAGE
           ================================================= */

        .message {

            text-align: center;

            margin-bottom: 18px;

            padding: 11px 13px;

            border-radius: 9px;

            background:
                #fee2e2;

            color:
                #b91c1c;

            font-size: 13px;

            font-weight: bold;

            border:
                1px solid #fecaca;

        }


        /* =================================================
           FORM
           ================================================= */

        .form-group {

            margin-bottom: 18px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            font-weight: bold;

            color: #334155;

            font-size: 13px;

        }


        /* =================================================
           INPUT
           ================================================= */

        input {

            width: 100%;

            padding: 13px 14px;

            border:
                1px solid #cbd5e1;

            border-radius: 9px;

            font-size: 14px;

            background:
                rgba(248,250,252,0.95);

            color: #1e293b;

            transition: 0.2s;

        }


        input::placeholder {

            color: #94a3b8;

        }


        input:focus {

            outline: none;

            border-color: #3b82f6;

            background: white;

            box-shadow:
                0 0 0 3px
                rgba(59,130,246,0.12);

        }


        /* =================================================
           LOGIN BUTTON
           ================================================= */

        button {

            width: 100%;

            padding: 13px;

            margin-top: 5px;


            border: none;

            border-radius: 9px;


            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );


            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;


            box-shadow:
                0 8px 18px
                rgba(37,99,235,0.25);


            transition:
                transform 0.2s,
                box-shadow 0.2s;

        }


        button:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 25px
                rgba(37,99,235,0.32);

        }


        button:active {

            transform:
                translateY(0);

        }


        /* =================================================
           REGISTER
           ================================================= */

        .register {

            text-align: center;

            margin-top: 22px;

            color: #64748b;

            font-size: 13px;

        }


        .register a {

            color: #2563eb;

            text-decoration: none;

            font-weight: bold;

        }


        .register a:hover {

            text-decoration: underline;

        }


        /* =================================================
           SYSTEM FOOTER
           ================================================= */

        .system-footer {

            text-align: center;

            margin-top: 25px;

            color: #94a3b8;

            font-size: 11px;

            line-height: 1.5;

        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 500px) {

            body {

                padding: 15px;

            }


            .login-box {

                padding: 30px 22px;

                border-radius: 18px;

            }


            h1 {

                font-size: 24px;

            }


            .logo {

                width: 62px;

                height: 62px;

                font-size: 29px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     LOGIN CARD
     ===================================================== -->

<div class="login-box">


    <!-- ICON -->

    <div class="logo">

        🪪

    </div>


    <!-- TITLE -->

    <h1>

        Digital ID System

    </h1>


    <p class="subtitle">

        Student Access Verification

    </p>


    <!-- ERROR MESSAGE -->

    <?php if (!empty($message)): ?>

        <div class="message">

            <?php

            echo htmlspecialchars($message);

            ?>

        </div>

    <?php endif; ?>


    <!-- LOGIN FORM -->

    <form method="POST">


        <!-- STUDENT NUMBER -->

        <div class="form-group">

            <label>

                Student Number

            </label>


            <input

                type="text"

                name="student_number"

                placeholder="Enter student number"

                required

                autocomplete="username"

            >

        </div>


        <!-- PASSWORD -->

        <div class="form-group">

            <label>

                Password

            </label>


            <input

                type="password"

                name="password"

                placeholder="Enter password"

                required

                autocomplete="current-password"

            >

        </div>


        <!-- LOGIN -->

        <button type="submit">

            Login

        </button>


    </form>


    <!-- REGISTER -->

    <div class="register">

        Don't have an account?

        <a href="register.php">

            Register

        </a>

    </div>


    <!-- FOOTER -->

    <div class="system-footer">

        Digital Identification System

        <br>

        Student Access Verification © 2026

    </div>


</div>


</body>

</html>
