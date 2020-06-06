<?php
# Configuration File Repo saXBMC

define('DEBUG', true);

define('REPONAME', 'Kodinerds Addon Repo');
define('REPO_ID', 'repository.kodinerds');
define('REPOVERSION', '7.0.0');
define('PROVIDER', 'Kodinerds');

define('REPO_TEMPLATES', '__repo_templates/');  # Template Files
define('ADDON_TEMPLATE', '__addons.xml');       # Addon Template
define('DEFAULT_ADDON_ICON', 'default.png');    # default Icon, if icon.png in addon.zip is missing

define('REPOLOGO', 'css/9LWeq_c5_1920.jpg');
define('FLAG_BROKEN', 'css/broken.png');

# Root/Folders of CMS
# see also start.php in views folder
#
# .htaccess must reside in the root directory of the CMS e.g. ROOT (see below)

define('ROOT', 'https://repo.kodinerds.net/');
# define('ROOT', 'http://localhost/repo/');

define('CONTROLLER', 'index.php');
define('VIEWS', 'views/');

# special actions (encrypted path) shown in hidden formular inputs

$routing = array('setup_p1', 'setup_p2', 'setup_p3', 'upload_p2');

define('ADDONFOLDER', 'addons/');       # Rootfolder Upload
define('DATADIR', 'zip/');              # Addons data folder
define('TMPDIR', 'temp/');              # temporary files
define('ARCHIVE', 'archive/');          # Archive folder
define('ADDON_EXT', '.zip');            # Endung Addon-Objekt im Data-Verzeichnis
define('META_EXT', '.xml');             # Endung Metadaten-Datei
define('ETC', 'etc/');                  # Verschiedenes
define('USERDATA', 'userdata/');        # Nutzerdaten

# Stylesheets

define('CSS', 'css/styles.css');

# Views

define ('DEFAULTPAGE', 'login.php');    # Bootstrap
define ('UPLOAD', 'upload.php');        # Upload
define ('LISTVIEW', 'list.php');        # List view
define ('IMPRESS', 'impressum.php');    # Impressum
define ('DSGVO', 'dsgvo.php');          # DSVGO
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
define('LASTMODIFIED', date('y.m.d', filemtime(__FILE__)));

# Arrays (Kodi specific)

define('FALLBACK_TREE', 3);             # Default Tree zur Einordnung von Uploads, Jarvis (siehe Index $version_dirs)
define('DEFAULT_TREE', 4);              # Default Tree zur Anzeige im CMS

$kodiversions = array('Kodi 13 (Gotham)', 'Kodi 14 (Helix)', 'Kodi 15 (Isengard)', 'Kodi 16 (Jarvis)', 'Kodi 17 (Krypton)', 'Kodi 18 (Leia)',
    'Kodi 19 (Matrix)');

$version_dirs = array('gotham/', 'helix/', 'isengard/', 'jarvis/', 'krypton/', 'leia/', 'matrix/');

$addon_types = array('xbmc.gui.skin', 'xbmc.webinterface', 'xbmc.addon.repository', 'xbmc.service', 'xbmc.metadata.scraper.albuns',
    'xbmc.metadata.scraper.artists', 'xbmc.metadata.scraper.movies', 'xbmc.metadata.scraper.musicvideos', 'xbmc.metadata.scraper.tvshows',
    'xbmc.metadata.scraper.library', 'xbmc.ui.screensaver', 'xbmc.player.musicviz', 'xbmc.python.pluginsource', 'xbmc.python.script',
    'xbmc.python.weather', 'xbmc.subtitle.module', 'xbmc.python.lyrics', 'xbmc.python.library', 'xbmc.python.module', 'xbmc.addon.video',
    'xbmc.addon.audio', 'xbmc.addon.image', 'kodi.resource.images', 'kodi.source.languages');

$addon_category = array('Skin', 'Web Interface', 'Repository', 'Service', 'Album Information',
    'Artist Information', 'Movie Information', 'Music Video Information', 'TV Information',
    'Library', 'Screensaver', 'Visualization', 'Plugin', 'Script',
    'Weather', 'Subtitle Service Module', 'Lyrics', 'Python Library', 'Python Module', 'Video Addon',
    'Music Addon', 'Image Addon', 'Image Resource', 'Language Resource');

$addon_python = array('2.14.0', '2.19.0', '2.20.0', '2.24.0', '2.25.0', '2.26.0', '3.0.0');

# Thumbnails

define ('TBN_X', 130);                  # Breite
define ('TBN_Y', 130);                  # Höhe
define ('CPR', 3);                      # Cells per Row

define ('PHP_TAB', chr(9));
?>