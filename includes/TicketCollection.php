<?php

class TicketCollection {

	private $_table = 'ticket';

	private $_tickets;

	public function __construct( array $ids = array() ) {
		$db = DTBase::i();

		$whereIn = '';
		if ( count( $ids ) ) {
			$whereIn = " WHERE ticket_id IN ('";
			foreach ( $ids as &$id ) {
				$id = mysql_real_escape_string( $id, $db->getLink() );
			}
			$whereIn .= implode( "', '", $ids ) . "')";
		}

		$query = "SELECT * FROM {$this->_table}" . $whereIn . ' ORDER BY ticket_updated ASC';

		$rawResult = $db->query( $query, false );
		foreach ( $rawResult as $key => $value ) {
			$this->_tickets[(int)$value['ticket_id']] = Ticket::newFromData( $value );
		}
		//print_r( $this->_tickets );
	}

	public function getByID( $id ) {
		if ( !isset( $this->_tickets[(int)$id] ) ) {
			return false;
		}
		return $this->_tickets[(int)$id];
	}

	public function getAll() {
		return $this->_tickets;
	}
}