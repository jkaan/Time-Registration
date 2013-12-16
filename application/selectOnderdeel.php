<?php
	require('classes/database.class.php');
	$db = Database::getInstance();
	//$_POST['cursus_id'] = 1;
	$statement = $db->prepare("SELECT onderdeel_Id, onderdeel_Name FROM Onderdeel WHERE Cursus_cursus_Id = ".$_POST['cursus_id']);
	$statement->execute();
	$array = $statement->fetchALL(PDO::FETCH_ASSOC);
	// if($array !=0)
	// {
		// foreach($array as $row)
		// {
			// echo '<option value="'.$row['onderdeel_Id'].'">'.$row['onderdeel_Name'].'</option>';
		// }
	// }else
		// echo "<option value='0'>--Select Onderdeel--</option>";
		echo json_encode($array);
?>