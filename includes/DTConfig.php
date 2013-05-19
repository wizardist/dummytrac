<?php

class DTConfig {

	/**
	 * @var DTConfig
	 */
	private static $_instance = null;

	/**
	 * Read-only variables
	 * @var array
	 */
	private static $_readonly = array(
		'rootPath',
	);

	private $_settings = array();

	private function __construct() {
		$this->_settings += $this->_injectConfig();
	}

	private function __clone() {
	}

	/**
	 * @return DTConfig
	 */
	public static function i() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Makes an absolute file path
	 *
	 * @param string $path Path within the docroot
	 *
	 * @return string
	 */
	public static function makeFilePath( $path = '' ) {
		return self::i()->get( 'rootPath' ) . $path;
	}

	/**
	 * Makes an URL leading to static resources
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function makeResourcePath( $path = '' ) {
		return '//' . self::i()->get( 'host' ) . '/' . self::i()->get( 'path' ) . self::i()->get( 'resources' ) . $path;
	}

	/**
	 * Returns a configuration variable
	 *
	 * @param array|string $name Variable name or a collection of variable names
	 * @param mixed $default Default value to return if variable not found
	 *
	 * @return mixed
	 */
	public function get( $name, $default = null ) {
		if ( is_array( $name ) ) {
			return array_intersect_key( $this->_settings, array_flip( $name ) );
		} elseif ( $this->exists( $name ) ) {
			return $this->_settings[$name];
		} else {
			return $default;
		}
	}

	/**
	 * Sets a configuration variable
	 *
	 * @param $name Variable name
	 * @param $value Value to set
	 *
	 * @return bool Whether value was set properly
	 */
	public function set( $name, $value ) {
		if ( in_array( $name, self::$_readonly ) ) {
			return false;
		} else {
			$this->_settings[$name] = $value;
			return true;
		}
	}

	/**
	 * Checks if the variable exists
	 *
	 * @param string $name Variable name
	 *
	 * @return bool
	 */
	public function exists( $name ) {
		return in_array( $name, array_keys( $this->_settings ) );
	}

	/**
	 * Calculates the root path for the project
	 * @return mixed
	 */
	private function _getRootPath() {
		if ( $this->exists( 'rootPath' ) ) {
			return $this->get( 'rootPath' );
		}

		if ( realpath( '.' ) ) {
			$this->_settings['rootPath'] = realpath( '.' );
		} else {
			$this->_settings['rootPath'] = dirname( __DIR__ );
		}
		return $this->get( 'rootPath' );
	}

	/**
	 * Gets the values from the configuration file
	 *
	 * @param string $path Alternative path to the file
	 *
	 * @return array
	 */
	private function _injectConfig( $path = '/settings.php' ) {
		static $settings;

		if ( null === $settings ) {
			include( $this->_getRootPath() . $path );
		}

		return $settings;
	}
}
