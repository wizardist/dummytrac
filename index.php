<?php

define( 'DUMMYTRACKER', true );

require_once( __DIR__ . '/includes/DTConfig.php' );
require_once( __DIR__ . '/includes/functions.php' );

//DTConfig::i();
/*
function __autoload( $className ) {
	include( DTConfig::makeFilePath( '/includes/' . $className . '.php' ) );
}*/

//DTBase::i();

/*$ticket = new Ticket();
$ticket->ticketTitle = 'Тест2';
$ticket->ticketDescription = 'Описание тикета';
$ticket->ticketUpdated = '20130518143407';
$ticket->save();
$ticket->save();

$dbt = DTBase::i()->query( 'SELECT * FROM ticket WHERE ticket_id = 1' );
while( $row = mysql_fetch_array( $dbt, MYSQL_ASSOC ) ) {
	print_r( $row );
}*/
/*
$ticket = Ticket::newByID( 4 );

print_r( $ticket );

$ticket->setTitle( 'Анаконда' );
$ticket->save();

print_r( $ticket );*/

include 'views/index.php';