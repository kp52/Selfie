<?php

class dbDump {

function __construct($database_server, $database_user, $database_password, $dbname) {
    $this->db = new mysqli($database_server, $database_user, $database_password, $dbname);
    return;
}

function send_message($id, $message, $type='text', $progress=0) {
    $d = array('message' => $message , 'type' => $type, 'progress' => $progress);

    echo "id: $id" . PHP_EOL;
    echo "data: " . json_encode($d) . PHP_EOL;
    echo PHP_EOL;

    ob_flush();
    flush();
}

// add all files in folder $source to zip file
function zipData($source, $destination) {
    if (file_exists($source)) {
        $zip = new ZipArchive();
        if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
            $sourceFolder = $source . DIRECTORY_SEPARATOR;
            $source = realpath($source);
            if (is_dir($source)) {
                $fileCount = 0;
                $iterator = new RecursiveDirectoryIterator($source);
                // skip dot files while iterating
                $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
                $nFiles = iterator_count($files);
                $this->send_message('', '', 'target', $nFiles);
                foreach ($files as $file) {
                    $file = realpath($file);
                    $fileCount++;
                    if ($fileCount % 10 === 0) {
                        $this->send_message('', '', 'busy', $fileCount/$nFiles);
                    }
                    if (is_dir($file)) {
                        $zip->addEmptyDir(str_replace($this->root, '', $file . '/'));
                    } else if (is_file($file)) {
                       $zip->addFromString(str_replace($this->root, '', $file), file_get_contents($file));
                    }
                }
            $this->send_message('', '', 'reset');
            } else if (is_file($source)) {
                $this->send_message('', $source);
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }
        $success = $zip->close();
    } else {
        $success = false;
    }
    return $success;
}

// add string to zip file as a file inthe archive folder
function zipString($content, $filename, $destination) {
    $zip = new ZipArchive();

    if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
        $zip->addFromString($filename, $content);
    } else {
        $success = false;
    }
    return $success;
}


// return database contents as an SQL string
function dump($database) {
    $dbTables = $this->db->query("SHOW TABLES FROM $database");

    $tableMakers = array();
    $sqlAll = array();

    while ($table = $dbTables->fetch_row()) {
        $tableTop = $this->db->query("SHOW CREATE TABLE $database.{$table[0]}");
        while ($createRow = $tableTop->fetch_row()) {
            $tableSql = array();
    // [0] = table name; [1]=creation story
            $tableName = $createRow[0];
            $tableMakers[$tableName]['create'] = $createRow[1];
            $tableData = $this->db->query("SELECT * FROM $database.$tableName");

            $ins = "INSERT INTO `$tableName` VALUES(%s);";

            while($row = $tableData->fetch_row()) {
                $rowValues = array();
                foreach($row as $value) {
                    $value = addslashes($value);
                    $value = str_replace("\n", '\\r\\n', $value);
                    $value = str_replace("\r", '', $value);
                    $rowValues[] = "'$value'";
                }
                $tableSql[] = sprintf($ins, implode(',', $rowValues));
            }

            $sqlAll[] = "\n\n### Data for $tableName ###\n";
            $sqlAll[]  = "DROP TABLE IF EXISTS `$tableName`;";
            $sqlAll[] = $tableMakers[$tableName]['create'] . ";\n";
            $sqlAll[] = implode("\n", $tableSql);
        }
    }

    $sql = implode("\n", $sqlAll);

    return $sql;
}

}
?>