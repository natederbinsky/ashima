<?php
	
	define( 'PRIMARY_KEY', 'data_id' );
	define( 'VIEW_LIMIT', 100 );
	
	define( 'EXP_TYPE_INT', 1 );
	define( 'EXP_TYPE_DOUBLE', 2 );
	define( 'EXP_TYPE_STRING', 3 );
	
	function exp_type_2_english( $type )
	{
		switch ( $type )
		{
			case ( EXP_TYPE_INT ): return 'integer';
			case ( EXP_TYPE_DOUBLE ): return 'double';
			case ( EXP_TYPE_STRING ): return 'string';
		}
	}
	
	function exp_type_2_sql( $type )
	{
		switch ( $type )
		{
			case ( EXP_TYPE_INT ): return 'INT';
			case ( EXP_TYPE_DOUBLE ): return 'DOUBLE';
			case ( EXP_TYPE_STRING ): return 'VARCHAR (500)';
		}
	}
	
	function exp_sql_2_type( $type )
	{
		switch ( $type )
		{
			case ( 'int' ): 
			case ( 3 ):
				return EXP_TYPE_INT;

			case ( 'real' ): 
			case ( 5 ):
				return EXP_TYPE_DOUBLE;
			
			case ( 'string' ):
			case ( 'blob' ): 
			case ( 252 ):
				return EXP_TYPE_STRING;
		}
	}
	
	///////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	
	function _exp_valid_field( $exp_id, $field_name, $validate = true )
	{
		global $db;
		$exp_id = intval( $exp_id );
		$return_val = false;
		
		if ( !$validate || _exp_valid( $exp_id ) )
		{
			$table_name = _exp_table_name( $exp_id, false );
			
			$res = mysqli_query( $db, 'SELECT field_name FROM exp_schemas WHERE exp_id=' . db_quote_smart( $exp_id, $db ) . ' AND field_name=' . db_quote_smart( strval( $field_name ), $db ) );
			
			if ( mysqli_num_rows( $res ) == 1 )
			{
				$return_val = true;
			}
		}
		
		return $return_val;		
	}
	
	function exp_field_distinct_count( $exp_id, $field_name )
	{
		global $db;
		$exp_id = intval( $exp_id );
		$return_val = NULL;
		
		// ensure valid experiment
		if ( _exp_valid( $exp_id ) && _exp_valid_field( $exp_id, $field_name, false ) )
		{
			$res = mysqli_query( $db, 'SELECT COUNT(DISTINCT(' . _exp_field_name( strval( $field_name ) ) . ')) AS ct FROM ' . _exp_table_name( $exp_id, false ) );
			
			if ( mysqli_num_rows( $res ) == 1 )
			{
				$res = mysqli_fetch_assoc( $res );
				
				$return_val = intval( $res['ct'] );
			}
		}
		
		return $return_val;
	}
	
	function exp_field_distinct( $exp_id, $field_name )
	{
		global $db;
		$exp_id = intval( $exp_id );
		$return_val = NULL;
		
		// ensure valid experiment
		if ( _exp_valid( $exp_id ) && _exp_valid_field( $exp_id, $field_name, false ) )
		{
			$res = mysqli_query( $db, 'SELECT (' . _exp_field_name( strval( $field_name ) ) . ') AS dist, COUNT(*) AS ct FROM ' . _exp_table_name( $exp_id, false ) . ' GROUP BY ' . _exp_field_name( strval( $field_name ) ) . ' ORDER BY ct DESC, ' . _exp_field_name( strval( $field_name ) ) . ' ASC' );
			
			$return_val = array();
			while ( $row = mysqli_fetch_assoc( $res ) )
			{
				$return_val[] = $row['dist'];
			}
		}
		
		return $return_val;
	}
	
	///////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	
	// return: experiment number on success, NULL on duplicate
	function _exp_add_experiment( $name )
	{
		global $db;
		
		if ( mysqli_query( $db, 'INSERT INTO experiments (exp_name) VALUES (' . db_quote_smart( strval( $name ), $db ) . ')' ) === false )
		{
			return NULL;			
		}  
		else
		{
			return mysqli_insert_id();
		}
	}
	
	// return: true if experiment id is valid, false otherwise
	function _exp_valid( $exp_id )
	{
		global $db;		
		$exp_id = intval( $exp_id );
		
		return ( mysqli_num_rows( mysqli_query( $db, 'SELECT exp_id FROM experiments WHERE exp_id=' . db_quote_smart( $exp_id, $db ) ) ) == 1 );
	}
	
	// return: experiment table name on success, NULL on invalid experiment number
	function _exp_table_name( $exp_id, $validate = true )
	{
		global $db;		
		$exp_id = intval( $exp_id );
		
		if ( !$validate || _exp_valid( $exp_id ) )
		{
			return ( 'exp_data_' . $exp_id );
		}
		else
		{
			return NULL;
		}
	}
	
	function _exp_field_name( $field_name )
	{
		return ( 'd_' . $field_name );
	}
	
	function _exp_drop_schema( $exp_id )
	{
		global $db;
		
		// clear old schema
		mysqli_query( $db, 'DELETE FROM exp_schemas WHERE exp_id=' . db_quote_smart( $exp_id, $db ) );
		
		// clear old table
		mysqli_query( $db, 'DROP TABLE ' . _exp_table_name( $exp_id, false ) );
	}
	
	// input: experiment id, array ( field name => type )
	// return: true on success, false on failure
	function _exp_set_schema( $exp_id, $exp_fields )
	{
		global $db;		
		$exp_id = intval( $exp_id );
		$return_val = false;
		
		// drop old table/schema
		_exp_drop_schema( $exp_id );
		
		// ensure valid experiment
		if ( _exp_valid( $exp_id ) )
		{
			$table_name = _exp_table_name( $exp_id, false );
			
			// validate schema
			$field_count = 0;
			$table_values = array();
			$schema_values = array();
			$schema_indexes = array();
			$field_good = true;
			foreach ( $exp_fields as $field_name => $field_type )
			{
				if ( ( $field_type === EXP_TYPE_INT ) ||
				     ( $field_type === EXP_TYPE_DOUBLE ) ||
				     ( $field_type === EXP_TYPE_STRING ) )
				{
					$field_name = strtolower( ereg_replace( '[^A-Za-z0-9]', '', trim( $field_name ) ) );
					
					// table values
					{
						$temp = array();
						
						$temp[] = db_quote_smart( $exp_id, $db );
						$temp[] = db_quote_smart( ++$field_count, $db );
						$temp[] = db_quote_smart( $field_name, $db );
						$temp[] = db_quote_smart( $field_type, $db );
						
						$table_values[] = ( '(' . implode( ',', $temp ) . ')' );
					}
					
					// schema definition
					{
						$schema_values[] = ( _exp_field_name( $field_name ) . ' ' . exp_type_2_sql( $field_type ) );
						$schema_indexes[] = _exp_field_name( $field_name );
					}
				}
			}
			
			// if valid schema, implement
			if ( ( $field_count > 0 ) && ( $field_good ) )
			{
				// schema table
				{
					mysqli_query( $db, 'INSERT INTO exp_schemas (exp_id,field_id,field_name,field_type) VALUES ' . implode( ',', $table_values ) );
				}
				
				// data table
				{
					mysqli_query( $db, 'CREATE TABLE ' . $table_name . '(' . PRIMARY_KEY . ' INT PRIMARY KEY AUTO_INCREMENT,' . implode( ',', $schema_values ) . ')' );
					
					foreach ( $schema_indexes as $field_name )
					{
						mysqli_query( $db, 'CREATE INDEX ' . ( $table_name . '_' . $field_name ) . ' ON ' . $table_name . ' (' . $field_name . ')' );
					}
				}
				
				$return_val = true;
			}
		}
		
		return $return_val;
	}
	
	///////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////
	
	// return: true if valid experiment id, false otherwise
	function exp_valid( $exp_id )
	{
		return _exp_valid( $exp_id );
	}
	
	// return: experiment id on success, NULL on failure
	function exp_id( $exp_name )
	{
		global $db;
		$return_val = NULL;
		
		$res = mysqli_query( $db, 'SELECT exp_id FROM experiments WHERE exp_name=' . db_quote_smart( strval( $exp_name ), $db ) );
		
		if ( mysqli_num_rows( $res ) == 1 )
		{
			$res = mysqli_fetch_assoc( $res );
			
			$return_val = intval( $res['exp_id'] );
		}
		
		return $return_val;
	}
	
	// return: array (field_name => field_type) on success, NULL on invalid experiment id
	function exp_schema( $exp_id )
	{
		global $db;
		$exp_id = intval( $exp_id );
		$return_val = NULL;
		
		if ( _exp_valid( $exp_id ) )
		{
			$return_val = array();
			
			$res = mysqli_query( $db, 'SELECT field_name, field_type FROM exp_schemas WHERE exp_id=' . db_quote_smart( $exp_id, $db ) . ' ORDER BY field_id ASC' );
			while ( $row = mysqli_fetch_assoc( $res ) )
			{
				$return_val[ strval( $row['field_name'] ) ] = intval( $row['field_type'] );
			}
		}
		
		return $return_val;
	}
	
	function exp_remove_experiment( $exp_id )
	{
		global $db;
		$exp_id = intval( $exp_id );
		
		// drop data
		_exp_drop_schema( $exp_id );
		
		// drop entry
		mysqli_query( $db, 'DELETE FROM experiments WHERE exp_id=' . db_quote_smart( $exp_id, $db ) );
	}
	
	// return: experiment id on success, NULL on failure
	function exp_add_experiment( $exp_name, $exp_fields )
	{
		$exp_id = _exp_add_experiment( $exp_name );
		if ( !is_null( $exp_id ) )
		{
			if ( !_exp_set_schema( $exp_id, $exp_fields ) )
			{
				exp_remove_experiment( $exp_id );
				
				$exp_id = NULL;
			}
		}
		
		return $exp_id;
	}
	
	// return: array ( experiment id => name )
	function exp_list( $asc = true )
	{
		global $db;
		$return_val = array();
		
		$res = mysqli_query( $db, 'SELECT exp_id, exp_name FROM experiments ORDER BY exp_id ' . (($asc)?('ASC'):('DESC')) );
		while ( $row = mysqli_fetch_assoc( $res ) )
		{
			$return_val[ intval( $row['exp_id'] ) ] = strval( $row['exp_name'] );
		}
		
		return $return_val;
	}
	
	// return: data id if successful, NULL otherwise
	function exp_add_datum( $exp_id, $values )
	{
		global $db;		
		$exp_id = intval( $exp_id );
		$return_val = NULL;
				
		// ensure valid experiment
		if ( _exp_valid( $exp_id ) )
		{
			// ensure valid schema
			$schema = exp_schema( $exp_id );
			$insert_values = array();
								
			foreach ( $schema as $field_name => $field_type )
			{
				if ( isset( $values[ $field_name ] ) )
				{
					$val = $values[ $field_name ];
					
					switch ( $field_type )
					{
						case EXP_TYPE_INT: $val = intval( $val ); break;
						case EXP_TYPE_DOUBLE: $val = doubleval( $val ); break;
						case EXP_TYPE_STRING: $val = strval( $val ); break;
					}
					
					$insert_values[ _exp_field_name( $field_name ) ] = db_quote_smart( $val, $db );
				}
			}
			
			if ( count( $schema ) == count( $insert_values ) )
			{
				$sql = ( 'INSERT INTO ' . _exp_table_name( $exp_id, false ) . ' (' . implode( ',', array_keys( $insert_values ) ) . ') VALUES (' . implode( ',', array_values( $insert_values ) ) . ')' );
				
				if ( mysqli_query( $db, $sql ) )
				{
					$return_val = mysqli_insert_id();
				}
			}
		}
		
		return $return_val;
	}
	
	function exp_clear_data( $exp_id )
	{
		global $db;
		$exp_id = intval( $exp_id );
			
		// drop entry
		mysqli_query( $db, 'DELETE FROM ' . _exp_table_name( $exp_id, false ) );
	}
	
	// return: number of data points, NULL if invalid experiment
	function exp_data_size( $exp_id )
	{
		global $db;
		$exp_id = intval( $exp_id );
		$return_val = NULL;		
		
		if ( _exp_valid( $exp_id ) )
		{
			$res = mysqli_query( $db, 'SELECT COUNT(*) AS exp_ct FROM ' . _exp_table_name( $exp_id, false ) );
			$res = mysqli_fetch_assoc( $res );
			
			$return_val = intval( $res['exp_ct'] );
		}
			
		return $return_val;
	}
	
	// return: array( schema, data ), NULL if invalid experiment
	function exp_data( $exp_id, $sql = NULL, $page = NULL )
	{
		global $db;
		$exp_id = intval( $exp_id );
		$return_val = NULL;		
		
		if ( _exp_valid( $exp_id ) )
		{			
			$exp_schema = exp_schema( $exp_id );
			$data = array();
			$schema = array();
			$err = NULL;
			
			if ( is_null( $sql ) )
			{
				$sql = 'SELECT * FROM {table} ORDER BY {primary} DESC';
			}
			else
			{
				$sql = trim( $sql );
				if ( strtolower( substr( $sql, 0, 6 ) ) != 'select' )
				{
					$sql = 'SELECT * FROM {table} ORDER BY {primary} DESC';
				}
				
				if ( !is_null( $page ) )
				{
					$limit = strpos( strtolower( $sql ), 'limit' );
					if ( $limit !== false )
					{
						$sql = substr( $sql, 0, $limit-1 );
					}
				}
			}
			
			$modified_sql = $sql;
			
			// limit
			if ( !is_null( $page ) )
			{
				$modified_sql .= ( ' LIMIT ' . ( $page * VIEW_LIMIT ) . ', ' . VIEW_LIMIT );
			}
			
			// variable replacements
			{
				$modified_sql = str_replace( '{table}', _exp_table_name( $exp_id, false ), $modified_sql );
				$modified_sql = str_replace( '{primary}', PRIMARY_KEY, $modified_sql );
				
				foreach ( $exp_schema as $field_name => $field_type )
				{
					$modified_sql = str_replace( ( '{field_' . $field_name . '}' ), _exp_field_name( $field_name ), $modified_sql );
				}
			}			
			
			$res = mysqli_query( $db, $modified_sql );
			if ( $res === false )
			{
				$err = mysqli_error( $db );
			}
			else
			{
				$rev_exp_schema = array();
				foreach ( $exp_schema as $field_name => $field_type )
				{
					$rev_exp_schema[ _exp_field_name( $field_name ) ] = $field_name;
				}
				
				$name_convert = array();
				for ( $i=0; $i<mysqli_num_fields( $res ); $i++ )
				{					
					$field_name = mysqli_fetch_field_direct( $res, $i )->name;
					
					if ( $field_name != PRIMARY_KEY )
					{
						if ( isset( $rev_exp_schema[ $field_name ] ) )
						{
							$schema[ $rev_exp_schema[ $field_name ] ] = exp_sql_2_type( mysqli_fetch_field_direct( $res, $i )->type );							
							$name_convert[ $rev_exp_schema[ $field_name ] ] = $field_name;
						}
						else
						{
							$schema[ $field_name ] = exp_sql_2_type( mysqli_fetch_field_direct( $res, $i )->type );
							$name_convert[ $field_name ] = $field_name;
						}						
					}
				}
				
				while ( $row = mysqli_fetch_assoc( $res ) )
				{
					$temp = array();
					
					foreach ( $schema as $field_name => $field_type )
					{						
						switch ( $field_type )
						{
							case ( EXP_TYPE_INT ): $temp[ $field_name ] = intval( $row[ $name_convert[ $field_name ] ] ); break;
							case ( EXP_TYPE_DOUBLE ): $temp[ $field_name ] = doubleval( $row[ $name_convert[ $field_name ] ] ); break;
							case ( EXP_TYPE_STRING ): $temp[ $field_name ] = strval( $row[ $name_convert[ $field_name ] ] ); break;
						}
					}
					
					$data[] = $temp;
				}
			}
			
			$return_val = array( 'schema'=>$schema, 'data'=>$data, 'sql'=>$sql, 'modified'=>$modified_sql, 'err'=>$err );
		}
			
		return $return_val;
	}
	
	// return: experiment table name on success, NULL on invalid experiment number
	function exp_table_name( $exp_id )
	{
		return _exp_table_name( intval( $exp_id ) );
	}
	
	// return: string
	function exp_field_name( $field_name )
	{
		return _exp_field_name( $field_name );
	}

?>
