<?php
/**
 * abis muter-muter dari mbah google ketemu ama script cara copy folder pake PHP
 * dan berikut cara PHP Copy Folder nya.
 */
 
function recursive_copy($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				  recursive_copy($src .'/'. $file, $dst .'/'. $file);
			}
			else {
				copy($src .'/'. $file,$dst .'/'. $file);
			}
		}
	}
	closedir($dir);
}

# cara pakai #
$folder_copy_dir  = 'main';
$folder_paste_dir = 'main-inc'; 

recursive_copy($folder_copy_dir, $folder_paste_dir);
