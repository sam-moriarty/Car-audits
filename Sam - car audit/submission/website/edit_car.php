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
		errorlog("edit_car.php","$conn conection error");
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
					errorlog("edit_car.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
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
							//execute and save $sql_command to both $commandresult1 and $commandresult2 variables
							$sql_command = "SELECT * FROM cars WHERE vin_num = '".$_GET['id']."';";
							
							$commandresult1 = $conn->query($sql_command);
							$commandresult2 = $conn->query($sql_command);

							//if sql result returned more than 0 rows
							if($commandresult1->num_rows > 0)
							{
								//while fetch associated array
								while($rowcommand = $commandresult1->fetch_assoc())
								{
									//define sql query in variable
									$dealership_command = "SELECT dealership_name FROM dealerships WHERE dealership_id = '".$rowcommand['dealership_id']."';";

									//execute and store the result of that query
									$dealership_cmd_result = $conn->query($dealership_command);

									//if sql result returned more than 0 rows
									if($dealership_cmd_result->num_rows > 0)
									{
										//while fetch associated array
										while($rowdealership = $dealership_cmd_result->fetch_assoc())
										{
											//call headerbar() function
											headerbar();
											//call head() function
											head("Edit Car");
											echo "<body>";
											//call navbar() function and set appropriate links according to admin status
											navbar("Edit Car", "edit_car.php?id=".$_GET['id'], 0, htmlentities($rowdealership['dealership_name']), "cars.php?id=".htmlentities($rowcommand['dealership_id']), 0, "Dealerships", "dealerships.php", 0, "Create an Account", "create_an_account.php", 1);	
											//start table
											echo "<table class='table'>";										
										}
									}
								}
							}	


							//if there are rows in the database
							if ($commandresult2->num_rows > 0)
							{
								//whil fetch associated array
								while($rowcommand = $commandresult2->fetch_assoc())
								{
									//echo form and table
									echo "<form name='edit_car' id='edit_car' action='edit_execute.php' method='POST' enctype='multipart/form-data'>

									<tr class='tr'>
										<td class='td'>
											<label for 'vin_num'>Vin Number:</label></td>
										<td class='td'>
											<input type='text' id='vin_num' name='vin_num' required='required' readonly value=".htmlentities($rowcommand['vin_num'])." /></td></tr>

									<tr class='tr'>								
										<td class='td'>
											<label for 'code'>Code:</label></td>
										<td class='td largetablefont'>
											<select id='code' name='code' required='required'>
											<option value='IT'>IT -- In Transit</option>
											<option value='LO'>LO -- Loan Car</option>
											<option value='SF'>SF -- Sold, Funds Collected</option>
											<option value='SO'>SO -- Sold, Owing</option>
											<option value='TR'>TR -- Transfer Out</option>
											<option value='CO'>CO -- Compound</option>
											<option value='DPP'>DPP -- Deferred Payment Plan</option>
											<option value='SL'>SL -- Sold, Paid Late</option>
											<option value='RP'>RP -- Repairers</option>
											<option value='BB'>BB -- Bodybuilders</option>
											<option value='PN'>PN -- Pending</option>
											<option value='OT'>OT -- Other</option>
											</select></td>
											</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'registration'>Registration:</label></td>
										<td class='td'>
											<input type='text' id='registration' name='registration' value='".htmlentities($rowcommand['registration'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'image'>Image:</label></td>
										<td class='td'>
											<img alt='an image of this car' width=120 src='data:image/jpeg;base64,".base64_encode($rowcommand['image'])."'/>
											<br />
											<input type='file' name='image' id='image'>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'make'>Make:</label></td>
										<td class='td'>
											<input type='text' id='make' name='make' value='".htmlentities($rowcommand['make'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'model'>Model:</label></td>
										<td class='td'>
											<input type='text' id='model' name='model' value='".htmlentities($rowcommand['model'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'colour'>Colour:</label></td>
										<td class='td'>
											<input type='text' id='colour' name='colour' value='".htmlentities($rowcommand['colour'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'body'>Body:</label></td>
										<td class='td'>
											<input type='text' id='body' name='body' value='".htmlentities($rowcommand['body'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'year_of_manufacture'>Year of Manufacture:</label></td>
										<td class='td'>
											<input type='number' id='year_of_manufacture' name='year_of_manufacture' value='".htmlentities($rowcommand['year_of_manufacture'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'notes'>Notes:</label></td>
										<td class='td'>
											<input type='text' id='notes' name='notes' value='".htmlentities($rowcommand['notes'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'engine_num'>Engine Number:</label></td>
										<td class='td'>
											<input type='text' id='engine_num' name='engine_num' value='".htmlentities($rowcommand['engine_num'])."' required='required' readonly />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'dsn'>Dealer Stock Number:</label></td>
										<td class='td'>
											<input type='text' id='dsn' name='dsn' value='".htmlentities($rowcommand['dsn'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'asset_num'>Asset Number:</label></td>
										<td class='td'>
											<input type='text' id='asset_num' name='asset_num' value='".htmlentities($rowcommand['asset_num'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'sub_model'>Sub Model:</label></td>
										<td class='td'>
											<input type='text' id='sub_model' name='sub_model' value='".htmlentities($rowcommand['sub_model'])."' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'payout_value'>Payout Value:</label></td>
										<td class='td'>
											<input type='number' id='payout_value' name='payout_value' value='".htmlentities($rowcommand['payout_value'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'bailed_date'>Bailed date:</label></td>
										<td class='td'>
											<input type='date' id='bailed_date' name='bailed_date' value='".htmlentities($rowcommand['bailed_date'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'bailment_price'>Bailment Price:</label></td>
										<td class='td'>
											<input type='number' id='bailment_price' name='bailment_price' value='".htmlentities($rowcommand['bailment_price'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'gst_inclusive'>GST Inclusive:</label></td>
										<td class='td'>
											<input type='number' id='gst_inclusive' name='gst_inclusive' value='".htmlentities($rowcommand['gst_inclusive'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'gst_exclusive'>GST Exclusive:</label></td>
										<td class='td'>
											<input type='number' id='gst_exclusive' name='gst_exclusive' value='".htmlentities($rowcommand['gst_exclusive'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'next_curtailment_date'>Next Curtailment Date:</label></td>
										<td class='td'>
											<input type='date' id='next_curtailment_date' name='next_curtailment_date' value='".htmlentities($rowcommand['next_curtailment_date'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'next_curtailment_amount'>Next Curtailment Amount:</label></td>
										<td class='td'>
											<input type='number' id='next_curtailment_amount' name='next_curtailment_amount' value='".htmlentities($rowcommand['next_curtailment_amount'])."' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'curtailment_remaining'>Curtailment Remaining:</label></td>
										<td class='td'>
											<input type='number' id='curtailment_remaining' name='curtailment_remaining' value='".htmlentities($rowcommand['curtailment_remaining'])."' required='required' />
										</td>
										</tr>
										</table>
										";
									}
								}
							else
							{
								echo "</table>";
							}
							//echo out submit button
							echo "<div align=center><input class='btn btn-primary' type='submit' value='Edit Car' /></div></form>"
							;
						}
					}
				}
			}
		}
	}
	else
	{
		//errorlog function
		errorlog("edit_car.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}
	$conn->close();
?>