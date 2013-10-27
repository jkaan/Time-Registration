<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
	<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
	<title>Start Page</title>
</head>
<body>
	<?php if(isset($_POST['userId'])) {
		echo $_POST['userId'];
		echo "<br>";
		echo $_POST['userNaam'];
		echo "<br>";
		echo $_POST['rolNaam'];
		echo "<br>";
	}
	?>
	<div data-role="header"><h1>Uren Registratie</h1></div>

	<div data-role="content" id="content"><?php echo $page; ?></div>

</body>
</html>