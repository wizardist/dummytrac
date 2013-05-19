<?php

class History {

	private static $_table = 'history';

	/**
	 * @var Ticket
	 */
	private $_ticket;

	var $historyID = 0;
	var $entries = array();

	/**
	 * @param int $id Ticket ID
	 */
	public function __construct( $id ) {
		$this->_ticket = Ticket::newByID( (int)$id );
		$this->historyID = $this->getTicket()->getId();
	}

	/**
	 * Creates a new object and gets full ticket history from the DB
	 *
	 * @param int $id
	 *
	 * @return History
	 */
	public static function newByID( $id ) {
		$id = (int)$id;
		$db = DTBase::i();
		$query = "SELECT * FROM " . self::$_table . " WHERE history_ticket = " . $id . " ORDER BY history_date ASC";

		$entries = $db->query( $query, false );
		$history = new History( $id );
		foreach ( $entries as $entry ) {
			$history->entries[] = HistoryEntry::newFromData( $entry );
		}

		return $history;
	}

	/**
	 * Links the ticket relevant to this object
	 * @return Ticket
	 */
	public function getTicket() {
		return $this->_ticket;
	}
}