<?php

function debug($obj) {
    if (DEBUG) {
        echo '<pre>';
        var_dump($obj);
        echo '</pre>';
    }
}

# Formatting for xml trees

function init_domxml() {
    $domxml = new DOMDocument('1.0', 'UTF-8');
    $domxml->preserveWhiteSpace = false;
    $domxml->formatOutput = true;
    return $domxml;
}

function getVersion($kv, $kd) {
    $i = 0;
    foreach ($kv as $version) {
        if ($_SESSION['version'] == $kd[$i]) {
            return $version;
        }
        $i++;
    }
    return "unknown";
}

function resetSession() {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', 0, $cookie['path'], $cookie['domain']);
    $_SESSION['state'] = 0;
    $_SESSION['user'] = '';
    session_destroy();
    if (!headers_sent()) {
        header('Location: ' . ROOT . CONTROLLER);
        exit();
    }
}

function passwdGen() {
    $base = '';
    for ($i = 0; $i < 8; $i++) {
        do {
            $val = rand(64,122);
        } while ($val > 90 and $val < 97);
        $base .= chr($val);
    }
    return $base;
}

function delTree($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

# Function calculating numeric version numbers from version strings
# assume that version is not above 999.999.999 eg myaddon-999.999.999.zip
# returns numeric value: 3.10.1 ==> 300010001 (300.010.001)

function calculateNumVersion($version) {
    $subvers = explode('.', $version);
    $subvers = array_reverse($subvers);
    $numvers = 0;
    $multiplier = 1;
    foreach ($subvers as $element) {
        $numvers += intval($element) * $multiplier;
        $multiplier *= 1000;
    }
    return $numvers;
}

function scanFolder($folder, $exceptions) {
    if (is_dir($folder) and is_array(scandir($folder))) return array_diff(scandir($folder), $exceptions);
    return false;
}

function convertHRV($value) {
    $units = array(' Bytes', ' kB', ' MB', ' GB', ' TB');
    $i = 0;
    while ($value > 1024) {
        $i++;
        $value = $value / 1024;
    }
    return sprintf('%01.2f', $value).$units[$i];
}

function createThumb($path, $icon) {
    $image = getimagesize($icon);

    if ($image[0]/$image[1] >= 1) {
        $x = TBN_X;
        $y = TBN_X * $image[1]/$image[0];
    } else {
        $y = TBN_Y;
        $x = TBN_Y * $image[0]/$image[1];
    }

    if ($image[2] == IMAGETYPE_GIF) {
        $ram = imagecreatefromgif($icon);
    } elseif ($image[2] == IMAGETYPE_JPEG) {
        $ram = imagecreatefromjpeg($icon);
    } elseif ($image[2] == IMAGETYPE_PNG) {
        $ram = imagecreatefrompng($icon);
    }
    $ram_tbn = imagecreatetruecolor($x, $y);
    imagecopyresampled($ram_tbn, $ram, 0, 0, 0,0,
        $x, $y, $image[0], $image[1]);

    imagejpeg($ram_tbn, $path.'icon.tbn');

    imagedestroy($ram);
    imagedestroy($ram_tbn);
}
?>