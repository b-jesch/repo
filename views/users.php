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
echo 'Registrierte Maintainer (davon mit <b>Administrationsrechten</b>): '.implode(', ', $users->getallusers(true));
$user = new User($_SESSION['user']);

?>
<h4>Maintainer-Bereich</h4>
<div class="setup">
    <form name="maintainer" id="maintainer" action="<?php echo ROOT.CONTROLLER; ?>" method="post">
        <input type="hidden" name="action" value="<?php echo crypt('setup_p1'); ?>">
        <input type="text" class="textfield_form" readonly name="loginname" id="loginname" value="<?php echo $user->username; ?>">
        <label for="loginname">Login-Name</label><br>
        <input type="password" class="textfield_form" name="newpw" id="newpw" value="">
        <label for="newpw">neues Passwort</label><br>
        <input type="password" class="textfield_form" name="confirmpw" id="confirmpw" value="">
        <label for="confirmpw">neues Passwort erneut eingeben</label>
        <p class="alert">Werden die Passwortfelder nicht befüllt, erfolgt keine Änderung des bestehenden Passworts! <br>
            Ansonsten erfolgt nach der Validierung und Übernahme in das System eine automatische Abmeldung des aktuellen Benutzers.</p>
        <hr class="spacer">
        <input type="text" class="textfield_form" name="realname" id="realname" value="<?php echo $user->realname; ?>">
        <label for="realname">Name</label><br>
        <input type="email" class="textfield_form" required name="email" id="email" value="<?php echo $user->email; ?>">
        <label for="email">Email-Adresse</label><br>
        <hr class="spacer">
        <input type="submit" class="button_red" value="übernehmen"><br>
    </form>
</div>
<?php
    if ($_SESSION['isadmin']) {
        ?>
        <!-- ADMIN LOUNGE -->
        <h4>Admin-Bereich - Maintainer hinzufügen</h4>
        <div class="setup">
            <form name="admin_a" id="admin_a" action="<?php echo ROOT . CONTROLLER; ?>" method="post">
                <input type="hidden" name="action" value="<?php echo crypt('setup_p2'); ?>">
                <input type="text" class="textfield_form" name="m_loginname" id="m_loginname" value="">
                <label for="m_loginname">Login-Name</label><br>
                <input type="text" class="textfield_form" name="passwd" id="passwd" value="<?php echo passwdGen(); ?>">
                <label for="passwd">generiertes Passwort</label><br>
                <input type="submit" class="button_red" value="anwenden"><br>
            </form>
        </div>
    
        <h4>Admin-Bereich - Maintainer ändern/löschen</h4>
        <div class="setup">
            <form name="admin_b" id="admin_b" action="<?php echo ROOT . CONTROLLER; ?>" method="post">
                <input type="hidden" name="action" value="<?php echo crypt('setup_p3'); ?>">
                <select class="select" name="users" id="userlist">
                    <option value="" selected>--Maintainer--</option>
                    <?php
                    $usr_list = $users->getallusers(false);
                    foreach($usr_list as $usr) {
                        if ($usr != $_SESSION['user']) echo "<option value='$usr'>$usr</option>";
                    }
                    ?>
                </select>
                <label for="userlist">Maintainer auswählen</label><br>
                <hr class="spacer">
                <label class="radio" for="adm_lounge_1">
                    <input type="radio" name="adm_lounge" id="adm_lounge_1" value="create_pw" checked>neues Passwort generieren</label><br>
                <label class="radio" for="adm_lounge_2">
                    <input type="radio" name="adm_lounge" id="adm_lounge_2" value="grant_adm">Administrations-Status setzen/löschen</label><br>
                <label class="radio" for="adm_lounge_3">
                    <input type="radio" name="adm_lounge" id="adm_lounge_3" value="delete_user">Maintainer löschen</label><br>
                <input type="submit" class="button_red" value="anwenden"><br>
            </form>
        </div>
        <?php
    }
        ?>

<?php
# Epilog
include FOOTER;
