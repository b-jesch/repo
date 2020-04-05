<ul>
    <li><a href="<?php echo ROOT.CONTROLLER.'?action=list'; ?>" title="Repository Start Page">Home</a></li>
    <li class="dropdown">
        <a href="javascript:void(0)" class="dropbtn">Kodi Version</a>
        <div class="dropdown-content">
            <?php
            $i = 0;
            foreach ($kodiversions as $version) {
                echo "<a href='".ROOT.CONTROLLER.'?version='.$version_dirs[$i]."'>".$version."</a>".PHP_EOL;
                $i++;
            }
            ?>
        </div>
    </li>
    <li><a href="<?php echo ROOT.ADDONFOLDER.REPO_ID.'/'.REPO_ID.'-'.REPOVERSION.ADDON_EXT; ?>"
           title="Download and install the <?php echo REPONAME; ?> Addon to receive automatic Updates from this Repo">Download Repo Addon</a></li>
    <?php
        if ($_SESSION['state'] == 1) {
            echo "<li><a href=\"".ROOT.CONTROLLER.'?action=upload'."\" title='Addon Upload'>Upload</a></li>".PHP_EOL;
            echo "<li><a href=\"".ROOT.CONTROLLER.'?action=setup'."\" title='My settings and contact data'>Setup</a></li>".PHP_EOL;
            echo "<li style=\"float: right\"><a href=\"".ROOT.CONTROLLER.'?action=logout'."\">Maintainer Logout</a></li>".PHP_EOL;
        } else {
            echo "<li style=\"float: right\"><a href=\"".ROOT.CONTROLLER.'?action=login'."\" title='Developers only'>Maintainer Login</a></li>".PHP_EOL;
        }
    ?>
</ul>
<hr class="spacer">