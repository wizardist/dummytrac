<?php

class DTApi {

	private $_request = array();

	public function __construct() {
		$this->_request = $_POST;
	}

	public function create() {
		$title = trim( $this->_request['title'] );
		$description = trim( $this->_request['description'] );
		if ( strval( $title ) === '' || strval( $description ) === '' ) {
			return;
		}

		$ticket = new Ticket();
		$ticket->setTitle( $this->_request['title'] );
		$ticket->setDescription( $this->_request['description'] );
		$ticket->save();
		return $ticket;
	}

	public function browse() {
		$tickets = new TicketCollection();
		$rawResult = $tickets->getAll();
		if ( !count( $rawResult ) ) {
			return array();
		}
		$result = array();
		foreach ( $rawResult as $id => &$ticket ) {
			unset( $ticket->ticketDescription );
			$result['_' . $id] = $ticket;
		}
		return $result;
	}

	public function read() {
		$ticket = Ticket::newByID( $this->_request['id'] );
		$history = History::newByID( $ticket->getId() )->entries;

		$result = array(
			'ticket'  => $ticket,
			'history' => $history
		);
		return $result;
	}

	public function changeStatus() {
		$ticketID = (int)$this->_request['id'];
		$status = (int)$this->_request['status'];

		$ticket = Ticket::newByID( $ticketID );
		$ticketOldStatus = $ticket->getStatus();
		$ticket->setStatus( $status );
		if ( $ticket->save() ) {
			$result = array(
				'ticketID'  => $ticket->getId(),
				'entryDate' => getCurrentTimestampUTC(),
				'entryOld'  => $ticketOldStatus,
				'entryNew'  => $status
			);
			return $result;
		}
	}
}