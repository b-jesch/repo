<?php
# Prolog
if (!defined('CONTEXT')) {
    require 'start.php';
    header('Location: '.ROOT);
    exit();
}
include HEADER;
include NAVIGATION;
?>

<form name="d" id="d" action="<?php echo ROOT.CONTROLLER; ?>" method="post">
</form>

<?php

function compare_upload($p1, $p2) {
    $d1 = DateTime::createFromFormat('d.m.Y H:i', $p1->upload);
    $d2 = DateTime::createFromFormat('d.m.Y H:i', $p2->upload);
    return strcmp($d2->getTimestamp(), $d1->getTimestamp());
}

$addons = array();
$addondirs = array();


echo '<h3>Letzte Uploads</h3>';
foreach ($version_dirs as $version) {
    $v_dirs = scanFolder(ADDONFOLDER.$version.DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
    if ($v_dirs) {
        foreach($v_dirs as $v) {
            if (is_dir(ADDONFOLDER.$version.DATADIR.$v)) $addondirs[] = ADDONFOLDER.$version.DATADIR.$v;
        }
    }
}

foreach ($addondirs as $addondir) {
    if (is_dir($addondir)) {
        $meta = glob($addondir.'/*.zip');
        foreach ($meta as $item) {
            $addon = new Addon($item);
            $addon->read();
            $addons[] = $addon;
        }
    }
}

usort($addons, 'compare_upload');

$tc = 0;
echo '<table id="outer"><tr>';

foreach($addons as $addon) {
    # if (!empty($c_pars['user']) and $c_pars['user'] != $addon->provider) continue;
    createItemView($tc, $addon);
    $tc++;
    if ($tc >= MAX_ITEMS) break;
}
echo '</tr></table>';

?>
<?php
# Epilog
include FOOTER;