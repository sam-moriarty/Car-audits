<?php


	//required php functions
	require 'php_functions/headerbar.php';
	require 'php_functions/head.php';
	require 'php_functions/navbar.php';
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
					errorlog("assign_dealership.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
					//redirect
					header("Location: index.php");
				}
				else
				{
					//define sql query
					$sql_administrator = "SELECT administrator FROM users WHERE e_id = '".$_SESSION['eID']."';";

					//execute and store sql result
					$sql_adminresult = $conn->query($sql_administrator);

					//if sql result returned more than 0 rows
					if($sql_adminresult->num_rows > 0)
					{
						//while fetch associated array
						while($row = $sql_adminresult->fetch_assoc())
						{
							//execute and save sql command to $commandresult1 variable
							$sql_command = "SELECT * FROM dealerships WHERE dealership_id = '".$_GET['id']."';";
							$commandresult1 = $conn->query($sql_command);

							//if there are rows in the database
							if ($commandresult1->num_rows > 0)
							{
								//while fetch associated array
								while($rowcommand = $commandresult1->fetch_assoc())
								{
									//if user is admin
									if($row['administrator'] == 1)
									{
									//call headerbar() function
									headerbar();
									//call head() function and title page 'Assign Dealership';
									head("Assign Dealership");
									//echo body
									echo "<body>";
									//call navbar() function with appropriate links depending on admin status
									navbar("Assign ".htmlentities($rowcommand['dealership_name']), "assign_dealership.php?id=".$_GET['id'], 1, "Dealerships", "dealerships.php", 0, "Create an Account", "create_an_account.php", 1);
									
									//create form and table
									echo "<form name='assign_dealership' id='assign_dealership' action='assign_execute.php?id=".$_GET['id']."' method='POST' enctype='multipart/form-data'>
									<table class='table'>
									<tr class='tr'>
									<td class='td'>
									Assign ".htmlentities($rowcommand['dealership_name'])." to:
									</td>
									
									<td class='td'>
									";

									//execute and save sql_cmd_users to $sql_cmd_result variable
									$sql_cmd_users = "SELECT * FROM users";
									$sql_cmd_result = $conn->query($sql_cmd_users);

										//if there are rows in the database
										if($sql_cmd_result->num_rows > 0)
										{
											//echo html <select> form element to allow admin to assign dealership to user
											echo "<select id='e_id' name='e_id' required='required'>";
											while($usersrow = $sql_cmd_result->fetch_assoc())
											{
												echo "<option value='".htmlentities($usersrow['e_id'])."'>".htmlentities($usersrow['first_name'])." ".htmlentities($usersrow['last_name'])." -- ".htmlentities($usersrow['e_id'])."</option>";
											}

											echo "</select>
											</td>
											</tr>
											</table>
											<div align=center><input class='btn btn-primary' type='submit' value='Assign' /></div>
											</form>
											";
										}

									}
									else
									{
										//errorlog functino
										errorlog("assign_dealership.php","User tried to access administrator feature");
										//redirect
										header("Location: index.php");
									}
								}
							}
							else
							{
								//errorlog functino
								errorlog("assign_dealership.php","User tried to access administrator feature");
								//redirect
								header("Location: index.php");
							}

						}
					}
				}
			}
		}
	}
	else
	{
		//errorlog function
		errorlog("assign_dealership.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	$conn->close();
?>