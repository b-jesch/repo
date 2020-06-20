<h1>Repository framework for Kodi Repositories with Web Interface.</h2>

<h2>Important</h2>

After Downloading/Cloning take al look at first to the config.php and change the ROOT entry to your installation folder.
After that you can browse to the root of the repository. All necessary folders will be created on first call.

    define('ROOT', 'https://repo.kodinerds.net/');
    # define('ROOT', 'http://localhost/repo/');

Your repository addon will be named like the settimgs in config.php

    define('REPONAME', 'Kodinerds Addon Repo');
    define('REPO_ID', 'repository.kodinerds');
    define('REPOVERSION', '7.0.0');
    define('PROVIDER', 'Kodinerds');

Deleting the content in /etc will create a new user list and a user with administrator access: admin/admin
After first login create a user, change the adminitrator status to admin and logout. Login with the new user account
and delete the admin entry, as the account is known and therefore a security hole.

