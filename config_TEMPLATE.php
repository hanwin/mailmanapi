<?php
  require("class.mailman.php");
  //////////////////////////////////////////////////////
  /// Config file
  /////////////////////////////////////////////////////

  $servername = "hostname";
  $name = "Name of the maillinglist";
  $password = "Sectet password";

  // Create connection
  $conn = new PHPMailman($servername,$name,$password);

  // Check connection
  if (!$conn) {
    die("Connection failed!");
}
?>
