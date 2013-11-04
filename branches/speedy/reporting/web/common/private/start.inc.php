<?php
	session_start();
	
	// Useful for debugging
	//error_reporting( -1 );
	//ini_set( 'display_errors', 'On' );
	//ini_set( 'display_startup_errors', 'On' );
	
	// quell warnings
	date_default_timezone_set( 'America/New_York' );
	
	// required libraries
	require_once 'db.inc.php';
	require_once 'auth.inc.php';
	require_once 'experiment.inc.php';
	require_once 'tables.inc.php';
	require_once 'misc.inc.php';
	require_once 'jquery.inc.php';
	require_once 'report.inc.php';
	
	// "constants"
	$page_info = array();
	
	$page_info['title'] = '';
	$page_info['align'] = 'left';
	$page_info['head'] = '';
		
	// supported: full, blank
	$page_info['type'] = ( isset( $_GET['blank'] ) && ( $_GET['blank'] === 'Y' ) )?( 'blank' ):( 'full' );
	
	// supported: non-negative integers
	$page_info['depth'] = 0;
	
	ob_start();
?>
