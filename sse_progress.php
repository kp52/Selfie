<?php
header('Content-Type: text/event-stream');
// recommended to prevent caching of event data.
header('Cache-Control: no-cache');

$version = '0.7';
$folders = $_GET['folders'];
$archive = $_GET['archive'];
$clipperConfig = $_GET['config'];
$rootFiles = $_GET['rootFiles'];
$dbDump = $_GET['db'];

$startTime =  microtime(true);

require $clipperConfig;
require './dump.class.inc.php';

$data = new dbDump($database_server, $database_user, $database_password, $dbname);
$data->root = realpath(MODX_BASE_PATH) . DIRECTORY_SEPARATOR;

$msg = 1;
$data->send_message($msg++, '<h2>Selfie v' . $version . '</h2> <p>Starting backup</p> ');

if (!empty($folders)) {

    $folders = explode(',', $folders);

    foreach ($folders as $folder) {
        $data->send_message($msg++, "saving $folder to $archive ... \n");;
        $folder = trim($folder);
        $folder = $data->root . $folder;
        $zipped = $data->zipData($folder, $archive);
    }
}

if (!empty($rootFiles)) {
    $data->send_message($msg++, "saving root files to $archive ... \n");

    $rootFiles = explode(',', $rootFiles);
    foreach ($rootFiles as $rootFile) {
        $rootFile = $data->root . $rootFile;
        $zipped = $data->zipData($rootFile, $archive);
    }
}

$created = strftime('%d %B %Y at %H%M');
$site = $_SERVER['HTTP_HOST'];

if ($dbDump === 'true') {
    $data->send_message($msg++, "dumping database") ;

    $sql = <<<DUMP
#
# Database dump of $dbase from $site
# Created by Selfie backup application $created
#
DUMP;

    $sql .= $data->dump($dbase);
    $sqlFile = str_replace('.zip', '.sql', basename($archive));
    $data->zipString($sql, $sqlFile, $archive);
    $data->send_message ($msg++, "Database done!\n");
}

$elapsed =  microtime(true) - $startTime;
$elapsed = round($elapsed, 2);
$IP = $_SERVER['REMOTE_ADDR'];
$created = strftime('%d %B %Y at %H%M');

$logMessage = <<<LOG
Selfie site backup\n
Backup of $site created $created\n
from IP address $IP\n
Time to build: $elapsed seconds
LOG;

$data->zipString($logMessage, 'log.txt', $archive);

$data->send_message('CLOSE', 'Process complete');
?>