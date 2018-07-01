<?php
require('../Support/utils.php');
$error = '';
if (isset($_POST['login'])) {
    startSession();
    $username = trim($_POST['username']);
    $password = trim($_POST['pwd']);
    $_SESSION['timezone'] = trim($_POST['timezone']);
    $redirectName = '';
    require_once ('../Includes/authenticate.inc.php');
}