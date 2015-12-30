<?php

function get_query_search_results( $words, $word_count, $list, $url ) {
	$results = array();

	$items = array();
	foreach ( $list as $item ) {
		$i = 0;
		foreach ( $words as $word ) {
			if ( ( $pos = strpos( strtolower( $item->title ), $word  ) ) !== false ) {
				++$i;
			}
		}
		if ( $i == $word_count )
			$items[] =  $item;
	}

	$i = 0;
	foreach ( $items as $link ) {
		$results[] = array(
			'title' => $link->title,
			'icon' => 'icon.png',
			'valid' => '',
			'uid' => $i,
			'subtitle' => '',
			'arg' => $url.'/'.$link->slug
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
