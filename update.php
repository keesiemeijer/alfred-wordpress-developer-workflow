<?php

$files    = array( 'functions', 'hooks', 'actions', 'filters', 'classes', 'version' );
$base_url = "https://raw.githubusercontent.com/keesiemeijer/alfred-wordpress-developer-workflow/master/";

// if $update_files variable is not set
// this file was not included from the wpdev update keyword
$update_files = isset( $update_files ) && $update_files;
$updated      = 0;

$msg = $update_files ? "Checking for Updates\n" : '';

// Get current version from version file
if ( file_exists( 'version.json' ) ) {
	$current_version = json_decode( file_get_contents( 'version.json' ) );
} else {
	echo $msg . "Not Updated\nCould not find workflow version";
	return;
}

if ( !$update_files ) {
	// Update timestamp in version.json
	$updated_timestamp = update_timestamp();
	if ( !$updated_timestamp ) {
		echo $msg . "Not Updated\nCould not write time of update to file!";
		return;
	}
}

// Check if Curl is installed
if ( !function_exists( 'curl_version' ) ) {
	echo $msg . "Not Updated\nCurl is not installed";
	return;
}

// Get version from GitHub
$version = request( $base_url . 'version.json' );

if ( !empty(  $version  ) && ( 'error' !== $version ) ) {
	$version = json_decode( $version );
} else {
	echo $msg . "Not updated\nNo internet connection. Try again later";
	return;
}

$msg = '';

// Check if newer version is available
if ( version_compare( $version->version, $current_version->version, '>' ) ) {

	foreach ( $files as $file ) {

		if ( file_exists( $file . '.json' ) ) {
			$contents = request( $base_url . '/' . $file . '.json' );

			if ( !empty( $contents ) && ( 'error' !== $contents ) ) {

				// update file
				if ( false !== file_put_contents( $file . '.json', $contents ) ) {
					$msg = "Updated to WP version {$version->version} ";
					$updated++;
				} else {
					$msg = "Error: Could not write to file. ";
					break;
				}
			} else {
				$msg = "Error: Request to GitHub failed. ";
				break;
			}
		} else {
			$msg = "Error: Could not find workflow file. ";
			break;
		}
	}

	// Extra message if update failed
	if ( ( $updated < 6 ) && ( $updated > 0 ) ) {
		$msg .= "\nNot all workflow files updated. ";
	}

	if ( $updated === 0  ) {
		$msg .= "\nNo workflow files updated. ";
	}

} else {
	$msg = "No new updates available\nCurrent version {$version->version} ";
	$same_version = true;
}

if ( isset( $same_version ) && $update_files ) {
	// No Errors
	// Current version is the same as GitHub version
	// Nothing is updated but the timestamp
	// No need to show a message
	return;
}

$msg = $update_files ? "Checking for Updates\n" . $msg : $msg;

echo $msg;