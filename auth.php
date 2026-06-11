<?php
// ============================================================
//  Session helpers - procedural (no OOP)
//  Included on pages that need login / role checking.
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Return true if someone is logged in
function est_connecte() {
    return isset($_SESSION['role']);
}

// Force a given role; otherwise redirect to the login page
function exiger_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: login.php");
        exit;
    }
}
?>
