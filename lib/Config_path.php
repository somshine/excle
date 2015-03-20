<?php

define( 'SITE_PATH', 'http://' . $_SERVER['HTTP_HOST'] );

$strRootDir = __DIR__;

$arrstrDirLastFolder = explode( '\\', $strRootDir );

if( true == is_array( $arrstrDirLastFolder ) ) {
	array_pop( $arrstrDirLastFolder );

	$strRootDir = '';

	if( true == is_array( $arrstrDirLastFolder ) ) {
		foreach( $arrstrDirLastFolder as $strDirName ) {
			$strRootDir .= $strDirName . '/';
		}
	}
}

define( 'SITE_ABSOLUTE_PATH', $strRootDir );

define( 'SERVER_HOST', 		'localhost' );
define( 'USER_NAME', 		'root' );
define( 'PASSWORD', 		'' );
define( 'DATABASE_NAME', 	'live_16' );
?>