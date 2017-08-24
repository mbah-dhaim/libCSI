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

class Join {
	/**
	 *
	 * @var Criteria
	 */
	private $criteria;
	/**
	 * table to join
	 *
	 * @var string
	 */
	private $table = '';
	private $JOIN_TYPE = array (
			"JOIN",
			"INNER JOIN",
			"OUTER JOIN",
			"LEFT JOIN",
			"RIGHT JOIN",
			"LEFT OUTER JOIN",
			"RIGHT OUTER JOIN"
	);
	/**
	 *
	 * @var integer
	 */
	private $joinType = 0;
	/**
	 * ctor
	 *
	 * @param array $options
	 * @throws \InvalidArgumentException
	 */
	public function __construct($options, $type = 0) {
		$num = count ( $options );
		if ($num < 2) {
			throw new InvalidArgumentException ( "Number of arguments at least 2", 300 );
		}
		if (! is_int ( $type ) || $type < 0 || $type > 6) {
			throw new \InvalidArgumentException ( "Type must be an integer and between 0 to 6", 303 );
		}
		$this->joinType = $type;
		if ($num == 2) {
			$this->table = $options [0];
			$c = $options [1];
			if (! ($c instanceof Criteria)) {
				throw new \InvalidArgumentException ( "Argument must be instance of Criteria", 301 );
			}
			$c->setIsParamValue ( false );
			$this->criteria = $c;
		} elseif ($num == 3) {
			$this->table = $options [0];
			$c = new Criteria ( $options [1], $options [2] );
			$c->setIsParamValue ( false );
			$this->criteria = $c;
		} elseif ($num == 4) {
			$this->table = $options [0];
			$c = new Criteria ( $options [1], $options [2], $options [3] );
			$c->setIsParamValue ( false );
			$this->criteria = $c;
		} elseif ($num == 5) {
			$this->table = $options [0];
			$c = new Criteria ( $options [1], $options [2], $options [3], $options [4] );
			$c->setIsParamValue ( false );
			$this->criteria = $c;
		} elseif ($num == 6) {
			$this->table = $options [0];
			$c = new Criteria ( $options [1], $options [2], $options [3], $options [4], $options [5] );
			$c->setIsParamValue ( false );
			$this->criteria = $c;
		} elseif ($num == 7) {
			$this->table = $options [0];
			$c = new Criteria ( $options [1], $options [2], $options [3], $options [4], $options [5], $options [6] );
			$this->criteria = $c;
		}
	}
	public function buildQuery() {
		$result = array ();
		$result [] = $this->JOIN_TYPE [$this->joinType];
		$result [] = $this->table;
		$result [] = "ON (";
		$result [] = $this->criteria->buildQuery ();
		$result [] = ")";
		return implode ( " ", $result );
	}
	/**
	 *
	 * @return \CSI\Data\Criteria $criteria
	 */
	public function getCriteria() {
		return $this->criteria;
	}
	/**
	 *
	 * @param \CSI\Data\Criteria $criteria
	 */
	public function setCriteria($criteria) {
		$this->criteria = $criteria;
		return $this;
	}
}

