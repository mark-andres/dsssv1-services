<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated'])) {
    exit ;   // fail silently
}

require('../Includes/connection.inc.php');
$conn = dbConnect('read');

$storeId = 0;
if (isset($_GET['storeId'])) {
    $storeId = $_GET['storeId'];
} else {
    $storeId = $_SESSION['store_id'];
}
$columns = 'staff_id, lastname, firstname, nickname, role_name, username';
$sql = "SELECT $columns FROM get_staff WHERE store_id = $storeId ORDER BY role_name, lastname, firstname;";

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
