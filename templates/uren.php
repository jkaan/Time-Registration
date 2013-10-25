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
		<!-- Veld voor het onderdeel-->
		<label for="select-choice-0" class="select">Onderdeel:</label>
		<select name="onderdeel" id="select-choice-0">
		   <option value="1">1</option>
		   <option value="2">2</option>
		   <option value="3">3</option>
		   <option value="4">4</option>
		</select>
		<!-- Veld voor de datum -->
		<label for="date">Datum:</label>
		<input type="date" width=" 30px;" name="date" id="date" value=""  />	
		
		<!-- Veld voor studielast -->
		<label for="basic">Studielast:</label>
		<input type="text" name="studielast" id="basic" data-mini="true" />
		
		<!-- Submit knop -->
		<input type="submit" value="Opslaan" data-theme="b">
		</form>

</body>
</html>