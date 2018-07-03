<?php
require('../Support/utils.php');
beginServiceFunction();

$orderId = 0;
$count = setVarsFromArray($_POST, 
    array('phoneId' => &$phoneId, 'addressId' => &$addressId, 'customerId' => &$customerId, 'storeId' => &$storeId, 
    'distance' => &$distance, 'amount' => &$amount, 'orderComment' => &$orderComment, 'estimate' => &$estimate)
);
if ($count) {
    require('../Includes/connection.inc.php');
    $conn = dbConnect('write');
    $stmt = $conn->stmt_init();
    
    $sql = 'CALL addOrder(?, ?, ?, ?, ?, ?, ?, ?);';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('iiiiddsi', 
        $phoneId, $addressId, $customerId, $storeId, $distance, $amount, $orderComment, $estimate
    ) || dbErrorExit('bind_param() failed ' . $stmt->error); 
        
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    $stmt->bind_result($orderId) || dbErrorExit('bind_result() failed ' . $stmt->error);
    $stmt->fetch() || dbErrorExit('fetch() failed ' . $stmt->error);
    echo json_encode(array('result' => 1, 'orderId' => $orderId)); 
}

