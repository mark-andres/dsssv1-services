<?php
require('../Support/utils.php');
beginServiceFunction();

$member = json_decode($_POST['obj']);
$username = trim($member->username);
$password = trim($member->password);
$retyped = trim($member->confirm);
require_once ('../Classes/DSSS/CheckPassword.php');
$usernameMinChars = 5;
$errors = array();
if (strlen($username) < $usernameMinChars) {
    $errors[] = "Username must be at least $usernameMinChars characters.";
}
if (preg_match('/\s/', $username)) {
    $errors[] = 'Username should not contain spaces.';
}
$checkPwd = new DSSS_CheckPassword($password);
$passwordOK = $checkPwd->check();
if (!$passwordOK) {
    $errors = array_merge($errors, $checkPwd->getErrors());
}
if ($password != $retyped) {
    $errors[] = "Your passwords don't match.";
}
if (!$errors) {
    // include the connection file
    require_once ('../Includes/connection.inc.php');
    $conn = dbConnect('write');
    // create a salt using the current timestamp
    $salt = time();
    // encrypt the password and salt
    $pwd = sha1($password . $salt);
    
    // prepare SQL statement
    if ($member->staffId) {
        $sql = 'UPDATE staff SET username = ?, salt = ?, pwd = ?, store_id = ?, lastname = ?, firstname = ?, nickname = ?, role_id = ?
                WHERE staff_id = ?';    
    } else {
        $sql = 'INSERT INTO staff (username, salt, pwd, store_id, lastname, firstname, nickname, role_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
    }
    
    $stmt = $conn->stmt_init();
    $stmt = $conn->prepare($sql);
    // bind parameters and insert the details into the database
    if ($member->staffId) {
        $stmt->bind_param('sisisssii', $username, $salt, $pwd, $member->storeId, 
            $member->lastname, $member->firstname, $member->nickname, $member->role, $member->staffId
        );        
    } else {
        $stmt->bind_param('sisisssi', $username, $salt, $pwd, $member->storeId, 
            $member->lastname, $member->firstname, $member->nickname, $member->role
        );
    }
    $stmt->execute();
    if ($stmt->affected_rows == 1) {
        $success = "$username has been registered. You may now log in.";
    } elseif ($stmt->errno == 1062) {
        $errors[] = "$username is already in use. Please choose another username.";
    } else {
        $errors[] = 'Sorry, there was a problem with the database.';
    }
}
if ($errors) {
    $result = array('result' => 0, 'errors' => $errors);
} else {
    $result = array('result' => 1, 'success' => $success);
}

echo json_encode($result);

