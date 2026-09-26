<?php

require_once "db_connect.php";

/* =========================
   ADMIN ACCOUNT DETAILS
   ========================= */

$username = "admin";
$password = "Admin123!";
$full_name = "System Administrator";


/* =========================
   HASH PASSWORD
   ========================= */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* =========================
   CHECK IF ADMIN EXISTS
   ========================= */

$check = $conn->prepare("
    SELECT id
    FROM admins
    WHERE username = ?
    LIMIT 1
");

$check->bind_param(
    "s",
    $username
);

$check->execute();

$result = $check->get_result();


/* =========================
   CREATE ADMIN
   ========================= */

if ($result->num_rows > 0) {

    echo "Admin account already exists.";

} else {

    $stmt = $conn->prepare("
        INSERT INTO admins
        (
            username,
            password,
            full_name,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            1
        )
    ");

    $stmt->bind_param(
        "sss",
        $username,
        $hashed_password,
        $full_name
    );

    if ($stmt->execute()) {

        echo "Admin account created successfully.<br><br>";

        echo "Username: admin<br>";

        echo "Password: Admin123!";

    } else {

        echo "Failed to create admin account.";

    }

    $stmt->close();
}


$check->close();

$conn->close();

?>