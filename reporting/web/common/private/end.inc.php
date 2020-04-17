<?php
	
	// get page content
	$page_info['content'] = ob_get_contents();
	ob_clean();
	
	// get the template
	$template = '';
	
	if ( $page_info['type'] == 'full' )
	{
		require 'template.inc.php';
		$template = ob_get_clean();
	}
	else if ( $page_info['type'] == 'blank' )
	{
		$template = '{content}';
	}
	ob_end_clean();
	
	// final template values
	$page_info['homedir'] = ( '.' . DIRECTORY_SEPARATOR );
	for ( $i=0; $i<$page_info['depth']; $i++ )
	{
		$page_info['homedir'] .= ( '..' . DIRECTORY_SEPARATOR );
	}
	$page_info['dash_title'] = ( ( strlen( $page_info['title'] ) )?( '- ' . $page_info['title'] ):( '' ) );
	$page_info['nav'] = 'howdy';
	{
		$nav_info = array(
			array( 'title'=>'experiments', 'url'=>( $page_info['homedir'] . 'experiments.php' ) ),
		);
		@include( 'nav-config.inc.php' );
		
		if ( !empty( $nav_info ) )
		{
			foreach ( $nav_info as $key => $val )
			{
				$nav_info[ $key ] = ( '<a href="' . htmlentities( $val['url'] ) . '"' . ( ( isset( $val['new'] ) )?( ' target="_blank"' ):('') ) . '>' . htmlentities( $val['title'] ) . '</a>' );
			}
			
			$page_info['nav'] .= ( ' - ' . implode( ' | ', $nav_info ) );
		}
	}
	
	// replace values in the template
	foreach ( $page_info as $key => $val )
	{
		$template = str_replace( ( '{' . $key . '}' ), $val, $template );
	}
		
	// output the page
	echo trim( $template );
	
	mysqli_close( $db );
?>
