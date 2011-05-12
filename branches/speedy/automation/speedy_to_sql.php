<?php
    
    function to_field( $f )
    {
        return ( 'd_' . $f );
    }
    
    //
    
    function usage()
    {
        global $argv;
        
        echo ( $argv[0] . ' <speedy file> <table> <max records per insert>' . "\n" );
    }
    
    if ( $argc != 4 )
    {
        usage();
        exit;
    }
    
    $fname = $argv[1];
    if ( !file_exists( $fname ) || !is_readable( $fname ) )
    {
        echo ( 'Cannot read file.' . "\n" );
        usage();
        exit;
    }
    
    $table = $argv[2];
    
    $max_recs = intval( $argv[3] );
    if ( $max_recs < 1 )
    {
        usage();
        exit;
    }
    
    //
    
    $f = fopen( $fname, 'r' );
    
    //
    
    echo ( 'BEGIN;' . "\n" );
    
    $ct = 0;
    $mod = 0;
    while ( ( $buffer = fgets( $f ) ) !== false )
    {
        $buffer = explode( ' ', trim( $buffer ) );
        $mod = ( $ct % $max_recs );
        
        if ( $mod == 0 )
        {
            echo ( 'INSERT INTO ' . $table . ' (' );
            
            $temp = array();
            foreach ( $buffer as $v )
            {
                $v = explode( '=', $v );
                $temp[] = to_field( $v[0] );
            }
            
            echo ( implode( ',', $temp ) . ') VALUES' );
        }
        
        {
            $temp = array();
            foreach ( $buffer as $v )
            {
                $v = explode( '=', $v );
                $v = $v[1];
                
                if ( !is_numeric( $v ) )
                {
                    $v = ( '\'' . $v . '\'' );
                }
                
                $temp[] = $v;
            }
            
            if ( $mod != 0 )
            {
                echo ',';
            }
            echo ( ' (' . implode( ',', $temp ) . ')' );
        }
        
        if ( $mod == ( $max_recs - 1 ) )
        {
            echo ( ';' . "\n" );
        }
        
        $ct++;
        
        //if ( $ct == 100000 )
        //    break;
    }
    
    if ( $mod != ( $max_recs - 1 ) )
    {
        echo ( ';' . "\n" );
    }
    
    echo ( 'COMMIT;' . "\n" );
    
    //
    
    fclose( $f );
?>
