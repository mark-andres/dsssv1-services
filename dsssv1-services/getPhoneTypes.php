<?php
require('../Support/utils.php');
beginServiceFunction();

require('../Includes/connection.inc.php');
$conn = dbConnect('read');

$sql = 'SELECT phone_type_id AS phoneTypeId, phone_type_name AS phoneTypeName FROM phone_type ORDER BY phone_type_id ASC;';

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);