<?php
require('../Support/utils.php');
beginServiceFunction();

$customerId = 0;
$count = setVarsFromArray($_POST, 
    array('phoneId' => &$phoneId, 'addressId' => &$addressId, 'lastname' => &$lastname, 'firstname' => &$firstname, 'customerComment' => &$comment)
);
if ($count) {
    require('../Includes/connection.inc.php');
    $conn = dbConnect('write');
    $stmt = $conn->stmt_init();
    
    $sql = 'CALL addCustomer(?, ?, ?, ?, ?);';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('iisss', $phoneId, $addressId, $lastname, $firstname, $comment) || dbErrorExit('bind_param() failed ' . $stmt->error); 
        
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    $stmt->bind_result($customerId) || dbErrorExit('bind_result() failed ' . $stmt->error);
    $stmt->fetch() || dbErrorExit('fetch() failed ' . $stmt->error);
    echo json_encode(array('result' => 1, 'customerId' => $customerId)); 
}

