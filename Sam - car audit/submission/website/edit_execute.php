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
		errorlog("create_an_account.php","$conn conection error");
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
					errorlog("edit_execute.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
					//redirect
					header("Location: index.php");
				}
				else
				{
					//define variables from POST
					$vin_num = mysqli_real_escape_string($conn, $_POST['vin_num']);
					$registration = mysqli_real_escape_string($conn, $_POST['registration']);
					$code = mysqli_real_escape_string($conn, $_POST['code']);

					//store sql query
					$code_last_sql = "SELECT code FROM cars WHERE vin_num = '".$vin_num."';";
					//store result of sql query
					$code_last_result = $conn->query($code_last_sql);
					//if sql result returned more than 0 rows
					if ($code_last_result->num_rows > 0)
					{
						//while fetch associated array
						while($row = $code_last_result->fetch_assoc())
						{
							//define variable
							$code_last = $row['code'];
						}
					}
					//continue defining variables from POST
					$make = mysqli_real_escape_string($conn, $_POST['make']);
					$model = mysqli_real_escape_string($conn, $_POST['model']);
					$colour = mysqli_real_escape_string($conn, $_POST['colour']);
					$body = mysqli_real_escape_string($conn, $_POST['body']);
					$year_of_manufacture = mysqli_real_escape_string($conn, $_POST['year_of_manufacture']);
					$notes = mysqli_real_escape_string($conn, $_POST['notes']);
					$engine_num = mysqli_real_escape_string($conn, $_POST['engine_num']);
					$dsn = mysqli_real_escape_string($conn, $_POST['dsn']);
					$asset_num = mysqli_real_escape_string($conn, $_POST['asset_num']);
					$sub_model = mysqli_real_escape_string($conn, $_POST['sub_model']);
					$payout_value = mysqli_real_escape_string($conn, $_POST['payout_value']);
					$bailed_date = mysqli_real_escape_string($conn, $_POST['bailed_date']);
					$bailment_price = mysqli_real_escape_string($conn, $_POST['bailment_price']);
					$gst_inclusive = mysqli_real_escape_string($conn, $_POST['gst_inclusive']);
					$gst_exclusive = mysqli_real_escape_string($conn, $_POST['gst_exclusive']);
					$next_curtailment_date = mysqli_real_escape_string($conn, $_POST['next_curtailment_date']);
					$next_curtailment_amount = mysqli_real_escape_string($conn, $_POST['next_curtailment_amount']);
					$curtailment_remaining = mysqli_real_escape_string($conn, $_POST['curtailment_remaining']);

					//if $_FILES image exists
					if(($_FILES['image']['tmp_name']))
					{
						//get contents of image and store in variable $image
						$image = file_get_contents($_FILES['image']['tmp_name']);

						//begin prepared statement for update
						$stmt = $conn->prepare("UPDATE `cars` SET `image`=?, `registration`=?, `code`=?, `code_last`=?, `make`=?, `model`=?, `colour`=?, `body`=?, `year_of_manufacture`=?, `notes`=?, `engine_num`=?, `dsn`=?, `asset_num`=?, `sub_model`=?, `payout_value`=?, `bailed_date`=?, `bailment_price`=?, `gst_inclusive`=?, `gst_exclusive`=?, `next_curtailment_date`=?, `next_curtailment_amount`=?, `curtailment_remaining`=? WHERE `vin_num`=?;");
						//bind parameters
						$stmt->bind_param("bsssssssdsssssdsdddsdds", $image, $registration, $code, $code_last, $make, $model, $colour, $body, $year_of_manufacture, $notes, $engine_num, $dsn, $asset_num, $sub_model, $payout_value, $bailed_date, $bailment_price, $gst_inclusive, $gst_exclusive, $next_curtailment_date, $next_curtailment_amount, $curtailment_remaining, $vin_num);
						//send blob image
						$stmt->send_long_data(0, $image);
					}
					//else if $_FILES image does not exist
					else
					{
					//begin prepared statement for update
					$stmt = $conn->prepare("UPDATE `cars` SET `registration`=?, `code`=?, `code_last`=?, `make`=?, `model`=?, `colour`=?, `body`=?, `year_of_manufacture`=?, `notes`=?, `engine_num`=?, `dsn`=?, `asset_num`=?, `sub_model`=?, `payout_value`=?, `bailed_date`=?, `bailment_price`=?, `gst_inclusive`=?, `gst_exclusive`=?, `next_curtailment_date`=?, `next_curtailment_amount`=?, `curtailment_remaining`=? WHERE `vin_num`=?;");
					//bind parameters
					$stmt->bind_param("sssssssdsssssdsdddsdds", $registration, $code, $code_last, $make, $model, $colour, $body, $year_of_manufacture, $notes, $engine_num, $dsn, $asset_num, $sub_model, $payout_value, $bailed_date, $bailment_price, $gst_inclusive, $gst_exclusive, $next_curtailment_date, $next_curtailment_amount, $curtailment_remaining, $vin_num);
					}

					//if statement successfully executes
					if($stmt->execute() === TRUE)
					{
						//alert and redirect
						echo "<script type='text/javascript'>
				        alert('Car Successfully Edited');
				        window.history.go(-2);
				        </script>";
					}
					else
					{
						//write to error log
						error_log("\n(edit_execute.php) Error: user unable to edit Car", 3, "logs/errors.log");
						//alert and redirect
						echo "<script type='text/javascript'>
				        alert('Error: Unable to Edit Car. Please try again.');
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
		errorlog("edit_execute.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	//close connection
	$conn->close();
?>