<?php

	//required php functions
	require 'php_functions/headerbar.php';
	require 'php_functions/head.php';
	require 'php_functions/navbar.php';
	require 'php_functions/errorlog.php';

	//start sesion
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
					errorlog("cars.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
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
							//execute and save sql command to $commandresult variable
							$sql_command = "SELECT * FROM cars WHERE dealership_id = '".$_GET['id']."';";
							$commandresult = $conn->query($sql_command);

							//excute and save and additional sql query to $dealership_command_result variable
							$dealership_command = "SELECT dealership_name FROM dealerships WHERE dealership_id = '".$_GET['id']."';";
							$dealership_command_result = $conn->query($dealership_command);

							//if $dealership_command_result returned any rows
							if ($dealership_command_result->num_rows > 0)
							{
								//while fetch associated array
								while($rowdealership = $dealership_command_result->fetch_assoc())
								{
									//call headerbar() function
									headerbar();
									//call head() function and set title to the name of the dealership
									head(htmlentities($rowdealership['dealership_name']));
									echo "<body>";
									//call navbar() function and set links appropriately depending on admin status
									navbar(htmlentities($rowdealership['dealership_name']), "cars.php?id=".$_GET['id'], 0, "Dealerships", "dealerships.php?id=".$_GET['id'], 0, "Add Car to Dealership", "add_car.php?id=".$_GET['id'], 1, "Create an Account", "create_an_account.php", 1);

									echo "<table class='table'>";
								}
							}
							//if there are rows in the database for $commandresult
							if ($commandresult->num_rows > 0)
							{
								echo "<tr>
									<th class='th'>Code</th>
									<th class='th'>Details</th>
									<th class='th'>Previous Code</th>
									<th class='th'>Image</th>
									<th class='th'>Edit</th>";

									//if user is admin
									if($row['administrator'] == 1)
									{
										//echo out delete table heading
										echo "<th class='th'>Delete</th>";
									}
									
								echo "</tr>";

								//while fetch associate array
								while($rowcommand = $commandresult->fetch_assoc())
								{
									echo "<tr class='tr uppercase'>
											<td class='td largetablefont'><b>".htmlentities($rowcommand['code'])."</b></td>
											<td class='td'>".htmlentities($rowcommand['year_of_manufacture'])."   ".htmlentities($rowcommand['make'])."  ".htmlentities($rowcommand['model'])." ".htmlentities($rowcommand['colour'])."  ".htmlentities($rowcommand['registration'])."<br />".htmlentities($rowcommand['body'])."  ".htmlentities($rowcommand['vin_num'])."<br />".htmlentities($rowcommand['notes'])."</td>
											<td class='td largetablefont'><b>".htmlentities($rowcommand['code_last'])."</b></td>

											<td class='td'>

											<img alt='an image of this car' width=120 src='data:image/jpeg;base64,".base64_encode($rowcommand['image'])."'/>
											</td>";

											echo "<td class='td largetablefont'><b><a href='edit_car.php?id=".htmlentities($rowcommand['vin_num'])."'>Edit</a></b></td>";

											//if user is admin
											if($row['administrator'] == 1)
											{
												//echo out delete table cell
												echo "<td class='td largetablefont'><b><a href='delete_car.php?id=".htmlentities($rowcommand['vin_num'])."'>Delete</a></b></td>";
											}
										echo "</tr>";
								}
								echo "</table>";
							}
							else
							{
								//display message that no cars exist in the database for this dealership
								echo "<tr class='tr'>
									<td class='td'>
									No cars exist in the database for this dealerships
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
		errorlog("cars.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	$conn->close();
?>