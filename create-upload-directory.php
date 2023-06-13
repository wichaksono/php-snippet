<?php
/**
 * @return array 
 */
function upload_directory($document_root = '', $upload_directory_name = 'uploads') {
	if ( empty($document_root) ) {
		$document_root = $_SERVER['DOCUMENT_ROOT'];
	}

	$upload_directory_path = $document_root . DIRECTORY_SEPARATOR . $upload_directory_name;
	if ( ! is_dir($upload_directory_path) ) {
		mkdir($upload_directory_path);

		// adding index.php to hidden directory listing
		touch($upload_directory_path . '/index.php');
	}

	/**
	 * subdirectory by date YYYY-MM-DD
	 * result /uploads/2023/05/20/
	 */

	// start check or create year directory
	$sub_directory_year = $upload_directory_path . DIRECTORY_SEPARATOR . date('Y');
	if ( ! is_dir($sub_directory_year) ) {
		mkdir($sub_directory_year);

		// adding index.php to hidden directory listing
		touch($sub_directory_year . '/index.php');
	}

	// start check or create month directory
	$sub_directory_month = $sub_directory_year . DIRECTORY_SEPARATOR . date('m');
	if ( ! is_dir($sub_directory_month) ) {
		mkdir($sub_directory_month);

		// adding index.php to hidden directory listing
		touch($sub_directory_month . '/index.php');
	}

	// start check or create day number directory
	$sub_directory_day = $sub_directory_month . DIRECTORY_SEPARATOR . date('d');
	if ( ! is_dir($sub_directory_day) ) {
		mkdir($sub_directory_day);

		// adding index.php to hidden directory listing
		touch($sub_directory_day . '/index.php');
	}

	return array(
		'full_path' => $sub_directory_day,
		'path' => str_replace($document_root, '', $sub_directory_day)
	);
}
