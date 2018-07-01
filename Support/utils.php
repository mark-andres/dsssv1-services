<?php
function getApplicationRoot() {
    $parts = explode('.', $_SERVER['SERVER_NAME']);
    $name = '';
    if ($parts[0] == 'localhost' || $parts[0] == 'www' || is_numeric($parts[0])) {
        $name = getApplicationName();   // use base directory name
    }
    if ($name == '') {      // if we're using a sub-domain
        return '/';         // the root is '/'
    } else {
        return '/' . $name . '/';
    }
}

function getApplicationName() {
    $urlparts = parse_url($_SERVER['SCRIPT_NAME']);
    $parts = explode('/', $urlparts['path']);
    array_shift($parts);    // remove empty first part
    if (count($parts) == 1) {
        $name = '';
    } else {
        $name = $parts[0];
    }
    return $name;
}

function getSessionName() {
    $parts = explode('.', $_SERVER['SERVER_NAME']);
    if ($parts[0] == 'localhost' || $parts[0] == 'www' || is_numeric($parts[0])) {
        $name = getApplicationName();   // use base directory name
    } else {
        $name = $parts[0];              // use sub-domain name
    }
    if ($name == '') {
        return $parts[0];
    } else {
        return $name;
    }
}

function setVarsFromArray($arr, $parms) {
    $count = 0;
    if (!isset($arr) || !is_array($arr)) {
        return 0;
    }
    foreach (array_keys($parms) as $key) {
        if (array_key_exists($key, $arr)) {
            $parms[$key] = $arr[$key];
            if (is_array($parms[$key])) {
                for ($i = 0; $i < count($parms[$key]); $i++) {
                    if (!isset($parms[$key][$i])) {
                        $parms[$key][$i] = NULL;
                    }
                }
            }
            $count++;
        } else {
            $parms[$key] = NULL;
        }
    }
    return $count;   
}

function dbErrorExit($errorMsg) {
    echo json_encode(array('result' => 0, 'error' => $errorMsg));
    exit();   
}

function isMobile()
{   
    if (preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sagem|sharp|sie-|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT']))
        return true;
    else
        return false;
}

function isiOS()  {
    if (preg_match('/(iphone|ipad|ipod)/i', $_SERVER['HTTP_USER_AGENT']))
        return true;
    else
        return false;    
}

function isAndroidApp() {
    if (preg_match('/^\$\$DSSS\$1.0\$/', $_SERVER['HTTP_USER_AGENT'])) {
        return true;
    } else {
        return false;
    }
}

function getConfig($sessionName) {
    $configFile = $_SERVER['DOCUMENT_ROOT'] . '/config/' . $sessionName . '.ini';
    return parse_ini_file($configFile);
}

function startSession() {
    $sessionName = getSessionName();
    $config = getConfig($sessionName);
    ini_set('session.gc_probability', 0);           // keep sessions alive
    session_save_path($config['session_dir']);
    session_name($sessionName);
    session_start();
    ob_start();
}

function beginServiceFunction() {
    startSession();
    if (!isset($_SESSION['authenticated'])) {
        http_response_code(401);
        exit ;   
    }
}