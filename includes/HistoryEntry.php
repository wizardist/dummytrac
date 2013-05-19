<?php

class HistoryEntry {

	private $_table = 'history';

	/**
	 * @var Ticket
	 */
	private $_ticket;

	private $_isSaved = true;

	var $ticketID = 0;
	var $entryDate = '';
	var $entryOld = 0;
	var $entryNew = 0;

	/**
	 * @param int $ticketID
	 */
	private function __construct( $ticketID ) {
		$this->ticketID = $ticketID;
		$this->_ticket = Ticket::newByID( $this->ticketID );
	}

	/**
	 * Creates a new HistoryEntry object with
	 * given data pushed into
	 *
	 * @param array $data Array with key names according to the DB schema
	 *
	 * @return HistoryEntry
	 */
	public static function newFromData( array $data ) {
		$entry = new HistoryEntry( (int)$data['history_ticket'] );
		$entry->entryDate = $data['history_date'];
		$entry->entryOld = (int)$data['history_old'];
		if ( array_key_exists( 'history_new', $data ) ) {
			$entry->entryNew = (int)$data['history_new'];
		}

		return $entry;
	}

	/**
	 * Links to the relevan ticket
	 * @return Ticket
	 */
	public function getTicket() {
		return $this->_ticket;
	}

	public function getDate() {
		return $this->entryDate;
	}

	public function getOld() {
		return $this->entryOld;
	}

	public function getNew() {
		return $this->entryNew;
	}

	/**
	 * Assigns this history entry a new ticket
	 *
	 * @param Ticket $ticket
	 *
	 * @return $this
	 */
	public function setTicket( Ticket $ticket ) {
		$this->_ticket = $ticket;
		$this->ticketID = $ticket->getId();
		$this->entryOld = $ticket->getStatus();
		$this->touch();

		return $this;
	}

	/**
	 * Sets the new state for the ticket in the history
	 *
	 * @param int $status
	 *
	 * @return $this
	 */
	public function setNew( $status ) {
		$this->entryNew = (int)$status;
		$this->touch();

		return $this;
	}

	/**
	 * Marks the object for update in the DB
	 * @return $this
	 */
	public function touch() {
		$this->_isSaved = false;
		return $this;
	}

	/**
	 * Saves the object into the DB, returns a resource link
	 * if the insertion was actually performed
	 *
	 * @return bool|resource
	 */
	public function save() {
		if ( $this->_isSaved ) {
			return true;
		}

		if ( $this->getOld() === $this->getNew() ) {
			return true;
		}

		$data = array(
			'history_ticket' => $this->getTicket()->getId(),
			'history_date'   => getCurrentTimestampUTC(),
			'history_old'    => $this->getOld(),
			'history_new'    => $this->getNew()
		);
		return DTBase::i()->insert( $this->_table, $data );
	}
}
