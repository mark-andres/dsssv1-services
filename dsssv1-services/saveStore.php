<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated']) || !isset($_POST)) {
    exit ;   // fail silently
}

require('../Includes/connection.inc.php');
$conn = dbConnect('write');

require('../Support/dbAccess.php');

$store = json_decode($_POST['obj']);
$result = saveStore($conn, $store);

echo json_encode($result);

