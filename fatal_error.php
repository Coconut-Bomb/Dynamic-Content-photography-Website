<?php
// adds header.php to the top of the page
require 'header.php';
// informs the user a error occurred
echo "<h1>Sorry A Fatal Error occurred dorry for any inconvenience. Please try again<h1/>";
if (isset($_GET["error"])) {
  // informs the user of the cause of the error
  echo "<h1>Cause of Error: ".$_GET["error"]."<h1/>";

}
// adds footer.php to the bottom of the page
require 'footer.php';
 ?>
