<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated'])) {
    exit ;   // fail silently
}

require('../Includes/connection.inc.php');
$conn = dbConnect('read');
$stmt = $conn->stmt_init();

$sql = 'SELECT role_id AS roleId, role_name AS roleName FROM role ORDER BY role_id ASC;';
($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);