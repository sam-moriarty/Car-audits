<?php


// Create errorlog function with $filename and $errortext as parameters
	function errorlog($filename,$errortext)
	{
		//errorlog() calls php error_log function
		error_log("\n(".$filename.") Error: ".$errortext.", 3, logs/errors.log");
	}
?>