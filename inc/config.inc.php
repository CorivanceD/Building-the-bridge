<?php

#timezone settings
date_default_timezone_set('Europe/Amsterdam');

# set locales to NL by default
setlocale(LC_TIME, 'en_EN');

# set UTF-8 encoding with header
header('Content-Type:text/html; charset=UTF-8');

define("PATH_PREFIX", "/default");

# set image folder
define("IMAGE_FOLDER", '/images');

# set admin folder
define("ADMIN_FOLDER", '/admin');

# set include folder
define("INC_FOLDER", DOCUMENT_ROOT . '/inc');
# load credentials (not in git)
require_once INC_FOLDER . '/secrets.inc.php';

# set classes folder
define("CLASSES_FOLDER", DOCUMENT_ROOT . '/classes');

# set include folder
define("PAGES_FOLDER", DOCUMENT_ROOT . '/pages' . PATH_PREFIX);

# set javascript folder
define("JS_FOLDER", '/js');

# set css folder
define("CSS_FOLDER", '/css' . PATH_PREFIX);

# set plugins folder
define("PLUGINS_FOLDER", '/plugins');

# set templates folder
define("TEMPLATES_FOLDER", DOCUMENT_ROOT . '/templates' . PATH_PREFIX);

# admin images foler
define("ADMIN_IMAGE_FOLDER", ADMIN_FOLDER . '/images');

# set admin include folder
define("ADMIN_INC_FOLDER", DOCUMENT_ROOT . ADMIN_FOLDER . '/inc');

# set admin include folder
define("ADMIN_TEMPLATES_FOLDER", DOCUMENT_ROOT . ADMIN_FOLDER . '/templates');

# set admin paginas folder
define("ADMIN_PAGES_FOLDER", DOCUMENT_ROOT . ADMIN_FOLDER . '/pages');

# set admin javascript folder
define("ADMIN_JS_FOLDER", ADMIN_FOLDER . '/js');

# set admin css folder
define("ADMIN_CSS_FOLDER", ADMIN_FOLDER . '/css');

# set admin plugins folder
define("ADMIN_PLUGINS_FOLDER", ADMIN_FOLDER . '/plugins');

# set the temporary folder 
define("ADMIN_TEMP", ADMIN_FOLDER . '/temp');


# content types
define('CONTENT_HOPE', 1);
define('CONTENT_LATEST_LETTERS', 2);
define('CONTENT_LUMINARIES', 3);
define('CONTENT_SEND_LETTER', 4);
define('CONTENT_WORLD_LEADERS', 5);

# include the base functions
require_once(INC_FOLDER . '/base_functions.inc.php');

# register the autoload function
spl_autoload_register('autoloadClass');

// SWF upload fix
if (http_post('PHPSESSID'))
    session_id(http_post('PHPSESSID'));

# start session
session_start();

// calculates the amount of days since the website went LIVE compared to today
$iDaysSinceLIVE = Date::strToDate('19-01-2015')->daysDiff(new Date('today')); // @todo enter the date the website went LIVE (first date)

# To echo query's and other debug stuff, true. Otherwise false
define("DEBUG", (isAside() ? 1 : 0)); // @todo set else to 0
define("DEBUG_QUERIES", 0);
define("DO_NOT_EMAIL_HTTP_ERRORS", $iDaysSinceLIVE >= 31); // optionally change the lenght of time, in days, of the http error emails

# error reporting
error_reporting(E_ALL | E_STRICT);
set_error_handler('error_handler', E_ALL | E_STRICT);
register_shutdown_function('shutdown_handler');

# Communication item settings
define("COMMUNICATION_COST_EMAIL", 0.05);
define("COMMUNICATION_COST_SMS", 0.20);
define("COMMUNICATION_API_USERNAME", "asidemedia");
define("COMMUNICATION_API_RESPONSETYPE", "XML");
define("COMMUNICATION_EMAIL_REPORT", "youri@a-side.nl");
define("COMMUNICATION_CLIENT_FROM_NAME", "Building the Bridge");
define("COMMUNICATION_CLIENT_EMAIL_FROM", "info@buildingthebridge.eu");
define("COMMUNICATION_IMAGES_W", "290");
define("COMMUNICATION_IMAGES_H", "140");
define("COMMUNICATION_EMAIL_WIDTH", "650");
define("COMMUNICATION_BOUNCE_EMAIL", "ihandle@bounce.a-side.nl");
define("COMMUNICATION_BOUNCE_URL", "http://bounce.a-side.nl/index.php");
define("COMMUNICATION_SENDBULK_EMAIL_URL", "https://a-side.email/api");
define("COMMUNICATION_SENDBULK_CUSTOMER_ID", 22);
define("COMMUNICATION_SENDBULK_CUSTOMERACCOUNT_ID", 46);

/*
 * Database connection details
 * To make a connection to an other container, make yourself some acces in 'Direct Admin' and
 * use the first letter of the user to connect. e.g. c.a-sidemedia.nl as host
 */
define("DB_HOST", getenv('DB_HOST') ?: "localhost");
define("DB_USER", getenv('DB_USER') ?: "bvkempen_btbuser");
define("DB_DATABASE", getenv('DB_DATABASE') ?: "bvkempen_btbdb");

# client details
define("CLIENT_NAME", "Building the Bridge"); //@todo change name
define("CLIENT_URL", getenv('CLIENT_URL') ?: "buildingthebridge.eu"); //@todo change url
define("CLIENT_IP", "149.210.143.248"); // client IP(s) komma seperated (x.x.x.x,x.x.x.x)
define('CLIENT_HTTP_URL', getenv('CLIENT_HTTP_URL') ?: 'https://buildingthebridge.eu');
define('CLIENT_HTTPS_URL', getenv('CLIENT_HTTPS_URL') ?: 'https://buildingthebridge.eu');
define("CLIENT_DEFAULT_EMAIL_TO", "info@buildingthebridge.eu, editor@buildingthebridge.eu, webmaster@buildingthebridge.eu, support@buildingthebridge.eu");

define("DEFAULT_EMAIL_FROM", CLIENT_NAME . " <info@buildingthebridge.eu>"); //@todo change email
define("DEFAULT_EMAIL_REPLY_TO", CLIENT_NAME . " <info@buildingthebridge.eu>"); //@todo change email
define("DEFAULT_EDITOR_EMAIL", "abraham@buildingthebridge.eu, tony@loyals.nl");

define("DEVELOPER_NAME", "Sergio Conde"); //name of the maker(s)
define("DEFAULT_ERROR_EMAIL", "error@loyals.nl"); //error@a-side.nl

define("ASIDE_LOG_FOLDER", DOCUMENT_ROOT . "/a-side_logs"); //level below public_html
# Current logged in user 
$oCurrentUser = http_session("oCurrentUser", null);
?>