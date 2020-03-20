<?php

# Prolog

if (!defined('CONTEXT')) {
    die(__FILE__.' ausserhalb des MVC-Kontextes');
}

include HEADER;
include NAVIGATION;

# Inhalt der View

?>

    <form name="n" id="n" action="<?php echo ROOT.CONTROLLER; ?>" method="post">
    <div class="login">
    <table><tr><td class="desc_form">Login:</td>
        <td>
            <input type="text" class="textfield_form" name="login" form="n">
        </td></tr>
        <tr><td class="desc_form">Passwort:</td>
        <td><input type="password" class="textfield_form" name="passwd" form="n">
        </td></tr>
        <tr><td colspan="2" align="center">
                <input class="button" type="submit" value="Login">
                <input class="button" type="reset" value="Abbrechen">
        </td></tr>
    </table>
    </div>

<?php
# Epilog
include FOOTER;