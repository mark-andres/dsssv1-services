<?php
require('../Support/utils.php');
beginServiceFunction();

require('../Includes/connection.inc.php');
$conn = dbConnect('write');
$staffId = $_GET['staffId'];

$sql = "DELETE FROM staff WHERE staff_id = " . $staffId;

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

echo json_encode(array('result' => 1));
