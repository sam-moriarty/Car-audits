<?php
	
	//required php functions
	require 'php_functions/errorlog.php';
	require 'php_functions/jsalert.php';

	//start session
	session_start();
	//set variables to connect to database
	$dbservername = "localhost";
	$dbusername = "root";
	$dbpassword = "";
	$dbname = "auditdb";

	//Create connection to database using mysqli
	$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);

	//Check connection to database. If there is an error, output it
	if($conn->connect_error)
	{
		//errorlog function
		errorlog("create_an_account_check_pwfail.php","$conn conection error");
		//kill the connection
		die("Connection failed: " . $conn->connect_error);
	}

	//if session variable isset
	if(isset($_SESSION['eID']))
	{
		//define sql query
		$sql_email = "SELECT email FROM users WHERE e_id = '".$_SESSION['eID']."';";
		//execute and store sql result
		$sql_result = $conn->query($sql_email);

		//if sql result returned more than 0 rows
		if ($sql_result->num_rows > 0)
		{
			//while fetch associated array
			while($row = $sql_result->fetch_assoc())
			{
				//decrypt $_SESSION['enc_email']
				$dcrypted_email = openssl_decrypt(base64_decode($_SESSION['enc_email']), "AES-256-CBC", 'jamesKey', 0, 'bluetealomarmark');

				//if email in database matches decrypted $_SESSION['enc_email']
				if($row['email'] != $dcrypted_email)
				{
					//errorlog function
					errorlog("create_an_account_check_pwfail.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
					//redirect
					header("Location: index.php");
				}
				else
				{
					//alert and redirect
					jsalert("Error: The entered password does not match the retyped password, please try again.","create_an_account.php");
				}
			}
		}
		else
		{
		//errorlog function
		errorlog("create_an_account_check_pwfail.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
		}
	}
	else
	{
		//errorlog function
		errorlog("create_an_account_check_pwfail.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}

	$conn->close();

?>




	