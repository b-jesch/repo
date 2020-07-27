<?php

class CreateRepoXML
{

    public $repo_folder = NULL;                          # Folder for addon.xml in summary
    public $repo_sources = NULL;                         # Folder of zipped addons

    function __construct($repo_folder, $sources) {

        $this->repo_folder = $repo_folder;
        $this->repo_sources = $repo_folder . $sources;
    }

    function createRepoXML() {

        $addons = scanFolder($this->repo_sources, array('.', '..'));
        $out = '<?xml version="1.0"?>' . PHP_EOL . '<addons>' . PHP_EOL;
        if ($addons) {
            foreach ($addons as $addon) {
                if (!is_dir($this->repo_sources.$addon)) continue;
                $content = file($this->repo_sources . $addon . '/addon.xml', FILE_SKIP_EMPTY_LINES);
                $s = true;
                foreach ($content as $line) {
                    if (substr($line, 0, 7) != '<addon ' and $s) {
                        continue;
                    } else {
                        $s = false;
                        $out .= '    ' . $line;
                    }
                }
            }
        }
        $out .= '</addons>' . PHP_EOL;

        $handle = fopen($this->repo_folder . '/addons.xml', 'w');
        fwrite($handle, $out);
        fclose($handle);
    }

    public function createMasterXML() {
        if (is_file($this->repo_sources.'addon.xml')) {
            $out = '<?xml version="1.0"?>' . PHP_EOL . '<addons>' . PHP_EOL;
            $content = file($this->repo_sources.'addon.xml', FILE_SKIP_EMPTY_LINES);
            $s = true;
            foreach ($content as $line) {
                if (substr($line, 0, 7) != '<addon ' and $s) {
                    continue;
                } else {
                    $s = false;
                    $out .= '    ' . $line;
                }
            }
        }
        $out .= '</addons>' . PHP_EOL;

        $handle = fopen($this->repo_folder . '/addons.xml', 'w');
        fwrite($handle, $out);
        fclose($handle);
    }

    public function createAddonXML($dest) {

        # Open Template

        $out = '';
        $content = file($this->repo_sources, FILE_SKIP_EMPTY_LINES);
        foreach ($content as $line) {
            $inject = array('$root/' => ROOT, '$id' => REPO_ID, '$name' => REPONAME, '$version' => REPOVERSION, '$provider' => PROVIDER);
            $line = strtr($line, $inject);
            $out .= $line;
        }
        $handle = fopen($dest, 'w');
        fwrite($handle, $out);
        fclose($handle);
    }

    function createMD5() {

        # create md5

        $out = '';
        $content = file($this->repo_folder . '/addons.xml');
        foreach ($content as $line) $out .= $line;
        $handle = fopen($this->repo_folder . '/addons.xml.md5', 'w');
        fwrite($handle, md5($out));
        fclose($handle);
    }
}

class Addon {

    public $file = NULL;                            # Dateiname im Addon-Data-Verzeichnis
    public $object_id = NULL;                       # Objekt-ID
    public $category = NULL;                        # Addon-Kategorie
    public $name = NULL;                            # Addon-Name
    public $id = NULL;                              # Addon-Id
    public $version = NULL;                         # Addon-Version
    public $summary = NULL;                         # Kurzbeschreibung
    public $size = NULL;                            # Dateigröße ZIP
    public $meta = NULL;                            # Dateiname Metadaten

    public $tree = NULL;                            # Kodi Version (Tree)
    public $upload = NULL;                          # Uploaddatum
    public $provider = NULL;                        # Addon-Maintainer/Uploader (Provider)
    public $author = NULL;                          # Addon-Autor
    public $downloads = NULL;                       # Anzahl Downloads aktuelle Version
    public $downloads_total = NULL;                 # Anzahl Downloads über alle Versionen im Tree
    public $status = NULL;                          # Status des Addons (z.B. "broken")

    public $addon_types = NULL;
    public $addon_category = NULL;
    public $python = NULL;
    public $version_dirs = NULL;
    public $thumb = NULL;


    function __construct($file, $id='') {
        $this->file = $file;
        $this->object_id = $id;
        $this->meta = substr($file,0, strlen(ADDON_EXT) * -1).META_EXT;
        $this->upload = date('d.m.Y H:i');
        $this->size = filesize($file);
        $this->downloads = 0;
        $this->category = 'Unknown';
        $this->thumb = pathinfo($file, PATHINFO_DIRNAME).'/icon.tbn';
    }

    public function create() {
        $this->meta = substr($this->file,0, strlen(ADDON_EXT) * -1).META_EXT;
        $this->writeProperties();
    }

    public function read() {
        $this->readProperties();
    }

    public function download() {
        $this->readProperties();
        $this->downloads = intval($this->downloads) + 1;
        $this->downloads_total = intval($this->downloads_total) + 1;
        $this->writeProperties();

        # ob_clean();
        # flush();

        header('Content-Type: application.zip');
        header('Content-Disposition: attachment; filename="'.basename($this->file).'"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.$this->size);

        readfile($this->file);
    }

    public function delete() {
        $path = pathinfo($this->file, PATHINFO_DIRNAME).'/';
        $archive_content = scanFolder($path.ARCHIVE, array('.', '..'));
        if (!$archive_content) {
            delTree($path);
        } else {

            # remove last archived item back to Repository

            $archive_content = array_reverse($archive_content);
            
            rename($path.ARCHIVE.$archive_content[0], $path.$archive_content[0]);
            rename($path.ARCHIVE.$archive_content[1], $path.$archive_content[1]);

            # check if archive folder is empty, delete it

            if (!scanFolder($path.ARCHIVE, array('.', '..'))) delTree($path.ARCHIVE);
        }
        if (is_file($this->meta)) unlink($this->meta);
        if (is_file($this->file)) unlink($this->file);
    }

    public function getAttrFromAddonXML() {
        if (!is_file(pathinfo($this->file, PATHINFO_DIRNAME).'/addon.xml')) return false;

        $xml = simplexml_load_file(pathinfo($this->file, PATHINFO_DIRNAME).'/addon.xml');
        if (!$xml) return false;

        # Get short description (summary), Addon-Type

        foreach($xml->extension as $ep) {
            if ($ep['point'] == 'xbmc.addon.metadata') {
                # remove BB code
                $this->summary = preg_replace('#\[[^\]]+\]#', '', $ep->summary[0]);
                if ($ep->broken) $this->status = 'broken';
            }
            if (array_search($ep['point'], $this->addon_types) === false) {
                continue;
            } else {
                if ($this->category == 'Unknown') {
                    $this->category = $this->addon_category[array_search($ep['point'], $this->addon_types)];
                    if (!empty($ep->provides)) $this->category .= ' ('.ucwords($ep->provides).')';
                }
            }
        }

        # Get Python Version (tree)

        foreach($xml->requires->import as $import) {
            if ($import['addon'] == 'xbmc.python') $this->tree = $this->version_dirs[array_search($import['version'], $this->python)];
        }

        $addon_attributes = iterator_to_array($xml->attributes());
        $this->author = $addon_attributes['provider-name'];
        $this->version = $addon_attributes['version'];
        # remove BB code
        $this->name = preg_replace('#\[[^\]]+\]#', '', $addon_attributes['name']);
        $this->id = $addon_attributes['id'];

        return true;
    }

    private function readProperties() {
        $xml = simplexml_load_file($this->meta);
        if ($xml) {
            $this->object_id = $xml->object_id;
            $this->name = $xml->name;
            $this->id = $xml->addon_id;
            $this->version = $xml->version;
            $this->tree = $xml->tree;
            $this->summary = $xml->summary;
            $this->upload = $xml->upload;
            $this->provider = $xml->provider;
            $this->author = $xml->author;
            $this->status = $xml->status;
            $this->downloads = $xml->downloads;
            $this->downloads_total = $xml->downloads_total;
            if (empty($this->downloads_total)) $this->downloads_total = $this->downloads;
            $this->category = $xml->category;
        }
    }

    private function writeProperties() {
        $xml = simplexml_load_string('<addon/>');
        $xml->addChild('name', htmlspecialchars($this->name));
        $xml->addChild('addon_id', $this->id);
        $xml->addChild('version', $this->version);
        $xml->addChild('category', $this->category);
        $xml->addChild('tree', $this->tree);
        $xml->addChild('summary', htmlspecialchars($this->summary));
        $xml->addChild('object', $this->file);
        $xml->addChild('object_id', $this->object_id);
        $xml->addChild('upload', $this->upload);
        $xml->addChild('provider', htmlspecialchars($this->provider));
        $xml->addChild('author', htmlspecialchars($this->author));
        $xml->addChild('status', $this->status);
        $xml->addChild('downloads', $this->downloads);
        $xml->addChild('downloads_total', $this->downloads_total);

        $dom = init_domxml();
        $dom->loadXML($xml->saveXML());
        $dom->save($this->meta);
    }
}

class User
{
    public $database = NULL;        # User Database File
    public $indb = false;           # User is in Database
    public $isadmin = false;        # User has Administrator Rights
    public $node = NULL;            # XML Node if user with attribute 'login'
    public $success = false;        # Login succeeded
    public $username = NULL;        # Username, Loginname
    public $passwd = NULL;          # Password
    public $realname = NULL;        # Name in real World
    public $email = NULL;           # Email-Adress
    public $last_login = NULL;      # Timestamp of last Login

    function __construct($user='')
    {
        if (!scanFolder(ETC.USERDATA, array('.', '..'))) {
            if (!is_dir(ETC.USERDATA)) mkdir(ETC.USERDATA, 0775, true);
            $this->database = passwdGen().META_EXT;
            $init = '<users><user login="admin"><passwd>'.crypt('admin').'</passwd>';
            $init.= '<isadmin>true</isadmin></user></users>';
            $this->users = simplexml_load_string($init);
            $this->persist();
        } else {
            $this->database = scanFolder(ETC.USERDATA, array('.', '..'))[0];
        }
        $this->users = simplexml_load_file(ETC.USERDATA.$this->database);
        if ($user != '') {
            foreach ($this->users->children() as $node) {
                if ($node->attributes()->login == $user) {
                    $this->indb = true;
                    $this->username = $user;

                    $this->node = $node;

                    $this->passwd = $node->passwd;
                    $this->last_login = $node->last_login;
                    $this->realname = $node->realname;
                    $this->email = $node->email;
                    $this->isadmin = ($node->isadmin == 'true') ? true : false;
                }
            }
        }
    }

    public function getallusers($formatted=true) {
        $list = [];
        foreach ($this->users->children() as $node) {
            $list[] = $node->attributes()->login;
        }
        asort($list, SORT_NATURAL | SORT_FLAG_CASE);

        if ($formatted) {
            $sorted_list = [];
            foreach ($list as $user) {
                foreach ($this->users->children() as $node) {
                    if ($user == $node->attributes()->login) {
                        $sorted_list[] = ($node->isadmin == 'true') ? '<b>' . $user . '</b>' : $user;
                    }
                }
            }
            return $sorted_list;
        }
        return $list;
    }

    public function getadmins() {
        $list = [];
        foreach ($this->users->children() as $node) {
            if ($node->isadmin == 'true' and !empty($node->email)) $list[] = $node->email;
        }
        asort($list, SORT_NATURAL | SORT_FLAG_CASE);
        return $list;
    }

    public function login($pw) {

        if ($this->indb) {
            if ($this->node->passwd == crypt($pw, $this->node->passwd)) {
                $this->set_node($this->node, $this->node->last_login, 'last_login', date('d.m.Y H:i'));
                $this->persist();
                $this->success = true;
            }
        }
    }

    public function logout() {
        resetSession();
    }

    public function create($username, $passwd) {
        $user = $this->users->addChild('user');
        $user->addAttribute('login', $username);
        $user->addChild('passwd', crypt($passwd));
        $user->addChild('isadmin', 'false');
        $this->persist();
    }

    public function persist() {
        $dom = init_domxml();
        $dom->loadXML($this->users->saveXML());
        $dom->save(ETC.USERDATA.$this->database);
    }

    public function update() {
        $this->set_node($this->node, $this->node->passwd, 'passwd', $this->passwd);
        $this->set_node($this->node, $this->node->realname, 'realname', $this->realname);
        $this->set_node($this->node, $this->node->email, 'email', $this->email);
        $this->set_node($this->node, $this->node->isadmin, 'isadmin', ($this->isadmin) ? 'true' : 'false');
        $this->persist();
    }

    private function set_node($parent, $child, $child_name, $value) {
        if (isset($parent)) {
            if (isset($child)) {
                $parent->$child_name = $value;
            } else {
                $parent->addChild($child_name, $value);
            }
        }
    }
}
?>