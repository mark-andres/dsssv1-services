<?php
require('../Support/utils.php');
beginServiceFunction();

function createRoute($conn, $driverId, $mapServiceRoute) {
    $stmt = $conn->stmt_init();
    
    $sql = 'INSERT INTO route (`staff_id`,`created`,`map_service_route`) VALUES (?, ?, ?)';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $created = new DateTime;
    $createdStr = $created->format('Y-m-d H:i:s');
    
    $stmt->bind_param('iss', $driverId, $createdStr, $mapServiceRoute) || dbErrorExit('bind_param() failed ' . $stmt->error);
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    
    $routeId = $stmt->insert_id;
    
    $stmt->close();
    
    return array($routeId, $created);
}

function updateOrder($conn, $orderId, $routeId, $routeStep, $delivered) {
    $orderStatus = 1;
    
    if ($delivered) {
        $orderStatus = 2;
    }
    
    $stmt = $conn->stmt_init();
    
    $sql = 'UPDATE `order` SET order_status_id = ?, route_id = ?, route_step = ?, delivered = ? WHERE order_id = ?';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('iiisi', $orderStatus, $routeId, $routeStep, $delivered, $orderId) || dbErrorExit('bind_param() failed ' . $stmt->error);
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    
    $affectedRows = $stmt->affected_rows;
    
    $stmt->close();  
    
    return $affectedRows;  
}

$routeUpdates = json_decode($_POST['routeUpdates']);

require('../Includes/connection.inc.php');
$conn = dbConnect('write');
$response = new stdClass;
$response->routes = array();
$created = null;

for ($i = 0; $i < count($routeUpdates->routes); $i++) {
    $route = $routeUpdates->routes[$i];
    $routeId= $route->routeId;
    if (!$routeId) {
        list($routeId, $created) = createRoute($conn, $route->driverId, $route->mapServiceRoute);
    }
    
    for ($j = 0; $j < count($route->steps); $j++) {
        $step = $route->steps[$j];
        updateOrder($conn, $step->orderId, $routeId, $step->routeStep, $step->delivered);
    }
    
    $response->routes[] = (object)array('routeId' => $routeId, 'created' => $created, 'driverId' => $route->driverId);
}

for ($i = 0; $i < count($routeUpdates->unassigned); $i++) {
    $orderInfo = $routeUpdates->unassigned[$i];
    updateOrder($conn, $orderInfo->orderId, NULL, NULL, NULL);
}

$response->result = 1;

echo json_encode($response);
