<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
    <title><?php echo REPONAME.' '.REPOVERSION;?></title>
    <link rel="stylesheet" href="<?php echo ROOT.CSS; ?>" />
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