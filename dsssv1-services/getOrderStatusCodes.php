<?php
require('../Support/utils.php');
beginServiceFunction();

require('../Includes/connection.inc.php');
$conn = dbConnect('read');

$sql = 'SELECT order_status_id AS orderStatusId, order_status AS orderStatus FROM order_status ORDER BY order_status_id ASC;';

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);