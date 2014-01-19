<?php

require "../vendor/autoload.php";

use Application\Config\Database;

$db = Database::getInstance();
$statement = $db->prepare("SELECT onderdeel_Id, onderdeel_Name FROM Onderdeel WHERE Cursus_cursus_Id = ".$_POST['cursus_id']);
$statement->execute();
$array = $statement->fetchALL(PDO::FETCH_ASSOC);

echo json_encode($array);
?>