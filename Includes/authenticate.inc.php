<?php
require_once('connection.inc.php');
$conn = dbConnect('read');
// get the username's details from the database
$sql = 'SELECT staff_id,lastname, firstname, nickname, role_name, salt, pwd, str, store_id FROM get_staff WHERE username = ?';
// initialize and prepare statement
$stmt = $conn->stmt_init();
$stmt->prepare($sql);
// bind the input parameter
$stmt->bind_param('s', $username);
// bind the result, using a new variable for the password
$stmt->bind_result($staffId, $lastname, $firstname, $nickname, $role, $salt, $storedPwd, $storeAddress, $storeId);
$stmt->execute();
$stmt->fetch();
// encrypt the submitted password with the salt and compare with stored password
if (sha1($password . $salt) == $storedPwd) {
    $_SESSION['authenticated'] = true;
    // get the time the session started
    $_SESSION['start'] = time();
    $_SESSION['username'] = $username;
    $_SESSION['store'] = $storeAddress;
    $_SESSION['store_id'] = $storeId;
    $_SESSION['role'] = $role;
    $_SESSION['lastname'] = $lastname;
    $_SESSION['firstname'] = $firstname;
    $_SESSION['nickname'] = $nickname;
    $_SESSION['staff_id'] = $staffId;
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $_SESSION['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $_SESSION['remote_addr'] = $_SERVER['REMOTE_ADDR'];
    }
    session_regenerate_id(false);
    if (isAndroidApp() && $redirectName === "") {
        echo 'authenticated';
    } else {
        header("Location: $redirectName");
    }
    exit ;
} else {
    // if no match, prepare error message
    $error = 'Invalid username or password';
}
