<?php
	

	//required php functions
	require 'php_functions/errorlog.php';

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
		//error_log("\n(".$filename.") Error: $conn connection error", 3, "logs/errors.log");
		die("Connection failed: " . $conn->connect_error);
	}
		
	//obtain variables from index.php textfields through post, mitigating HTML injection
	$eID = mysqli_real_escape_string($conn, $_POST['eID']);
	$password = mysqli_real_escape_string($conn, $_POST['password']);

	//if no email or password is entered
	if($eID == null OR $password == null)
	{
		//write to error log
		errorlog("index_check.php", "User has not logged in, and has tried to access this page through their URL");
		//redirect
 		header("Location: index.php");
	}
	else
	{
		//sql statement to search for account by unique eID
		$sql = "SELECT * FROM users WHERE e_id = '".$eID."';";
		//save into variable for next step
		$result = $conn->query($sql);

		//if sql query returned more than 0 rows
		if ($result->num_rows > 0)
		{
			//while loop to do the following for that row
			while($row = $result->fetch_assoc())
			{
				//decrypt and decode password in database
				$dcrypted  = openssl_decrypt(base64_decode($row['password']), "AES-256-CBC", 'jamesKey', 0, 'bluetealomarmark');

				//if entered email matches database email AND entered password matches database decrypted password, display and alert. If no match, display a different alert
				if(trim($eID) == trim($row['e_id']) && trim($password) == trim($dcrypted))
				{
					//begin session
					session_start();
					//set session id to user_id
					$_SESSION['eID'] = $row['e_id'];
					//set session['enc_email'] to the encrypted email of that user
					$_SESSION['enc_email'] = mysqli_real_escape_string($conn, base64_encode(openssl_encrypt($row['email'], "AES-256-CBC", 'jamesKey', 0, 'bluetealomarmark')));

					//throw to index_success.php
					header("Location: index_success.php");
				}
				else
				{
					errorlog("index_check.php","Incorrect password login attempt");
					//throw to index_failure.php
					header("Location: index_failure.php");
				}
			}
		}
		else
		{
			errorlog("index_check.php", "Incorrect email login attempt");
			//throw to index_failure.php
			header("Location: index_failure.php");
		}

	}
	$conn->close();
?>