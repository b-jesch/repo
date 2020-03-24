<?php
require ('config.php');
require (CLASSES.'Addon.php');
require (FUNCTIONS.'functions.php');

# Session starten

session_start();

$c_pars = array_merge($_POST, $_GET, $_FILES);

if (!$_SESSION['state'] or $_SESSION['state'] == 0) {
    $_SESSION['state'] = 0;
}

if ($c_pars['login'] != '' and $c_pars['passwd'] != '') {
    $users = file(ROOT.USERS, FILE_IGNORE_NEW_LINES);
    foreach ($users as $user) {
        list($username, $passwd) = explode(':', $user);
        if ($username == $c_pars['login'] and
            $passwd == crypt($c_pars['passwd'], $passwd)) {
            $_SESSION['state'] = 1;
            $_SESSION['user'] = $username;
            break;
        }
    }
    if ($_SESSION['state'] == 1) {
        $c_pars['action'] = 'list';
    }
}

# determine Kodi Version, select first if undetermined

if (($c_pars['version'] != '') and (in_array($c_pars['version'], $version_dirs))) {
    $_SESSION['version'] = $c_pars['version'];
} elseif ($_SESSION['version'] == '') {
    $_SESSION['version'] = $version_dirs[0];
}

$i = 0;
foreach($version_dirs as $version) {
    if ($_SESSION['version'] == $version) break;
    $i++;
}
$_SESSION['version_name'] = $kodiversions[$i];

# Main Controller

if (isset($c_pars['item'])) {
    list($c_pars['action'], $c_pars['item']) = explode('=', $c_pars['item']);
}

switch ($c_pars['action']) {
    case 'list':
        require VIEWS.LISTVIEW;
        break;
    case 'impress':
        require VIEWS.IMPRESS;
        break;
    case 'upload':
        if ($_SESSION['state'] == 1) {
            require VIEWS.UPLOAD;
        } else {
            require VIEWS.LISTVIEW;
        }
        break;
    case 'login':
        require VIEWS.LOGINPAGE;
        break;
    case 'logout':
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', 0, $cookie['path'], $cookie['domain']);
        $_SESSION['state'] = 0;
        $_SESSION['user'] = '';
        session_destroy();
        require VIEWS.LISTVIEW;
        break;
    case 'upload_p2':
        if ($_SESSION['state'] == 1) {
            if ($c_pars['upload']['error'] == UPLOAD_ERR_NO_FILE) {
                require VIEWS . UPLOAD;
                break;
            }
            if ($c_pars['upload']['error'] == UPLOAD_ERR_OK) {
                $addon_name = $c_pars['upload']['name'];

                # Determine Addonname, Addonversion, be aware for multiple delimiters
                # and use only the last part for version

                $pieces = explode('-', basename($addon_name, ADDON_EXT));

                $addon_version = array_pop($pieces);
                $addon_basename = implode('-', $pieces);
                $addon_numversion = calculateNumVersion($addon_version);

                # handle special folders of Repo Addon

                if ($addon_basename == REPO_ID) {
                    $addon_dir = ADDONFOLDER . REPO_ID . '/';
                    $summaries = ADDONFOLDER;
                    $master = true;
                } else {
                    $addon_dir = ADDONFOLDER . $_SESSION['version'] . DATADIR . $addon_basename . '/';
                    $summaries = ADDONFOLDER . $_SESSION['version'];
                    $master = false;
                }

                if (!is_dir(TMPDIR)) mkdir(TMPDIR, 0755, true);

                if (!is_dir($addon_dir)) {

                    # new Addon

                    mkdir($addon_dir, 0755, true);
                    $success = move_uploaded_file($c_pars['upload']['tmp_name'], $addon_dir . $addon_name);
                    if ($success) {
                        $addon = new Addon($addon_dir . $addon_name, time());
                        $addon->tree = substr($_SESSION['version'], 0, -1);
                        $addon->provider = $_SESSION['user'];
                        $addon->create();
                    }
                } elseif (is_file($addon_dir.$addon_name)) {

                    # existing Addon, check overwrite option

                    if (isset($c_pars['overwrite'])) {
                        $success = move_uploaded_file($c_pars['upload']['tmp_name'], $addon_dir . $addon_name);
                        if ($success) {
                            $addon = new Addon($addon_dir . $addon_name, time());
                            $addon->read();
                        }
                    } else {
                        $errmsg = "Das Überschreiben vorhandener Addonversionen ist nicht zulässig! ";
                        $errmsg .= "Setzen Sie dazu die entsprechende Option im Upload-Dialog.";
                        delTree(TMPDIR);
                        require VIEWS . ERRORPAGE;
                        break;
                    }

                } else {

                    # update Addon
                    # check for existing files when overwrite option is not set

                    $files = glob($addon_dir.'*.zip');
                    $errmsg = '';
                    foreach ($files as $file) {
                        $version = explode('-',basename($file, ADDON_EXT));
                        $vn = array_pop($version);
                        if (calculateNumVersion($vn) >= $addon_numversion) {
                            $errmsg = "Die Versionsnummer des hochgeladenen Addons ist älter als die aktuell vorhandene Version. ";
                            $errmsg .= "Der Upload älterer Versionen ist nicht zulässig ($addon_version aka $vn)";
                            break;
                        }
                    }
                    if (!empty($errmsg)) {
                        delTree(TMPDIR);
                        require VIEWS.ERRORPAGE;
                        break;
                    }

                    # move existing addon version into archive

                    $files = glob($addon_dir . $addon_basename . '*.*');
                    if ($files) {
                        if (!is_dir($addon_dir . ARCHIVE)) mkdir($addon_dir . ARCHIVE, 0755, true);
                        foreach ($files as $file) rename($file, $addon_dir . ARCHIVE . basename($file));
                    }
                    $success = move_uploaded_file($c_pars['upload']['tmp_name'], $addon_dir . $addon_name);
                    if ($success) {
                        $addon = new Addon($addon_dir . $addon_name, time());
                        $addon->tree = substr($_SESSION['version'], 0, -1);
                        $addon->provider = $_SESSION['user'];
                        $addon->create();
                    }
                }

                if (!$success) {
                    $errmsg = "ZIP '<b>$addon_basename</b>' konnte nicht geöffnet werden. Upload ist fehlerhaft und wird gelöscht.";

                    delTree(TMPDIR);
                    unlink($addon_dir . $addon_name);
                    require VIEWS . ERRORPAGE;
                    break;
                }

                $zip = new ZipArchive();
                $zip->open($addon_dir . $addon_name);

                if ($zip->status == ZipArchive::ER_OK) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        if (in_array(basename($zip->statIndex($i)['name']), array('addon.xml', 'fanart.jpg', 'icon.png', 'changelog.txt'))) {
                            $zip->extractTo(TMPDIR, $zip->statIndex($i)['name']);
                            rename(TMPDIR . $zip->statIndex($i)['name'], $addon_dir . basename($zip->statIndex($i)['name']));
                        }
                    }
                    $zip->close();

                    # remove temporary folders (created by extraction of addon.xml, ...

                    delTree(TMPDIR);


                    # check for item presence (addon.xml, icon.png)

                    $addon->getAttrFromAddonXML();
                    createThumb($addon_dir, $addon_dir . 'icon.png');
                } else {
                    $errmsg = "Upload konnte nicht zum Repository hinzugefügt werden";
                    require VIEWS . ERRORPAGE;
                    break;
                }

                if ($master) {
                    $repo = new CreateRepoXML($summaries, REPO_ID);
                    $repo->createMasterXML();
                } else {
                    $repo = new CreateRepoXML($summaries, DATADIR);
                    $repo->createRepoAddonSummary();
                }
                $repo->createMD5();
            }
        }
        require VIEWS . LISTVIEW;
        break;
    case 'download':
        $addondirs = scanFolder(ADDONFOLDER.$_SESSION['version'].DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
        if ($addondirs) {
            foreach ($addondirs as $addondir) {
                $metafiles = glob(ADDONFOLDER.$_SESSION['version'].DATADIR.$addondir.'/*.zip');
                foreach ($metafiles as $metadata) {
                    $addon = new Addon($metadata);
                    $addon->read();
                    if ($addon->object_id != '' and $c_pars['item'] == $addon->object_id) $addon->download();
                }
            }
        }
        require VIEWS.LISTVIEW;
        break;
    case 'delete':
        if ($_SESSION['state'] == 1) {
            $addondirs = scanFolder(ADDONFOLDER.$_SESSION['version'].DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
            if ($addondirs) {
                foreach ($addondirs as $addondir) {
                    $metafiles = glob(ADDONFOLDER.$_SESSION['version'].DATADIR.$addondir.'/*.zip');
                    foreach ($metafiles as $metadata) {
                        $addon = new Addon($metadata);
                        $addon->read();
                        if ($addon->object_id != '' and $c_pars['item'] == $addon->object_id) {
                            $addon->delete();
                        }
                    }
                }
            }
            $repo = new CreateRepoXML(ADDONFOLDER.$_SESSION['version'], DATADIR);
            $repo->createRepoAddonSummary();
            $repo->createMD5();
        }
        require VIEWS.LISTVIEW;
        break;
    default:
        # Bootstrap
        require VIEWS.LISTVIEW;
}