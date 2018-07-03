<?php
require('../Support/utils.php');
beginServiceFunction();

require('../Includes/connection.inc.php');
$conn = dbConnect('write');

require('../Support/dbAccess.php');

$store = json_decode($_POST['obj']);
$result = saveStore($conn, $store);

echo json_encode($result);

