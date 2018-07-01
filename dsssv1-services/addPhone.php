<?php
require('../Support/utils.php');
beginServiceFunction();

$phoneId = 0;
$count = setVarsFromArray($_POST, 
    array('phone' => &$phone, 'phoneTypeId' => &$phoneTypeId, 'addressId' => &$addressId, 'customerId' => &$customerId)
);
if ($count) {
    require('../Includes/connection.inc.php');
    $conn = dbConnect('write');
    $stmt = $conn->stmt_init();
    
    $sql = 'CALL addPhone(?, ?, ?, ?);';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('siii', $phone, $phoneTypeId, $addressId, $customerId) || dbErrorExit('bind_param() failed ' . $stmt->error); 
        
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    $stmt->bind_result($phoneId) || dbErrorExit('bind_result() failed ' . $stmt->error);
    $stmt->fetch() || dbErrorExit('fetch() failed ' . $stmt->error);
    echo json_encode(array('result' => 1, 'phoneId' => $phoneId)); 
}

