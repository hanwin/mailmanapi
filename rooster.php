<?php
	require("config.php");
	//get all subscribers
	echo $conn->roster();
?>
