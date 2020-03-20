<?php

function debug($obj) {
    if (DEBUG) {
        echo '<pre>';
        var_dump($obj);
        echo '</pre>';
    }
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

function delTree($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function scanFolder($folder, $exceptions) {
    if (is_array(scandir($folder))) {
        return array_diff(scandir($folder), $exceptions);
    }
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