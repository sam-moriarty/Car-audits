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
		errorlog("dealerships.php","$conn conection error");
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
					errorlog("dealerships.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
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
							//if user is administrator
							if($row["administrator"] == 1)
							{
								//execute and save sql command to $sql_command variable
								$sql_command = "SELECT * FROM dealerships";
							}
							else
							{
								//execute and save sql command to $sql_command variable
								$sql_command = "SELECT * FROM dealerships WHERE assigned_to = '".$_SESSION['eID']."';";
							}

							//execute and store the result of $sql_command query to $commandresult variable
							$commandresult = $conn->query($sql_command);

							//call headerbar() function
							headerbar();
							//call head() function and title page 'Dealerships'
							head("Dealerships");
							echo "<body>";
							//call navbar() function with appropriate links depending on admin status
							navbar("Dealerships", "dealerships.php", 0, "Create an Account", "create_an_account.php", 1);

							//if there are rows in the database
							if ($commandresult->num_rows > 0)
							{
								//echo table and table headings
								echo "<table class='table'>
										<tr>
											<th class='th'>Dealership</th>
											<th class='th'>Floorplan</th>
											<th class='th'>Assigned To</th>";

								//if user is admin
								if($row["administrator"] == 1)
								{
									//echo out additional table heading for assigning dealerships
									echo "<th class='th'>Assign</th>";
								}

								//while fetch associated array
								while($rowcommand = $commandresult->fetch_assoc())
								{
									//echo out <tbody>, table rows and table cells
									echo "<tbody>
											<tr class='tr'>
											<td class='td'><a href='cars.php?id=".htmlentities($rowcommand['dealership_id'])."'><b>".htmlentities($rowcommand["dealership_name"])."</b><br />ID: ".htmlentities($rowcommand["dealership_id"])."</a></td>
											<td class='td'><a href='cars.php?id=".htmlentities($rowcommand['dealership_id'])."'>".htmlentities($rowcommand["floorplan_desc"])."<br />Code: ".htmlentities($rowcommand["floorplan_code"])."</a></td>";

									//if user is admin
									if($row["administrator"] == 1)
									{
										//define sql query
										$sql_users = "SELECT * FROM users WHERE e_id = '".$rowcommand['assigned_to']."';";
										//execute and store sql result
										$usersresult = $conn->query($sql_users);

										//if sql result returned more than 0 rows
										if($usersresult->num_rows > 0)
										{
											//while fetch associated array
											while($usersrow = $usersresult->fetch_assoc())
											{
												echo "<td class='td'><a href='cars.php?id=".htmlentities($rowcommand['dealership_id'])."'><b>".htmlentities($usersrow['first_name']." ".htmlentities($usersrow['last_name']))."</b><br />eID: ".htmlentities($usersrow['e_id'])."<br />".htmlentities($usersrow['email'])."</a></td>
													";

													echo "<td class='td largetablefont uppercase'><b><a href='assign_dealership.php?id=".htmlentities($rowcommand['dealership_id'])."'>Assign</a></b></td>
													</tr>";
											}
										}
										//else if dealership is not assigned
										else
										{
											//echo out not assigned
											echo "<td class='td largetablefont uppercase'><b>Not Assigned</td>
											<td class='td largetablefont uppercase'><b><a href='assign_dealership.php?id=".htmlentities($rowcommand['dealership_id'])."'>Assign</a></b></td>";
										}
									}
									//else if user is not admin
									else
									{
										//echo out 'You'
										echo "<td class='td'><a href='cars.php?id=".htmlentities($rowcommand['dealership_id'])."'><b>You</b></a></td>
										</tr>";
									}
								
								}
								//close tbody and table
								echo "</tbody></table>";
							}
							//else when user is not assigned dealerships
							else
							{
								//echo out not assigned
								echo "<table class='table'>
										<tr class='tr'>
											<td class='td'>
												You have not been assigned any dealerships
											</td>
										</tr>
									</table>";
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
		errorlog("dealerships.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	$conn->close();
?>