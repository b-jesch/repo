<?php
# Prolog
if (!defined('CONTEXT')) {
    require 'start.php';
    header('Location: '.ROOT);
    exit();
}
include HEADER;
include NAVIGATION;

# functions for progress upload

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo ROOT.FUNCTIONS; ?>jquery.form.js"></script>
<script type="text/javascript" src="<?php echo ROOT.FUNCTIONS; ?>upload_progress.js"></script>
<?php

# Inhalt der View

$kn = KODI_NAMES;
$ks = array_shift($kn);
$ke = array_pop($kn);

$users = new User();
?>

<h3>Addon Upload</h3>

    <p>Addons for Kodi versions from <?php echo "$ks to $ke"; ?> can be uploaded here. The following guidelines must be
        observed:</p>

    <ul>
    <li>The file name of the ZIP should follow the rules for naming compressed Kodi add-ons, e.g.
        <b>&lt;addonname&gt;-&lt;x.y.z&gt;.zip</b>, where &lt;addonname&gt; must correspond to the addon ID and &lt;x.y.z&gt; to the addon
        version - as they are also noted in addon.xml. Otherwise, the guidelines following according to
        <a href="https://www.python.org/dev/peps/pep-0440/">PEP 440</a>.</li>
    <li>If the name of the uploaded ZIP does not correspond to the information in the addon.xml contained in the zip
        (addon ID, addon version), the ZIP will be renamed according to Kodi's specifications. This makes it possible,
        for example, to upload directly from Git (e.g. 'myAddon-Master.zip'). However, you must first check whether the
        naming and structure of the folders in the ZIP are correct.</li>
    <li>The structure in the zip must follow the structure of an add-on. Unnecessary or hidden files and/or folders
        should be removed (.git, .gitignore, .idea etc.).</li>
    <li>The addon is assigned to the appropriate Kodi version (called Tree here) via the
        <a href="https://kodi.wiki/view/Addon.xml#version_attribute_2">versioning</a> of the xbmc.python or xbmc.gui (skins).
        If this is not possible, the addon is assigned to the Kodi version
        "<?php echo ucwords(substr(VERSION_DIRS[FALLBACK_TREE], 0, -1)); ?>".</li>
    <li>The upload of addons that enable the consumption of illegally/unlawful acquired or provided content is not permitted.
        The rules of conduct of the Kodinerds community is applied.</li>
    </ul>

    <div class="upload">
    <form name="u" id="u" method="post"
          action="<?php echo ROOT.CONTROLLER; ?>"
          enctype="multipart/form-data">


        <input type="text" class="textfield_form" name="upload_info">
        <input type="file" name="upload" id="upload" class="fileupload" accept="application/zip, application/vnd.android.package-archive"
               onchange="window.u.upload_info.value = this.value.replace('C:\\fakepath\\', '');">
        <label for="upload" class="button" >Addon auswählen</label>
        <br>
        <input type="checkbox" name="overwrite" id="overwrite" value="overwrite">
        <label for="overwrite">Overwrite existing version</label><br>
        <input type="checkbox" name="devtool" id="devtool" value="devtool">
        <label for="devtool">Developer addon (only visible for maintainers)</label><br>

        <?php
        if ($_SESSION['isadmin']) {
            echo '<hr class="spacer">';
            echo '<label for="userlist">Select maintainer: </label>';
            echo '<select class="select" name="provider" id="userlist">';
            $usr_list = $users->getallusers(false);
            foreach($usr_list as $usr) {
                echo ($usr != $_SESSION['user']) ? "<option value='$usr'>$usr</option>" : "<option selected value='$usr'>$usr</option>";
            }
            echo '</select>';
        }
        ?>
        <hr class="spacer">
        <input type="submit" class="button" name="submit_btn" id="submit_btn" value="Upload" onclick="upload_addon();">
            <div class='progress' id="progress_div">
            <div class='bar' id='bar'></div>
            <div class='percent' id='percent'>0%</div>
        </div>
        <input type="hidden" name="action" value="<?php echo crypt('upload_p2', 'KN'); ?>" />
    </form>
    </div>
<?php
# Epilog
include FOOTER;