<?php
// Destroy the session and go back to the home page
session_start();
session_destroy();
header("Location: index.php");
exit;
?>
