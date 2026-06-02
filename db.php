<?php
// ============================================================
//  Database connection - procedural mysqli (no OOP, no PDO)
// ============================================================
mysqli_report(MYSQLI_REPORT_OFF);   // renvoie false en cas d'erreur (au lieu de planter)
$db = mysqli_connect("localhost", "root", "", "sae23_iot");
if (!$db) {
    die("Erreur de connexion a la base : " . mysqli_connect_error());
}
mysqli_set_charset($db, "utf8mb4");
?>
