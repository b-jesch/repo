<?php
# Prolog
if (!defined('CONTEXT')) {
    require 'start.php';
    header('Location: '.ROOT);
    exit();
}
include HEADER;
include NAVIGATION;

# Inhalt der View

echo '<h3>Fehler</h3>';
echo "<div class='alertbox' id='alertbox'>".$errmsg;
echo "</div>";
unset ($errmsg);

# Epilog
include FOOTER;
