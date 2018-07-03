<?php
require('../Support/utils.php');
beginServiceFunction();

function updateOrder($conn, $orderId) {
    $orderStatus = 3;
    
    $stmt = $conn->stmt_init();
    
    $sql = 'UPDATE `order` SET order_status_id = ? WHERE order_id = ? AND route_id IS NULL';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('ii', $orderStatus, $orderId) || dbErrorExit('bind_param() failed ' . $stmt->error);
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    
    $affectedRows = $stmt->affected_rows;
    
    $stmt->close();  
    
    return $affectedRows;  
}

$orderId = $_GET['orderId'];

require('../Includes/connection.inc.php');
$conn = dbConnect('write');
$response = new stdClass;

$affectedRows = updateOrder($conn, $orderId);

$response->result = 1;
$response->affectedRows = $affectedRows;
$response->updated = date("Y-m-d H:i:s");

echo json_encode($response);
