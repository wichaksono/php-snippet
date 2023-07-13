<?php
/**
 * Library: https://github.com/googleapis/google-api-php-client
 */
require_once 'vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

class BackupToGoogleDrive {

	private $domain = 'domain.com';

	private $parentFolderId = '1v0pvLRyJS1u0Cm_O5sFIMzUB5VBilhi6';

	private $directoryTarget;

	private $client;

	private $service;

	private $file;

	public function __construct($file) {
		try {
			$this->client = new Google\Client();

			putenv('GOOGLE_APPLICATION_CREDENTIALS=./credentials.json');
			$this->client->useApplicationDefaultCredentials();
			$this->client->addScope(Google\Service\Drive::DRIVE);
		
			$this->service =  new Google\Service\Drive($this->client);


			$this->directoryTarget = $this->setDirectory();

			$this->file = $file;

		} catch( Exception $e ) {
			echo "Error Message: " . $e;
		}
	}

	public function push() {
		# upload file
		$fileName =  basename($this->file);
		$mimeType = mime_content_type($this->file);

		$fileMetaData = new Drive\DriveFile([
			'name' => $fileName,
			'parents' => [$this->directoryTarget]
		]);

		$content = file_get_contents( $this->file );
		$file = $this->service->files->create($fileMetaData, [
			'data' => $content,
			'mimeType' => $mimeType,
			'uploadType' => 'multipart',
			'fields' => 'id'
		]);
	}

	private function setDirectory() {

		// domain
		$direcotryDomainId = $this->findDirectory($this->domain, $this->parentFolderId);
		if ( empty($direcotryDomainId) ) {
			$direcotryDomainId = $this->createDirectory($this->domain, $this->parentFolderId);
		}

		$currentYear = date('Y');
		$direcotryYearId = $this->findDirectory($currentYear, $direcotryDomainId);
		if ( empty($direcotryYearId) ) {
			$direcotryYearId = $this->createDirectory($currentYear, $direcotryDomainId);
		}

		// month
		$currentMonth = date('m');
		$direcotryMonthId = $this->findDirectory($currentMonth, $direcotryYearId);
		if ( empty($direcotryMonthId) ) {
			$direcotryMonthId = $this->createDirectory($currentMonth, $direcotryYearId);
		}

		$currentDay = date('d');
		$direcotryDayId = $this->findDirectory($currentDay, $direcotryMonthId);
		if ( empty($direcotryDayId) ) {
			$direcotryDayId = $this->createDirectory($currentDay, $direcotryMonthId);
		}

		return $direcotryDayId;

	}

	private function createDirectory($name, $parent_id) {
		# create folder
		$fileMetadata = new Drive\DriveFile(array(
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents'  => [$parent_id]
        ));

        $file = $this->service->files->create($fileMetadata, array(
            'fields' => 'id'));

        return $file->id;
	}

	private function findDirectory($name, $parent_id) {
		
		$response = $this->service->files->listFiles(array(
	        'q' => "mimeType='application/vnd.google-apps.folder' and name = '{$name}' and '{$parent_id}' in parents",
	        'spaces' => 'drive',
	        'fields' => 'nextPageToken, files(id, name)',
        ));

		$directory_id = '';
		if ( $response->files ) {
			foreach ($response->files as $file) {
				$directory_id = $file->id;
				break;
			}	
		}
		
		return $directory_id;
	} 

}

(new BackupToGoogleDrive('csv-1.csv') ) -> push();
