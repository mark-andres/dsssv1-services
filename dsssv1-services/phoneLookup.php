<?php
require('../Support/utils.php');
beginServiceFunction();

if (isset($_GET['phone'])) {
    require('../Includes/connection.inc.php');
    $conn = dbConnect('read');

    $phone = $_GET['phone'];
    $sql = "SELECT * FROM get_phone_addresses WHERE phone = '$phone';";
    ($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
}