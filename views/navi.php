<div class="nav">
    <nav>
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
        <li><a href="<?php echo ROOT.CONTROLLER.'?action=last'; ?>" title="Last uploaded Addons">last Addons</a></li>
        <li><a href="<?php echo ROOT.ADDONFOLDER.REPO_ID.'/'.REPO_ID.'-'.REPOVERSION.ADDON_EXT; ?>"
               title="Download and install the '<?php echo REPONAME; ?>' Addon to receive automatic Updates of all Addons from this Repo">Download Repo Addon</a></li>
        <li><input type='search' name='search' class='search' form='s'><img alt='Suche' src='<?php echo SYMBOL_SEARCH; ?>'
                   class='symbol' onclick='document.s.action.value="search"; document.s.submit();'>
        </li>
        <?php
            if ($_SESSION['state'] == 1) {
                if ($_SESSION['isadmin']) {
                    $users = new User();
                    echo '<li class="dropdown">';
                    echo PHP_TAB.'<a href="javascript:void(0)" class="dropbtn">Maintainer Addons</a>';
                    echo PHP_TAB.'<div class="dropdown-content">';

                    $usr_list = $users->getallusers(false);
                    foreach($usr_list as $usr) {
                        echo "<a href='".ROOT.CONTROLLER."?action=list&user=".$usr."'>$usr</a>".PHP_EOL;
                    }
                    echo "</div></li>";

                } else {
                    echo "<li><a href=\"".ROOT.CONTROLLER.'?action=list&user='.$_SESSION['user']."\" title='My Addons'>Meine Addons</a></li>".PHP_EOL;
                }
                echo "<li><a href=\"".ROOT.CONTROLLER.'?action=upload'."\" title='Addon Upload'>Upload</a></li>".PHP_EOL;
                echo "<li><a href=\"".ROOT.CONTROLLER.'?action=setup'."\" title='My settings and contact data'>Setup</a></li>".PHP_EOL;
                echo "<li style=\"float: right\"><a href=\"".ROOT.CONTROLLER.'?action=logout'."\">Maintainer Logout</a></li>".PHP_EOL;
            } else {
                echo "<li style=\"float: right\"><a href=\"".ROOT.CONTROLLER.'?action=login'."\" title='Developers only'>Maintainer Login</a></li>".PHP_EOL;
            }
        ?>
        </ul>
    </nav>
</div>
<form name="s" id="s" action="<?php echo CONTROLLER; ?>" method="post">
    <input type="hidden" name="action" value="">
</form>
<hr class="spacer">