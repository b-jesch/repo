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

function compare_upload($p1, $p2) {
    $d1 = DateTime::createFromFormat('d.m.Y H:i', $p1->upload);
    $d2 = DateTime::createFromFormat('d.m.Y H:i', $p2->upload);
    return strcmp($d2->getTimestamp(), $d1->getTimestamp());
}

function compare_category($p1, $p2) {
    return strcmp($p1->category, $p2->category);
}

$addons = array();
$addondirs = getAllAddonDirs();

foreach ($addondirs as $addondir) {
    if (is_dir($addondir)) {
        $meta = glob($addondir.'/*.zip');
        foreach ($meta as $item) {
            $addon = new Addon($item);
            $addon->read();
            switch ($c_pars['scope']) {
                case 'all':
                    $header = '<h3>Addons ab ' . $_SESSION['version_name'] . '</h3>';
                    if ($addon->tree != $_SESSION['version']) continue;
                    $addons[] = $addon;
                    break;
                case 'user':
                    $header = '<h3>Alle Addons in allen Versionen von '.$c_pars['item'].'</h3>';
                    if ($c_pars['item'] != $addon->provider) continue;
                    $addons[] = $addon;
                    break;
                case 'cat':
                    $header = '<h3>Alle Addons der Kategorie "'.$c_pars['item'].'"</h3>';
                    if ($c_pars['item'] != $addon->category) continue;
                    $addons[] = $addon;
                    break;
                case 'search':
                    $header = '<h3>Alle Addons in allen Versionen, die "'.$c_pars['item'].'" enthalten</h3>';
                    if (!stristr($addon->name, $c_pars['item'])) continue;
                    $addons[] = $addon;
                    break;
                case 'last':
                    $header = '<h3>Zuletzt hochgeladene oder aktualisierte Addons</h3>';
                    $addons[] = $addon;
                    break;
                default:
                    $header = '<h3>Addons ab ' . $_SESSION['version_name'] . '</h3>';
                    if ($addon->tree != $_SESSION['version']) continue;
                    $addons[] = $addon;
            }
        }
    }
}

if ($c_pars['scope'] == 'last') {
    usort($addons, 'compare_upload');
} elseif ($c_pars['scope'] == 'all') {
    usort($addons, 'compare_tree');
    usort($addons, 'compare_names');
} elseif ($c_pars['scope'] == 'cat') {
    usort($addons, 'compare_category');
    usort($addons, 'compare_names');
}

$tc = 0;
echo $header;
echo '<table id="outer"><tr>';

foreach($addons as $addon) {
    if ($_SESSION['state'] == 0 and $addon->status & DEVTOOL) continue;
    createItemView($tc, $addon);
    $tc++;
    if ($c_pars['scope'] == 'last' and $tc >= MAX_ITEMS) break;
}
echo '</tr></table>';
if ($tc == 0) echo "<p class='alert'>Es wurden keine passenden Einträge gefunden.</p>".PHP_EOL;

# Epilog
include FOOTER;