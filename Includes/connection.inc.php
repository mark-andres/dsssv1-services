<?php
function dbConnect($usertype) {
    $config = getConfig(getSessionName());
    $host = $config['host'];
    $db = $config['database'];
    if ($usertype == 'read') {
        $user = $config['reader'];
        $pwd = $config['reader_password'];
    } elseif ($usertype == 'write') {
        $user = $config['writer'];
        $pwd = $config['writer_password'];
    } else {
        exit('Unrecognized connection type');
    }
    $conn = new mysqli($host, $user, $pwd, $db) or die('Cannot open database');
    
    // timezone stuff
    $timezone = $_SESSION['timezone'];
    date_default_timezone_set('Etc/GMT+' . $timezone);
    $conn->query("SET time_zone='-" . $timezone . ":00';");
    
    return $conn;
}
