<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated']) || !isset($_POST)) {
    exit ;   // fail silently
}

require('connection.inc.php');
$conn = dbConnect('write');

require('../Support/dbAccess.php');
$parms = $_POST;
$count = setVarsFromArray($parms, array(
    'updateAddress' => &$updateAddress, 'updateCustomer' => &$updateCustomer, 'addressId' => &$addressId, 'customerId' => &$customerId, 'locationId' => &$locationId
));

if ($updateAddress) {
    if (!isset($parms['addressId']) || !$parms['addressId']) {
        dbErrorExit('address update requested but no address id specified');
    }
    $result = updateAddress($conn, $parms);
    if ($result > 1) {
        dbErrorExit('updateAddress affected invalid number of rows (' . $result . ')');
    }
}

if ($updateCustomer) {
    if (!$parms['customerId']) {
        dbErrorExit('customer update requested but no customer id specified');
    }
    $result = updateCustomer($conn, $parms);
    if ($result != 1) {
        dbErrorExit('updateCustomer affected invalid number of rows (' . $result . ')');
    }
}

$newAddress = FALSE;
if (!$addressId) {
    $result = addAddress($conn, $parms);
    if ($result['result'] != 1) {
        dbErrorExit($result['error']);
    }
    $parms['addressId'] = $addressId = $result['addressId'];
    $parms['locationId'] = $locationId = $result['locationId'];
    $newAddress = TRUE;
}

setVarsFromArray($parms, array('lastname' => &$lastname, 'firstname' => &$firstname));
if ($customerId && $newAddress) {                           // the customer already exists but the address is new
    assocCustomerAddress($conn, $customerId, $addressId);   // associate the customer with the address
} elseif (!$customerId && ($lastname || $firstname)) {      // the customer doesn't exist but there is customer info
    $result = addCustomer($conn, $parms);
    if ($result['result'] != 1) {
        dbErrorExit($result['error']);
    }
    $parms['customerId'] = $customerId = $result['customerId'];
}

$result = addPhone($conn, $parms);
$result['addressId'] = $addressId;
$result['customerId'] = $customerId;
if ($locationId) {
    $result['locationId'] = $locationId;
}
