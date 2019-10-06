<?php


//create head() function with the title of a webpage as a parameter
	function head($title) 
	{

	echo '<!-- define !DOCTYPE for HTML5 -->
		<!DOCTYPE html>
		<!-- set the language of the webpage to English -->
		<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
			<head>
				<!-- set charset (character set) to UTF-8 -->
				<meta charset="UTF-8" />
				<!-- set viewport for responsive design -->
				<meta name="viewport" content="width=device-width, initial-scale=1.0" />
				<!-- link to styles.css file -->
				<link rel="stylesheet" type="text/css" href="css/styles.css" />
				<!-- defining the title of the page for the window/tab name -->
				<title>'.$title.'
				</title>
			</head>';
	}
?>