<?php

class ExtensionUtils {

	/**
	* Description:
	* Convert an associative array into XML format
	*
	* @param $a - An associative array to convert
	* @return - XML string representation of the array
	*/
	public function toxml( $a=null, $format='array' ) {

		if ( $format == 'json' ):
			$a = json_decode( $a, TRUE );
		endif;

		if ( is_null( $a ) || !is_array( $a ) ): 			// if the value passed is not an array
			return false;									// return false
		endif;

		$items = new SimpleXMLElement("<items></items>"); 	// Create new XML element

		foreach( $a as $b ):								// Lop through each object in the array
			$c = $items->addChild( 'item' );				// Add a new 'item' element for each object
			$c_keys = array_keys( $b );						// Grab all the keys for that item
			foreach( $c_keys as $key ):						// For each of those keys
				if ( $key == 'uid' ):
					$c->addAttribute( 'uid', $b[$key] );
				elseif ( $key == 'arg' ):
					$c->addAttribute( 'arg', $b[$key] );
				elseif ( $key == 'valid' ):
					$c->addAttribute( 'valid', $b[$key] );
				elseif ( $key == 'autocomplete' ):
					$c->addAttribute( 'autocomplete', $b[$key] );
				else:
					$c->addChild( $key, $b[$key] );			// Add an element for it and set its value
				endif;
			endforeach;
		endforeach;

		return $items->asXML();								// Return XML string representation of the array

	}

	/**
	* Description:
	* Remove all items from an associative array that do not have a value
	*
	* @param $a - Associative array
	* @return bool
	*/
	private function empty_filter( $a ) {
		if ( $a == '' || $a == null ):						// if $a is empty or null
			return false;									// return false, else, return true
		else:
			return true;
		endif;
	}


	/**
	* Description:
	* Save an array of values to the plist specified in $b
	*
	* @param $a - associative array of values to save
	* @param $b - the value of the setting
	* @param $c - the plist to save the values into
	* @return string - execution output
	*/
	public function set( $a=null, $b=null, $c=null )
	{
		$dir = exec('pwd');
		$c = $dir.'/'.$c;

		if ( is_array($a) ):
			foreach( $a as $k=>$v ):
				exec( 'defaults write "'. $c .'" '. $k .' "'. $v .'"');
			endforeach;
		else:
			exec( 'defaults write "'. $c .'" '. $a .' "'. $b .'"');
		endif;
	}

	/**
	* Description:
	* Read a value from the specified plist
	*
	* @param $a - the value to read
	* @param $b - plist to read the values from
	* @return bool false if not found, string if found
	*/
	public function get( $a, $b ) {

		$dir = exec('pwd');
		$b = $dir.'/'.$b;

		exec( 'defaults read "'. $b .'" '.$a, $out );	// Execute system call to read plist value

		if ( $out == "" ):
			return false;
		endif;

		$out = $out[0];
		return $out;											// Return item value
	}

	/**
	* Description:
	* Read data from a remote file/url, essentially a shortcut for curl
	*
	* @param $url - URL to request
	* @param $options - Array of curl options
	* @return result from curl_exec
	*/
	public function request( $url=null, $options=null )
	{
		if ( is_null( $url ) ):
			return false;
		endif;

		$defaults = array(									// Create a list of default curl options
			CURLOPT_RETURNTRANSFER => true,					// Returns the result as a string
			CURLOPT_URL => $url,							// Sets the url to request
			CURLOPT_FRESH_CONNECT => true
		);

		if ( $options ):
			foreach( $options as $k => $v ):
				$defaults[$k] = $v;
			endforeach;
		endif;

		array_filter( $defaults, 							// Filter out empty options from the array
			array( $this, 'empty_filter' ) );

		$ch  = curl_init();									// Init new curl object
		curl_setopt_array( $ch, $defaults );				// Set curl options
		$out = curl_exec( $ch );							// Request remote data
		$err = curl_error( $ch );
		curl_close( $ch );									// End curl request

		if ( $err ):
			return $err;
		else:
			return $out;
		endif;
	}

	/**
	* Description:
	* Allows searching the local hard drive using mdfind
	*
	* @param $query - search string
	* @return array - array of search results
	*/
	public function mdfind( $query )
	{
		exec('mdfind "'.$query.'"', $results);
		return $results;
	}

	/**
	* Description:
	* Accepts data and a string file name to store data to local file as cache
	*
	* @param array - data to save to file
	* @param file - filename to write the cache data to
	* @return none
	*/
	public function cache( $a, $b )
	{
		if ( is_array( $a ) ):
			$a = json_encode( $a );
			file_put_contents( $b, $a );
			return true;
		else:
			return false;
		endif;
	}

	/**
	* Description:
	* Returns data from a local cache file
	*
	* @param file - filename to read the cache data from
	* @return none
	*/
	public function getcache( $a )
	{
		if ( ! file_exists( $a ) ):
			return false;
		else:
			$out = file_get_contents( $a );
			$out = json_decode( $out );
			return $out;
		endif;
	}

	/**
	* Description:
	* Helper function that just makes it easier to pass values into a function
	* and create an array result to be passed back to Alfred
	*
	* @param $uid - the uid of the result, should be unique
	* @param $arg - the argument that will be passed on
	* @param $title - The title of the result item
	* @param $sub - The subtitle text for the result item
	* @param $icon - the icon to use for the result item
	* @param $valid - sets whether the result item can be actioned
	* @param $auto - the autocomplete value for the result item
	* @return array - array item to be passed back to Alfred
	*/
	public function result( $uid, $arg, $title, $sub, $icon, $valid='yes', $auto=null )
	{
		if ( is_null( $auto ) ):
			$auto = $title;
		endif;

		$temp = array(
			'uid' => $uid,
			'arg' => $arg,
			'title' => $title,
			'subtitle' => $sub,
			'icon' => $icon,
			'valid' => $valid,
			'autocomplete' => $auto
		);
		return $temp;
	}

}


/**
* LocalDB class is an extension of SQLite3. There are several shortcut functions
* created just to make interaction with the databse a little simpler and faster.
*/
class LocalDB extends SQLite3 {

	/**
	* Description:
	* Class constructor. Accepts a database name as an argument, if one
	* isn't specified, it falls back to database.db, and opens that database.
	*
	* @param $name - name of the database to create or connect to
	*/
	function __construct( $name = "database.db" )
	{
		$this->open( $name );
	}

	/**
	* Description:
	* Single function performs a query on the database but only
	* returns a single, json object result instead of a list of
	* results
	*
	* @param $table - the table to query
	* @param $where - the query where clause, used for matching
	* @param $select - the fields to return
	* @return mixed - false for fail, json object on successful query
	*/
	public function single( $table=null, $where='1', $select='*' )
	{
		if ( is_null( $table) ):
			return false;
		endif;

		$result = $this->querySingle( 'select '. $select .' from '. $table .' where '. $where, true );
		return json_decode( json_encode( $result, JSON_FORCE_OBJECT ) );
	}

	/**
	* Description:
	* Queries the database for matching values and returns a json
	* formatted object of all results.
	*
	* @param $table - the table to query
	* @param $where - the query where clause, used for matching
	* @param $select - the fields to return
	* @return mixed - false for fail, json object on successful query
	*/
	public function get( $table=null, $where='1', $select='*' )
	{
		if ( is_null( $table) ):
			return false;
		endif;

		$results = $this->query( 'select '. $select .' from '. $table .' where '. $where );
		$return = array();

		while( $result = $results->fetchArray( SQLITE3_ASSOC ) ):
			array_push( $return, $result );
		endwhile;

		return json_decode( json_encode( $return ) );
	}

	/**
	* Description:
	* Insert values into the specified table of the database. Values should
	* be passed as an associative array
	*
	* @param $table - table to insert the data in to.
	* @param $values - the values to insert into the table
	* @return bool - success/fail
	*/
	public function insert( $table=null, $values=null )
	{

		if ( is_null( $table ) || is_null( $values ) || ! is_array( $values ) ):
			return false;
		endif;

		$fieldOrder = "";
		$valueOrder = "";
		$numFields = count( $values );
		$inc = 1;

		foreach( $values as $field => $value ):
			$fieldOrder .= $field;
			if ( $inc != $numFields ):
				$fieldOrder .= ', ';
			endif;
			$valueOrder .= '"'.$value. '"';
			if ( $inc != $numFields ):
				$valueOrder .= ', ';
			endif;
			$inc++;
		endforeach;

		$this->exec( 'insert into ' .$table. ' values ( ' .$valueOrder. ' )' );

		return true;
	}

	/**
	* Description:
	* Delete records from the specified table that match the where query.
	*
	* @param $table - The table to remove the records from
	* @param $where - The where clause to match which records to delete
	* @return bool - boolean whether or not the command was executed
	*/
	public function delete( $table=null, $where=null )
	{
		if ( is_null( $table ) || is_null( $where ) ):
			return false;
		else:
			$this->exec( 'delete from ' .$table. ' where '. $where );
		endif;
	}

	public function update( $table, $where, $values )
	{
		$setValues = "";
	}

	/**
	* Description:
	* Create a new table with the specified name. The $fields variable
	* should contain an array of field names and data types to create
	*
	* @param $table - the name of the table to create
	* @param $fields - array of field names and data types where the key
	*					is the name of the field and the value is the data type
	* @return bool - bool success
	*/
	public function createtable( $table=null, $fields=null )
	{
		if ( is_null( $table) || is_null( $fields ) || ! is_array( $fields ) ):
			return false;
		endif;

		$fieldDef = "";
		$numFields = count( $fields );
		$inc = 1;

		foreach( $fields as $field => $type ):
			$fieldDef .= $field. " " .strtoupper( $type );
			if ( $inc != $numFields ):
				$fieldDef .= ', ';
			endif;
			$inc++;
		endforeach;

		$this->exec( 'create table if not exists ' .$table. '( ' .$fieldDef. ' )' );

		return true;
	}

	/**
	* Description:
	* Drop the specified table if it exists in the current database.
	*
	* @param $table - name of the table to drop
	* @return bool - bool true or false on success
	*/
	public function droptable( $table=null )
	{
		if ( is_null( $table ) ):
			return false;
		else:
			$this->exec( 'drop table if exists ' .$table );
			return true;
		endif;
	}

	/**
	* Description:
	* LocalDB class destructor. Closes the connection to the database on
	* destruction of the class object
	*/
	function __destruct()
	{
		$this->close();
	}

}