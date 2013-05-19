<?php

class Ticket {

	static private $_cachedTickets = array();

	private static $_table = 'ticket';

	const STATUS_NEW = 0;
	const STATUS_UNCONFIRMED = 1;
	const STATUS_ASSIGNED = 2;
	const STATUS_FIXED = 4;
	const STATUS_VERIFIED = 8;

	public static $statusTexts = array(
		0 => 'New',
		1 => 'Unconfirmed',
		2 => 'Assigned',
		4 => 'Fixed',
		8 => 'Verified'
	);

	/**
	 * Whether Ticket was pushed to the DB after it was changed
	 * @var bool
	 */
	private $_isSaved = true;

	/**
	 * @var HistoryEntry
	 */
	private $_historyEntry;

	var $ticketID = 0;
	var $ticketStatus = Ticket::STATUS_NEW;
	var $ticketUpdated = '';
	var $ticketTitle = '';
	var $ticketDescription = '';

	/**
	 * Returns a new ticket object from the DB
	 *
	 * @param int $id Ticket ID
	 *
	 * @return Ticket
	 */
	public static function newByID( $id ) {
		if ( array_key_exists( (int)$id, self::$_cachedTickets ) ) {
			return self::$_cachedTickets[(int)$id];
		}

		$ticketData = DTBase::i()->query( "SELECT * FROM " . self::$_table . " WHERE ticket_id = " . (int)$id );
		$ticket = Ticket::newFromData( $ticketData );

		return $ticket;
	}

	/**
	 * Returns a new ticket object and pushes the
	 * given data into it.
	 * If `ticket_id` is specified and/or > 0 then the
	 * object is automatically marked for update.
	 *
	 * @param array $data Column-value array according to the DB schema
	 *
	 * @return Ticket
	 */
	public static function newFromData( array $data ) {
		$ticket = new Ticket();
		$ticket->ticketStatus = (int)$data['ticket_status'];
		$ticket->ticketUpdated = $data['ticket_updated'];
		$ticket->ticketTitle = $data['ticket_title'];
		$ticket->ticketDescription = $data['ticket_description'];
		if ( isset( $data['ticket_id'] ) && (int)$data['ticket_id'] > 0 ) {
			$ticket->ticketID = (int)$data['ticket_id'];
			$ticket->touch();
		}

		self::$_cachedTickets[$data['ticket_id']] = $ticket;
		return $ticket;
	}

	public function getId() {
		return (int)$this->ticketID;
	}

	public function getStatus() {
		return (int)$this->ticketStatus;
	}

	public function getUpdated() {
		return $this->ticketUpdated;
	}

	public function getTitle() {
		return $this->ticketTitle;
	}

	public function getDescription() {
		return $this->ticketDescription;
	}

	/**
	 * Change the status of the ticket
	 *
	 * @param int $value
	 *
	 * @return bool
	 */
	public function setStatus( $value ) {
		$value = (int)$value;
		if ( $value === $this->ticketStatus ) {
			return true;
		}

		static $statusValues;

		if ( null === $statusValues ) {
			$statusValues = array(
				Ticket::STATUS_NEW, Ticket::STATUS_UNCONFIRMED, Ticket::STATUS_ASSIGNED,
				Ticket::STATUS_FIXED, Ticket::STATUS_VERIFIED
			);
		}

		if ( in_array( $value, $statusValues ) ) {
			$this->_historyEntry = HistoryEntry::newFromData( array(
				'history_ticket' => $this->getId(),
				'history_date'   => getCurrentTimestampUTC(),
				'history_old'    => $this->getStatus(),
				'history_new'    => $value
			) )->touch();

			$this->ticketStatus = (int)$value;
			$this->_isSaved = false;
			return true;
		} else {
			return false;
		}
	}

	public function setTitle( $value ) {
		if ( $value === $this->ticketTitle ) {
			return true;
		}
		$this->ticketTitle = $value;
		$this->_isSaved = false;
		return true;
	}

	public function setDescription( $value ) {
		if ( $value === $this->ticketDescription ) {
			return true;
		}
		$this->ticketDescription = $value;
		$this->_isSaved = false;
		return true;
	}

	/**
	 * Marks the object for update
	 */
	public function touch() {
		$this->_isSaved = false;
	}

	/**
	 * Saves the object in the DB (insert/update).
	 * Resource link returned if the action was performed.
	 * @return bool|resource
	 */
	public function save() {
		if ( $this->_isSaved ) {
			return true;
		}

		$db = DTBase::i();

		$timestamp = getCurrentTimestampUTC();
		if ( $this->ticketID > 0 ) {
			$data = array(
				'ticket_status'      => $this->ticketStatus,
				'ticket_updated'     => $timestamp,
				'ticket_title'       => $this->ticketTitle,
				'ticket_description' => $this->ticketDescription
			);
			$condition = array( 'ticket_id' => $this->ticketID );
			$result = $db->update( self::$_table, $data, $condition );
		} else {
			$data = array(
				'ticket_status'      => $this->ticketStatus,
				'ticket_updated'     => $timestamp,
				'ticket_title'       => $this->ticketTitle,
				'ticket_description' => $this->ticketDescription
			);
			$result = $db->insert( self::$_table, $data );
		}

		if ( $result ) {
			$this->ticketUpdated = $timestamp;
			$this->ticketID = $db->getInsertID( $this->ticketID );

			if ( $this->_historyEntry instanceof HistoryEntry ) {
				$this->_historyEntry->save();
			}
		}
		$this->_isSaved = $result;
		return $result;
	}
}