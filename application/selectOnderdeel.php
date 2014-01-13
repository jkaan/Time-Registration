<?php
	require('classes/database.class.php');
	require('config.php');
	
	$db = Database::getInstance(DBNAME, DBHOST, DBUSER, DBPASS);
	//$_POST['cursus_id'] = 1;
	$statement = $db->prepare("SELECT onderdeel_Id, onderdeel_Name FROM Onderdeel WHERE Cursus_cursus_Id = ".$_POST['cursus_id']);
	$statement->execute();
	$array = $statement->fetchALL(PDO::FETCH_ASSOC);

		echo json_encode($array);
?>