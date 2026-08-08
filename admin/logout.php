<?php
// ============================================================
// FILE: admin/logout.php
// PURPOSE: Log out the admin
// HOW IT WORKS:
//   1. Destroy the session (remove the keycard)
//   2. Redirect to login page
// ============================================================

session_start();
session_destroy();  // Remove all session data
header("Location: login.php");  // Go back to login
exit();
?>
