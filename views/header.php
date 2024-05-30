<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=0.29" />
    <title><?php echo REPONAME.' '.REPOVERSION;?></title>
    <link rel="stylesheet" href="<?php echo ROOT.CSS; ?>" />
</head>
<body id="xhr_output">
<hr class="spacer">

<div class="banner">
    <div class="banner_txt">
        <h1><?php echo REPONAME.' '.REPOVERSION; ?></h1>
        <h2><?php echo 'Tree: '.substr($_SESSION['version'], 0, -1);
        if ($_SESSION['state'] == 1) {
            echo "<br>logged in as: ".$_SESSION['user'];
        }
        ?></h2>
    </div>
</div>
<hr class="spacer">

<?php
if (!empty($_SESSION['notice'])) {
    echo "<div class='alertbox' id='alertbox'>";
    echo "<div class='alertbox_text'>".$_SESSION['notice']."</div>";
    echo "<div class='closebtn' onclick='document.getElementById(\"alertbox\").style.display=\"none\";'>";
    echo "&times;</div></div>";
    unset($_SESSION['notice']);
}
?>

