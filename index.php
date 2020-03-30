<?php

# ob_start();

require ('config.php');
require (FUNCTIONS.'functions.php');
require (CLASSES.'Addon.php');

# Handle direct Downloads

$c_pars = array_merge($_POST, $_GET, $_FILES);

if ($c_pars['action'] == 'direct_dl') {
    $addon = new Addon($c_pars['f']);
    $addon->download();
    exit();
}

# debug($c_pars);

# :::BOOTSTRAP:::

# Session starten

session_start();
if (!$_SESSION['state'] or $_SESSION['state'] == 0) {
    $_SESSION['state'] = 0;
}

# Decrypt encrypted Routes

if (isset($c_pars['action'])) {
    foreach($routing as $route) {
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

    if (!is_dir(ADDONFOLDER.REPO_ID)) mkdir(ADDONFOLDER.REPO_ID, 0775, true);
    $files = scanFolder(ADDONFOLDER.REPO_TEMPLATES, array('.', '..', ADDON_TEMPLATE));
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

    $master = new CreateRepoXML(ADDONFOLDER, REPO_ID.'/');
    $master->createMasterXML();
    $master->createMD5();

    # create version folder and XML for certain Kodi versions as Kodi looks up at first in these
    # dependent on Kodi version. Kodi fails if these folders doesn't exist (maybe a bug?)

    foreach ($version_dirs as $version_dir) {
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
if (is_dir(TMPDIR)) delTree(TMPDIR);

# :::END OF BOOTSTRAP:::

if (isset($c_pars['login'])) {
    if ($c_pars['user'] != '' and $c_pars['passwd'] != '') {
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

if (($c_pars['version'] != '') and (in_array($c_pars['version'], $version_dirs))) {
    $_SESSION['version'] = $c_pars['version'];
} elseif (empty($_SESSION['version'])) {
    $_SESSION['version'] = $version_dirs[FALLBACK_TREE]; # Krypton
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
        $user = new User($_SESSION['user']);
        $user->logout();
        resetSession();
        break;
    case 'upload_p2':
        if ($_SESSION['state'] == 1) {
            if ($c_pars['upload']['error'] == UPLOAD_ERR_NO_FILE) {
                require VIEWS . UPLOAD;
                break;
            }
            if ($c_pars['upload']['error'] == UPLOAD_ERR_OK) {

                # :::PREREQUISITES:::

                $addon_name = $c_pars['upload']['name'];

                # Determine Addonname, Addonversion, be aware for multiple delimiters
                # and use only the last part for version

                $pieces = explode('-', basename($addon_name, ADDON_EXT));

                if (count($pieces) > 1) {
                    $addon_version = array_pop($pieces);
                }
                $addon_basename = implode('-', $pieces);

                mkdir(TMPDIR, 0755, true);

                # move and unpacking upload to TMPDIR

                move_uploaded_file($c_pars['upload']['tmp_name'], TMPDIR.$addon_name);
                $zip = new ZipArchive();
                $zip->open(TMPDIR.$addon_name);

                if ($zip->status == ZipArchive::ER_OK) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        if (in_array(basename($zip->statIndex($i)['name']), array('addon.xml', 'fanart.jpg', 'icon.png', 'changelog.txt'))) {
                            $zip->extractTo(TMPDIR, $zip->statIndex($i)['name']);
                            rename(TMPDIR . $zip->statIndex($i)['name'], TMPDIR . basename($zip->statIndex($i)['name']));
                        }
                    }
                    $zip->close();
                } else {
                    $notice = 'Die Zip-Datei ist defekt und konnte nicht geöffnet werden! Das Addon wurde nicht gespeichert.';
                    require VIEWS . UPLOAD;
                    break;
                }

                # create addon object and thumbnail

                $addon = new Addon(TMPDIR.$addon_name, time());
                $addon->provider = $_SESSION['user'];

                $addon->addon_types = $addon_types;
                $addon->addon_category = $addon_category;
                $addon->python = $addon_python;
                $addon->version_dirs = $version_dirs;

                if (is_file(TMPDIR.'addon.xml')) {
                    $addon->getAttrFromAddonXML();

                    # missing xbmc.python attribute in addon.xml, assign to FALLBACK_TREE

                    if (empty($addon->tree)) {
                        $addon->tree = $version_dirs[FALLBACK_TREE];
                        $notice = "Der Upload wird der der Kodiversion '".$kodiversions[FALLBACK_TREE]."' zugeordnet";
                    }

                } else {
                    $notice = "Im hochgeladenen ZIP befindet sich keine 'addon.xml'. Der Upload wird verworfen";
                    require VIEWS . UPLOAD;
                    break;
                }

                createThumb(TMPDIR, TMPDIR.'icon.png');
                $addon_dir = ADDONFOLDER . $addon->tree . DATADIR . $addon_basename . '/';
                $summaries = ADDONFOLDER . $addon->tree;

                # :::END PREREQUISITES:::

                if (!is_dir($addon_dir)) {

                    # new Addon

                    mkdir($addon_dir, 0755, true);

                    $addon->file = $addon_dir.$addon_name;
                    $addon->create();

                    $files = scanFolder(TMPDIR, array('.', '..', $addon_basename));
                    foreach($files as $file) rename(TMPDIR.$file, $addon_dir.basename($file));

                } elseif (scanFolder($addon_dir, array('.', '..', 'archive'))) {

                    # existing addon files, check overwrite option and user permissions
                    # get info from current addon objects

                    $files = glob($addon_dir.$addon_basename.'*.zip');
                    foreach ($files as $c_file) {
                        $c_addon = new Addon($c_file);
                        $c_addon->read();
                        if (calculateNumVersion($c_addon->version) < calculateNumVersion($addon->version)) {
                            continue;

                        } elseif (calculateNumVersion($c_addon->version) > calculateNumVersion($addon->version)) {
                            $notice = 'Ein Überschreiben vorhandener Addons mit älteren Addon-Versionen ist nicht zulässig!';
                            require VIEWS . UPLOAD;
                            exit();

                        } else {

                            # identical version but possibly different filenames

                            if (isset($c_pars['overwrite']) and $_SESSION['user'] == $c_addon->provider) {

                                $addon->object_id = $c_addon->object_id;
                                if (!isset($c_pars['reset_count'])) $addon->downloads = $c_addon->downloads;

                                unlink($c_addon->file);
                                unlink($c_addon->meta);
                            } else {
                                $notice = "Die Option 'vorhandene Version überschreiben' ist nicht gesetzt oder der ";
                                $notice .= "angemeldete Nutzer ist nicht der Maintainer des Addons.";
                                require VIEWS . UPLOAD;
                                exit();
                            }
                        }
                    }

                    # Addon update with new filename
                    # check version and - if success - move existing addon versions into archive

                    # get older addons

                    $archive_files = glob($addon_dir . $addon_basename . '*.*');
                    if ($archive_files) {
                        if (!is_dir($addon_dir . ARCHIVE)) mkdir($addon_dir . ARCHIVE, 0755, true);
                        foreach ($archive_files as $file) rename($file, $addon_dir . ARCHIVE . basename($file));
                    }
                    $addon->file = $addon_dir.$addon_name;
                    $addon->create();

                    # move uploaded addon to destination

                    $files = scanFolder(TMPDIR, array('.', '..', $addon_basename));
                    foreach($files as $file) rename(TMPDIR.$file, $addon_dir.basename($file));
                }
            }
            $repo = new CreateRepoXML(ADDONFOLDER.$addon->tree, DATADIR);
            $repo->createRepoXML();
            $repo->createMD5();

            $_SESSION['version'] = $addon->tree;
            header('Location: ' . ROOT . CONTROLLER);
            exit();
        }
        require VIEWS.LISTVIEW;
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
            $repo->createRepoXML();
            $repo->createMD5();
        }
        require VIEWS.LISTVIEW;
        break;
    case 'setup':
        if ($_SESSION['state'] == 1) {
            require VIEWS.SETUP;
        } else {
            $c_pars['action'] = '';
        }
        break;
    case 'setup_p1':
        if ($_SESSION['state'] == 1) {
            $cpw = false;
            $user = new User($_SESSION['user']);
            if ($c_pars['newpw'] != "") {
                if ($c_pars['newpw'] != $c_pars['confirmpw']) {
                    $notice = "Die Passwörter in den Feldern 'neues Passwort' und 'neues Passwort erneut eingeben' sind unterschiedlich. ";
                    require VIEWS.SETUP;
                    break;
                }
                $user->passwd = crypt($c_pars['newpw']);
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
            if (empty($c_pars['loginname'])) {
                $notice = 'Es wurde kein Login-Name angegeben.';
                require VIEWS.SETUP;
                break;
            }
            $user = new User($c_pars['loginname']);
            if ($user->indb) {
                $notice = 'Ein Nutzer mit dem Login-Namen \''.$c_pars['loginname'].'\' befindet sich bereits in der Datenbank! ';
                require VIEWS.SETUP;
                break;
            }
            if (empty($c_pars['passwd'])) {
                $notice = 'Das Erstellen eines Maintainer-Logins ohne Passwort ist nicht zulässig! ';
                require VIEWS.SETUP;
                break;
            }
            $user = new User();
            $user->create($c_pars['loginname'], $c_pars['passwd']);
            $notice = 'Diese Daten kopieren und per Email an den Nutzer schicken. ';
            $notice.= 'Username: '.$c_pars['loginname'].' Passwort: '.$c_pars['passwd'];
        }
        require VIEWS . LISTVIEW;
        break;
    case 'setup_p3':
        if ($_SESSION['state'] == 1 and !empty($c_pars['users'])) {
            switch ($c_pars['adm_lounge']) {
                case 'create_pw':
                    $user = new User($c_pars['users']);
                    $user->passwd = passwdGen();
                    $user->update();
                    $notice = 'Diese Daten kopieren und per Email an den Nutzer schicken. ';
                    $notice.= 'Username: '.$c_pars['users'].' Passwort: '.$user->passwd;
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
            $notice = "Es wurde kein Nutzer aus der Maintainerliste ausgewählt. Die gewünschte Aktion kann nicht ausgeführt werden.";
            require VIEWS.SETUP;
        }
        break;
    default:
        # Bootstrap
        require VIEWS.LISTVIEW;
}
