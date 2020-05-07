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

$admins = new User();
$admins->getadmins();
?>
<p>Du bist Addon-Entwickler und möchtest Deine Addons hier auf dem <?php echo REPONAME; ?> hosten? Dazu benötigst Du ein
Maintainer-Login, welches Dir mit dem gewünschten Login-Namen (bitte mitteilen) und einem vorläufigen Passwort auf Anfrage
    per Email an die Administratoren (<?php echo implode(', ', $admins->getadmins()); ?>) zugesendet wird.</p>

    <form name="n" id="n" action="<?php echo ROOT.CONTROLLER; ?>" method="post">
    <div class="login">
    <table><tr><td class="desc_form">Login:</td>
        <td>
            <input type="text" class="textfield_form" name="user" form="n">
        </td></tr>
        <tr><td class="desc_form">Passwort:</td>
        <td><input type="password" class="textfield_form" name="passwd" form="n">
        </td></tr>
        <tr><td colspan="2" align="center">
                <input class="button" type="submit" name='login' value="Login">
                <input class="button" type="submit" name='abort' value="Abbrechen">
        </td></tr>
    </table>
    </div>

<?php
# Epilog
include FOOTER;
