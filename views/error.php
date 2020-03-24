<?php

# Prolog

include HEADER;
include NAVIGATION;

if (!defined('CONTEXT')) {
    die(__FILE__.' ausserhalb des MVC-Kontextes');
}

# Inhalt der View

header('HTTP/1.0 400 Bad Request');
echo '<h3>Fehler</h3>';
echo '<p>Ein Fehler ist aufgetreten: '.$errmsg.'</p>';

# Epilog
include FOOTER;
