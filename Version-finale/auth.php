<?php

// Start the session only if it is not already running
// (avoids the "session already started" notice).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Return true when a user is currently logged in
// (a role is stored in the session).
function est_connecte() {
    return isset($_SESSION['role']);
}

// Require a specific role to access the page.
// If the user is not logged in, or does not have the expected
// role, redirect to the login page and stop the script.
function exiger_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: login.php");
        exit;
    }
}
?>
