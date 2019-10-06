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
		errorlog("delete_car.php","$conn conection error");
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
					errorlog("delete_car.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
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
							//execute and save sql_command to $commandresult1 variable
							$sql_command = "SELECT * FROM cars WHERE vin_num = '".$_GET['id']."';";
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
										//save sql query to variable
										$dealership_command = "SELECT dealership_name FROM dealerships WHERE dealership_id = '".$rowcommand['dealership_id']."';";

										//execute and save result of sql query to variable
										$dealership_cmd_result = $conn->query($dealership_command);

										//if there are rows in the database
										if($dealership_cmd_result->num_rows > 0)
										{
											//while fetch associated array
											while($rowdealership = $dealership_cmd_result->fetch_assoc())
											{
												//call headerbar() function
												headerbar();
												//call head() function and title page 'Delete Car'
												head("Delete Car");
												echo "<body>";
												//call navbar() function and set appropriate links depending on admin status
												navbar("Delete Car ".htmlentities($rowcommand['registration']),"delete_car.php?id=".$_GET['id'], 1, htmlentities($rowdealership['dealership_name']), "cars.php?id=".htmlentities($rowcommand['dealership_id']), 0, "Dealerships", "dealerships.php", 0, "Create an Account", "create_an_account.php", 1);
											}
										}
									
									//echo table
									echo "<table class='table'>";
									//echo form
									echo "<form name='delete_car' id='delete_car' action='delete_execute.php' method='POST' enctype='multiplart/form-data'>

									<tr class='tr'>
										<td class='td'>
											<label for 'vin_num'>Vin Number:</label></td>
										<td class='td'>
											<input type='text' id='vin_num' name='vin_num' readonly value=".htmlentities($rowcommand['vin_num'])." /></td></tr>

									<tr class='tr'>								
										<td class='td'>
											<label for 'code'>Code:</label></td>
										<td class='td largetablefont'>
											<input type='text' id='code' name='code' readonly value=".htmlentities($rowcommand['code'])."
											</td>
											</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'registration'>Registration:</label></td>
										<td class='td'>
											<input type='text' id='registration' name='registration' readonly value='".htmlentities($rowcommand['registration'])."'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'image'>Image:</label></td>
										<td class='td'>
											<img alt='an image of this car' width=120 src='data:image/jpeg;base64,".base64_encode($rowcommand['image'])."'/>
										</td>
										</tr>
										
										<tr class='tr'>
										<td class='td'>
											<label for 'make'>Make:</label></td>
										<td class='td'>
											<input type='text' id='make' name='make' readonly value='".htmlentities($rowcommand['make'])."'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'model'>Model:</label></td>
										<td class='td'>
											<input type='text' id='model' name='model' readonly value='".htmlentities($rowcommand['model'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'colour'>Colour:</label></td>
										<td class='td'>
											<input type='text' id='colour' name='colour' readonly value='".htmlentities($rowcommand['colour'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'body'>Body:</label></td>
										<td class='td'>
											<input type='text' id='body' name='body' readonly value='".htmlentities($rowcommand['body'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'year_of_manufacture'>Year of Manufacture:</label></td>
										<td class='td'>
											<input type='number' id='year_of_manufacture' name='year_of_manufacture' readonly value='".htmlentities($rowcommand['year_of_manufacture'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'notes'>Notes:</label></td>
										<td class='td'>
											<input type='text' id='notes' name='notes' readonly value='".htmlentities($rowcommand['notes'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'engine_num'>Engine Number:</label></td>
										<td class='td'>
											<input type='text' id='engine_num' name='engine_num' value='".htmlentities($rowcommand['engine_num'])."' readonly />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'dsn'>Dealer Stock Number:</label></td>
										<td class='td'>
											<input type='text' id='dsn' name='dsn' readonly value='".htmlentities($rowcommand['dsn'])."'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'asset_num'>Asset Number:</label></td>
										<td class='td'>
											<input type='text' id='asset_num' name='asset_num' readonly value='".htmlentities($rowcommand['asset_num'])."'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'sub_model'>Sub Model:</label></td>
										<td class='td'>
											<input type='text' id='sub_model' name='sub_model' readonly value='".htmlentities($rowcommand['sub_model'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'payout_value'>Payout Value:</label></td>
										<td class='td'>
											<input type='number' id='payout_value' name='payout_value' readonly value='".htmlentities($rowcommand['payout_value'])."'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'bailed_date'>Bailed date:</label></td>
										<td class='td'>
											<input type='text' id='bailed_date' name='bailed_date' readonly value='".htmlentities($rowcommand['bailed_date'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'bailment_price'>Bailment Price:</label>
											</td>
										<td class='td'>
											<input type='number' id='bailment_price' name='bailment_price' readonly value='".htmlentities($rowcommand['bailment_price'])."'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'gst_inclusive'>GST Inclusive:</label>
											</td>
										<td class='td'>
											<input type='number' id='gst_inclusive' name='gst_inclusive' readonly value='".htmlentities($rowcommand['gst_inclusive'])."'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'gst_exclusive'>GST Exclusive:</label>
											</td>
										<td class='td'>
											<input type='number' id='gst_exclusive' name='gst_exclusive' readonly value='".htmlentities($rowcommand['gst_exclusive'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'next_curtailment_date'>Next Curtailment Date:</label></td>
										<td class='td'>
											<input type='text' id='next_curtailment_date' name='next_curtailment_date' readonly value='".htmlentities($rowcommand['next_curtailment_date'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'next_curtailment_amount'>Next Curtailment Amount:</label></td>
										<td class='td'>
											<input type='number' id='next_curtailment_amount' name='next_curtailment_amount' readonly value='".htmlentities($rowcommand['next_curtailment_amount'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'curtailment_remaining'>Curtailment Remaining:</label></td>
										<td class='td'>
											<input type='number' id='curtailment_remaining' name='curtailment_remaining' readonly value='".htmlentities($rowcommand['curtailment_remaining'])."'/>
										</td>
										</tr>
										</table>
										";
										echo "<div align=center><input class='btn btn-primary' type='submit' value='Delete Car' /></div>";
									}
									else
									{
										//errorlog function
										errorlog("delete_car.php","User tried to access administrator feature");
										//redirect
										header("Location: index.php");
									}
								}
							}
							else
							{
								//errorlog function
								errorlog("delete_car.php","User tried to access administrator feature");
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
		errorlog("cars.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	$conn->close();
?>