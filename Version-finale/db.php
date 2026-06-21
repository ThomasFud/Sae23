<?php

// Turn mysqli error reporting off: functions return false on
// failure instead of throwing, so a query error never crashes
// the whole page (important for the site and the cron script).
mysqli_report(MYSQLI_REPORT_OFF);

// Open the connection to the local MySQL server (XAMPP):
// host localhost, user root, empty password, database sae23_iot.
$db = mysqli_connect("localhost", "root", "", "sae23_iot");

// If the connection could not be opened, stop with a clear message.
if (!$db) {
    die("Erreur de connexion a la base : " . mysqli_connect_error());
}

// Use UTF-8 so accented characters are read/written correctly.
mysqli_set_charset($db, "utf8mb4");
?>
