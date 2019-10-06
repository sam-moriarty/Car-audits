<?php
	
//start session
session_start();
//destroy session and session variables
session_destroy();
//redirect
header("Location: index.php");
?>