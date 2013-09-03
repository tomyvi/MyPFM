<?php
class SPDO {
	/**
	  * Instance de la classe PDO
	  *
	  * @var PDO
	  * @access private
	  */
	private $PDOInstance = null;

	/**
	  * Instance de la classe SPDO
	  *
	  * @var SPDO
	  * @access private
	  * @static
	  */
	private static $instance = null;

	/**
	  * Constructeur
	  *
	  * @param void
	  * @return void
	  * @see PDO::__construct()
	  * @access private
	  */
	private function __construct() {
		global $_config;
		$this->PDOInstance = new PDO('mysql:dbname=' . $_config['mysql_db'] . ';host=' . $_config['mysql_server'], $_config['mysql_user'], $_config['mysql_pwd']);
	}

	/**
	  * Crée et retourne l'objet SPDO
	  *
	  * @access public
	  * @static
	  * @param void
	  * @return SPDO $instance
	  */
	public static function getInstance() {
		if (is_null(self :: $instance)) {
			self :: $instance = new SPDO();
		}
		return self :: $instance;
	}

	public function __call($method, $args){
		if ( method_exists( $this->PDOInstance, $method ) ) {
            return call_user_func_array(array( $this->PDOInstance, $method ), $args );
        }
	}
}