<?php
# Prolog
if (!defined('CONTEXT')) {
    # be aware to configure path to minimal config (start.php) properly
    # inside start.php must defined at least a root path (ROOT) of the CMS
    require 'views/start.php';
    header('Location: '.ROOT);
    exit();
}
include HEADER;
include NAVIGATION;

# Inhalt der View

echo 'Hallo Welt...';

# Epilog
include FOOTER;