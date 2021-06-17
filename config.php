<?php
# Configuration File Repo saXBMC

const DEBUG = false;

const REPONAME = 'Kodinerds Addon Repo';
const REPO_ID = 'repository.kodinerds';
const REPOVERSION = '7.0.1.1';
const PROVIDER = 'Kodinerds';

const REPO_TEMPLATES = '__repo_templates/';  # Template Files
const ADDON_TEMPLATE = '__addons.xml';       # Addon Template
const DEFAULT_ADDON_ICON = 'default.png';    # default Icon, if icon.png in addon.zip is missing

const REPOLOGO = 'css/9LWeq_c5_1920.jpg';

const BROKEN = 1;
const FLAG_BROKEN = 'css/broken.png';
const DEVTOOL = 2;
const FLAG_DEVTOOL = 'css/devtool.png';


# Root/Folders of CMS
# see also start.php in views folder
#
# .htaccess must reside in the root directory of the CMS e.g. ROOT (see below)

define('SCRIPT_ROOT', dirname(__FILE__));

const ROOT = 'https://repo.kodinerds.net/';
# const ROOT = 'http://localhost/repo/';

const CONTROLLER = 'index.php';
const VIEWS = 'views/';

# special actions (encrypted path) shown in hidden formular inputs

const ROUTE = array('setup_p1', 'setup_p2', 'setup_p3', 'upload_p2');

const ADDONFOLDER = 'addons/';           # Rootfolder Upload
const DATADIR = 'zip/';                  # Addons data folder
const TMPDIR = 'temp/';                  # temporary files
const LOCKFILE = '.locked';              # mark folder as locked (prevent for deletion), when LOCKFILE exists

const FLOOD_DB = 'antiflood/database';   # Antiflood database
const FLOOD_LOCKDIR = 'antiflood/lock/'; # Antiflood locked folder

const ARCHIVE = 'archive/';              # Archive folder
const ADDON_EXT = '.zip';                # Endung Addon-Objekt im Data-Verzeichnis
const META_EXT = '.xml';                 # Endung Metadaten-Datei
const ETC = 'etc/';                      # Verschiedenes
const USERDATA = 'userdata/';            # Nutzerdaten
const WEBDAV = 'webdav/';                # Webdav-/Repoverzeichnis

# Antiflood Parameters

const FLOOD_MAX_REQ = 10;                # max allowed page requests for an IP
const FLOOD_REQ_TIMEOUT = 3;             # start counting page requests if time between request lower than this value
const FLOOD_BAN_TIME = 120;              # lock delay, after this time without page requests, lock file will be removed on next request

# Stylesheets & Icons

const CSS = 'css/styles.css';
const SYMBOL_SEARCH = 'css/lupe2.svg';
const SYMBOL_GIT = 'css/octicon.svg';

# Misc

const GITHUB = 'github.com';


# Views

const DEFAULTPAGE = 'login.php';    # Bootstrap
const UPLOAD = 'upload.php';        # Upload
const LISTVIEW = 'list.php';        # List view
const IMPRESS = 'impressum.php';    # Impressum
const DSGVO = 'dsgvo.php';          # DSVGO
const MODIFYVIEW = 'modify.php';    # Modify View
const LOGINPAGE = 'login.php';      # Login View
const SETUP = 'users.php';          # User Maintenance View
const ERRORPAGE = 'error.php';      # Fehlerseite

# Model

const CLASSES = 'classes/';

# Functions

const FUNCTIONS = 'functions/';

# Helpers

const HEADER = 'header.php';         # Header
const FOOTER = 'footer.php';         # Footer
const NAVIGATION = 'navi.php';       # Navigation

# global MVC-Context

const CONTEXT = true;
define('LASTMODIFIED', date('y.m.d', filemtime(__FILE__)));

# Arrays (Kodi specific)

const FALLBACK_TREE = 3;             # Index d. default Trees zur Einordnung von Uploads, Jarvis (siehe Index VERSION_DIRS)
const DEFAULT_TREE = 4;              # Index d. default Trees zur Anzeige im CMS (Krypton)

const KODI_NAMES = array('Kodi 13 (Gotham)', 'Kodi 14 (Helix)', 'Kodi 15 (Isengard)', 'Kodi 16 (Jarvis)', 'Kodi 17 (Krypton)', 'Kodi 18 (Leia)',
    'Kodi 19 (Matrix)');

const VERSION_DIRS = array('gotham/', 'helix/', 'isengard/', 'jarvis/', 'krypton/', 'leia/', 'matrix/');

const AD_TYPES = array('xbmc.gui.skin', 'xbmc.webinterface', 'xbmc.addon.repository', 'xbmc.service', 'xbmc.metadata.scraper.albuns',
    'xbmc.metadata.scraper.artists', 'xbmc.metadata.scraper.movies', 'xbmc.metadata.scraper.musicvideos', 'xbmc.metadata.scraper.tvshows',
    'xbmc.metadata.scraper.library', 'xbmc.ui.screensaver', 'xbmc.player.musicviz', 'xbmc.python.pluginsource', 'xbmc.python.script',
    'xbmc.python.weather', 'xbmc.subtitle.module', 'xbmc.python.lyrics', 'xbmc.python.library', 'xbmc.python.module', 'xbmc.addon.video',
    'xbmc.addon.audio', 'xbmc.addon.image', 'kodi.resource.images', 'kodi.resource.language', 'kodi.resource.uisounds');

const AD_CATEGORIES = array('Skin', 'Web Interface', 'Repository', 'Service', 'Album Information',
    'Artist Information', 'Movie Information', 'Music Video Information', 'TV Information',
    'Library', 'Screensaver', 'Visualization', 'Plugin', 'Script',
    'Weather', 'Subtitle Service Module', 'Lyrics', 'Python Library', 'Python Module', 'Video Addon',
    'Music Addon', 'Image Addon', 'Image Resource', 'Language Resource', 'Sound Resource');

const AD_PYTHON_VERS = array('2.14.0', '2.19.0', '2.20.0', '2.24.0', '2.25.0', '2.26.0', '3.0.0');

# Thumbnails

const TBN_X = 130;                  # Breite
const TBN_Y = 130;                  # Höhe
const CPR = 3;                      # Cells per Row
const MAX_ITEMS = 18;               # max Items in List/Last Views
define ('PHP_TAB', chr(9));
?>