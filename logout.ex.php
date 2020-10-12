<?php
// if logout button has been pressed
if (isset($_GET["logout-submit"])) {
  // a new session is started or ressumed
  session_start();
  // all session data is destroyed / reset
  session_destroy();
  // users is redirected to index.php
  header("Location: ../index.php");
}
?>
