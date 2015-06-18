<?php
// Selfie
// Simple comprehensive backup for Clipper site
// KP June 2015

// Get the essential Clipper path and DB details from config.inc.php
// Will require something to know where that is!
require './selfie.config.php';

// using authentication?
if (!empty($passwords)) {
    if ($config['PHP'] == 'CGI') {
        if (preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            list($name, $password) = explode(':', base64_decode($matches[1]));
            $_SERVER['PHP_AUTH_USER'] = strip_tags($name);
            $_SERVER['PHP_AUTH_PW'] = strip_tags($password);
        }
    }

    // login using directory passwords from config file
    $user = $_SERVER['PHP_AUTH_USER'];
    $pass = $_SERVER['PHP_AUTH_PW'];

    $users = array_keys($passwords);
    $validated = (in_array($user, $users)) && ($pass == $passwords[$user]);

    if (!$validated) {
      header('WWW-Authenticate: Basic realm="Selfie Backup System"');
      header('HTTP/1.0 401 Unauthorized');
      die ("Not authorized");
    }
}

$clipperConfig = $config['mgr_path'] . 'includes/config.inc.php';

if (!empty($config['site'])) {
    $prefix = $config['site'];
} else {
    $prefix = str_replace('www.', '', $_SERVER['SERVER_NAME']);
}

$prefix .= strftime('_%y%m%d_%H%M_%S');
$archiveFile = $prefix . '.zip';
$archive = realpath($config['archive']) .  DIRECTORY_SEPARATOR . $archiveFile;
$archiveUrl = '/' . trim ($config['archiveUrl'],'/') . '/' . $archiveFile;

$folders = explode(',', $config['folders']);
$roots = explode(',', $config['rootFiles']);

$rootFiles = array();
$rootPath = realpath($config['site_root']) . DIRECTORY_SEPARATOR;

if (in_array('clipper', $roots) || $roots[0] === '*') {
    $clipperFiles = explode(',', 'index.php, index-ajax.php, .htaccess, robots.txt');

    foreach ($clipperFiles as $clipperFile) {
        $clipperF = trim($clipperFile);
        if (file_exists($rootPath . $clipperF)) {
            $rootFiles[] = $clipperF;
        }
    }

    $rootList = implode(',', $rootFiles);
}

require './selfie.html';

?>
