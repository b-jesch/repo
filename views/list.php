<?php
/**
 * Created by PhpStorm.
 * User: jesch
 * Date: 06.11.2018
 * Time: 09:10
 */
# Prolog
include HEADER;
include NAVIGATION;

if (!defined('CONTEXT')) {
    die(__FILE__.' ausserhalb des MVC-Kontextes');
}
?>

<form name="d" id="d" action="<?php echo ROOT.CONTROLLER; ?>" method="post">
</form>

<?php

function compare_names($p1, $p2) {
    return strcmp($p1->name, $p2->name);
}

function compare_tree($p1, $p2) {
    return strcmp($p1->tree, $p2->tree);
}

function createItemView($column, $addon) {
    if ($column % CPR == 0 and $column > 0) echo '</tr><tr>'.PHP_EOL;
    echo '<td class="item">' .PHP_EOL;
    echo PHP_TAB.'<table class="inner">'.PHP_EOL;
    echo PHP_TAB.'<tr><td class="header" colspan="3">'.$addon->name.' - '.convertHRV($addon->size).'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td rowspan="8" class="tbn_inner"><img src="'.$addon->thumb.'" title="'.$addon->summary.'" width="'.TBN_X.'" height="'.TBN_Y.'"></td>';
    echo PHP_TAB.'<tr><td>Kategorie:</td><td class="data">'.$addon->category.'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Addon ID:</td><td class="data">'.$addon->id.'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Version:</td><td class="data">'.$addon->version.' ('.ucwords(substr($addon->tree, 0, -1)).')</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Autor:</td><td class="data">'.$addon->author.'</td></tr>'.PHP_EOL;
    echo PHP_TAB.'<tr><td>Upload:</td><td class="data">'.$addon->upload.'</td></tr>'.PHP_EOL;

    if ($_SESSION['state'] == 1) {
        echo PHP_TAB.'<tr><td>durch:</td><td class="data">'.$addon->provider.'</td></tr>'.PHP_EOL;
        echo PHP_TAB.'<tr><td>Downloads:</td><td class="data">'.$addon->downloads.' (total: '. $addon->downloads_total .')</td></tr>'.PHP_EOL;
    } else {
        echo PHP_TAB.'<tr><td>&nbsp;</td><td class="data">&nbsp;</td></tr>'.PHP_EOL;
        echo PHP_TAB.'<tr><td>&nbsp;</td><td class="data">&nbsp;</td></tr>'.PHP_EOL;
    }

    echo PHP_TAB.'<tr><td colspan="3">';
    if ($_SESSION['state'] == 1 and $_SESSION['user'] == $addon->provider) {
        echo '<button form="d" name="item" type="submit" class="button_red" value="delete='.$addon->object_id.'" onclick="return fConfirm()">löschen</button>';
    }
    echo '<button form="d" name="item" type="submit" class="button" title="Download '.basename($addon->file).'" value="download='.$addon->object_id.'">downloaden</button></td></tr></table>'.PHP_EOL;
    echo '</td>'.PHP_EOL.PHP_EOL;
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