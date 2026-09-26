<?php

session_start();

require_once "db_connect.php";


/* =========================
   CHECK IF ALREADY LOGGED IN
   ========================= */

if (isset($_SESSION["admin_id"])) {

    header("Location: admin_dashboard.php");

    exit;
}


/* =========================
   VARIABLES
   ========================= */

$message = "";


/* =========================
   LOGIN PROCESS
   ========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";


    /* =========================
       CHECK EMPTY
       ========================= */

    if ($username === "" || $password === "") {

        $message = "Please enter your username and password.";

    } else {


        /* =========================
           FIND ADMIN
           ========================= */

        $stmt = $conn->prepare("
            SELECT
                id,
                username,
                password,
                full_name,
                status
            FROM admins
            WHERE username = ?
            LIMIT 1
        ");


        if ($stmt) {

            $stmt->bind_param("s", $username);

            $stmt->execute();

            $result = $stmt->get_result();


            /* =========================
               CHECK ADMIN ACCOUNT
               ========================= */

            if ($result->num_rows === 1) {

                $admin = $result->fetch_assoc();


                /* =========================
                   CHECK STATUS
                   ========================= */

                if ((int)$admin["status"] !== 1) {

                    $message = "Admin account is inactive.";


                /* =========================
                   CHECK PASSWORD
                   ========================= */

                } elseif (
                    password_verify(
                        $password,
                        $admin["password"]
                    )
                ) {


                    /* =========================
                       CREATE ADMIN SESSION
                       ========================= */

                    $_SESSION["admin_id"] =
                        $admin["id"];

                    $_SESSION["admin_username"] =
                        $admin["username"];

                    $_SESSION["admin_name"] =
                        $admin["full_name"];


                    /* =========================
                       REDIRECT
                       ========================= */

                    header(
                        "Location: admin_dashboard.php"
                    );

                    exit;


                } else {

                    $message =
                        "Invalid username or password.";
                }


            } else {

                $message =
                    "Invalid username or password.";
            }


            $stmt->close();


        } else {

            $message =
                "Database error. Please check the admins table.";
        }
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


<title>
Admin Login - Digital ID System
</title>


<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}


body {

    margin: 0;

    min-height: 100vh;

    display: flex;

    justify-content: center;

    align-items: center;

    background: #eef2f7;
}


.login-box {

    width: 400px;

    max-width: 90%;

    background: white;

    padding: 35px;

    border-radius: 15px;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,0.12);
}


.icon {

    text-align: center;

    font-size: 45px;

    margin-bottom: 10px;
}


h1 {

    text-align: center;

    color: #1e3a8a;

    margin-bottom: 5px;
}


.subtitle {

    text-align: center;

    color: #64748b;

    margin-bottom: 25px;
}


label {

    display: block;

    margin-bottom: 7px;

    font-weight: bold;

    color: #334155;
}


input {

    width: 100%;

    padding: 12px;

    margin-bottom: 18px;

    border: 1px solid #cbd5e1;

    border-radius: 7px;

    font-size: 15px;
}


input:focus {

    outline: none;

    border-color: #2563eb;
}


button {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 7px;

    background: #2563eb;

    color: white;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}


button:hover {

    background: #1d4ed8;
}


.message {

    text-align: center;

    margin-bottom: 18px;

    padding: 10px;

    border-radius: 7px;

    background: #fee2e2;

    color: #991b1b;

    font-weight: bold;
}


.student-login {

    text-align: center;

    margin-top: 20px;

    color: #64748b;

    font-size: 14px;
}


.student-login a {

    color: #2563eb;

    text-decoration: none;

    font-weight: bold;
}

</style>

</head>


<body>


<div class="login-box">


    <div class="icon">
        👨‍💼
    </div>


    <h1>
        Admin Login
    </h1>


    <p class="subtitle">
        Digital ID System
    </p>


    <?php if ($message !== ""): ?>

        <div class="message">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <form
        method="POST"
        action="admin_login.php"
    >


        <label>
            Username
        </label>


        <input
            type="text"
            name="username"
            placeholder="Enter admin username"
            autocomplete="username"
            required
        >


        <label>
            Password
        </label>


        <input
            type="password"
            name="password"
            placeholder="Enter admin password"
            autocomplete="current-password"
            required
        >


        <button type="submit">
            Login as Admin
        </button>


    </form>


    <div class="student-login">

        Student?

        <a href="index.php">
            Student Login
        </a>

    </div>


</div>


</body>

</html>
