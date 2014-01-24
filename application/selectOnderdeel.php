<?php

/**
 * This file is responsible for getting all of the assignments of a specific course
 * It's used while a student fills in it's hours
 *
 * A student chooses a course, after he clicks on the course this file is used to get all of the assignments
 * Then he can choose an assignment and fill in the rest.
 */

require "../vendor/autoload.php";

use Application\Config\Database;

$db = Database::getInstance();
$statement = $db->prepare("SELECT onderdeel_Id, onderdeel_Name FROM Onderdeel WHERE Cursus_cursus_Id = ".$_POST['cursus_id']);
$statement->execute();
$array = $statement->fetchALL(PDO::FETCH_ASSOC);

echo json_encode($array);
?>