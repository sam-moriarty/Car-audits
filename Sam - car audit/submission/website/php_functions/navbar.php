<?php


//create navbar function to provide navbar with different links depending on the webpage you are one
	function navbar($active, $active_link, $active_adminrequired, $extra1 = NULL, $extra1_link = NULL, $extra1_adminrequired = NULL, $extra2 = NULL, $extra2_link = NULL, $extra2_adminrequired = NULL,$extra3 = NULL, $extra3_link = NULL, $extra3_adminrequired = NULL)
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
			errorlog("navbar.php","$conn conection error");
			die("Connection failed: " . $conn->connect_error);
		}

		//if session variable isset
		if(isset($_SESSION['eID']))
		{
			//query to check user administrator status
			$sql_administrator = "SELECT administrator FROM users WHERE e_id = '".$_SESSION['eID']."';";
			$sql_result = $conn->query($sql_administrator);

			if($sql_result->num_rows > 0)
			{
				while($row = $sql_result->fetch_assoc())
				{
					//if user is administrator
					if($row["administrator"] == 1)
					{
						//show administrator-only links on top of user links
						$admin_show = 1;
					}
					else
					{
						//only show user links
						$admin_show = 0;
					}
				}
			}
		}
		else
		{
			//only show user links
			$admin_show = 0;
		}

		// echo out the logo
		echo '</div>
			<ul class="navbar">
			<li class="navbar"><img src="images/logo.png" alt="Westpac Logo" /></li>';
		//if a link requires admin
	  	if($active_adminrequired == 1)
	  	{
	  		// if user is admin
	  		if($admin_show == 1)
	  		{
	  			//show link
		  		echo '<li class="navbar"><a class="active" href="'.$active_link.'">'.$active.'</a></li>';
	  		}
		}
		//else if admin not required
		else
		{
			//show link
			echo '<li class="navbar"><a class="active" href="'.$active_link.'">'.$active.'</a></li>';
		}

		//define array variables from parameters
	  	$extra_name = array($extra1, $extra2, $extra3);
	  	$extra_link = array($extra1_link, $extra2_link, $extra3_link);
	  	$extra_adminrequired = array($extra1_adminrequired, $extra2_adminrequired, $extra3_adminrequired);
	  	$arrlength = count($extra_name);

	  	//for loop through array
		for ($i = 0; $i < $arrlength; $i++)
		{
			//if extra_name, extra_link, or _extra_adminrequired are not NULL
			if($extra_name[$i] != NULL OR $extra_link[$i] != NULL OR $extra_adminrequired[$i] != NULL)
			{
				//if admin is required for link
				if($extra_adminrequired[$i] == 1)
				{
					//if user is admin
					if($admin_show == 1)
					{
						//show link
						echo '<li class="navbar"><a href="'.$extra_link[$i].'">'.$extra_name[$i].'</a></li>';
					}
				}
				//else if admin not required
				else
				{
					//who link
					echo '<li class="navbar"><a href="'.$extra_link[$i].'">'.$extra_name[$i].'</a></li>';
				}
			}
		}	
		echo '</ul>
		</div>';
		//close conn
		$conn->close();
	}
?>