<?php
/**
 * NEED
 * composer require google/apiclient:^2.15
 */
require 'vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

date_default_timezone_set('Asia/Jakarta');

// Konfigurasi
$DB_NAME   = "nama_database";
$DB_USER   = "root";
$DB_PASS   = "password";
$WEB_DIR   = "/var/www/html";
$DATE      = date("Y-m-d_H-i");
$TMP_DIR   = "/tmp/backup_$DATE";
$ZIP_FILE  = "/tmp/backup_{$DB_NAME}_$DATE.zip";

// Siapkan folder sementara
if (!file_exists($TMP_DIR)) {
    mkdir($TMP_DIR, 0777, true);
}

// Backup database
exec("mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $TMP_DIR/db.sql");

// Backup file website
exec("rsync -a $WEB_DIR $TMP_DIR/website");

// Buat ZIP
exec("cd /tmp && zip -r $ZIP_FILE backup_$DATE");

// Google Drive Client
$client = new Client();
$client->setAuthConfig('/var/www/credentials.json');
$client->addScope(Drive::DRIVE_FILE);
$service = new Drive($client);

// Upload ke Google Drive
$file = new Drive\DriveFile();
$file->setName(basename($ZIP_FILE));
$file->setParents(["root"]); // bisa ganti folder ID Google Drive

$content = file_get_contents($ZIP_FILE);
$uploadedFile = $service->files->create($file, [
    'data' => $content,
    'mimeType' => 'application/zip',
    'uploadType' => 'multipart',
]);

echo "Backup berhasil diupload ke Google Drive dengan ID: " . $uploadedFile->id . PHP_EOL;

// Hapus file sementara
exec("rm -rf $TMP_DIR");
unlink($ZIP_FILE);
