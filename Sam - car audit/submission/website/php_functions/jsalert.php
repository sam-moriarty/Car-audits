<?php


// create function for easier javascript alerts called jsalert() with parameters $alerttext and $location
	function jsalert($alerttext,$location)
	{
		//javascript alert
		echo "<script type='text/javascript'>
		alert('".$alerttext."');
		location='".$location."';
		</script>";
	}
?>