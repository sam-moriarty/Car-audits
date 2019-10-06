<?php
 

    //require the following php files
    require 'php_functions/errorlog.php';

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
		die("Connection failed: " . $conn->connect_error);
	}

	//define variables from POST whilst mitigating against HTML injection
    $eID = mysqli_real_escape_string($conn, $_POST['eID']);
    $mID = mysqli_real_escape_string($conn, $_POST['mID']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $retype_password = mysqli_real_escape_string($conn, $_POST['retype_password']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);

    //if any of the fields == NULL
    if($eID == null OR $mID == null OR $email == null OR $password == null OR $retype_password == null OR $first_name == null OR $last_name == null)
    {
        //errorlog
    	errorlog("create_an_account_check.php","User has not logged in, and has tried to access this page through their URL");
        //redirect
    	header("Location: index.php");
    }
    else
    {
    	//check if the eID entered does not already exist in the databse
    	$sql_eID = "SELECT * FROM users WHERE e_id = '".$eID."';";
    	//the result of that eID check
    	$result_eID = $conn->query($sql_eID);
    	//if there are more than 0 instances of the entered eID in the database
    	if($result_eID->num_rows >= 1)
    	{
            //errorlog
    		errorlog("create_an_account_check.php","User tried to enter an eID already in the database");
    		//throw to create_an_account_check_eIDfail
    		header("Location: create_an_account_check_eIDfail.php");
    	}
    	else
    	{
    		//if password == retyped_password
            if($password == $retype_password)
            {
                //if admin value isset and if $_POST['administrator'] checbox is checked with value 'administrator'
            	if(isset($_POST['administrator']) && $_POST['administrator'] == 'administrator')
            	{
                    //set administrator value
            		$administrator = 1;
            	}
            	else
            	{
                    //set administrator value
            		$administrator = 0;
            	}

            	//encrypt password using AES-256
                $Encpw = mysqli_real_escape_string($conn, base64_encode(openssl_encrypt($password, "AES-256-CBC", 'jamesKey', 0, 'bluetealomarmark')));

                //begin prepared statement for insert
                $stmt = $conn->prepare("INSERT INTO users(e_id, m_id, email, password, administrator, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssiss", $eID, $mID, $email, $Encpw, $administrator, $first_name, $last_name);
                $stmt->execute();
                //throw to create_an_account_success
                $stmt->close();
                header("Location: create_an_account_check_success.php");

            }
            else
            {
                //errorlog
            	errorlog("create_an_account_check.php","Create account attempt: passwords do not match");
            	//throw to create_an_account_check_pwfail
            	header("Location: create_an_account_check_pwfail.php");
            }
    	}

    }
$conn->close();
?>