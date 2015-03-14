<?php

	$params = array( 1=>'db', 2=>'table' );
	if ( $argc != ( count( $params ) + 1 ) )
	{
		die( 'usage: ' . $argv[0] . ' ' . implode( ' ', array_values( $params ) ) . "\n" );
	}
	else
	{
		$param_values = array();
		foreach ( $params as $k => $v )
		{
			$param_values[ $v ] = $argv[ $k ];
		}
		$params = $param_values;
	}
	
	//
	
	function db_get_assoc( $sql, &$db, $unique_field = null )
	{
		$return_val = array();
		
		$stmt = $db->prepare( $sql );
		$result = $stmt->execute();
		
		while ( $row = $result->fetchArray( SQLITE3_ASSOC ) )
		{
			if ( is_null( $unique_field ) )
			{
				$return_val[] = $row;
			}
			else
			{
				$return_val[ $row[ $unique_field ] ] = $row;
			}
		}
		
		return $return_val;
	}
	
	//
	
	// open db
	$db = null;
	{
		try
		{
			$open_result = ( $db = new SQLite3( $params['db'], SQLITE3_OPEN_READONLY ) );
		}
		catch ( Exception $e )
		{
			echo ( 'SQLite open error (' . $e->getCode() . '): "' . $e->getMessage() . '"' . "\n" );
			exit;
		}
	}
	
	// get data
	$data = db_get_assoc( ( 'SELECT * FROM ' . $params['table'] ), $db );
	
	// output with filters
	$baddies = array( ' ', '_' );
	
	foreach ( $data as $row )
	{
		$output = array();
		
		foreach ( $row as $k => $v )
		{
			$output[] = ( str_replace( $baddies, '', $k ) . '=' . str_replace( $baddies, '', $v ) );
		}
		
		echo implode( ' ', $output ) . "\n";
	}
	
	$db->close();
	
?>
