<?php

define( 'DUMMYTRACKER', true );

require_once( __DIR__ . '/includes/DTConfig.php' );
require_once( __DIR__ . '/includes/functions.php' );

DTConfig::i();

$api = new DTApi();

switch( $_GET['action'] ) {
	case 'create':
		$result = $api->create();
		break;
	case 'browse':
		$result = $api->browse();
		break;
	case 'read':
		$result = $api->read();
		break;
	case 'changeStatus':
		$result = $api->changeStatus();
		break;
}

echo json_encode( $result );