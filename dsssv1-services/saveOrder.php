<?php
require ('../Includes/saveAddress.inc.php');

if ($result['result']) {
    $phoneId = $parms['phoneId'] = $result['phoneId'];
    
    if (isset($parms['orderId'])) {
        $orderId = $parms['orderId'];
        $amount = $parms['amount'];
        $requestedBy = $parms['requestedBy'];
        $comment = $parms['orderComment'];
        $created = $parms['created'];
        
        updateOrder($conn, $orderId, $amount, $requestedBy, $comment, $customerId, $created);
        
        $result['updated'] = date("Y-m-d H:i:s");
        $result['result'] = 2;
    } else {
        $orderResult = addOrder($conn, $parms);
        if ($orderResult['result']) {
            $orderId = $orderResult['orderId'];  
            $created = $orderResult['created'];  
            for ($i = 1; $i < count($phoneId); $i++) {
                assocOrderPhone($conn, $orderId, $phoneId[$i]);
            }
            $result['orderId'] = $orderId;
            $result['created'] = $created;
            $result['updated'] = $created;
        } else {
            $result['result'] = 0;
        }
    }
    echo json_encode($result);  
}

