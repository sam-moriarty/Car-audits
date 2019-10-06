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
		errorlog("add_execute.php","$conn conection error");
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
					errorlog("add_execute.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
					//redirect
					header("Location: index.php");
				}
				else
				{
					//set variable from POST
					$vin_num = mysqli_real_escape_string($conn, $_POST['vin_num']);

					//store sql query
					$sql_vin = "SELECT vin_num FROM cars WHERE vin_num = '".$vin_num."';";

					//execute and store result of sql query
					$sql_vinresult = $conn->query($sql_vin);

					//if there are 1 or more results from the database with the same vin number
					if($sql_vinresult->num_rows >= 1)
					{
						//javascript alert and redirect
						echo "<script type='text/javascript'>
				        alert('Error: Sorry, that VIN number is already in the database. Please enter another one.');
				        history.back();
				        </script>";
					}
					else
					{
					//define variables from POST
					$dealership_id = mysqli_real_escape_string($conn, $_POST['dealership_id']);
					$e_id = mysqli_real_escape_string($conn, $_POST['e_id']);
					$registration = mysqli_real_escape_string($conn, $_POST['registration']);
					$code = mysqli_real_escape_string($conn, $_POST['code']);
					$code_last = "";
					$qr_code = "";
					$image = file_get_contents($_FILES['image']['tmp_name']);
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

					//begin prepared statement for insertion
					$stmt = $conn->prepare("INSERT INTO `cars`(`image`, `dealership_id`, `e_id`, `vin_num`, `registration`, `code`, `code_last`, `make`, `model`, `colour`, `body`, `year_of_manufacture`, `notes`, `engine_num`, `dsn`, `asset_num`, `sub_model`, `payout_value`, `bailed_date`, `bailment_price`, `gst_inclusive`, `gst_exclusive`, `next_curtailment_date`, `next_curtailment_amount`, `curtailment_remaining`, `qr_code`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

					//bind variables
					$stmt->bind_param("bssssssssssdsssssdsdddsdds", $image, $dealership_id, $e_id, $vin_num, $registration, $code, $code_last,  $make, $model, $colour, $body, $year_of_manufacture, $notes, $engine_num, $dsn, $asset_num, $sub_model, $payout_value, $bailed_date, $bailment_price, $gst_inclusive, $gst_exclusive, $next_curtailment_date, $next_curtailment_amount, $curtailment_remaining, $qr_code);

					//send blob image
					$stmt->send_long_data(0, $image);

					//if statement executes
					if($stmt->execute() === TRUE)
					{
						//alert and redirect
						echo "<script type='text/javascript'>
				        alert('Car Successfully Added');
				        window.history.go(-2);
				        </script>";
					}
					else
					{

						//write to error log
						error_log("\n(edit_execute.php) Error: user unable to edit Car", 3, "logs/errors.log");
						//alert and redirect
						echo "<script type='text/javascript'>
				        alert('Error: Unable to Add Car. Please try again.');
				        history.back();
				        </script>";
					}
					//close $stmt
					$stmt->close();
					//close connection
					$conn->close();
					}	
				}
			}
		}
		else
		{
			//errorlog function
			errorlog("add_execute.php","User tried to access administrator feature");
			//redirect
			header("Location: index.php");
		}
	}
	else
	{
		//errorlog function
		errorlog("edit_execute.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	$conn->close();
?>