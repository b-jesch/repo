<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
    <title><?php echo REPONAME.' '.REPOVERSION;?></title>
    <link rel="stylesheet" href="<?php echo ROOT.CSS; ?>" />
    <script src="<?php echo ROOT.FUNCTIONS.'functions.js'; ?>"></script>
</head>
<body>
<hr class="spacer">
<div class="banner">
    <div class="banner_txt">
        <h1><?php echo REPONAME.' '.REPOVERSION; ?></h1>
        <h2><?php echo 'Tree: '.substr($_SESSION['version'], 0, -1); ?></h2>
        <h2><?php
        if ($_SESSION['state'] == 1) {
            echo "logged in as: ".$_SESSION['user'];
        }
        ?></h2>
    </div>
</div>
<hr class="spacer">

<?php
if ($notice) {
    echo "<div class='alertbox' id='alertbox'>".$notice;
    echo "<span class='closebtn'>";
    echo "<input type='button' class='toolbox' value='kopieren' onclick='CopyClipboard(\"alertbox\");'>".PHP_EOL;
    echo "<input type='button' class='toolbox' value='schliessen' onclick='document.getElementById(\"alertbox\").style.display=\"none\";'>";
    echo "</span></div>";
    # echo "<span class='closebtn' onclick='this.parentElement.style.display=\"none\";'>&times;</span></div>";
    # echo "<input type='button' id='copybtn' value='kopieren' onclick='CopyClipboard(\"alertbox\")'>&times;</span></div>";
    unset($notice);
}
?>

