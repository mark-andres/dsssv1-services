<?php
require('../Support/utils.php');
beginServiceFunction();

require('../Includes/connection.inc.php');
$conn = dbConnect('write');
$storeId = $_GET['storeId'];

$sql = "DELETE FROM store WHERE store_id = " . $storeId;

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

echo json_encode(array('result' => 1));
