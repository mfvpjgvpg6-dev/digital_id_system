<?php

session_start();

/* =========================
   REMOVE ADMIN SESSION
   ========================= */

unset($_SESSION["admin_id"]);
unset($_SESSION["admin_username"]);
unset($_SESSION["admin_name"]);


/* =========================
   REDIRECT TO ADMIN LOGIN
   ========================= */

header("Location: admin_login.php");

exit;

?>