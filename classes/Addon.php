<?php

class CreateRepoXML
{

    public $repo_folder = NULL;                          # Folder for addon.xml in summary
    public $repo_sources = NULL;                            # Folder of zipped addons

    function __construct($repo_folder, $sources) {

        $this->repo_folder = $repo_folder;
        $this->repo_sources = $repo_folder . $sources;
    }

    function createRepoXML() {

        $addons = scanFolder($this->repo_sources, array('.', '..'));
        $out = '<?xml version="1.0"?>' . PHP_EOL . '<addons>' . PHP_EOL;
        if ($addons) {
            foreach ($addons as $addon) {
                if (!is_dir($addon)) continue;
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
            $line = str_replace('$root/', ROOT, $line);
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
    public $downloads = NULL;                       # Anzahl Downloads

    public $addon_types = NULL;
    public $addon_category = NULL;
    public $python = NULL;
    public $version_dirs = NULL;


    function __construct($file, $id='') {
        $this->file = $file;
        $this->object_id = $id;
        $this->meta = substr($file,0, strlen(ADDON_EXT) * -1).META_EXT;
        $this->upload = date('d.m.Y H:i');
        $this->size = filesize($file);
        $this->downloads = 0;
        $this->category = 'Unknown';
    }

    public function create() {
        $this->writeProperties();
    }

    public function read() {
        $this->readProperties();
    }

    public function modify() {
        $this->writeProperties();
    }

    public function download() {
        $this->readProperties();
        $this->downloads = intval($this->downloads) + 1;
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
        $xml = simplexml_load_file(pathinfo($this->file, PATHINFO_DIRNAME).'/addon.xml');
        if ($xml) {

            # Get short description (summary), Addon-Type

            foreach($xml->extension as $ep) {
                if ($ep['point'] == 'xbmc.addon.metadata') {
                    $this->summary = $ep->summary[0];
                }
                if (array_search($ep['point'], $this->addon_types)) {
                    if ($this->category == 'Unknown') $this->category = $this->addon_category[array_search($ep['point'], $this->addon_types)];
                }
            }

            # Get Python Version (tree)

            foreach($xml->requires->import as $import) {
                if ($import['addon'] == 'xbmc.python') $this->tree = $this->version_dirs[array_search($import['version'], $this->python)];
            }

            $addon_attributes = iterator_to_array($xml->attributes());
            $this->author = $addon_attributes['provider-name'];
            $this->version = $addon_attributes['version'];
            $this->name = $addon_attributes['name'];
            $this->id = $addon_attributes['id'];
            # $this->writeProperties();
        }
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
            $this->downloads = $xml->downloads;
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
        $xml->addChild('downloads', $this->downloads);

        $dom = init_domxml();
        $dom->loadXML($xml->saveXML());
        $dom->save($this->meta);
    }
}

class User
{

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

        if (!is_file(USER_DB)) {
            # $init = '<users><user login="admin"><passwd>$1$9rQmP7mh$4Aewg5ppAb161Rc4Cc45F.</passwd>';
            $init = '<users><user login="admin"><passwd>'.crypt('admin').'</passwd>';
            $init.= '<isadmin>true</isadmin></user></users>';
            $this->users = simplexml_load_string($init);
        } else {
            $this->users = simplexml_load_file(USER_DB);
        }
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
            if ($formatted) {
                $list[] = ($node->isadmin == 'true') ? '<b>'.$node->attributes()->login.'</b>' : $node->attributes()->login;
            } else {
                $list[] = $node->attributes()->login;
            }
        }
    return $list;
    }

    public function getadmins() {
        $list = [];
        foreach ($this->users->children() as $node) {
            if ($node->isadmin == 'true' and !empty($node->email)) $list[] = $node->email;
        }
        return $list;
    }

    public function login($pw) {

        if ($this->indb) {
            if ($this->node->passwd == crypt($pw, $this->node->passwd)) {
                $this->success = true;
            }
        }
    }

    public function logout() {
        $this->set_node($this->node, $this->node->last_login, 'last_login', date('d.m.Y H:i'));
        $this->persist();
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
        $dom->save(USER_DB);
    }

    public function update() {
        $this->set_node($this->node, $this->node->passwd, 'passwd', $this->passwd);
        $this->set_node($this->node, $this->node->realname, 'realname', $this->realname);
        $this->set_node($this->node, $this->node->email, 'email', $this->email);
        $this->set_node($this->node, $this->node->isadmin, 'isadmin', ($this->isadmin) ? 'true' : 'false');
        $this->persist();
    }

    private function set_node($mother, $child, $childname, $value) {
        if (isset($child)) {
            $mother->$childname = $value;
        } else {
            $mother->addChild($childname, $value);
        }
    }
}
?>