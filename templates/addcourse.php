<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
	<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
	
	<!-- DATEPICKER includes -->

	<link rel="stylesheet" href="jquery.ui.datepicker.mobile.css" /> 
	<script src="jQuery.ui.datepicker.js"></script>
	<script src="jquery.ui.datepicker.mobile.js"></script>
	<title>Uren</title>
</head>
<body>
	
	<div data-role="header"><h1><?php echo $page; ?></h1></div>

	<form data-ajax="false" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
				
		<!-- Veld voor coursenaam -->
		<label for="basic">Naam:</label>
		<input type="text" name="coursename" id="basic" data-mini="true" />
		
		<!-- Veld voor coursecode -->
		<label for="basic">Cursuscode:</label>
		<input type="text" name="coursecode" id="basic" data-mini="true" />		
		
		<!-- Submit knop -->
		<input type="submit" value="Opslaan" data-theme="b">
		</form>

</body>
</html>