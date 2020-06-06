<?php

# ob_start();

require ('config.php');
require (FUNCTIONS.'functions.php');
require (CLASSES.'Addon.php');

# Handle direct Downloads

$c_pars = array_merge($_POST, $_GET, $_FILES);

if ($c_pars['action'] == 'direct_dl') {
    $file = pathinfo($c_pars['f'], PATHINFO_DIRNAME).'/'.urlencode(basename($c_pars['f']));
    $addon = new Addon($file);
    $addon->download();
    exit();
}

# :::BOOTSTRAP:::

# start session

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

    mkdir(ADDONFOLDER.REPO_ID, 0775, true);

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

if (($c_pars['version'] != '') and (in_array($c_pars['version'], $version_dirs))) {
    $_SESSION['version'] = $c_pars['version'];
} elseif (empty($_SESSION['version'])) {
    $_SESSION['version'] = $version_dirs[DEFAULT_TREE]; # Krypton
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
            if ($c_pars['upload']['error'] == UPLOAD_ERR_NO_FILE) {
                require VIEWS . UPLOAD;
                break;
            }
            if ($c_pars['upload']['error'] == UPLOAD_ERR_OK) {

                # :::PREREQUISITES:::

                $upload = $c_pars['upload']['name'];
                mkdir(TMPDIR, 0755, true);

                # move and unpacking upload to TMPDIR, copy default icon to TMPDIR

                move_uploaded_file($c_pars['upload']['tmp_name'], TMPDIR.$upload);
                $zip = new ZipArchive();
                $zip->open(TMPDIR.$upload);

                if ($zip->status == ZipArchive::ER_OK) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        if (in_array(basename($zip->statIndex($i)['name']), array('addon.xml', 'fanart.jpg', 'icon.png', 'icon.jpg', 'changelog.txt'))) {
                            $zip->extractTo(TMPDIR, $zip->statIndex($i)['name']);
                            rename(TMPDIR . $zip->statIndex($i)['name'], TMPDIR . basename($zip->statIndex($i)['name']));
                        }
                    }
                    $zip->close();

                    $icon = TMPDIR.'icon.png';
                    if (is_file(TMPDIR.'icon.jpg')) $icon = TMPDIR.'icon.jpg';
                    elseif (!is_file(TMPDIR.'icon.png')) {
                        copy(ADDONFOLDER.REPO_TEMPLATES.DEFAULT_ADDON_ICON, TMPDIR.'icon.png');
                    }
                } else {
                    $_SESSION['notice'] .= 'Die Zip-Datei ist defekt und konnte nicht geöffnet werden! Das Addon wurde nicht gespeichert. ';
                    require VIEWS . UPLOAD;
                    break;
                }

                # create addon object and thumbnail

                $addon = new Addon(TMPDIR.$upload, time());
                $addon->provider = ($_SESSION['isadmin']) ? $c_pars['provider'] : $_SESSION['user'];

                $addon->addon_types = $addon_types;
                $addon->addon_category = $addon_category;
                $addon->python = $addon_python;
                $addon->version_dirs = $version_dirs;

                if (is_file(TMPDIR.'addon.xml')) {
                    $addon->getAttrFromAddonXML();

                    # missing xbmc.python attribute in addon.xml, search for tree in addon name, else
                    # assign to FALLBACK_TREE anywhere

                    if (empty($addon->tree)) {
                        foreach ($version_dirs as $vdir) {
                            if (strpos($addon->version, substr($vdir, 0, -1))) {
                                $addon->tree = $vdir;
                                break;
                            }
                        }
                        if (empty($addon->tree)) $addon->tree = $version_dirs[FALLBACK_TREE];
                        $_SESSION['notice'] .= "Der Upload wird der der Kodiversion '". ucwords(substr($addon->tree, 0, -1)) ."' zugeordnet. ";
                    }

                } else {
                    $_SESSION['notice'] .= "Im hochgeladenen ZIP befindet sich keine 'addon.xml'. Der Upload wird verworfen. ";
                    require VIEWS . UPLOAD;
                    break;
                }

                createThumb(TMPDIR, $icon, $addon->status);
                $addon_dir = ADDONFOLDER . $addon->tree . DATADIR . $addon->id . '/';
                $summaries = ADDONFOLDER . $addon->tree;

                # (Re)name upload properly to addonId-addonVersion.zip

                if ($upload != $addon->id.'-'.$addon->version.ADDON_EXT) {
                    rename(TMPDIR.$upload, TMPDIR.$addon->id.'-'.$addon->version.ADDON_EXT);
                    $upload = $addon->id.'-'.$addon->version.ADDON_EXT;
                    $addon->file = $upload;
                    $_SESSION['notice'] .= "Die hochgeladene Datei entspricht nicht den Namensregeln für Kodi Addons und wurde in '$upload' umbenannt. ";
                }

                # :::END PREREQUISITES:::

                if (!is_dir($addon_dir)) {

                    # new Addon

                    mkdir($addon_dir, 0755, true);

                    $addon->file = $addon_dir.$upload;
                    $addon->downloads_total = 0;
                    $addon->create();

                    $files = scanFolder(TMPDIR, array('.', '..', $addon->id));
                    foreach($files as $file) {
                        if (is_file(TMPDIR.$file)) rename(TMPDIR . $file, $addon_dir . basename($file));
                    }

                } elseif (scanFolder($addon_dir, array('.', '..', 'archive'))) {

                    # existing addon files, check overwrite option and user permissions
                    # get info from current addon objects

                    $files = glob($addon_dir.$addon->id.'*.zip');
                    foreach ($files as $c_file) {
                        $c_addon = new Addon($c_file);
                        $c_addon->read();
                        $dl_current = $c_addon->downloads;
                        $dl_total = $c_addon->downloads_total;
                        if (calculateNumVersion($c_addon->version) < calculateNumVersion($addon->version)) {
                            continue;

                        } elseif (calculateNumVersion($c_addon->version) > calculateNumVersion($addon->version)) {
                            $_SESSION['notice'] .= 'Ein Überschreiben vorhandener Addons mit älteren Addon-Versionen ist nicht zulässig! ';
                            require VIEWS . UPLOAD;
                            exit();

                        } else {

                            # identical version but possibly different filenames

                            if (isset($c_pars['overwrite']) and ($_SESSION['user'] == $c_addon->provider or
                                    $_SESSION['isadmin'])) {

                                $addon->object_id = $c_addon->object_id;
                                $addon->downloads = $dl_current;

                                unlink($c_addon->file);
                                unlink($c_addon->meta);
                            } else {
                                $_SESSION['notice'] = "Die Option 'vorhandene Version überschreiben' ist nicht gesetzt oder der ";
                                $_SESSION['notice'] .= "angemeldete Nutzer ist nicht der Maintainer des Addons. ";
                                require VIEWS . UPLOAD;
                                exit();
                            }
                        }
                    }

                    # Addon update with new filename
                    # check version and - if success - move existing addon versions into archive

                    # get older addons

                    $archive_files = glob($addon_dir . $addon->id . '*.*');
                    if ($archive_files) {
                        if (!is_dir($addon_dir . ARCHIVE)) mkdir($addon_dir . ARCHIVE, 0755, true);
                        foreach ($archive_files as $file) rename($file, $addon_dir . ARCHIVE . basename($file));
                    }
                    $addon->file = $addon_dir.$upload;
                    $addon->downloads_total = $dl_total;
                    $addon->create();

                    # move uploaded addon to destination

                    $files = scanFolder(TMPDIR, array('.', '..', $addon->id));
                    foreach($files as $file) {
                        if (is_file(TMPDIR.$file)) rename(TMPDIR . $file, $addon_dir . basename($file));
                    }
                }
            }
            $repo = new CreateRepoXML(ADDONFOLDER.$addon->tree, DATADIR);
            $repo->createRepoXML();
            $repo->createMD5();

            $_SESSION['version'] = $addon->tree;
            header('Location: ' . ROOT . CONTROLLER.'?user='.$_SESSION['user']);
            exit();
        }
        require VIEWS.LISTVIEW;
        break;

    case 'download':
        foreach ($version_dirs as $version_dir) {
            $addondirs = scanFolder(ADDONFOLDER.$version_dir.DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
            if ($addondirs) {
                foreach ($addondirs as $addondir) {
                    $metafiles = glob(ADDONFOLDER.$version_dir.DATADIR.$addondir.'/*.zip');
                    foreach ($metafiles as $metadata) {
                        $addon = new Addon($metadata);
                        $addon->read();
                        if ($addon->object_id != '' and $c_pars['item'] == $addon->object_id) {
                            $addon->download();
                            break;
                        }
                    }
                }
            }
        }
        require VIEWS.LISTVIEW;
        break;

    case 'delete':
        if ($_SESSION['state'] == 1) {
            foreach ($version_dirs as $version_dir) {
                $addondirs = scanFolder(ADDONFOLDER.$version_dir.DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
                if ($addondirs) {
                    foreach ($addondirs as $addondir) {
                        $metafiles = glob(ADDONFOLDER.$version_dir.DATADIR.$addondir.'/*.zip');
                        foreach ($metafiles as $metadata) {
                            $addon = new Addon($metadata);
                            $addon->read();
                            if ($addon->object_id != '' and $c_pars['item'] == $addon->object_id) {
                                $modified_tree = $version_dir;
                                $addon->delete();
                                break;
                            }
                        }
                    }
                }
            }
            if (!empty($modified_tree)) {
                $repo = new CreateRepoXML(ADDONFOLDER.$_SESSION['version'], DATADIR);
                $repo->createRepoXML();
                $repo->createMD5();
            }
            $c_pars['user'] = $_SESSION['user'];
        }
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
        require VIEWS . LISTVIEW;
        break;

    case 'setup_p3':
        if ($_SESSION['state'] == 1 and !empty($c_pars['users'])) {
            switch ($c_pars['adm_lounge']) {
                case 'create_pw':
                    $user = new User($c_pars['users']);
                    $user->passwd = passwdGen();
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
        $errmsg = "Die von Dir aufgerufene Seite existiert nicht!";
        require VIEWS.ERRORPAGE;
        break;

    case '403':
        $errmsg = "Das was Du vor hast, ist hier nicht erlaubt!";
        require VIEWS.ERRORPAGE;
        break;

    default:
        # Bootstrap
        require VIEWS.LISTVIEW;
}
