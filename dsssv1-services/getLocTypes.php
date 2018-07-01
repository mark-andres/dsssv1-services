<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated'])) {
    exit ;   // fail silently
}

require('../Includes/connection.inc.php');
$conn = dbConnect('read');
$stmt = $conn->stmt_init();

$sql = 'SELECT loc_type_id AS locTypeId, loc_type_desc AS locDesc FROM location_type ORDER BY loc_type_id ASC;';

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);