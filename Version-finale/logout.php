<?php
//  Logout: destroy the session and return to the home page
session_start();      // resume the current session
session_destroy();    // delete all session data (logs the user out)
header("Location: index.php");  // redirect to the home page
exit;
?>
