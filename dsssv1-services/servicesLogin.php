<?php
require('../Support/utils.php');
$error = '';
startSession();
$username = trim($_POST['username']);
$password = trim($_POST['pwd']);
$_SESSION['timezone'] = trim($_POST['timezone']);
$redirectName = '';
require_once ('../Includes/authenticate.inc.php');
$result = array();
if (isset($error) && $error != '') {
    $result['error'] = $error;
} else {
    $result['start'] = $_SESSION['start'];
    $result['username'] = $_SESSION['username'];
    $result['store'] = $_SESSION['store'];
    $result['storeId'] = $_SESSION['store_id'];
    $result['role'] = $_SESSION['role'];
    $result['lastname'] = $_SESSION['lastname'];
    $result['firstname'] = $_SESSION['firstname'];
    $result['nickname'] = $_SESSION['nickname'];
    $result['staff_id'] = $_SESSION['staff_id'];
}
echo json_encode($result);