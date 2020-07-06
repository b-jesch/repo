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

    <ul>
    <li>Der Dateiname des ZIPs sollte den Regeln für die Namensgebung von komprimierten Kodi-Addons folgen, z.B.
        <b>&lt;addonname&gt;-&lt;x.y.z&gt;.zip</b>, wobei &lt;addonname&gt; der Addon-ID sowie &lt;x.y.z&gt; der Addon-Version - wie sie
        auch in der addon.xml notiert sind - entsprechen muss. Ansonsten gelten die Richtlinien nach
        <a href="https://www.python.org/dev/peps/pep-0440/">PEP 440</a>.</li>
    <li>Entspricht die Namensgebung des hochgeladenen ZIPs nicht den Angaben der im Zip enthaltenen addon.xml (Addon-ID, Addon Version), wird das
        ZIP den Vorgaben seitens Kodi entsprechend umbenannt. Das ermöglicht z.B. den Upload direkt von Git (z.B. 'meinAddon-Master.zip').
        Jedoch ist vorher zu überprüfen, ob Benennung und Struktur der Ordner im ZIP korrekt sind.</li>
    <li>Die Struktur im Zip muss der Struktur eines Addons folgen. Nicht benötigte oder versteckte Dateien und/oder Ordner
        sollten entfernt werden (.git, .gitignore, .idea usw.).</li>
    <li>Die Einordnung des Addons in die passende Kodi-Version (hier Tree genannt) erfolgt über die
        <a href="https://kodi.wiki/view/Addon.xml#version_attribute_2">Versionierung der xbmc.python</a>. Ist
        das nicht möglich (z.B. bei Skins) wird das Addon der Kodi-Version
        "<?php echo ucwords(substr($version_dirs[FALLBACK_TREE], 0, -1)); ?>" zugeordnet.</li>
    <li>Der Upload von Addons, die das Konsumieren von illegal/widerrechtlich erworbenen oder bereitgestellten Content ermöglichen,
        ist nicht erlaubt. Es gelten die Verhaltensregeln der Kodinerds Community.</li>
    </ul>

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