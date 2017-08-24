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

use InvalidArgumentException;

class Criteria {
	private $field;
	private $oper;
	private $value;
	private $acceptableOper = array (
			"=",
			"<>",
			">",
			"<",
			">=",
			"=<",
			"LIKE",
			"NOT LIKE",
			"IS",
			"IS NOT",
			"IN",
			"NOT IN",
			"BETWEEN"
	);
	private $group = 0;
	private $combine = "";
	private $hasParam = true;
	/**
	 * Default value dianggap parameter, kecuali dalam keadaan join bisa dianggap sebagai field
	 *
	 * @var string
	 */
	private $isParamValue = true;
	public function __construct() {
		$num = func_num_args ();
		if ($num < 2) {
			throw new InvalidArgumentException ( "Number of arguments at least 2", 100 );
		}
		$this->field = func_get_arg ( 0 );
		$this->oper = "=";
		$this->group = 0;
		switch ($num) {
			case 2 :
				$this->value = func_get_arg ( 1 );
				break;
			case 3 :
				$this->oper = strtoupper ( func_get_arg ( 1 ) );
				$this->value = func_get_arg ( 2 );
				break;
			case 4 :
				$this->oper = strtoupper ( func_get_arg ( 1 ) );
				$this->value = func_get_arg ( 2 );
				$this->group = func_get_arg ( 3 );
				break;
			case 5 :
				$this->oper = strtoupper ( func_get_arg ( 1 ) );
				$this->value = func_get_arg ( 2 );
				$this->group = func_get_arg ( 3 );
				$this->isParamValue = func_get_arg ( 4 );
				break;
		}
	}
	/**
	 * validate input
	 *
	 * @throws \InvalidArgumentException
	 */
	private function validate() {
		if (empty ( $this->field ) || ! is_string ( $this->field )) {
			throw new \InvalidArgumentException ( "Field must be string", 101 );
		}
		if (! in_array ( $this->oper, $this->acceptableOper )) {
			throw new \InvalidArgumentException ( "Invalid condition", 102 );
		}
		if (($this->oper == "LIKE" || $this->oper == "NOT LIKE") &&
				 ! is_string ( $this->value )) {
			throw new \InvalidArgumentException ( "Value must be string", 103 );
		}
		if (($this->oper == "IS" || $this->oper == "IS NOT") &&
				 ! is_null ( $this->value )) {
			throw new \InvalidArgumentException ( "Value must be null", 104 );
		}
		if (($this->oper == "IN" || $this->oper == "NOT IN") &&
				 ! is_array ( $this->value )) {
			throw new \InvalidArgumentException ( "Value must be an array", 105 );
		}
		if ($this->oper == "BETWEEN") {
			if (! is_array ( $this->value )) {
				throw new \InvalidArgumentException ( "Value must be an array", 106 );
			} elseif (count ( $this->value ) != 2) {
				throw new \InvalidArgumentException ( "Array must contains 2 members", 107 );
			}
		}
	}
	public function buildQuery() {
		$this->validate ();
		$result = array ();
		if ($this->combine) $result [] = strtoupper ( $this->combine );
		if ($this->group === 1) $result [] = "(";
		$result [] = $this->field;
		if ($this->oper == "=" && is_null ( $this->value )) {
			$this->oper = "IS";
		}
		if ($this->oper == "<>" && is_null ( $this->value )) {
			$this->oper = "IS NOT";
		}
		$result [] = $this->oper;
		if ($this->oper == "IN" || $this->oper == "NOT IN") {
			$this->hasParam = false;
			$result [] = "(";
			$tmp = array ();
			for($i = 0; $i < count ( $this->value ); $i ++) {
				$tmp [] = "'" . $this->value [$i] . "'";
			}
			$result [] = implode ( ",", $tmp );
			$result [] = ")";
		} elseif ($this->oper == "BETWEEN") {
			if ($this->isParamValue) {
				$result [] = "?";
			} else {
				$this->hasParam = false;
				$result [] = $this->value;
			}
			$result [] = "AND";
			if ($this->isParamValue) {
				$result [] = "?";
			} else {
				$this->hasParam = false;
				$result [] = $this->value;
			}
		} elseif ($this->oper == "IS" || $this->oper == "IS NOT") {
			$result [] = "NULL";
		} else {
			if ($this->isParamValue) {
				$result [] = "?";
			} else {
				$this->hasParam = false;
				$result [] = $this->value;
			}
		}
		if ($this->group === 2) $result [] = ")";
		return implode ( " ", $result );
	}
	/**
	 *
	 * @return string $field
	 */
	public function getField() {
		return $this->field;
	}
	/**
	 *
	 * @return string $oper
	 */
	public function getOper() {
		return $this->oper;
	}
	/**
	 *
	 * @return mixed $value
	 */
	public function getValue() {
		return $this->value;
	}
	/**
	 *
	 * @return boolean $hasParam
	 */
	public function getHasParam() {
		return $this->hasParam;
	}
	/**
	 *
	 * @param string $field
	 */
	public function setField($field) {
		$this->field = $field;
		return $this;
	}
	/**
	 *
	 * @param string $oper
	 */
	public function setOper($oper) {
		$this->oper = $oper;
		return $this;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
	/**
	 *
	 * @param string $combine
	 */
	public function setCombine($combine) {
		$this->combine = $combine;
		return $this;
	}
	/**
	 * @param boolean $isParamValue
	 */
	public function setIsParamValue($isParamValue) {
		$this->isParamValue = $isParamValue;
		return $this;
	}

}

