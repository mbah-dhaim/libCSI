<?php
namespace CSI\Helper;

use ArrayAccess;
use IteratorAggregate;

class Set implements ArrayAccess, IteratorAggregate {
	protected $data = array ();
	public function __construct() {
	}
	public function setData($key, $value) {
		$this->data [$key] = $value;
		return $this;
	}
	public function getData($key, $default = null) {
		if ($this->has ( $key )) return $this->data [$key];
		return $default;
	}
	public function has($key) {
		return array_key_exists ( $key, $this->data );
	}
	public function remove($key) {
		unset ( $this->data [$key] );
		return $this;
	}
	public function keys() {
		return array_keys ( $this->data );
	}
	public function clear() {
		$this->data = array ();
		return $this;
	}
	public function replace($items) {
		foreach ( $items as $key => $value ) {
			$this->setData ( $key, $value );
		}
		return $this;
	}
	public function offsetGet($offset) {
		return $this->getData ( $offset );
	}
	public function offsetExists($offset) {
		return $this->has ( $offset );
	}
	public function offsetSet($offset, $value) {
		return $this->setData ( $offset, $value );
	}
	public function offsetUnset($offset) {
		return $this->remove ( $offset );
	}
	public function getIterator() {
		return new \ArrayIterator ( $this->data );
	}
	public function __get($key) {
		return $this->getData ( $key );
	}
	public function __set($key, $value) {
		return $this->setData ( $key, $value );
	}
	public function __isset($key) {
		return $this->has ( $key );
	}
	public function __unset($key) {
		return $this->remove ( $key );
	}
}

