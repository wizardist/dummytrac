<?php
if ( !defined( 'DUMMYTRACKER' ) ) {
	exit;
}
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
	<meta charset="utf-8" />
	<title><?php echo DTConfig::i()->get( 'sitename' ); ?></title>
	<link rel="stylesheet" href="<?php echo DTConfig::makeResourcePath( 'reset.css' ); ?>" />
	<link rel="stylesheet" href="<?php echo DTConfig::makeResourcePath( 'style.css' ); ?>" />
	<!--[if IE]>
	<script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<script src="<?php echo DTConfig::makeResourcePath( 'jquery-1.9.1.min.js' ); ?>"></script>
	<script src="<?php echo DTConfig::makeResourcePath( 'tickets.js' ); ?>"></script>
	<script src="<?php echo DTConfig::makeResourcePath( 'jquery.tablesorter.min.js' ); ?>"></script>

	<link rel="stylesheet" href="<?php echo DTConfig::makeResourcePath( 'ts-css/theme.blue.css' ); ?>" />

	<script>
		window.statusTexts = <?php echo json_encode( Ticket::$statusTexts ); ?>;

		$.tablesorter.addParser( {
			id: 'statusParser',
			is: function( s ) {
				return false;
			},
			format: function( s, table, cell, cellIndex ) {
				return $( cell ).data( 'ticketstatus' ) + 1;
			},
			type: 'numberic'
		} );
	</script>
</head>
<body>

<div id="container">
	<header id="header">
		<h1><?php echo DTConfig::i()->get( 'sitename' ); ?> <img id="spinner" src="<?php
			echo DTConfig::makeResourcePath( 'loader.gif' ); ?>" width="16" height="16" alt="Loading"></h1>
	</header>
	<div id="body">
		<div id="view-index" class="view" data-view="index">
			<div id="buttons-index">
				<div id="button-create" data-targetview="create" class="button changeview">Create a new ticket</div>
				<div id="button-browse" data-targetview="browse" class="button changeview">Browse existing tickets</div>
			</div>
		</div>
		<div id="view-browse" class="view" data-view="browse" style="display: none;">
			<h2>Browse existing tickets</h2>
			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th style="text-align: left;">Status</th>
						<th style="text-align: left; width: 50%;">Title</th>
						<th style="text-align: left; width: 25%;">Last updated</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<div id="view-read" class="view" data-view="read" data-ticketid="" style="display: none;">
			<div class="button changeview" data-targetview="browse">&larr; Back to the list of tickets</div>
			<h2></h2>
			<div id="read-status">Ticket status: <select>
				<?php
foreach ( Ticket::$statusTexts as $id => $status ) {
	echo '<option value="' . $id . '">' . $status . '</option>';
}
?>

			</select></div>
			<pre id="read-description"></pre>
			<h3>History</h3>
			<table>
				<tbody>
				</tbody>
			</table>
		</div>
		<div id="view-create" class="view" data-view="create" style="display: none;">
			<fieldset>
				<legend>Enter a new ticket</legend>
				<table>
					<tr>
						<td style="vertical-align: middle;"><label for="create-title">Title:</label></td>
						<td><input type="text" id="create-title" placeholder="Enter a self-descriptive title for the ticket"></td>
					</tr>
					<tr>
						<td style="vertical-align: top;"><label for="create-description">Description:</label></td>
						<td><textarea id="create-description" placeholder="Leave a detailed description of the issue."></textarea></td>
					</tr>
					<tr>
						<td></td>
						<td><div id="create-submit" class="button">Submit!</div></td>
					</tr>
				</table>
			</fieldset>
		</div>
	</div>
</div>

<script>
$( '.changeview' ).click( function() {
	dt.setView( $( this ).data( 'targetview' ) );
} );

$( '#header' ).click( function() {
	dt.setView( 'index' );
} );

$( '#create-submit' ).click( function() {
	var title = $( '#create-title' ).val();
	var description = $( '#create-description' ).val();
	if ( title === "" || description === "" ) {
		alert( 'You cannot submit a ticket with the empty title and/or description.');
		return;
	}
	dt.submitTicket( $( '#create-title' ).val(), $( '#create-description' ).val(), function() {
		dt.setView( 'index' );
		dt.clearCreateView();
	} );
});

$( '#view-read select' ).change( function( e ) {
	dt.changeStatus();
} );

$( '#view-browse table' ).tablesorter( {
	theme: 'blue',
	headers: {
		1: {
		   sorter: 'statusParser'
		}
	}
} );

$( document ).ajaxSend( function() {
	$( '#spinner' ).show();
} );

$( document ).ajaxComplete( function() {
	$( '#spinner' ).hide();
} );

$( function() {
	if ( window.location.hash.match( /ticket\/\d+$/i ) ) {
		dt.setView( 'read' );
	}
	dt.setView( $( '.view:visible' ).data( 'view' ) );
} );
</script>
</body>
</html>