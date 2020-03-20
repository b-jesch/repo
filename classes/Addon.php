<?php

class CreateRepoXML
{

    public $repo_folder = NULL;                          # Folder for addon.xml in summary
    public $repo_zips = NULL;                            # Folder of zipped addons

    function __construct($repo_folder, $zips) {

        $this->repo_folder = $repo_folder;
        $this->repo_zips = $repo_folder . $zips;
    }

    function createRepoAddonSummary() {

        $addons = scanFolder($this->repo_zips, array('.', '..'));
        if ($addons) {
            $out = '<?xml version="1.0"?>' . PHP_EOL . '<addons>' . PHP_EOL;
            foreach ($addons as $addon) {
                $content = file($this->repo_zips . $addon . '/addon.xml', FILE_SKIP_EMPTY_LINES);
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
    }

    public function createMasterXML() {
        $out = '<?xml version="1.0"?>' . PHP_EOL . '<addons>' . PHP_EOL;
        $content = file($this->repo_zips.'/addon.xml', FILE_SKIP_EMPTY_LINES);
        $s = true;
        foreach ($content as $line) {
            if (substr($line, 0, 7) != '<addon ' and $s) {
                continue;
            } else {
                $s = false;
                $out .= '    ' . $line;
            }
        }
        $out .= '</addons>' . PHP_EOL;
        $handle = fopen($this->repo_folder . '/addons.xml', 'w');
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

    function __construct($file, $id='') {
        $this->file = $file;
        $this->object_id = $id;
        $this->meta = substr($file,0, strlen(ADDON_EXT) * -1).META_EXT;
        $this->addonxml = pathinfo($file, PATHINFO_DIRNAME).'/addon.xml';
        $this->upload = date('d.m.Y H:i');
        $this->size = filesize($file);
        $this->downloads = 0;
    }

    public function create() {
        $this->writeProperties();
    }

    public function read() {
        $this->readProperties();
    }

    public function download() {
        $this->readProperties();
        $this->downloads = intval($this->downloads) + 1;
        $this->writeProperties();
        header('Content-Type: application.zip');
        header('Content-Disposition: attachment; filename="'.basename($this->file).'"');
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

            $archive_content = scanFolder($path.ARCHIVE, array('.', '..'));
            if (!$archive_content) delTree($path.ARCHIVE);
        }
        unlink($this->meta);
        unlink($this->file);
    }

    public function getAttrFromAddonXML() {
        $xml = simplexml_load_file($this->addonxml);
        if ($xml) {
            foreach($xml->extension as $extensionpoint) {
                if ($extensionpoint['point'] == 'xbmc.addon.metadata') $this->summary = $extensionpoint->summary[0];
            }

            $addon_attributes = iterator_to_array($xml->attributes());

            $this->author = $addon_attributes['provider-name'];
            $this->version = $addon_attributes['version'];
            $this->name = $addon_attributes['name'];
            $this->id = $addon_attributes['id'];
            $this->writeProperties();
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
        }
    }

    private function writeProperties() {
        $xml = simplexml_load_string('<addon/>');
        $xml->addChild('name', htmlspecialchars($this->name));
        $xml->addChild('addon_id', $this->id);
        $xml->addChild('version', $this->version);
        $xml->addChild('tree', $this->tree);
        $xml->addChild('summary', htmlspecialchars($this->summary));
        $xml->addChild('object', $this->file);
        $xml->addChild('object_id', $this->object_id);
        $xml->addChild('upload', $this->upload);
        $xml->addChild('provider', htmlspecialchars($this->provider));
        $xml->addChild('author', htmlspecialchars($this->author));
        $xml->addChild('downloads', $this->downloads);

        # XML nach DOM formatieren

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->saveXML());
        $dom->save($this->meta);
    }
}
?>