<?php
$databaseHost = 'localhost';
$databaseName = 'your_database';
$databaseUser = 'your_username';
$databasePassword = 'your_password';
$dumpFile = 'path/to/your/dump.sql';
$zipFile = 'path/to/your/dump.zip';

// Create the MySQL dump
$command = "mysqldump -h {$databaseHost} -u {$databaseUser} -p{$databasePassword} {$databaseName} > {$dumpFile}";
exec($command);

// Create a new ZIP archive
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
    exit('Failed to create ZIP archive');
}

// Add the SQL dump file to the ZIP archive
$zip->addFile($dumpFile, basename($dumpFile));

// Close the ZIP archive
$zip->close();

// Delete the SQL dump file
unlink($dumpFile);

echo 'Conversion completed. MySQL dump converted to ZIP.';
?>
