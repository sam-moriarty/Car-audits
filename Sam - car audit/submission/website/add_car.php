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
		//execute and store result of sql query
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
					errorlog("add_car.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
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
							//if user is administrator, allow them to view the add_car.php page
							if($row['administrator'] == 1)
							{
								//call headerbar();
								headerbar();
									//call head() and title page 'Add Car'
									head("Add Car");
									echo "<body>";
									//create navbar with appropriate links depending on admin status
									navbar("Add Car","add_car.php?id=".$_GET['id'], 1, "Dealerships", "dealerships.php", 0, "Create an Account", "create_an_account.php", 1);
									
									//create table
									echo "<table class='table'>";
									//create form
									echo "<form name='add_car' id='add_car' action='add_execute.php' method='POST' enctype='multipart/form-data'>

									<tr class='tr'>
										<td class='td'>
											<label for 'dealership_id'>Dealership ID:</label></td>
										<td class='td'>
											<input type='text' id='dealership_id' name='dealership_id' readonly value=".$_GET['id']." /></td></tr>

									<tr class='tr'>
										<td class='td'>
											<label for 'e_id'>eID of Creator:</label></td>
										<td class='td'>
											<input type='text' id='e_id' name='e_id' readonly value=".$_SESSION['eID']." /></td></tr>		

									<tr class='tr'>
										<td class='td'>
											<label for 'vin_num'>Vin Number <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='text' id='vin_num' name='vin_num' required='required'/></td></tr>

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
											</select>
											</td>
											</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'registration'>Registration:</label></td>
										<td class='td'>
											<input type='text' id='registration' name='registration' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'image'>Image <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='file' id='image' name='image' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'make'>Make <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='text' id='make' name='make' required='required'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'model'>Model <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='text' id='model' name='model' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'colour'>Colour <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='text' id='colour' name='colour' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'body'>Body:</label></td>
										<td class='td'>
											<input type='text' id='body' name='body' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'year_of_manufacture'>Year of Manufacture:</label></td>
										<td class='td'>
											<input type='number' id='year_of_manufacture' name='year_of_manufacture' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'notes'>Notes:</label></td>
										<td class='td'>
											<input type='text' id='notes' name='notes' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'engine_num'>Engine Number:</label></td>
										<td class='td'>
											<input type='text' id='engine_num' name='engine_num' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'dsn'>Dealer Stock Number <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='text' id='dsn' name='dsn' required='required'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'asset_num'>Asset Number <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='text' id='asset_num' name='asset_num' required='required'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'sub_model'>Sub Model:</label></td>
										<td class='td'>
											<input type='text' id='sub_model' name='sub_model' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'payout_value'>Payout Value <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='number' id='payout_value' name='payout_value' required='required'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'bailed_date'>Bailed date <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='date' id='bailed_date' name='bailed_date' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'bailment_price'>Bailment Price: <b>(REQUIRED)</b></label>
											</td>
										<td class='td'>
											<input type='number' id='bailment_price' name='bailment_price' required='required'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'gst_inclusive'>GST Inclusive <b>(REQUIRED)</b>:</label>
											</td>
										<td class='td'>
											<input type='number' id='gst_inclusive' name='gst_inclusive' required='required'/>
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'gst_exclusive'>GST Exclusive <b>(REQUIRED)</b>:</label>
											</td>
										<td class='td'>
											<input type='number' id='gst_exclusive' name='gst_exclusive' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'next_curtailment_date'>Next Curtailment Date <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='date' id='next_curtailment_date' name='next_curtailment_date' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'next_curtailment_amount'>Next Curtailment Amount <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='number' id='next_curtailment_amount' name='next_curtailment_amount' required='required' />
										</td>
										</tr>

										<tr class='tr'>
										<td class='td'>
											<label for 'curtailment_remaining'>Curtailment Remaining <b>(REQUIRED)</b>:</label></td>
										<td class='td'>
											<input type='number' id='curtailment_remaining' name='curtailment_remaining' required='required'/>
										</td>
										</tr>
										</table>
										";
										//echo out submit button
										echo "<div align=center><input class='btn btn-primary' type='submit' value='Add Car' /></div></form>";
							}
							else
							{
								//errorlog function
								errorlog("add_car.php","User tried to access administrator feature");
								//redirect
								header("Location: index.php");
							}
						}
					}
					else
					{
						//errorlog functino
						errorlog("add_car.php","User tried to access administrator feature");
						//redirect
						header("Location: index.php");
					}
				}
			}
		}
		else
		{
			//errorlog functino
			errorlog("add_car.php","User tried to access administrator feature");
			//redirect
			header("Location: index.php");
		}
	}
	else
	{
		//errorlog function
		errorlog("add_car.php","No $_SESSION[eID] set");
		//redirect
		header("Location: index.php");
	}

	$conn->close();
?>