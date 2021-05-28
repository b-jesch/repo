<?php
# Prolog
if (!defined('CONTEXT')) {
    require 'start.php';
    header('Location: '.ROOT);
    exit();
}

if ($errcode == 404) header("HTTP/1.0 404 Not Found", true, $errcode);
if ($errcode == 403) header("HTTP/1.0 403 Forbidden", true, $errcode);

include HEADER;
include NAVIGATION;

# Inhalt der View

echo '<h3>Fehler</h3>';
echo "<div class='alertbox' id='alertbox'>".$errmsg;
echo "</div>";
unset ($errmsg);

# Epilog
include FOOTER;
