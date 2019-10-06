<?php
	
 	//require the following php files
	require 'php_functions/head.php';
	require 'php_functions/navbar.php';

	//start session
	session_start();
	//call head() function and title page 'Westpac Login'
	head("Westpac Login");
	echo "<body>";
	//call navbar() function and set appropriate link
	navbar("Login", "index.php", 0);

	//echo out html form
	echo "<script src=javascript_functions/index_formValidation.js></script>
			<section>
				<form id='login' class='formpadding' name='login' onsubmit='return index_formValidation()' action='index_check.php' method='post'>
					<br />
					<br />
					<label for='eID'>Employee eID</label>
					<br />
					<br />
					<input class='input-field input-width-small' type='text' id='eID' name='eID' size='10' required='required' />
					<br />
					<label for='password'>Password</label>
					<br />
					<br />
					<input class='input-field input-width-small' type='password' id='password' name='password' size='30' required='required' />
					<br />
					<input class='btn btn-primary' type='submit' value='submit' />
				</form>
			</section>
			</body>
	</html>";
?>