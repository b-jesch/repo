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

function delTree($dir, $prefix='') {

    function delContent($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delContent("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    $cdir = getcwd();
    if (!empty($prefix) and is_dir($prefix)) {
        chdir($prefix);
    }
    delContent($dir);
    chdir($cdir);
}


# Function calculating numeric version numbers from version strings
# assume that version is not above 999.999.999 eg myaddon-999.999.999.zip
# returns numeric value: 3.10.1 ==> 3010001000 (3.010.001.000) or
#                        4.1    ==> 4001000000 (4.001.000.000)

function calculateNumVersion($versionstring) {
    preg_match_all('/[0-9]{1,3}/m', $versionstring, $version, PREG_SET_ORDER, 0);

    $vs = '';
    foreach ($version as $sv) {
        $vs .= $sv[0].'.';
    }

    $subversions = explode('.', $vs);
    $numvers = 0;
    $multiplier = 1000000000;
    foreach ($subversions as $subvers) {
        $numvers += intval($subvers) * $multiplier;
        $multiplier /= 1000;
    }
    return $numvers;
}

function scanFolder($folder, $exceptions) {
    if (is_dir($folder) and is_array(scandir($folder))) {
        return array_values(array_filter(array_diff(scandir($folder), $exceptions)));
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

function unpackZip($zipfile) {
    $path = pathinfo($zipfile, PATHINFO_DIRNAME).'/';
    $zipfile = pathinfo($zipfile, PATHINFO_BASENAME);

    $zip = new ZipArchive();
    $zip->open($path.$zipfile);

    if ($zip->status == ZipArchive::ER_OK) {
        $files = array('addon.xml', 'fanart.jpg', 'icon.png', 'icon.jpg', 'changelog.txt');
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $index = array_search(basename($zip->statIndex($i)['name']), $files);
            if ($index === false) continue;

            $zip->extractTo($path, $zip->statIndex($i)['name']);
            rename($path.$zip->statIndex($i)['name'], $path.basename($zip->statIndex($i)['name']));
            unset($files[$index]);
        }
        $zip->close();

        $icon = $path . 'icon.png';
        if (pathinfo($zipfile, PATHINFO_EXTENSION) == 'apk') {
            copy(ADDONFOLDER.REPO_TEMPLATES.DEFAULT_APK_ICON, $path.'icon.png');
        } else {
            if (is_file($path . 'icon.jpg')) $icon = $path . 'icon.jpg';
            elseif (!is_file($path . 'icon.png')) {
                copy(ADDONFOLDER . REPO_TEMPLATES . DEFAULT_ADDON_ICON, $path . 'icon.png');
            }
        }
        return $icon;
    }
    return false;
}

function createPropertyList($property) {
    $addondirs = getAllAddonDirs();
    $plist = array();
    foreach ($addondirs as $addondir) {
        if (is_dir($addondir)) {
            $meta = array_merge(glob($addondir . '/*.zip'), glob($addondir . '/*.apk'));
            foreach ($meta as $item) {
                $addon = new Addon($item);
                $addon->read();
                $plist[] = $addon->$property;
            }
        }
    }
    $pl = array_unique($plist);
    sort($pl, SORT_STRING);
    return $pl;
}

function getAllAddonDirs() {
    $addondirs = array();
    foreach (VERSION_DIRS as $version) {
        $v_dirs = scanFolder(ADDONFOLDER.$version.DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
        if ($v_dirs) {
            foreach($v_dirs as $v) {
                if (is_dir(ADDONFOLDER.$version.DATADIR.$v)) $addondirs[] = ADDONFOLDER.$version.DATADIR.$v;
            }
        }
    }
    return $addondirs;
}

function createThumb($storage_path, $source, $status) {
    if (is_file($source)) {
        $image = getimagesize($source);

        if ($image[0]/$image[1] >= 1) {
            $x = TBN_X;
            $y = TBN_X * $image[1]/$image[0];
        } else {
            $y = TBN_Y;
            $x = TBN_Y * $image[0]/$image[1];
        }

        if ($image[2] == IMAGETYPE_GIF) {
            $ram = imagecreatefromgif($source);
        } elseif ($image[2] == IMAGETYPE_JPEG) {
            $ram = imagecreatefromjpeg($source);
        } elseif ($image[2] == IMAGETYPE_PNG) {
            $ram = imagecreatefrompng($source);
        }
        $ram_tbn = imagecreatetruecolor($x, $y);
        imagecopyresampled($ram_tbn, $ram, 0, 0, 0,0,
            $x, $y, $image[0], $image[1]);

        if ($status & BROKEN) {
            $flag_image = imagecreatefrompng(FLAG_BROKEN);
            $flag_properties = getimagesize(FLAG_BROKEN);
            imagecopyresampled($ram_tbn, $flag_image,$x - $flag_properties[0],0,0,0,
                $flag_properties[0], $flag_properties[1], $flag_properties[0], $flag_properties[1]);
        }
        if ($status & DEVTOOL) {
            $flag_image = imagecreatefrompng(FLAG_DEVTOOL);
            $flag_properties = getimagesize(FLAG_DEVTOOL);
            imagecopyresampled($ram_tbn, $flag_image,$x - $flag_properties[0],0,0,0,
                $flag_properties[0], $flag_properties[1], $flag_properties[0], $flag_properties[1]);
        }

        imagejpeg($ram_tbn, $storage_path.'icon.tbn', 90);

        imagedestroy($ram);
        imagedestroy($ram_tbn);
    }
}

function createItemView($column, $addon) {
    if ($column % CPR == 0 and $column > 0) echo '</tr><tr>'.PHP_EOL;
    $desc = empty($addon->description) ? $addon->summary : $addon->description;
    echo PHP_EOL.'<td class="item">' .PHP_EOL;
    echo PHP_TAB.'<table class="inner">'.PHP_EOL;
    echo PHP_TAB.'<tr><td class="header" colspan="3">'.$addon->name.' - '.convertHRV($addon->size).'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td rowspan="8" class="tbn_inner"><img class="thumb" src="'.$addon->thumb.'" width="'.TBN_X.'" height="'.TBN_Y.'">';
    if (strlen($desc) > 0) echo '<span>'.$desc.'</span>';
    echo '</td>';
    echo PHP_TAB.'<tr><td>Kategorie:</td><td class="data">'.$addon->category.'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Addon ID:</td><td class="data">'.$addon->id.'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Version:</td><td class="data">'.$addon->version.' ('.ucwords(substr($addon->tree, 0, -1)).')</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Autor:</td><td class="data">'.$addon->author.'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Upload:</td><td class="data">'.$addon->upload.'</td></tr>'.PHP_EOL;

    if ($_SESSION['state'] == 1) {
        echo PHP_TAB.'<tr><td>durch:</td><td class="data">'.$addon->provider.'</td></tr>'.PHP_EOL;
        echo PHP_TAB.'<tr><td>Downloads:</td><td class="data">'.$addon->downloads.' (total: '. $addon->downloads_total .')</td></tr>'.PHP_EOL;
    } else {
        echo PHP_TAB.'<tr><td>&nbsp;</td><td class="data">&nbsp;</td></tr>'.PHP_EOL;
        echo PHP_TAB.'<tr><td>&nbsp;</td><td class="data">&nbsp;</td></tr>'.PHP_EOL;
    }

    echo PHP_TAB.'<tr><td colspan="3" class="button_bg">'.PHP_EOL;

    $archive = $addon->getArchiveFiles();
    echo '<form name="d'.$column.'" id="d'.$column.'" action="'.ROOT.CONTROLLER.'" method="post">'.PHP_EOL;
    if ($addon->source != "") echo "<a href='".$addon->source."' target='_blank'><img alt='View source on Github' title='View source on Github' src='".ROOT.SYMBOL_GIT."' class='symbol_git'></a>";
    if ($_SESSION['state'] == 1 and $_SESSION['user'] == $addon->provider) {
        echo '<select form="d'.$column.'" name="c_item[]" class="select button_red" type="small" title="aktuelle und Archivversionen löschen" ';
        echo 'onchange="document.getElementById(\'d'.$column.'\').submit()">'.PHP_EOL;
        echo '<option value="" selected>* delete *</option>'.PHP_EOL;
        if ($archive) {
            foreach ($archive as $file) {
                echo '<option value="delete='.$file.'">'.basename($file).'</option>'.PHP_EOL;
            }
        }
        echo '<option value="delete='.$addon->file.'">'.basename($addon->file).'</option>'.PHP_EOL;
        echo '</select>'.PHP_EOL;
    }

    if ($archive) {
        echo '<select form="d'.$column.'" name="c_item[]" class="select" type="small" title="ältere Versionen aus dem Archiv downloaden" ';
        echo 'onchange="document.getElementById(\'d'.$column.'\').submit()">'.PHP_EOL;
        echo '<option value="" selected>* Archiv *</option>'.PHP_EOL;
        foreach ($archive as $file) {
            echo '<option value="download='.$file.'">'.basename($file).'</option>'.PHP_EOL;
        }
        echo '</select>'.PHP_EOL;
    }

    echo '<button form="d'.$column.'" name="c_item[]" type="submit" class="button" title="Download '.basename($addon->file).' (aktuelle Version)" value="download='.$addon->file.'">Download</button></td></tr></table>'.PHP_EOL;
    echo '</form>'.PHP_EOL;
    echo '</td>'.PHP_EOL;
}
?>