<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated']) && $_SESSION['role'] != 'administrator') {
    exit ;   // fail silently
}

require('../Includes/connection.inc.php');
$conn = dbConnect('read');

$sql = 'SELECT store_id, street_number, street, city, state_code, zip, phone
        FROM store
        JOIN address USING (address_id)
        JOIN phone USING (phone_id);';

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
