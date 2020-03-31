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

# Inhalt der View

$ks = array_shift($kodiversions);
$ke = array_pop($kodiversions);
?>

<h3>Addon-Upload</h3>

    Hier können Addons für die Kodi-Versionen ab <?php echo "$ks bis $ke"; ?> hochgeladen werden. Folgende Richtlinien sind zu beachten:
    <ol><li>Der Dateiname des ZIPs hat den Regeln für die Namensgebung von komprimierten Dateien zu folgen: <b>&lt;addonname&gt;-&lt;x.y.z&gt;.zip</b>, wobei &lt;addonname&gt; der Addon-ID sowie &lt;x.y.z&gt; der
            Addon-Version entsprechen sollte.</li>
        <li>Die Struktur im Zip muss der Struktur eines Addons folgen.</li>
    </ol>

    <!--<div id="upload">-->
    <div class="upload">
    <form name="u" id="u" method="post"
          action="<?php echo ROOT.CONTROLLER; ?>"
          enctype="multipart/form-data">

    <input type="file" name="upload" id="upload" class="fileupload" accept="application/zip"
           onchange="window.u.upload_info.value = this.value.replace('C:\\fakepath\\', '');">
    <label for="upload" class="button" >Addon auswählen</label>
        <input type="text" class="textfield_form" name="upload_info">
        <br>
    <input type="checkbox" name="overwrite" id="overwrite" value="overwrite">
    <label for="overwrite">vorhandene Version überschreiben</label><br>
    <input type="checkbox" name="reset_count" id="reset_count" value="reset_count">
    <label for="reset_count">Downloadzähler zurücksetzen</label>
        <hr class="spacer">
    <input type="submit" class="button" value="Hochladen"><br>
    <input type="hidden" name="action" value="<?php echo crypt('upload_p2'); ?>" />

    </form>
    </div>


<?php
# Epilog
include FOOTER;