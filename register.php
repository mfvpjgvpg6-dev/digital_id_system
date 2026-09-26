<?php
require_once "db_connect.php";

$message = "";
$message_type = "";

// Variables para hindi mawala ang inputs kapag may error
$student_number = "";
$first_name = "";
$last_name = "";
$course = "";
$year_level = "";
$section = "";
$email = "";
$password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_number = trim($_POST["student_number"] ?? "");
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year_level = trim($_POST["year_level"] ?? "");
    $section = trim($_POST["section"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    /* =========================
       REQUIRED FIELD VALIDATION
       ========================= */

    if (
        empty($student_number) ||
        empty($first_name) ||
        empty($last_name) ||
        empty($course) ||
        empty($year_level) ||
        empty($section) ||
        empty($email) ||
        empty($password)
    ) {

        $message = "Please complete all fields.";
        $message_type = "error";

    /* =========================
       STUDENT NUMBER VALIDATION
       Numbers and hyphen only
       ========================= */

    } elseif (!preg_match("/^[0-9-]+$/", $student_number)) {

        $message = "Student number must contain numbers only, with hyphens if needed.";
        $message_type = "error";

    /* =========================
       FIRST NAME VALIDATION
       ========================= */

    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $first_name)) {

        $message = "First name must contain letters only.";
        $message_type = "error";

    /* =========================
       LAST NAME VALIDATION
       ========================= */

    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $last_name)) {

        $message = "Last name must contain letters only.";
        $message_type = "error";

    /* =========================
       COURSE VALIDATION
       ========================= */

    } elseif (!preg_match("/^[a-zA-Z0-9\s-]+$/", $course)) {

        $message = "Course contains invalid characters.";
        $message_type = "error";

    /* =========================
       YEAR LEVEL VALIDATION
       ========================= */

    } elseif (!in_array($year_level, [
        "1st Year",
        "2nd Year",
        "3rd Year",
        "4th Year"
    ])) {

        $message = "Please select a valid year level.";
        $message_type = "error";

    /* =========================
       SECTION VALIDATION
       ========================= */

    } elseif (!preg_match("/^[a-zA-Z0-9\s-]+$/", $section)) {

        $message = "Section contains invalid characters.";
        $message_type = "error";

    /* =========================
       EMAIL VALIDATION
       ========================= */

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    /* =========================
       PASSWORD VALIDATION
       ========================= */

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } else {

        /* =========================
           CHECK DUPLICATE STUDENT NUMBER
           ========================= */

        $check = $conn->prepare(
            "SELECT id FROM students WHERE student_number = ? LIMIT 1"
        );

        $check->bind_param("s", $student_number);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Student number already exists.";
            $message_type = "error";

        } else {

            /* =========================
               CHECK DUPLICATE EMAIL
               ========================= */

            $email_check = $conn->prepare(
                "SELECT id FROM students WHERE email = ? LIMIT 1"
            );

            $email_check->bind_param("s", $email);
            $email_check->execute();

            $email_result = $email_check->get_result();

            if ($email_result->num_rows > 0) {

                $message = "Email address is already registered.";
                $message_type = "error";

            } else {

                /* =========================
                   HASH PASSWORD
                   ========================= */

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $status = 1;

                /* =========================
                   INSERT STUDENT
                   ========================= */

                $stmt = $conn->prepare(
                    "INSERT INTO students
                    (
                        student_number,
                        first_name,
                        last_name,
                        course,
                        year_level,
                        section,
                        email,
                        password,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssssssssi",
                    $student_number,
                    $first_name,
                    $last_name,
                    $course,
                    $year_level,
                    $section,
                    $email,
                    $hashed_password,
                    $status
                );

                if ($stmt->execute()) {

                    $message = "Registration successful! You can now login.";
                    $message_type = "success";

                    // Clear form after successful registration
                    $student_number = "";
                    $first_name = "";
                    $last_name = "";
                    $course = "";
                    $year_level = "";
                    $section = "";
                    $email = "";
                    $password = "";

                } else {

                    $message = "Registration failed. Please try again.";
                    $message_type = "error";
                }

                $stmt->close();
            }

            $email_check->close();
        }

        $check->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Digital ID System</title>

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
            background: #f1f5f9;
            padding: 20px;
        }

        .register-box {
            width: 450px;
            max-width: 100%;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
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
            margin-bottom: 6px;
            font-weight: bold;
            color: #334155;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
            background: white;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            padding: 12px;
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
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 7px;
            font-weight: bold;
        }

        .success {
            color: #166534;
            background: #dcfce7;
        }

        .error {
            color: #991b1b;
            background: #fee2e2;
        }

        .login {
            text-align: center;
            margin-top: 18px;
            color: #64748b;
        }

        .login a {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
        }

        .login a:hover {
            text-decoration: underline;
        }

    </style>

</head>

<body>

<div class="register-box">

    <h1>Student Registration</h1>

    <p class="subtitle">
        Digital ID System
    </p>

    <?php if (!empty($message)): ?>

        <div class="message <?php echo htmlspecialchars($message_type); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <!-- STUDENT NUMBER -->

        <label>Student Number</label>

        <input
            type="text"
            name="student_number"
            placeholder="Enter student number"
            value="<?php echo htmlspecialchars($student_number); ?>"
            required
        >


        <!-- FIRST NAME -->

        <label>First Name</label>

        <input
            type="text"
            name="first_name"
            placeholder="Enter first name"
            value="<?php echo htmlspecialchars($first_name); ?>"
            required
        >


        <!-- LAST NAME -->

        <label>Last Name</label>

        <input
            type="text"
            name="last_name"
            placeholder="Enter last name"
            value="<?php echo htmlspecialchars($last_name); ?>"
            required
        >


        <!-- COURSE -->

        <label>Course</label>

        <input
            type="text"
            name="course"
            placeholder="Example: BSIT"
            value="<?php echo htmlspecialchars($course); ?>"
            required
        >


        <!-- YEAR LEVEL -->

        <label>Year Level</label>

        <select name="year_level" required>

            <option value="">Select Year Level</option>

            <option value="1st Year"
                <?php echo ($year_level == "1st Year") ? "selected" : ""; ?>>
                1st Year
            </option>

            <option value="2nd Year"
                <?php echo ($year_level == "2nd Year") ? "selected" : ""; ?>>
                2nd Year
            </option>

            <option value="3rd Year"
                <?php echo ($year_level == "3rd Year") ? "selected" : ""; ?>>
                3rd Year
            </option>

            <option value="4th Year"
                <?php echo ($year_level == "4th Year") ? "selected" : ""; ?>>
                4th Year
            </option>

        </select>


        <!-- SECTION -->

        <label>Section</label>

        <input
            type="text"
            name="section"
            placeholder="Example: A"
            value="<?php echo htmlspecialchars($section); ?>"
            required
        >


        <!-- EMAIL -->

        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter email"
            value="<?php echo htmlspecialchars($email); ?>"
            required
        >


        <!-- PASSWORD -->

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Create password"
            minlength="6"
            required
        >


        <!-- REGISTER BUTTON -->

        <button type="submit">
            Register Student
        </button>

    </form>


    <div class="login">

        Already have an account?

        <a href="index.php">
            Login
        </a>

    </div>

</div>

</body>

</html>