<?php

# Prolog

include HEADER;
include NAVIGATION;

if (!defined('CONTEXT')) {
    die(__FILE__.' ausserhalb des MVC-Kontextes');
}

# Inhalt der View

echo 'Ein Fehler ist aufgetreten: '.$errmsg;

# Epilog
include FOOTER;