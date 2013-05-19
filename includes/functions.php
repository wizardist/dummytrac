<?php

function __autoload( $className ) {
	include( DTConfig::makeFilePath( '/includes/' . $className . '.php' ) );
}

/**
 * Formats the current UTC time in a custom format
 * @return string
 */
function getCurrentTimestampUTC() {
	$timezone = new DateTimeZone( 'UTC' );
	$time = new DateTime( 'now', $timezone );
	return $time->format( 'YmdHis' );
}
