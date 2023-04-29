<h1>Repository framework for Kodi Repositories with Web Interface.</h1>

<h2>Additional Requirements</h2>

This repo uses X-Sendfile to deploy the addons. X-Sendfile requires an apache/nginx mod 'mod_xsendfile' to be installed 
and configured, see: https://tn123.org/mod_xsendfile/

<h2>Important</h2>

After Downloading/Cloning take al look at first to the ```config.php``` and change the ROOT entry to your installation folder.
After that you can browse to the root of the repository. All necessary folders will be created on first call.

    define('ROOT', 'https://repo.kodinerds.net/');
    # define('ROOT', 'http://localhost/repo/');

Your repository addon will be named like the settings in the config.php

    define('REPONAME', 'Kodinerds Addon Repo');
    define('REPO_ID', 'repository.kodinerds');
    define('REPOVERSION', '7.0.0');
    define('PROVIDER', 'Kodinerds');

Modify/change the images and the ```__addons.xml``` in the ```addons/__repo_templates``` folder for future Kodi version updates. 
Also extend the version arrays in config.php (KODI_NAMES, VERSION_DIRS, AD_PYTHON_VERS). Deleting the ```addon.xml``` and 
the ```addon.md5``` files will create new addon.xml/md5 files including the ones in the version subfolders on next call.

Deleting the content in ```etc/userdata/``` will create a new user database within a user with administrator access:

        Login:  admin
        Passwd: admin

**After first login with the credentials above create a new user, change the administrator status to admin for this user and logout. Login with the new user account and delete the admin entry, as the account is known on a plain installation and therefore a security risk.**

<h2>Backup</h2>

It is sufficient to back up the following folders/files:

- ```addons/``` folder (repositories and addons)
- ```etc/userdata/``` folder (user management)
- ```config.php``` (main configuration)

To restore your backup simply copy the folders/files to their destinations within the CMS