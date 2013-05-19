<?php

class DTBase {
	/**
	 * @var DTBase
	 */
	private static $_instance = null;

	/**
	 * @var resource
	 */
	private $_dbLink;

	private function __construct() {
		$dbhost = $dbname = $dbuser = $dbpassword = null;
		extract( DTConfig::i()->get(
			array( 'dbhost', 'dbname', 'dbuser', 'dbpassword' )
		) );

		$this->_dbLink = mysql_connect( $dbhost, $dbuser, $dbpassword );
		if ( !mysql_select_db( $dbname ) ) {
			throw new Exception( 'Database doesn\'t exist' );
		}
	}

	private function __clone() {
	}

	/**
	 * @return DTBase
	 */
	public static function i() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @param string $sql SQL query
	 * @param bool $spread if count(result) == 1 then return that only row
	 *
	 * @return array
	 */
	public function query( $sql, $spread = true ) {
		$internalResult = mysql_query( $sql, $this->getLink() );

		$result = array();
		while ( $row = mysql_fetch_array( $internalResult, MYSQL_ASSOC ) ) {
			$result[] = $row;
		}
		if ( count( $result ) === 1 && $spread ) {
			return array_pop( $result );
		} else {
			return $result;
		}
	}

	/**
	 * Inserts a new row in the given table
	 *
	 * Use Ticket::getInsertID() to get the ID
	 * of the new autoincremented key
	 *
	 * @param string $table Table name
	 * @param array $data Column-value array
	 *
	 * @return resource
	 */
	public function insert( $table, array $data ) {
		$keys = array_keys( $data );
		$values = array_values( $data );

		foreach ( $values as &$value ) {
			$value = mysql_real_escape_string( $value, $this->getLink() );
		}

		$query = "INSERT INTO `" . $table . "` (" . implode( ',', $keys ) . " ) "
			. "VALUES ( '" . implode( "', '", $values ) . "' )";

		return mysql_query( $query, $this->getLink() );
	}

	/**
	 * Returns the autoincrement ID of the new row
	 *
	 * @param int $fallback A value to return if no ID available
	 *
	 * @return int
	 */
	public function getInsertID( $fallback = 0 ) {
		$insertID = mysql_insert_id( $this->getLink() );
		if ( !$insertID ) {
			return $fallback;
		} else {
			return $insertID;
		}
	}

	/**
	 * Updates a row in a given table under a provided condition
	 *
	 * @param string $table
	 * @param array $data Column-value array
	 * @param array $condition Column-value array
	 *
	 * @return resource
	 */
	public function update( $table, array $data, array $condition = array() ) {
		$query = "UPDATE " . $table . " SET " . $this->_implodeStatements( $data );
		if ( !empty( $condition ) ) {
			$query .= ' WHERE ' . $this->_implodeStatements( $condition );
		}
		return mysql_query( $query, $this->getLink() );
	}

	/**
	 * Returns a link to the MySQL connection
	 * @return resource
	 */
	public function getLink() {
		return $this->_dbLink;
	}

	/**
	 * Compiles column-value array in a 'column = value' string
	 * for SQL statements
	 *
	 * @param array $data Column-value array
	 *
	 * @return string
	 */
	private function _implodeStatements( array $data ) {
		$statements = array();
		foreach ( $data as $field => $value ) {
			$statements[] = $field . " = '" . mysql_real_escape_string( $value, $this->getLink() ) . "'";
		}
		return implode( ', ', $statements );
	}
}