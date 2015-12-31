<?php

function get_query_search_results( $words, $word_count, $list, $url ) {
	$results = array();
	$items   = array();

	foreach ( $list as $item ) {
		$i = 0;
		foreach ( $words as $word ) {
			if ( ( $pos = strpos( strtolower( $item->title ), $word  ) ) !== false ) {
				++$i;
			}
		}
		if ( $i == $word_count ) {
			$items[] =  $item;
		}
	}

	$i = 0;
	foreach ( $items as $link ) {
		$results[] = array(
			'title'    => $link->title,
			'icon'     => 'icon.png',
			'valid'    => '',
			'uid'      => $i,
			'subtitle' => '',
			'arg'      => $url.'/'.$link->slug
		);

		++$i;
	}

	return $results;
}

function get_query_words( $query ) {

	// remove duplicate spaces
	$query = trim( strtolower( preg_replace( '/\s+/', ' ', (string) $query ) ) );

	// create array with words from the query
	$query = explode( ' ', $query );

	return $query;
}

/**
 * Description:
 * Read data from a remote file/url, essentially a shortcut for curl
 *
 * @param unknown $url     - URL to request
 * @param unknown $options - Array of curl options
 * @return result from curl_exec
 */
function request( $url=null ) {
	if ( is_null( $url ) ):
		return false;
	endif;

	$defaults = array(         // Create a list of default curl options
		CURLOPT_RETURNTRANSFER => true,     // Returns the result as a string
		CURLOPT_URL => $url,       // Sets the url to request
		CURLOPT_FRESH_CONNECT => true
	);

	$ch  = curl_init();         // Init new curl object
	curl_setopt_array( $ch, $defaults );    // Set curl options
	$out = curl_exec( $ch );       // Request remote data
	$err = curl_error( $ch );
	curl_close( $ch );         // End curl request

	if ( "Not Found" === $out ) {
		$out = 'error';
	}

	if ( $err ):
		return 'error';
	else:
		return $out;
	endif;
}

function update_files( $force_update = false ) {
	// Don't care about timezones as we check once a month
	// todo: use another way to avoid errors
	date_default_timezone_set( 'America/New_York' );

	if ( file_exists( 'version.json' ) ) {
		$current_version = json_decode( file_get_contents( 'version.json' ) );

		if ( !isset( $current_version->last_updated ) ) {
			$current_version->last_updated = time();
			file_put_contents( 'version.json', json_encode( $current_version ) );
		}

		if ( $force_update || ( $current_version->last_updated <= strtotime( '-2 week' ) ) ) {
			$current_version->last_updated = time();
			if ( false !== file_put_contents( 'version.json', json_encode( $current_version ) ) ) {
				return true;
			}
		}
	}

	return false;
}

function update_timestamp() {
	return update_files( true );
}