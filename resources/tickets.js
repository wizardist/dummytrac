var dt = ( function ( $ ) {

	function timeZeroPadding( value ) {
		if ( value < 10 ) {
			return '0' + value;
		}
		return value;
	}

	function formatDate( timestamp ) {
		var datetime = timestamp.match( /(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/ );
		datetime.shift();
		var d = new Date( Date.UTC(
			datetime[0] - 0,
			datetime[1] - 1,
			datetime[2] - 0,
			datetime[3] - 0,
			datetime[4] - 0,
			datetime[5] - 0
		) );

		return d.toDateString()
			+ ' @ '
			+ timeZeroPadding( d.getHours() )
			+ ':'
			+ timeZeroPadding( d.getMinutes() )
			+ ':'
			+ timeZeroPadding( d.getSeconds() );
	}

	function addHistoryRow( date, statusOld, statusNew ) {
		var row = $( '<tr></tr>' );
		$( row )
			.append( $( '<td></td>' ).text( formatDate( date ) ) )
			.append( $( '<td></td>' )
				.addClass( 'status-' + statusOld )
				.text( window.statusTexts[statusOld] )
			)
			.append( $( '<td>&rarr;</td>' ) )
			.append( $( '<td></td>' )
				.addClass( 'status-' + statusNew )
				.text( window.statusTexts[statusNew] )
			);
		$( '#view-read tbody' ).prepend( row );
	}

	return {

		currentTicket: 0,

		setView: function ( view ) {
			var currentView = $( '.view:visible' ).data( 'view' );
			console.log( 'View changed from [' + currentView + '] to [' + view + ']' );
			if ( currentView == 'create' ) {
				dt.clearCreateView();
			} else if ( view == 'browse' ) {
				dt.loadBrowseData( function ( data ) {
					dt.clearBrowseView();
					$.each( data, function ( id, ticket ) {
						var row = $( '<tr></tr>' );
						var cellID = $( '<td></td>' );
						$( cellID ).css( 'text-align', 'center' );
						$( cellID ).append( $( '<a></a>' )
							.attr( 'href', '#ticket/' + ticket.ticketID )
							.data( 'targetview', 'read' )
							.data( 'ticketID', ticket.ticketID )
							.click( function ( e ) {
								dt.currentTicket = $( e.currentTarget ).data( 'ticketID' ) - 0;
								dt.setView( $( e.currentTarget ).data( 'targetview' ) );
							} )
							.addClass( 'changeview' )
							.text( ticket.ticketID ) );
						var cellStatus = $( '<td></td>' )
							.text( window.statusTexts[ticket.ticketStatus] )
							.data( 'ticketstatus', ticket.ticketStatus )
							.addClass( 'status-' + ticket.ticketStatus );
						var cellTitle = $( '<td></td>' ).text( ticket.ticketTitle );
						var cellUpdated = $( '<td></td>' ).text( formatDate( ticket.ticketUpdated ) );
						$( row ).append( cellID, cellStatus, cellTitle, cellUpdated );
						$( '#view-browse tbody' ).append( row );
					} );
					$( '#view-browse table' ).trigger( 'update' );
				} );
			} else if ( view == 'read' ) {
				var ticketID = dt.currentTicket
					|| window.location.hash.match( /ticket\/(\d+)$/i )[1] - 0;
				dt.loadTicket( ticketID, function ( data ) {
					dt.clearReadView();

					var ticket = data.ticket;
					var history = data.history;

					$( '#view-read' ).data( 'ticketid', ticketID );
					$( '#view-read h2' ).text( 'Ticket #' + ticketID + ': ' + ticket.ticketTitle );
					$( '#read-description' ).text( ticket.ticketDescription );
					$( '#read-status select' ).val( ticket.ticketStatus );

					$.each( history, function ( i, entry ) {
						addHistoryRow( entry.entryDate, entry.entryOld, entry.entryNew );
					} );
				} );
			}
			$( '.view:visible' ).hide();
			$( '#view-' + view ).show();

			if ( view != 'read' ) {
				if ( window.history && 'pushState' in history ) {
					history.pushState( '', document.title, window.location.pathname + window.location.search );
				} else {
					window.location.hash = '';
				}
			}
		},

		submitTicket: function ( title, description, callback ) {
			data = {
				title:       title,
				description: description
			};
			$.post( '/api.php?action=create', data )
				.done( callback )
				.error( function () {
					alert( 'Something went wrong! Your ticket was not submitted.' );
				} );
		},

		loadBrowseData: function ( callback ) {
			$.getJSON( '/api.php?action=browse' )
				.done( callback )
				.error( function () {
					alert( 'Couldn\'t retrieve the list of tickets. Please try later!' );
				} );
		},

		loadTicket: function ( id, callback ) {
			$.post( '/api.php?action=read', { id: id }, null, 'json' )
				.done( callback )
				.error( function () {
					alert( 'Couldn\'t retrieve ticket data. Please try later!' );
				} );
		},

		changeStatus: function () {
			var ticketID = $( '#view-read' ).data( 'ticketid' );
			var newStatus = $( '#read-status select' ).val() - 0;
			$.post( '/api.php?action=changeStatus', { id: ticketID, status: newStatus }, null, 'json' )
				.done( function ( data ) {
					addHistoryRow( data.entryDate, data.entryOld, data.entryNew );
				} );
		},

		clearCreateView: function () {
			$( '#create-title, #create-description' ).val( '' );
		},

		clearBrowseView: function () {
			$( '#view-browse tbody tr' ).remove();
		},

		clearReadView: function () {
			$( '#view-read h2' ).text( '' );
			$( '#view-read tbody tr' ).remove();
		}
	};

}( jQuery ) );

window.dt = dt;