<?php

require ('config.php');
require (FUNCTIONS.'functions.php');
require (CLASSES.'Addon.php');

$c_pars = array_merge($_POST, $_GET, $_FILES);
debug($c_pars);

if (MAINTENANCE) $c_pars['action'] = '503';

# Handle direct Downloads

if (isset($c_pars['action']) and $c_pars['action'] == 'direct_dl') {

# Antiflood section

    $user_ip = $_SERVER['REMOTE_ADDR'];
    $flood_lockfile = FLOOD_LOCKDIR.md5($user_ip);
    $flood_entries = array();

    if (!file_exists(FLOOD_LOCKDIR)) mkdir(FLOOD_LOCKDIR, $mode=0755, $recursive=true);

    if (file_exists($flood_lockfile) and (time() - filemtime($flood_lockfile) > FLOOD_BAN_TIME)) {
        unlink($flood_lockfile);
    }

    # count flood requests

    if (file_exists(FLOOD_DB)) {
        $fh = fopen(FLOOD_DB, 'r');
        $flood_entries = array_merge($flood_entries, unserialize(fread($fh, filesize(FLOOD_DB))));
        fclose($fh);
    }
    if (isset($flood_entries[$user_ip])) {

        # prevent downloading the same file multiple times within FLOOD_REQ_TIMEOUT,
        # allow downloading different files (multiple addon updates from same IP)

        if ((time() - $flood_entries[$user_ip]['t'] < FLOOD_REQ_TIMEOUT) or ($flood_entries[$user_ip]['f'] == basename($c_pars['f']))) {
            $flood_entries[$user_ip]['c']++;
        } else {
            $flood_entries[$user_ip]['c'] = 1;
        }
    } else {
        $flood_entries[$user_ip]['c'] = 1;
    }
    $flood_entries[$user_ip]['f'] = basename($c_pars['f']);
    $flood_entries[$user_ip]['t'] = time();

    # write updated flood array

    $fh = fopen(FLOOD_DB, 'w');
    fwrite($fh, serialize($flood_entries));
    fclose($fh);

    if ($flood_entries[$user_ip]['c'] >= FLOOD_MAX_REQ) {
        $fh = fopen($flood_lockfile, 'w');
        fwrite($fh, serialize($flood_entries[$user_ip]));
        fclose($fh);

        header("HTTP/1.0 429 Too Many Requests", true, 429);
        exit();
    }

    # Manage direct downloads

    $file = pathinfo($c_pars['f'], PATHINFO_DIRNAME).'/'.urlencode(basename($c_pars['f']));
    $addon = new Addon($file);
    $addon->download();
    exit();
}

# :::BOOTSTRAP:::

# start session

session_start();
if (!isset($_SESSION['state']) or $_SESSION['state'] == 0) {
    $_SESSION['state'] = 0;
}

# Decrypt encrypted Routes

if (isset($c_pars['action'])) {
    foreach(ROUTE as $route) {
        $c = crypt($route, $c_pars['action']);
        if ($c == $c_pars['action']) {
            $c_pars['action'] = $route;
            break;
        }
    }
}

# create Master XML and Repo Addon XML if doesn't exists

if (!is_file(ADDONFOLDER.'addons.xml')) {

    # clear Repo-Addon Folder

    if (is_dir(ADDONFOLDER.REPO_ID)) delTree(ADDONFOLDER.REPO_ID);

    # copy files to Repo-Addon Folder from template folder

    mkdir(ADDONFOLDER.REPO_ID, 0755, true);

    $files = scanFolder(ADDONFOLDER.REPO_TEMPLATES, array('.', '..', ADDON_TEMPLATE, DEFAULT_ADDON_ICON, DEFAULT_APK_ICON));
    foreach($files as $file) copy(ADDONFOLDER.REPO_TEMPLATES.$file, ADDONFOLDER.REPO_ID.'/'.$file);
    $repo =new CreateRepoXML(ADDONFOLDER.REPO_TEMPLATES, ADDON_TEMPLATE);
    $repo->createAddonXML(ADDONFOLDER.REPO_ID.'/addon.xml');

    $files = glob(ADDONFOLDER.REPO_ID.'/*');
    $zip = new ZipArchive();
    $zip->open(ADDONFOLDER.REPO_ID.'/'.REPO_ID.'-'.REPOVERSION.ADDON_EXT, ZipArchive::CREATE);
    foreach($files as $file) $zip->addFile($file, REPO_ID.'/'.basename($file));
    $zip->close();

    $repo = new Addon(ADDONFOLDER.REPO_ID.'/'.REPO_ID.'-'.REPOVERSION.ADDON_EXT, time());
    $repo->name = REPONAME;
    $repo->id = REPO_ID;
    $repo->version = REPOVERSION;
    $repo->provider = PROVIDER;
    $repo->author = PROVIDER;
    $repo->create();

    # copy to webdav

    if (!file_exists(WEBDAV)) mkdir(WEBDAV, 0755);
    copy(ADDONFOLDER.REPO_ID.'/'.REPO_ID.'-'.REPOVERSION.ADDON_EXT, WEBDAV.REPO_ID.'-'.REPOVERSION.ADDON_EXT);

    $master = new CreateRepoXML(ADDONFOLDER, REPO_ID.'/');
    $master->createMasterXML();
    $master->createMD5();

    # create version folder and XML for certain Kodi versions as Kodi looks up at first in these
    # dependent on Kodi version. Kodi fails if these folders doesn't exist (maybe a bug?)

    foreach (VERSION_DIRS as $version_dir) {
        if (is_dir(ADDONFOLDER.$version_dir)) {
            if (is_file(ADDONFOLDER.$version_dir.'addons.xml')) continue;
        } else {
            mkdir(ADDONFOLDER.$version_dir, 0775, true);
            $repo = new CreateRepoXML(ADDONFOLDER.$version_dir, '');
            $repo->createRepoXML();
            $repo->createMD5();
        }
    }
}

# :::END OF BOOTSTRAP:::

if (isset($c_pars['login'])) {
    if (!empty($c_pars['user']) and !empty($c_pars['passwd'])) {
        $user = new User($c_pars['user']);
        if ($user->indb) $user->login($c_pars['passwd']);
        if ($user->success) {
            $_SESSION['state'] = 1;
            $_SESSION['user'] = $user->username;
            $_SESSION['isadmin'] = $user->isadmin;
            $c_pars['action'] = 'list';
        } else {
            $c_pars['action'] = 'login';
        }
    } else {
        $c_pars['action'] = 'login';
    }
} elseif (isset($c_pars['abort'])) $c_pars['action'] = 'list';


# determine Kodi Version, select first if undetermined

if (isset($_SESSION['version']) and isset($c_pars['version']) and ($c_pars['version'] != '') and (in_array($c_pars['version'], VERSION_DIRS))) {
    $_SESSION['version'] = $c_pars['version'];
} elseif (!isset($_SESSION['version'])) {
    $_SESSION['version'] = VERSION_DIRS[DEFAULT_TREE]; # Krypton
}

$i = 0;
foreach(VERSION_DIRS as $version) {
    if ($_SESSION['version'] == $version) break;
    $i++;
}
$_SESSION['version_name'] = KODI_NAMES[$i];

# Main Controller

if (isset($c_pars['c_item'])) {
    foreach($c_pars['c_item'] as $element) {
        if (empty($element)) continue;
        list($c_pars['action'], $c_pars['item']) = explode('=', $element);
        break;
    }
}

switch ($c_pars['action']) {
    case 'list':
        require VIEWS.LISTVIEW;
        break;
    case 'impress':
        require VIEWS.IMPRESS;
        break;
    case 'dsvgo':
        require VIEWS.DSGVO;
        break;
    case 'upload':
        if ($_SESSION['state'] == 1) {
            require VIEWS.UPLOAD;
        } else {
            require VIEWS.LISTVIEW;
        }
        break;
    case 'search':
        if (strlen($c_pars['item']) < 3) {
            $_SESSION['notice'] .= "Der Suchbegriff ist zu kurz. Es wird die Standardansicht angezeigt. Geben Sie wenigsten 3 Zeichen ein. ";
            $c_pars['action'] = 'list';
            $c_pars['scope'] = 'all';
            unset($c_pars['search']);
        }
        require VIEWS.LISTVIEW;
        break;

    case 'login':
        require VIEWS.LOGINPAGE;
        break;

    case 'logout':
        if ($_SESSION['state'] == 1) {
            $user = new User($_SESSION['user']);
            $user->logout();
        }
        # if it's impossible to send headers use listview instead
        require VIEWS.LISTVIEW;
        break;

    case 'upload_p2':
        if ($_SESSION['state'] == 1) {
            if ($c_pars['upload'] == '' or $c_pars['upload']['error'] == UPLOAD_ERR_NO_FILE) {
                require VIEWS.UPLOAD;
                exit();
            }
            if ($c_pars['upload']['error'] == UPLOAD_ERR_OK) {

                # :::PREREQUISITES:::

                $upload = $c_pars['upload']['name'];
                $rnddir = rand(1000, 9999).'/';
                mkdir(TMPDIR . $rnddir, 0755, true);

                # move and unpacking upload to TMPDIR, copy default icon to TMPDIR

                move_uploaded_file($c_pars['upload']['tmp_name'], TMPDIR.$rnddir.$upload);

                $icon = unpackZip(TMPDIR.$rnddir.$upload);
                if (!$icon) {
                    $_SESSION['notice'] .= 'Die Zip-Datei ist defekt und konnte nicht geöffnet werden! Der Upload wird verworfen. ';
                    delTree($rnddir, TMPDIR);
                    require VIEWS.UPLOAD;
                    exit();
                }

                # create addon object and thumbnail

                $addon = new Addon(TMPDIR.$rnddir.$upload, time());
                $addon->provider = ($_SESSION['isadmin']) ? $c_pars['provider'] : $_SESSION['user'];

                $addon->addon_types = AD_TYPES;
                $addon->addon_category = AD_CATEGORIES;
                $addon->python = AD_PYTHON_VERS;
                $addon->version_dirs = VERSION_DIRS;

                if (is_file(TMPDIR.$rnddir.'addon.xml')) {
                    if ($addon->getAttrFromAddonXML()) {
                        # missing xbmc.python attribute in addon.xml, search for tree in addon name, else
                        # assign to FALLBACK_TREE anywhere

                        if ($addon->tree === false) {
                            $_SESSION['notice'] .= "Die Version des Moduls 'xbmc.python' kann keiner Kodiversion zugeordnet werden. Der Upload wird verworfen. ";
                            delTree($rnddir, TMPDIR);
                            require VIEWS.UPLOAD;
                            break;
                        }

                        elseif (empty($addon->tree)) {
                            foreach (VERSION_DIRS as $vdir) {
                                if (strpos(strtolower($addon->version), substr($vdir, 0, -1))) {
                                    $addon->tree = $vdir;
                                    break;
                                }
                            }
                            if (empty($addon->tree)) $addon->tree = VERSION_DIRS[FALLBACK_TREE];
                            $_SESSION['notice'] .= "Der Upload wird der der Kodiversion '".ucwords(substr($addon->tree, 0, -1))."' zugeordnet. ";
                        }
                    } else {
                        $_SESSION['notice'] .= "Die 'addon.xml im hochgeladenen ZIP ist defekt. Der Upload wird verworfen. ";
                        delTree($rnddir, TMPDIR);
                        require VIEWS.UPLOAD;
                        exit();
                    }

                } else {
                    $_SESSION['notice'] .= "Im hochgeladenen ZIP befindet sich keine 'addon.xml'. Der Upload wird verworfen. ";
                    delTree($rnddir, TMPDIR);
                    require VIEWS.UPLOAD;
                    exit();
                }

                if (isset($c_pars['devtool'])) $addon->status += DEVTOOL;
                createThumb(TMPDIR.$rnddir, $icon, $addon->status);
                $addon_dir = ADDONFOLDER.$addon->tree.DATADIR.$addon->id.'/';
                $summaries = ADDONFOLDER.$addon->tree;

                # (Re)name upload properly to addonId-addonVersion.extension

                if ($upload != $addon->id.'-'.$addon->version.$addon->extension) {
                    rename(TMPDIR.$rnddir.$upload, TMPDIR.$rnddir.$addon->id.'-'.$addon->version.$addon->extension);
                    $upload = $addon->id.'-'.$addon->version.$addon->extension;
                    $_SESSION['notice'] .= "Der Name der hochgeladene Datei entspricht nicht den Namenskonventionen und wurde in '$upload' umbenannt. ";
                }

                # :::END PREREQUISITES:::

                if (!is_dir($addon_dir)) {

                    # new Addon

                    mkdir($addon_dir, 0755, true);
                    $addon->file = $addon_dir.$upload;
                    $addon->create();

                    $files = scanFolder(TMPDIR.$rnddir, array('.', '..', $addon->id));
                    foreach ($files as $file) {
                        if (is_file(TMPDIR.$rnddir.$file)) rename(TMPDIR.$rnddir.$file, $addon_dir.basename($file));
                    }

                } elseif (scanFolder($addon_dir, array('.', '..', 'archive'))) {

                    # existing addon files, check overwrite option and user permissions
                    # get info from current addon objects

                    $files = glob($addon_dir.$addon->id.'*'.$addon->extension);
                    foreach ($files as $c_file) {
                        $c_addon = new Addon($c_file);
                        $c_addon->read();
                        $dl_current = $c_addon->downloads;
                        $dl_total = $c_addon->downloads_total;
                        if (calculateNumVersion($c_addon->version) < calculateNumVersion($addon->version)) {
                            continue;

                        } elseif (calculateNumVersion($c_addon->version) > calculateNumVersion($addon->version)) {
                            $_SESSION['notice'] .= 'Ein Überschreiben vorhandener Addons mit älteren Addon-Versionen ist nicht zulässig! ';
                            delTree($rnddir, TMPDIR);
                            require VIEWS.UPLOAD;
                            exit();

                        } else {

                            # identical version

                            if (isset($c_pars['overwrite']) and ($_SESSION['user'] == $c_addon->provider or
                                    $_SESSION['isadmin'])) {

                                $addon->object_id = $c_addon->object_id;
                                $addon->downloads = $dl_current;
                                $addon->downloads_total = $dl_total;

                                unlink($c_addon->file);
                                unlink($c_addon->meta);
                            } else {
                                $_SESSION['notice'] = "Das hochgeladene Addon hat die gleiche Versionsnummer wie das aktuelle Addon, ";
                                $_SESSION['notice'] .= "jedoch ist die Option 'vorhandene Version überschreiben' nicht gesetzt oder der ";
                                $_SESSION['notice'] .= "angemeldete Nutzer ist nicht der Maintainer des Addons. ";
                                delTree($rnddir, TMPDIR);
                                require VIEWS.UPLOAD;
                                exit();
                            }
                        }
                    }

                    # Addon update with new filename
                    # check version and - if success - move existing addon versions into archive

                    # get older addons

                    $archive_files = glob($addon_dir.$addon->id.'*.*');
                    if ($archive_files) {
                        if (!is_dir($addon_dir.ARCHIVE)) mkdir($addon_dir.ARCHIVE, 0755, true);
                        foreach ($archive_files as $file) rename($file, $addon_dir.ARCHIVE.basename($file));
                    }
                    $addon->file = $addon_dir.$upload;
                    $addon->downloads_total = $dl_total;
                    $addon->create();

                    # move uploaded addon to destination

                    $files = scanFolder(TMPDIR.$rnddir, array('.', '..', $addon->id));
                    foreach ($files as $file) {
                        if (is_file(TMPDIR.$rnddir.$file)) rename(TMPDIR.$rnddir.$file, $addon_dir.basename($file));
                    }

                    # limit count of archive files to ARCHIVE_MAX_COUNT

                    $archive_files = $addon->getArchiveFiles();
                    for ($i = 0;  $i <= count($archive_files) - ARCHIVE_MAX_COUNT; $i++) {
                        $d_addon = new Addon($archive_files[$i]);
                        $d_addon->read();
                        $d_addon->delete();
                    }
                }
                delTree($rnddir, TMPDIR);
                $repo = new CreateRepoXML(ADDONFOLDER.$addon->tree, DATADIR);
                $repo->createRepoXML();
                $repo->createMD5();

                $_SESSION['version'] = $addon->tree;
                header('Location: '.ROOT.CONTROLLER.'?action=list&scope=user&item='.$_SESSION['user']);
                exit();
            }
        }
        require VIEWS.LISTVIEW;
        break;

    case 'download':
        $addon = new Addon($c_pars['item']);
        $addon->download();
        require VIEWS.LISTVIEW;
        break;

    case 'delete':
        if ($_SESSION['state'] == 1) {
            $addon = new Addon($c_pars['item']);
            $addon->read();
            $addon->delete();

            $repo = new CreateRepoXML(ADDONFOLDER.$addon->tree, DATADIR);
            $repo->createRepoXML();
            $repo->createMD5();

            $c_pars['user'] = $_SESSION['user'];
        }
        $c_pars['scope'] = 'user';
        $c_pars['item'] = $_SESSION['user'];
        require VIEWS.LISTVIEW;
        break;

    case 'setup':
        if ($_SESSION['state'] == 1) {
            require VIEWS.SETUP;
        } else {
            $c_pars['action'] = '';
            $_SESSION['notice'] = "Die aktuelle Session ist abgelaufen. Bitte erneut anmelden.";
            require VIEWS.LISTVIEW;
        }
        break;

    case 'setup_p1':
        if ($_SESSION['state'] == 1) {
            $cpw = false;
            $user = new User($_SESSION['user']);
            if ($c_pars['newpw'] != "") {
                if ($c_pars['newpw'] != $c_pars['confirmpw']) {
                    $_SESSION['notice'] = "Die Passwörter in den Feldern 'neues Passwort' und 'neues Passwort erneut eingeben' sind unterschiedlich. ";
                    require VIEWS.SETUP;
                    break;
                }
                $user->passwd = crypt($c_pars['newpw'], '$1$'.md5(rand()));
                $cpw = true;
            }
            $user->realname = $c_pars['realname'];
            $user->email = $c_pars['email'];
            $user->update();
            if ($cpw) resetSession();
        }
        require VIEWS.LISTVIEW;
        break;

    case 'setup_p2':
        if ($_SESSION['state'] == 1) {
            if (empty($c_pars['m_loginname'])) {
                $_SESSION['notice'] = 'Es wurde kein Login-Name angegeben. ';
                require VIEWS.SETUP;
                break;
            }
            $user = new User($c_pars['m_loginname']);
            if ($user->indb) {
                $_SESSION['notice'] = 'Ein Nutzer mit dem Login-Namen \''.$c_pars['m_loginname'].'\' befindet sich bereits in der Datenbank! ';
                require VIEWS.SETUP;
                break;
            }
            if (empty($c_pars['passwd'])) {
                $_SESSION['notice'] = 'Das Erstellen eines Maintainer-Logins ohne Passwort ist nicht zulässig! ';
                require VIEWS.SETUP;
                break;
            }
            $user = new User();
            $user->create($c_pars['m_loginname'], $c_pars['passwd']);
            $_SESSION['notice'] = 'Diese Daten kopieren und per Email an den Nutzer schicken. ';
            $_SESSION['notice'].= 'Username: '.$c_pars['m_loginname'].' Passwort: '.$c_pars['passwd'];
        }
        require VIEWS.LISTVIEW;
        break;

    case 'setup_p3':
        if ($_SESSION['state'] == 1 and !empty($c_pars['users'])) {
            switch ($c_pars['adm_lounge']) {
                case 'create_pw':
                    $user = new User($c_pars['users']);
                    $user->passwd = crypt(passwdGen(), '$1$'.md5(rand())) ;
                    $user->update();
                    $_SESSION['notice'] = 'Diese Daten kopieren und per Email an den Nutzer schicken. ';
                    $_SESSION['notice'].= 'Username: '.$c_pars['users'].' Passwort: '.$user->passwd;
                    break;
                case 'grant_adm':
                    $admin = new User($c_pars['users']);
                    $admin->isadmin = !$admin->isadmin;
                    $admin->update();
                    break;
                case 'delete_user':
                    $user = new User($c_pars['users']);
                    if ($user->indb) {
                        $dom = dom_import_simplexml($user->node);
                        $dom->parentNode->removeChild($dom);
                        $user->persist();
                    }
                    break;
                default:
                    break;
            }
            require VIEWS.SETUP;
            break;
        } else {
            $_SESSION['notice'] = "Es wurde kein Nutzer aus der Maintainerliste ausgewählt. Die gewünschte Aktion kann nicht ausgeführt werden. ";
            require VIEWS.SETUP;
        }
        break;

    case '404':
        $errmsg = "Die angeforderte Ressource existiert nicht!";
        $errcode = 404;
        require VIEWS.ERRORPAGE;
        break;

    case '403':
        $errmsg = "Der Zugriff auf die angeforderte Ressource ist nicht erlaubt!";
        $errcode = 403;
        require VIEWS.ERRORPAGE;
        break;

    case '503':
        $errmsg = "Der Dienst ist aufgrund von Wartungsarbeiten momentan nicht verfügbar.";
        $errcode = 503;
        require VIEWS.ERRORPAGE;
        break;

    default:
        # Bootstrap
        unset($c_pars);
        require VIEWS.LISTVIEW;
}
