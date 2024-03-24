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
<p>You are an addon developer and want to host your addons here on the <?php echo REPONAME; ?>? For this you need a
    maintainer login, which can be sent to you on request with the desired login name (please let us know) and a temporary password
    sending to the administrators via email (<?php echo implode(', ', $admins->getadmins()); ?>).</p>

    <form name="n" id="n" action="<?php echo ROOT.CONTROLLER; ?>" method="post">
    <div class="login">
    <table><tr><td class="desc_form">Login:</td>
        <td>
            <input type="text" class="textfield_form" name="user" form="n">
        </td></tr>
        <tr><td class="desc_form">Password:</td>
        <td><input type="password" class="textfield_form" name="passwd" form="n">
        </td></tr>
        <tr><td colspan="2" align="center">
                <input class="button" type="submit" name='login' value="Login">
                <input class="button" type="submit" name='abort' value="Abort">
        </td></tr>
    </table>
    </div>

<?php
# Epilog
include FOOTER;
