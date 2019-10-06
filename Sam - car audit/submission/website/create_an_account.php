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
				errorlog("create_an_account.php","Error: Incorrect or no SID, user has tried to enter a restricted area");
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
				if($sql_result->num_rows > 0)
				{
					//while fetch associated array
					while($row = $sql_adminresult->fetch_assoc())
					{
						//if user is administrator
						if($row["administrator"] == 1)
						{
							//create headerbar
							headerbar();
							//create head section
							head("Create an Account");
							//start body
							echo "<body>";
							//create navbar
							navbar("Create an Account", "create_an_account.php", 1, "Dealerships", "dealerships.php", 0);
							//include javascript_functions/create_an_account_formValidation.js and echo out form
							echo "<script src=javascript_functions/create_an_account_formValidation.js></script>
								<section>
									<form id='create_an_account' class='formpadding' name='create_an_account' onsubmit='return create_an_account_formValidation()'  action='create_an_account_check.php' method='post'>
										<br />
										<br />
										<label for='eID'>Employee eID</label>
										<br />
										<br />
										<input class='input-field input-width-small' type='text' id='eID' name='eID' size='10' required='required' />
										<br />
										<label for='mID'>Employee mID</label>
										<br />
										<br />
										<input class='input-field input-width-small' type='text' id='mID' name='mID' size='10' required='required' />
										<br />
										<label for='email'>Email</label>
										<br />
										<br />
										<input class='input-field input-width-small' type='email' id='email' name='email' size=40 pattern='[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$' required='required' />
										<br />
										<label for='password'>Password</label>
										<br />
										<br />
										<input class='input-field input-width-small' type='password' id='password' name='password' size='40' required='required' />
										<br />
										<label for='retype_password'>Retype Password</label>
										<br />
										<br />
										<input class='input-field input-width-small' type='password' id='retype_password' name='retype_password' size='40' required='required' />
										<br />
										<label for='first_name'>First Name</label>
										<br />
										<br />
										<input class='input-field input-width-small' type='text' id='first_name' name='first_name' size='20' required='required' />
										<br />
										<label for 'last_name'>Last Name</label>
										<br />
										<br />
										<input class='input-field input-width-small' type='text' id='last_name' name='last_name' size='20' required='required />'
										<br />
										<br />
										<input type='checkbox' id='administrator' name='administrator' value='administrator'> Administrator
										<br />
										<br />
										<input class='btn btn-primary' type='submit' value='submit' />
									</form>
								</section>
							</body>
						</html>";
							}
							else
							{
								//errorlog functino
								errorlog("create_an_account.php","User tried to access administrator feature");
								//redirect
								header("Location: index.php");
							}
						}
					}
				}
			}

		}
		else
		{
			//errorlog function
			errorlog("create_an_account.php","No $_SESSION[eID] set");
			//redirect
			header("Location: index.php");
		}
	}
	else
	{
	header("Location: index.php");
	}
	$conn->close();
?>