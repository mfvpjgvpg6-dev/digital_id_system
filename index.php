<?php

session_start();
require_once "db_connect.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_number = trim($_POST["student_number"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($student_number) || empty($password)) {

        $message = "Please enter your student number and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, student_number, first_name, last_name, course,
                    year_level, section, email, password, status
             FROM students
             WHERE student_number = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $student_number);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $student = $result->fetch_assoc();

            if ($student["status"] != 1) {

                $message = "Your account is inactive.";

            } elseif (password_verify($password, $student["password"])) {

                $_SESSION["student_id"] = $student["id"];
                $_SESSION["student_number"] = $student["student_number"];
                $_SESSION["first_name"] = $student["first_name"];
                $_SESSION["last_name"] = $student["last_name"];
                $_SESSION["course"] = $student["course"];
                $_SESSION["year_level"] = $student["year_level"];
                $_SESSION["section"] = $student["section"];
                $_SESSION["email"] = $student["email"];

                header("Location: dashboard.php");
                exit;

            } else {

                $message = "Invalid student number or password.";

            }

        } else {

            $message = "Invalid student number or password.";

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

            padding: 25px;

            background-image:

                linear-gradient(
                    rgba(15, 23, 42, 0.72),
                    rgba(30, 58, 138, 0.82)
                ),

                url("background.jpg");

            background-size: cover;

            background-position: center;

            background-attachment: fixed;

        }


        /* =================================================
           LOGIN WRAPPER
           ================================================= */

        .login-wrapper {

            width: 100%;

            max-width: 430px;

        }


        /* =================================================
           BRAND
           ================================================= */

        .brand {

            text-align: center;

            color: white;

            margin-bottom: 20px;

        }


        .brand-icon {

            width: 68px;

            height: 68px;

            margin: 0 auto 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #38bdf8
                );

            box-shadow:
                0 10px 30px
                rgba(0,0,0,0.25);

            font-size: 34px;

        }


        .brand h1 {

            font-size: 27px;

            margin-bottom: 5px;

            letter-spacing: 0.3px;

        }


        .brand p {

            color: #bfdbfe;

            font-size: 13px;

        }


        /* =================================================
           LOGIN CARD
           ================================================= */

        .login-box {

            width: 100%;

            background:
                rgba(255,255,255,0.94);

            backdrop-filter: blur(12px);

            -webkit-backdrop-filter: blur(12px);

            padding: 32px;

            border-radius: 22px;

            box-shadow:
                0 20px 50px
                rgba(0,0,0,0.28);

            border:
                1px solid
                rgba(255,255,255,0.55);

            position: relative;

            overflow: hidden;

        }


        /* decorative blue circles */

        .login-box::before {

            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            border-radius: 50%;

            background:
                rgba(59,130,246,0.10);

            top: -90px;

            right: -70px;

        }


        .login-box::after {

            content: "";

            position: absolute;

            width: 140px;

            height: 140px;

            border-radius: 50%;

            background:
                rgba(14,165,233,0.08);

            bottom: -70px;

            left: -50px;

        }


        .login-content {

            position: relative;

            z-index: 2;

        }


        /* =================================================
           TITLE
           ================================================= */

        .login-title {

            text-align: center;

            color: #1e3a8a;

            font-size: 24px;

            margin-bottom: 6px;

        }


        .login-subtitle {

            text-align: center;

            color: #64748b;

            font-size: 14px;

            margin-bottom: 25px;

        }


        /* =================================================
           MESSAGE
           ================================================= */

        .message {

            background:
                #fef2f2;

            border:
                1px solid #fecaca;

            color: #b91c1c;

            padding: 12px;

            border-radius: 9px;

            text-align: center;

            font-size: 13px;

            font-weight: bold;

            margin-bottom: 18px;

        }


        /* =================================================
           FORM
           ================================================= */

        .form-group {

            margin-bottom: 17px;

        }


        label {

            display: block;

            margin-bottom: 7px;

            color: #334155;

            font-size: 13px;

            font-weight: bold;

        }


        .input-wrapper {

            position: relative;

        }


        .input-icon {

            position: absolute;

            left: 14px;

            top: 50%;

            transform:
                translateY(-50%);

            font-size: 17px;

            color: #64748b;

            pointer-events: none;

        }


        input {

            width: 100%;

            padding: 14px 14px 14px 43px;

            border:
                1px solid #cbd5e1;

            border-radius: 10px;

            background:
                rgba(248,250,252,0.95);

            color: #1e293b;

            font-size: 14px;

            outline: none;

            transition: 0.2s;

        }


        input:focus {

            border-color: #2563eb;

            background: white;

            box-shadow:
                0 0 0 3px
                rgba(37,99,235,0.12);

        }


        input::placeholder {

            color: #94a3b8;

        }


        /* =================================================
           LOGIN BUTTON
           ================================================= */

        button {

            width: 100%;

            padding: 14px;

            margin-top: 5px;

            border: none;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            box-shadow:
                0 8px 18px
                rgba(37,99,235,0.25);

            transition: 0.2s;

        }


        button:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 24px
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

            padding-top: 18px;

            border-top:
                1px solid #e2e8f0;

            color: #64748b;

            font-size: 13px;

        }


        .register a {

            color: #2563eb;

            text-decoration: none;

            font-weight: bold;

        }


        .register a:hover {

            color: #1d4ed8;

            text-decoration: underline;

        }


        /* =================================================
           FOOTER
           ================================================= */

        .footer {

            text-align: center;

            color:
                rgba(255,255,255,0.78);

            font-size: 11px;

            margin-top: 18px;

        }


        /* =================================================
           MOBILE
           ================================================= */

        @media (max-width: 500px) {

            body {

                padding: 18px;

            }


            .login-box {

                padding: 25px 20px;

                border-radius: 18px;

            }


            .brand h1 {

                font-size: 23px;

            }


            .brand-icon {

                width: 58px;

                height: 58px;

                font-size: 29px;

            }


            .login-title {

                font-size: 21px;

            }

        }

    </style>

</head>


<body>


<div class="login-wrapper">


    <!-- =================================================
         BRAND
         ================================================= -->

    <div class="brand">

        <div class="brand-icon">

            🪪

        </div>


        <h1>

            Digital ID System

        </h1>


        <p>

            Our Lady of Lourdes College

        </p>

    </div>



    <!-- =================================================
         LOGIN CARD
         ================================================= -->

    <div class="login-box">


        <div class="login-content">


            <h2 class="login-title">

                Student Login

            </h2>


            <p class="login-subtitle">

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

                    <label for="student_number">

                        Student Number

                    </label>


                    <div class="input-wrapper">

                        <span class="input-icon">

                            🎓

                        </span>


                        <input
                            type="text"
                            id="student_number"
                            name="student_number"
                            placeholder="Enter student number"
                            autocomplete="username"
                            required
                        >

                    </div>

                </div>



                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">

                        Password

                    </label>


                    <div class="input-wrapper">

                        <span class="input-icon">

                            🔒

                        </span>


                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter password"
                            autocomplete="current-password"
                            required
                        >

                    </div>

                </div>



                <!-- LOGIN BUTTON -->

                <button type="submit">

                    Login →

                </button>


            </form>



            <!-- REGISTER -->

            <div class="register">

                Don't have an account?

                <a href="register.php">

                    Register

                </a>

            </div>


        </div>

    </div>



    <!-- FOOTER -->

    <div class="footer">

        Digital Identification System
        © 2026

    </div>


</div>


</body>

</html>
