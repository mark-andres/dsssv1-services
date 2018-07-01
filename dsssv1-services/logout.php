<?php
require ('../Support/utils.php');
startSession();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 86400, '/');
}
// end session
session_destroy();
$result = array();
$result['authenticated'] = false;
echo json_encode($result);