<?php
# Prolog
if (!defined('CONTEXT')) {
    require 'start.php';
    header('Location: '.ROOT);
    exit();
}
include HEADER;
include NAVIGATION;

function compare_names($p1, $p2) {
    return strcmp($p1->name, $p2->name);
}

function compare_tree($p1, $p2) {
    return strcmp($p1->tree, $p2->tree);
}

$addons = array();
$addondirs = array();

if (!empty($c_pars['user'])) {
    echo '<h3>Alle Addons in allen Versionen von '.$c_pars['user']. '</h3>';
    foreach ($version_dirs as $version) {
        $v_dirs = scanFolder(ADDONFOLDER.$version.DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
        if ($v_dirs) {
            foreach($v_dirs as $v) {
                if (is_dir(ADDONFOLDER.$version.DATADIR.$v)) $addondirs[] = ADDONFOLDER.$version.DATADIR.$v;
            }
        }
    }
} else {
    echo '<h3>Addons ab ' . $_SESSION['version_name'] . '</h3>';
    $v_dirs = scanFolder(ADDONFOLDER.$_SESSION['version'].DATADIR, array('.', '..', 'addons.xml', 'addons.xml.md5'));
    if ($v_dirs) {
        foreach($v_dirs as $v) {
            if (is_dir(ADDONFOLDER.$_SESSION['version'].DATADIR.$v)) $addondirs[] = ADDONFOLDER.$_SESSION['version'].DATADIR.$v;
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

usort($addons, 'compare_tree');
usort($addons, 'compare_names');

$tc = 0;
echo '<table id="outer"><tr>';

foreach($addons as $addon) {
    if (!empty($c_pars['user']) and $c_pars['user'] != $addon->provider) continue;
    createItemView($tc, $addon);
    $tc++;
}
echo '</tr></table>';

?>
<?php
# Epilog
include FOOTER;