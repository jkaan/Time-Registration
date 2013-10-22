<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
	<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
	<title>Login</title>
</head>
<body>
	
	<div data-role="header"><h1>Login Page</h1></div>

	<form data-ajax="false" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
		<input type="text" name="username" id="username" placeholder="Username" />
		<input type="password" name="password" id="password" placeholder="Password" />
		<input type="submit" value="Login" data-theme="b">
	</form>

</body>
</html>