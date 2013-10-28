<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
	<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
	<title>Start Page</title>
</head>
<body>
	<div data-role="header"><h1>Uren Registratie</h1></div>

	<div data-role="content" id="content">
		<?php echo $page; ?>
	</div>
	<?php if ($rol_id == 1){?>
	<a href="<?php echo BASE; ?>/student/<?php echo $id; ?>/uren/add" data-role="button">Uren invullen</a>
	<?php } elseif ($rol_id == 2){?>
	<a href="<?php echo BASE; ?>/docent/<?php echo $id; ?>/course/add" data-role="button">Nieuwe course</a>
	<?php }?>
</body>
</html>