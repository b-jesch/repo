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
$users = new User();
echo 'Registered maintainers (of which with <b>administration rights</b>): '.implode(', ', $users->getallusers(true));
$user = new User($_SESSION['user']);

?>
<h4>Maintainer-Bereich</h4>
<div class="setup">
    <form name="maintainer" id="maintainer" action="<?php echo ROOT.CONTROLLER; ?>" method="post">
        <input type="hidden" name="action" value="<?php echo crypt('setup_p1', 'KN'); ?>">
        <input type="text" class="textfield_form" readonly name="loginname" id="loginname" value="<?php echo $user->username; ?>">
        <label for="loginname">Login Name</label><br>
        <input type="password" class="textfield_form" name="newpw" id="newpw" value="">
        <label for="newpw">new Password</label><br>
        <input type="password" class="textfield_form" name="confirmpw" id="confirmpw" value="">
        <label for="confirmpw">re-enter new Password</label>
        <p class="alert">If the password fields are not filled in, the existing password will not be changed! <br>
            Otherwise, the current user is automatically logged out after validation and transfer to the system.</p>
        <hr class="spacer">
        <input type="text" class="textfield_form" name="realname" id="realname" value="<?php echo $user->realname; ?>">
        <label for="realname">Name</label><br>
        <input type="email" class="textfield_form" required name="email" id="email" value="<?php echo $user->email; ?>">
        <label for="email">Email Adress</label><br>
        <hr class="spacer">
        <input type="submit" class="button_red" value="adopt"><br>
    </form>
</div>
<?php
    if ($_SESSION['isadmin']) {
        ?>
        <!-- ADMIN LOUNGE -->
        <h4>Admin area - Add maintainer</h4>
        <div class="setup">
            <form name="admin_a" id="admin_a" action="<?php echo ROOT . CONTROLLER; ?>" method="post">
                <input type="hidden" name="action" value="<?php echo crypt('setup_p2', 'KN'); ?>">
                <input type="text" class="textfield_form" name="m_loginname" id="m_loginname" value="">
                <label for="m_loginname">Login Name</label><br>
                <input type="text" class="textfield_form" name="passwd" id="passwd" value="<?php echo passwdGen(); ?>">
                <label for="passwd">generated Password</label><br>
                <input type="submit" class="button_red" value="apply"><br>
            </form>
        </div>
    
        <h4>Admin area - Change/delete maintainer</h4>
        <div class="setup">
            <form name="admin_b" id="admin_b" action="<?php echo ROOT . CONTROLLER; ?>" method="post">
                <input type="hidden" name="action" value="<?php echo crypt('setup_p3', 'KN'); ?>">
                <select class="select" name="users" id="userlist">
                    <option value="" selected>* Maintainer *</option>
                    <?php
                    $usr_list = $users->getallusers(false);
                    foreach($usr_list as $usr) {
                        if ($usr != $_SESSION['user']) echo "<option value='$usr'>$usr</option>";
                    }
                    ?>
                </select>
                <label for="userlist">select Maintainer</label><br>
                <hr class="spacer">
                <label class="radio" for="adm_lounge_1">
                    <input type="radio" name="adm_lounge" id="adm_lounge_1" value="create_pw" checked>generate new password</label><br>
                <label class="radio" for="adm_lounge_2">
                    <input type="radio" name="adm_lounge" id="adm_lounge_2" value="grant_adm">set/delete administration status</label><br>
                <label class="radio" for="adm_lounge_3">
                    <input type="radio" name="adm_lounge" id="adm_lounge_3" value="delete_user">delete maintainer</label><br>
                <input type="submit" class="button_red" value="apply"><br>
            </form>
        </div>
        <?php
    }
        ?>

<?php
# Epilog
include FOOTER;
