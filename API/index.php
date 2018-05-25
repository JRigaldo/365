<?php
define ( 'SITE_PATH', realpath( dirname(__FILE__) ) ); 
$site_url = str_replace('\\', '/', str_replace( realpath( $_SERVER[ 'DOCUMENT_ROOT' ] ), '', SITE_PATH ) );

//$site_url = ( empty( $site_url ) ) ? 'http://' . $_SERVER['HTTP_HOST'] : $site_url;
define ( 'SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . $site_url );

include SITE_PATH . '/includes/init.php';