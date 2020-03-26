<?php
# Configuration File Repo saXBMC

define('DEBUG', true);

define('REPONAME', 'saXBMC Repo');
define('REPO_ID', 'repository.saxbmc');
define('REPOVERSION', '1.2.1');
define('REPOLOGO', 'css/wbbLogo.png');

# Root/Folders of CMS
#
# .htaccess must reside in the root directory of the CMS e.g. ROOT (see below)

define('ROOT', 'http://localhost/repo/');

# define('ROOT', 'https://www.quarantine.hs-mittweida.de/~jesch/repo/');

define('CONTROLLER', 'index.php');
define('VIEWS', 'views/');

define('ADDONFOLDER', 'addons/');       # Rootfolder Upload
define('DATADIR', 'zip/');              # Addons data folder
define('TMPDIR', 'temp/');              # temporary files
define('ARCHIVE', 'archive/');          # Archive folder
define('ADDON_EXT', '.zip');            # Endung Addon-Objekt im Data-Verzeichnis
define('META_EXT', '.xml');             # Endung Metadaten-Datei
define('USER_DB', 'etc/uPwFile.xml');   # User Management

# Stylesheets

define('CSS', 'css/styles.css');

# Views

define ('DEFAULTPAGE', 'login.php');    # Bootstrap
define ('UPLOAD', 'upload.php');        # Upload
define ('LISTVIEW', 'list.php');        # List view
define ('IMPRESS', 'impressum.php');    # Impressum
define ('MODIFYVIEW', 'modify.php');    # Modify View
define ('LOGINPAGE', 'login.php');      # Login View
define ('SETUP', 'users.php');          # User Maintenance View
define ('ERRORPAGE', 'error.php');      # Fehlerseite

# Model

define('CLASSES', 'classes/');

# Functions

define('FUNCTIONS', 'functions/');

# Helpers

define('HEADER', 'header.php');         # Header
define('FOOTER', 'footer.php');         # Footer
define('NAVIGATION', 'navi.php');       # Navigation


# global MVC-Context

define('CONTEXT', true);

# Arrays

$kodiversions = array('Kodi 17 (Krypton)', 'Kodi 18 (Leia)', 'Kodi 19 (Matrix)');
$version_dirs = array('krypton/', 'leia/', 'matrix/');
$routing = array('setup_p1', 'setup_p2', 'setup_p3', 'upload_p2');

# Thumbnails

define ('TBN_X', 150);                  # Breite
define ('TBN_Y', 150);                  # Höhe
define ('CPR', 3);                      # Cells per Row

define ('PHP_TAB', chr(9));
?>