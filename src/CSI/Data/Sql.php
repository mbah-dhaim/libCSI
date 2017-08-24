<?php
/**
 * Simple Mysql/MariaDB query builder components
 *
 * @author Mahmud A Hakim
 * @copyright 2017 Mahmud A Hakim
 * @package CSI
 * @category Data
 * @version 1.0
 */
namespace CSI\Data;

use Exception;

/**
 * Object untuk menghandle sebuah sql query dan parameternya untuk proses data pada \CSI\Data\DataAdapter
 *
 * @author mahmud
 */
class Sql {
	/**
	 *
	 * @var string[]
	 */
	private $text = array ();
	/**
	 *
	 * @var array
	 */
	private $params = array ();
	/**
	 *
	 * @var boolean
	 */
	private $isBound = false;
	/**
	 *
	 * @var boolean
	 */
	private $isBatch = false;
	/**
	 * ctor
	 *
	 * @param string $text
	 * @param array $params
	 * @param string $isBound
	 */
	public function __construct($text = '', $params = array(), $isBound = false) {
		if ($text) {
			$this->text [] = $text;
		}
		if ($params) {
			$this->params = $params;
		}
		$this->isBound = $isBound;
	}
	/**
	 * menambahkan sql query ke buffer
	 *
	 * @param string $text
	 * @return \CSI\Data\Sql
	 */
	public function addText($text) {
		if (is_array ( $text )) {
			for($i = 0; $i < count ( $text ); $i ++) {
				$this->text [] = $text [$i];
			}
		} else {
			$this->text [] = $text;
		}
		return $this;
	}
	/**
	 * memuat sql query dari buffer
	 *
	 * @return string
	 */
	public function text() {
		return implode ( " ", $this->text );
	}
	/**
	 * Menambahkan parameter
	 *
	 * @param mixed|array $value
	 *        	jika $value berupa array, maka dimasukkan dalam proses batch
	 * @param string $name
	 * @return \CSI\Data\Sql
	 */
	public function addParam($value, $name = null) {
		$numargs = func_num_args ();
		if ($numargs < 3) {
			if (is_array ( $value )) {
				$this->params [] = $value;
				$this->isBatch = true;
			} else {
				if ($this->isBatch) {
					return $this;
				}
				if ($name) {
					if (substr ( $name, 0, 1 ) !== ":") {
						$name = ":" . $name;
					}
					$this->params [$name] = $value;
					$this->isBound = true;
				} else {
					$this->params [] = $value;
				}
			}
		} else {
			for($i = 0; $i < $numargs; $i ++) {
				$this->params [] = func_get_arg ( $i );
			}
		}
		return $this;
	}
	public function addParameter() {
		$numargs = func_num_args ();
		$args = func_get_args ();
		if ($numargs == 0) {
			throw new Exception ( "Parameter tidak lengkap" );
		}
		if ($numargs == 1) {
			if (is_array ( $args [0] )) {
				// override parameters
				$this->params = $args [0];
			} else {
				$this->params [] = $args [0];
			}
		} elseif ($numargs == 2) {
			$name = $args [1];
			if (substr ( $name, 0, 1 ) !== ":") {
				$name = ":" . $name;
			}
			$this->params [$name] = $args [0];
			$this->isBound = true;
		} else {
			for($i = 0; $i < $numargs; $i ++) {
				$this->params [] = $args [$i];
			}
		}
		return $this;
	}
	/**
	 * Memuat parameter dari buffer
	 */
	public function params() {
		return $this->params;
	}
	/**
	 *
	 * @param boolean $isBound
	 * @return \CSI\Data\Sql
	 */
	public function setBound($isBound) {
		$this->isBound = $isBound;
		return $this;
	}
	/**
	 * cek apakah bounded
	 */
	public function isBound() {
		return $this->isBound;
	}
	/**
	 * cek apakah batch parameter
	 */
	public function isBatch() {
		return $this->isBatch;
	}
	/**
	 *
	 * @return \CSI\Data\Sql
	 */
	public function clearParams() {
		$this->params = array ();
		$this->isBound = false;
		$this->isBatch = false;
		return $this;
	}
	/**
	 * Menghapus data dalam buffer
	 *
	 * @return \CSI\Data\Sql
	 */
	public function clear() {
		$this->text = array ();
		return $this->clearParams ();
	}
	/**
	 *
	 * @param Sql $sql
	 * @param string $separator
	 * @return \CSI\Data\Sql
	 */
	public function addSql(\CSI\Data\Sql $sql, $separator = ", ") {
		$this->addText ( implode ( $separator, $sql->getText () ) );
		$params = $sql->params ();
		for($i = 0; $i < count ( $params ); $i ++) {
			$this->params [] = $params [$i];
		}
		return $this;
	}
	/**
	 *
	 * @return string[]
	 */
	public function getText() {
		return $this->text;
	}
	/**
	 *
	 * @param array $text
	 */
	public function setText(array $text) {
		$this->text = $text;
		return $this;
	}
}