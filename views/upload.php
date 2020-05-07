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

$ks = array_shift($kodiversions);
$ke = array_pop($kodiversions);

$users = new User();
?>

<h3>Addon-Upload</h3>

    <p>Hier können Addons für die Kodi-Versionen ab <?php echo "$ks bis $ke"; ?> hochgeladen werden. Folgende Richtlinien sind zu beachten:</p>

    <table><tr class="textrow"><td>-</td><td>Der Dateiname des ZIPs sollte den Regeln für die Namensgebung von komprimierten Kodi-Addons zu folgen, z.B.
                <b>&lt;addonname&gt;-&lt;x.y.z&gt;.zip</b>, wobei &lt;addonname&gt; der Addon-ID sowie &lt;x.y.z&gt; der Addon-Version - wie sie
                auch in der addon.xml notiert sind - entsprechen muss. Abweichende Versionierungen, z.B. nach PEP 440, sind
                zulässing und ab Matrix erwünscht.</td></tr>
        <tr class="textrow"><td>-</td><td>Entspricht die Namensgebung der hochgeladenen Datei nicht den Konventionen für ein Kodi-Addon, wird es
                den Vorgaben seitens Kodi entsprechend umbenannt. Das ermöglicht z.B. den Upload direkt von Git (z.B. 'meinAddon-Master.zip').</td></tr>
        <tr class="textrow"><td>-</td><td>Die Struktur im Zip muss der Struktur eines Addons folgen. Nicht benötigte oder versteckte
                Dateien und Ordner sollten entfernt werden (.git, .gitignore, .idea usw.).</td></tr>
    </table>

    <!--<div id="upload">-->
    <div class="upload">
    <form name="u" id="u" method="post"
          action="<?php echo ROOT.CONTROLLER; ?>"
          enctype="multipart/form-data">


        <input type="text" class="textfield_form" name="upload_info">
        <input type="file" name="upload" id="upload" class="fileupload" accept="application/zip"
               onchange="window.u.upload_info.value = this.value.replace('C:\\fakepath\\', '');">
        <label for="upload" class="button" >Addon auswählen</label>
        <br>
    <input type="checkbox" name="overwrite" id="overwrite" value="overwrite">
    <label for="overwrite">vorhandene Version überschreiben</label><br>
    <input type="checkbox" name="reset_count" id="reset_count" value="reset_count">
    <label for="reset_count">Downloadzähler zurücksetzen</label><br>


    <?php
    if ($_SESSION['isadmin']) {
        echo '<hr class="spacer">';
        echo '<label for="userlist">Maintainer auswählen: </label>';
        echo '<select class="select" name="provider" id="userlist">';
        $usr_list = $users->getallusers(false);
        foreach($usr_list as $usr) {
            echo ($usr != $_SESSION['user']) ? "<option value='$usr'>$usr</option>" : "<option selected value='$usr'>$usr</option>";
        }
        echo '</select>';
    }
    ?>
    <hr class="spacer">
    <input type="submit" class="button" value="Hochladen"><br>
    <input type="hidden" name="action" value="<?php echo crypt('upload_p2'); ?>" />

    </form>
    </div>


<?php
# Epilog
include FOOTER;