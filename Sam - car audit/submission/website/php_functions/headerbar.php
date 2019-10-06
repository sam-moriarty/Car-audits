<?php


	//create headerbar() function
	function headerbar()
	{
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
			errorlog("headerbar.php","$conn conection error");
			die("Connection failed: " . $conn->connect_error);
		}

		//sql query
		$sql_administrator = "SELECT administrator FROM users WHERE e_id = '".$_SESSION['eID']."';";
		//execute query
		$sql_result = $conn->query($sql_administrator);

		echo '<div>
			<ul class="headerbar">';
		if($sql_result->num_rows > 0)
		{
			while($row = $sql_result->fetch_assoc())
			{
				//if user is administrator
				if($row["administrator"] == 1)
				{
					echo '<li class="headerbar">Administrator</li>';
				}
			}
		}
		//create logout link
		echo '<li class="headerbar" style="float:right"><a href="logout.php">Logout</a></li>
		</ul>
		</div>';
		$conn->close();
	}
?>