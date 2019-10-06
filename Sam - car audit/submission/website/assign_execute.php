<?php


	//required php functions
	require 'php_functions/errorlog.php';

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
		errorlog("assign_execute.php","$conn conection error");
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
					errorlog("assign_execute.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
					//redirect
					header("Location: index.php");
				}
				else
				{
					//define variable from post
					$e_id = mysqli_real_escape_string($conn, $_POST['e_id']);

					//begin prepared statement for update
					$stmt = $conn->prepare("UPDATE dealerships SET assigned_to=? WHERE dealership_id=?");
					//bind parameters
					$stmt->bind_param("ss", $e_id, $_GET['id']);
					
					//if statement executes
					if($stmt->execute() === TRUE)
					{
						//alert and redirect
						echo "<script type='text/javascript'>
				        alert('Car Successfully Assigned');
				        window.history.go(-2);
				        </script>";
					}
					else
					{
						//alert and redirect
						echo "<script type='text/javascript'>
				        alert('Error: Unable to Assign Car. Please try again.');
				        history.back();
				        </script>";
					}
					//close $stmt
					$stmt->close();
				}
			}
		}
	}
	else
	{
		//errorlog function
		errorlog("assign_execute.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	//close connection
	$conn->close();
?>