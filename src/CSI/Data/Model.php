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

use CSI\Helper\Set;

/**
 * Simple data model
 */
class Model extends Set {
	/**
	 *
	 * @var DataAdapter
	 */
	protected $db;
	/**
	 *
	 * @var string
	 */
	protected $table = '';
	/**
	 *
	 * @var string
	 */
	protected $alias = '';
	/**
	 * table primary key
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	/**
	 *
	 * @var array
	 */
	protected $fields = array ();
	/**
	 *
	 * @var array
	 */
	protected $values = array ();
	/**
	 *
	 * @var Join[]
	 */
	protected $joins = array ();
	/**
	 *
	 * @var Criteria[]
	 */
	protected $wheres = array ();
	/**
	 *
	 * @var array
	 */
	protected $group = array ();
	/**
	 *
	 * @var Criteria[]
	 */
	protected $havings = array ();
	/**
	 *
	 * @var array
	 */
	protected $orders = array ();
	/**
	 *
	 * @var integer|null
	 */
	protected $limit = null;
	/**
	 *
	 * @var integer|null
	 */
	protected $offset = null;
	/**
	 *
	 * @var \CSI\Data\Sql
	 */
	protected $sql = null;
	/**
	 *
	 * @var boolean
	 */
	protected $timestamps = false;
	/**
	 * nama field untuk create auto timestamp
	 *
	 * @var string
	 */
	protected $createdTs = "created_at";
	/**
	 * nama field untuk update auto timestamp
	 *
	 * @var string
	 */
	protected $updatedTs = "updated_at";
	public function __construct($db = null) {
		if (! $db) {
			$db = DataAdapter::getInstace ();
		}
		$this->db = $db;
		$this->sql = new Sql ();
	}
	/**
	 *
	 * @param string|array $fields
	 * @throws \InvalidArgumentException
	 * @return \CSI\Data\Model
	 */
	public function select($fields = "*") {
		$this->fields = array ();
		if (is_array ( $fields )) {
			$this->fields = array_merge ( $this->fields, $fields );
		} elseif (is_string ( $fields )) {
			$this->fields [] = $fields;
		} else {
			throw new \InvalidArgumentException ( "Fields must be string or array of strings" );
		}
		return $this;
	}
	public function join() {
		$options = func_get_args ();
		$this->joins [] = new Join ( $options );
		return $this;
	}
	public function innerJoin() {
		$options = func_get_args ();
		$this->joins [] = new Join ( $options, 1 );
		return $this;
	}
	public function outerJoin() {
		$options = func_get_args ();
		$this->joins [] = new Join ( $options, 2 );
		return $this;
	}
	public function leftJoin() {
		$options = func_get_args ();
		$this->joins [] = new Join ( $options, 3 );
		return $this;
	}
	public function rightJoin() {
		$options = func_get_args ();
		$this->joins [] = new Join ( $options, 4 );
		return $this;
	}
	public function leftOuterJoin() {
		$options = func_get_args ();
		$this->joins [] = new Join ( $options, 5 );
		return $this;
	}
	public function rightOuterJoin() {
		$options = func_get_args ();
		$this->joins [] = new Join ( $options, 6 );
		return $this;
	}
	public function where() {
		$num = func_num_args ();
		$argc = null;
		switch ($num) {
			case 1 :
				$argc = func_get_arg ( 0 );
				if (! ($argc instanceof Criteria)) throw new \InvalidArgumentException ( "parameter 1 must be instance of Criteria", 201 );
				break;
			case 2 :
				$field = func_get_arg ( 0 );
				$value = func_get_arg ( 1 );
				$argc = new Criteria ( $field, $value );
				break;
			case 3 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$argc = new Criteria ( $field, $oper, $value );
				break;
			case 4 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$group = func_get_arg ( 3 );
				$argc = new Criteria ( $field, $oper, $value, $group );
				break;
			default :
				throw new \InvalidArgumentException ( "Number of parameters at least 1", 200 );
		}
		if (count ( $this->wheres ) > 0) $argc->setCombine ( "and" );
		$this->wheres [] = $argc;
		return $this;
	}
	public function orWhere() {
		$num = func_num_args ();
		$argc = null;
		switch ($num) {
			case 1 :
				$argc = func_get_arg ( 0 );
				if (! ($argc instanceof Criteria)) throw new \InvalidArgumentException ( "parameter 1 must be instance of Criteria", 201 );
				break;
			case 2 :
				$field = func_get_arg ( 0 );
				$value = func_get_arg ( 1 );
				$argc = new Criteria ( $field, $value );
				break;
			case 3 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$argc = new Criteria ( $field, $oper, $value );
				break;
			case 4 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$group = func_get_arg ( 3 );
				$argc = new Criteria ( $field, $oper, $value, $group );
				break;
			default :
				throw new \InvalidArgumentException ( "Number of parameters at least 1", 202 );
		}
		if (count ( $this->wheres ) > 0) $argc->setCombine ( "OR" );
		$this->wheres [] = $argc;
		return $this;
	}
	public function having() {
		$num = func_num_args ();
		$argc = null;
		switch ($num) {
			case 1 :
				$argc = func_get_arg ( 0 );
				if (! ($argc instanceof Criteria)) throw new \InvalidArgumentException ( "parameter 1 must be instance of Criteria", 201 );
				break;
			case 2 :
				$field = func_get_arg ( 0 );
				$value = func_get_arg ( 1 );
				$argc = new Criteria ( $field, $value );
				break;
			case 3 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$argc = new Criteria ( $field, $oper, $value );
				break;
			case 4 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$group = func_get_arg ( 3 );
				$argc = new Criteria ( $field, $oper, $value, $group );
				break;
			default :
				throw new \InvalidArgumentException ( "Number of parameters at least 1", 200 );
		}
		if (count ( $this->wheres ) > 0) $argc->setCombine ( "and" );
		$this->havings [] = $argc;
		return $this;
	}
	public function orHaving() {
		$num = func_num_args ();
		$argc = null;
		switch ($num) {
			case 1 :
				$argc = func_get_arg ( 0 );
				if (! ($argc instanceof Criteria)) throw new \InvalidArgumentException ( "parameter 1 must be instance of Criteria", 201 );
				break;
			case 2 :
				$field = func_get_arg ( 0 );
				$value = func_get_arg ( 1 );
				$argc = new Criteria ( $field, $value );
				break;
			case 3 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$argc = new Criteria ( $field, $oper, $value );
				break;
			case 4 :
				$field = func_get_arg ( 0 );
				$oper = func_get_arg ( 1 );
				$value = func_get_arg ( 2 );
				$group = func_get_arg ( 3 );
				$argc = new Criteria ( $field, $oper, $value, $group );
				break;
			default :
				throw new \InvalidArgumentException ( "Number of parameters at least 1", 202 );
		}
		if (count ( $this->wheres ) > 0) $argc->setCombine ( "OR" );
		$this->havings [] = $argc;
		return $this;
	}
	public function groupBy($fields) {
		if (is_array ( $fields )) {
			for($i = 0; $i < count ( $fields ); $i ++)
				$this->group [] = $fields [$i];
		} else {
			$this->group [] = $fields;
		}
		return $this;
	}
	public function orderBy($idx, $ord = null) {
		if ($ord) $idx .= " " . $ord;
		$this->orders [] = $idx;
		return $this;
	}
	public function take($limit) {
		$this->limit = $limit;
		return $this;
	}
	public function offset($offset = 0) {
		$this->offset = $offset;
		return $this;
	}
	/**
	 * build join sql
	 *
	 * @return string
	 */
	protected function buildJoin() {
		if (! $this->joins) return "";
		$result = array ();
		foreach ( $this->joins as $item ) {
			$result [] = $item->buildQuery ();
			$criteria = $item->getCriteria ();
			if ($criteria->getHasParam ()) {
				$value = $criteria->getValue ();
				if (is_array ( $value )) {
					for($i = 0; $i < count ( $value ); $i ++) {
						$this->sql->addParam ( $value [$i] );
					}
				} else {
					$this->sql->addParam ( $value );
				}
			}
		}
		return implode ( " ", $result );
	}
	/**
	 * build where sql
	 *
	 * @return string
	 */
	protected function buildWhere() {
		if (! $this->wheres) return "";
		$result = array ();
		$result [] = "WHERE";
		foreach ( $this->wheres as $item ) {
			$result [] = $item->buildQuery ();
			if ($item->getHasParam ()) {
				$value = $item->getValue ();
				if (is_array ( $value )) {
					for($i = 0; $i < count ( $value ); $i ++) {
						$this->sql->addParam ( $value [$i] );
					}
				} else {
					$this->sql->addParam ( $value );
				}
			}
		}
		return implode ( " ", $result );
	}
	/**
	 * build group by sql
	 *
	 * @return string
	 */
	protected function buildGroup() {
		if (! $this->group) return "";
		return implode ( ", ", $this->group );
	}
	/**
	 * build having sql
	 *
	 * @return string
	 */
	protected function buildHaving() {
		if (! $this->havings) return "";
		$result = array ();
		$result [] = "HAVING";
		foreach ( $this->havings as $item ) {
			$result [] = $item->buildQuery ();
			if ($item->getHasParam ()) {
				$value = $item->getValue ();
				if (is_array ( $value )) {
					for($i = 0; $i < count ( $value ); $i ++) {
						$this->sql->addParam ( $value [$i] );
					}
				} else {
					$this->sql->addParam ( $value );
				}
			}
		}
		return implode ( " ", $result );
	}
	/**
	 * build order by sql
	 *
	 * @return string
	 */
	protected function buildOrder() {
		if (! $this->orders) return "";
		return "ORDER BY " . implode ( ", ", $this->orders );
	}
	/**
	 * build limit sql
	 *
	 * @return string
	 */
	protected function buildLimit() {
		if (is_null ( $this->limit )) return "";
		$result = "LIMIT ";
		if (! is_null ( $this->offset )) {
			$result .= $this->offset . ", ";
		}
		$result .= $this->limit;
		return $result;
	}
	/**
	 * build select sql
	 *
	 * @return string
	 */
	protected function buildSelect() {
		// always clear last sql buffer
		$this->sql->clear ();
		// always clear last result buffer
		$this->clear ();
		$result = array ();
		$result [] = "SELECT";
		$result [] = $this->fields ? implode ( ",", $this->fields ) : "*";
		$result [] = "FROM";
		$result [] = $this->table;
		if ($this->alias) $result [] = $this->alias;
		if ($this->joins) $result [] = $this->buildJoin ();
		if ($this->wheres) $result [] = $this->buildWhere ();
		if ($this->group) $result [] = $this->buildGroup ();
		if ($this->havings) $result [] = $this->buildHaving ();
		if ($this->orders) $result [] = $this->buildOrder ();
		if (! is_null ( $this->limit )) $result [] = $this->buildLimit ();
		return implode ( " ", $result );
	}
	/**
	 * build insert sql
	 *
	 * @return string
	 */
	protected function buildInsert() {
		// always clear last sql buffer
		$this->sql->clear ();
		// always clear last result buffer
		$this->clear ();
		$result = array ();
		$result [] = "INSERT INTO";
		$result [] = $this->table;
		$result [] = "(" . implode ( ", ", $this->fields ) . ")";
		$result [] = "VALUES(";
		$params = array ();
		for($i = 0; $i < count ( $this->fields ); $i ++) {
			$params [] = "?";
		}
		$this->sql->addParameter ( $this->values );
		$result [] = implode ( ",", $params );
		$result [] = ")";
		return implode ( " ", $result );
	}
	/**
	 * build update sql
	 *
	 * @return string
	 */
	protected function buildUpdate() {
		// always clear last sql buffer
		$this->sql->clear ();
		// always clear last result buffer
		$this->clear ();
		$result = array ();
		$result [] = "UPDATE";
		$result [] = $this->table;
		$result [] = "SET";
		$params = array ();
		for($i = 0; $i < count ( $this->fields ); $i ++) {
			$params [] = $this->fields [$i] . " = ?";
		}
		$result [] = implode ( ",", $params );
		$this->sql->addParameter ( $this->values );
		if ($this->wheres) $result [] = $this->buildWhere ();
		return implode ( " ", $result );
	}
	/**
	 * build delete sql
	 *
	 * @return string
	 */
	protected function buildDelete() {
		// always clear last sql buffer
		$this->sql->clear ();
		// always clear last result buffer
		$this->clear ();
		$result = array ();
		$result [] = "DELETE FROM";
		$result [] = $this->table;
		$result [] = $this->buildWhere ();
		return implode ( " ", $result );
	}
	/**
	 * fetch all data
	 *
	 * @return object|NULL|array
	 */
	public function get() {
		if (! $this->table) throw new \InvalidArgumentException ( "Table not set", 201 );
		$query = $this->buildSelect ();
		$this->sql->addText ( $query );
		$data = $this->db->prepare ( $this->sql )
			->fetchObjects ();
		if ($data) $this->replace ( $data );
		return $data;
	}
	/**
	 * fetch first data
	 *
	 * @throws \InvalidArgumentException
	 * @return object|NULL|mixed
	 */
	public function first() {
		if (! $this->table) throw new \InvalidArgumentException ( "Table not set", 201 );
		$query = $this->buildSelect ();
		$this->sql->addText ( $query );
		$data = $this->db->prepare ( $this->sql )
			->fetchObject ();
		if ($data) $this->replace ( $data );
		return $data;
	}
	/**
	 * find a data
	 *
	 * @param object|array|mixed $key
	 *        	accepted array, object or raw value, if raw value used, it will use primary key for where clause
	 * @throws \InvalidArgumentException
	 * @return object|NULL|mixed
	 */
	public function find($key = null) {
		if (! $this->table) throw new \InvalidArgumentException ( "Table not set", 201 );
		if (is_null ( $key )) {
			if (! $this->wheres) {
				if ($this->has ( $this->primaryKey )) $this->where ( $this->primaryKey, $this [$this->primaryKey] );
				else return null;
			}
		} else {
			if (is_array ( $key ) || is_object ( $key )) {
				foreach ( $key as $field => $value ) {
					$this->where ( $field, $value );
				}
			} else {
				$this->where ( $this->primaryKey, $key );
			}
		}
		return $this->select ()
			->first ();
	}
	/**
	 * execute insert statement
	 *
	 * @param object|array $data
	 * @throws \InvalidArgumentException
	 * @return NULL|\CSI\Data\DataAdapter
	 */
	public function insert($data) {
		if (! $this->table) throw new \InvalidArgumentException ( "Table not set", 201 );
		if (! is_array ( $data )) throw new \InvalidArgumentException ( "Parameter must be an array with format key=>value or an object", 205 );
		// reset field values
		$this->fields = array ();
		$this->values = array ();
		foreach ( $data as $key => $value ) {
			$this->fields [] = $key;
			$this->values [] = $value;
		}
		if ($this->timestamps) {
			$now = new \DateTime ();
			$this->fields [] = $this->createdTs;
			$this->values [] = $now->format ( "Y-m-d H:i:s" );
			$this->fields [] = $this->updatedTs;
			$this->values [] = $now->format ( "Y-m-d H:i:s" );
		}
		$query = $this->buildInsert ();
		$this->sql->addText ( $query );
		return $this->db->execute ( $this->sql );
	}
	/**
	 * execute update statement
	 *
	 * @param object|array $data
	 * @throws \InvalidArgumentException
	 * @return NULL|\CSI\Data\DataAdapter
	 */
	public function update($data) {
		if (! $this->table) throw new \InvalidArgumentException ( "Table not set", 201 );
		if (! is_array ( $data )) throw new \InvalidArgumentException ( "Parameter must be an array with format key=>value or an object", 205 );
		// reset field values
		$this->fields = array ();
		$this->values = array ();
		foreach ( $data as $key => $value ) {
			$this->fields [] = $key;
			$this->values [] = $value;
		}
		if ($this->timestamps) {
			$now = new \DateTime ();
			$this->fields [] = $this->updatedTs;
			$this->values [] = $now->format ( "Y-m-d H:i:s" );
		}
		if (! $this->wheres) {
			if ($this->has ( $this->primaryKey )) $this->where ( $this->primaryKey, $this [$this->primaryKey] );
		}
		$query = $this->buildUpdate ();
		$this->sql->addText ( $query );
		return $this->db->execute ( $this->sql );
	}
	/**
	 * execute update statement
	 *
	 * @param object|array|mixed $data
	 * @throws \InvalidArgumentException
	 * @return NULL|\CSI\Data\DataAdapter
	 */
	public function delete($key = null) {
		if (! $this->table) throw new \InvalidArgumentException ( "Table not set", 201 );
		if (is_null ( $key )) {
			if (! $this->wheres) {
				if ($this->has ( $this->primaryKey )) $this->where ( $this->primaryKey, $this [$this->primaryKey] );
			}
		} else {
			if (is_array ( $key ) || is_object ( $key )) {
				foreach ( $key as $field => $value ) {
					$this->where ( $field, $value );
				}
			} else {
				$this->where ( $this->primaryKey, $key );
			}
		}
		$query = $this->buildDelete ();
		$this->sql->addText ( $query );
		return $this->db->execute ( $this->sql );
	}
	/**
	 * execute count statement
	 *
	 * @param string|null $fieldName
	 * @throws \InvalidArgumentException
	 * @return number
	 */
	public function count($fieldName = null) {
		if (! $this->table) throw new \InvalidArgumentException ( "Table not set", 201 );
		$text = "count(*)";
		if (! is_null ( $fieldName )) {
			if (! is_string ( $fieldName )) throw new \InvalidArgumentException ( "field name must a string", 202 );
			$text = "count($fieldName)";
		}
		$this->select ( $text );
		$query = $this->buildSelect ();
		$this->sql->addText ( $query );
		$count = $this->db->prepare ( $this->sql )
			->fetchOne ();
		return intval ( $count );
	}
	/**
	 * Get all data (remove limit statement)
	 *
	 * @return object|NULL|array
	 */
	public function all() {
		// reset limit dan offset
		$this->limit = null;
		$this->offset = null;
		return $this->get ();
	}
	/**
	 * display executed sql command text
	 *
	 * @return string
	 */
	public function toQuery() {
		return $this->sql->text ();
	}
	/**
	 * reset all properties
	 *
	 * @return \CSI\Data\Model
	 */
	public function reset() {
		$this->fields = array ();
		$this->group = array ();
		$this->havings = array ();
		$this->joins = array ();
		$this->limit = null;
		$this->offset = null;
		$this->orders = array ();
		$this->sql->clear ();
		$this->values = array ();
		$this->wheres = array ();
		$this->alias = "";
		return $this;
	}
	/**
	 *
	 * @param string $alias
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
	}
}

