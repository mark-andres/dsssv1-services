<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated']) || !isset($_POST)) {
    exit ;   // fail silently
}

$addressId = 0;
$count = setVarsFromArray($_POST, array('str' => &$str, 'street' => &$street, 'streetNumber' => &$streetNumber, 'unit' => &$unit,
    'locationId' => &$locationId, 'location' => &$location, 'locationType' => &$locationType, 'county' => &$county,
    'city' => &$city, 'state' => &$state, 'zip' => &$zip, 'comment' => &$comment));
if ($count) {
    require('../Includes/connection.inc.php');
    $conn = dbConnect('write');
    $stmt = $conn->stmt_init();
    
    $sql = 'CALL addAddress(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('ssssssssisis', 
        $str, $streetNumber, $street, $city, $county, $state, $zip, $unit, $locationId, $location, $locationType, $comment
    ) || dbErrorExit('bind_param() failed ' . $stmt->error); 
        
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    $stmt->bind_result($addressId, $locationId) || dbErrorExit('bind_result() failed ' . $stmt->error);
    $stmt->fetch() || dbErrorExit('fetch() failed ' . $stmt->error);
    echo json_encode(array('result' => 1, 'addressId' => $addressId, 'locationId' => $locationId)); 
}

