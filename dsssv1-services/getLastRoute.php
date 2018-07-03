<?php
require('../Support/utils.php');
beginServiceFunction();

require('../Includes/connection.inc.php');
$conn = dbConnect('read');
$staffId = $_GET['staffId'];

$sql = "SELECT route_id FROM route WHERE staff_id = $staffId and DATE(created) = CURDATE() ORDER BY created desc LIMIT 0,1;";

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
