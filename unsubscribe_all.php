<?php
	require("config.php");
	$conn->unsubscribe(json_decode($conn->roster()));
	echo $conn->roster();
?>
