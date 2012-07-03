<?php

include "murmur3.php";

class LaBloom
{
	/**
	 * array of bloom filters
	 */
	public static $filters = array();

	/**
	 * Bloom filter value
	 * @var bitfield
	 */
	public $filter = array();

	/**
	 * Filter lengtht
	 * @var integer
	 */
	public $len;

	/**
	 * Number of items in filter
	 * @var integer
	 */
	public $cnt = 0;

	/**
	 * Bloom filter name
	 * @var string
	 */
	public $name;

	/**
	 * Hash functions to use in filter
	 * @var array
	 */
	public $hash_f = array("sha1", "adler32", "crc32");

	/**
	 * Array of custom functions to use then calculating hashes, array values should be callable
	 * @var array
	 */
	public $hash_c = array();

	/**
	 * Create new filter and put it to container
	 * @param  string $name name of filter
	 * @return object       Bloom filter
	 */
	public static function make($name="default", $len = 256)
	{
		if( array_key_exists($name, static::$filters) )
		{
			return static::$filters[$name];
		}
		else
		{
			$fl = new static($name, $len);
			static::$filters[$name] = $fl;
			return $fl;
		}
	}

	public static function get($name="default")
	{
		return static::make($name);
	}

	/**
	 * Construct new bloom filter instance
	 * @param integer $length=256 filter length
	 */
	public function __construct($name, $length=256 )
	{
		$this->len = $length;
		$this->filter = array_fill(0, $length, 0);
		$this->name = $name;
	}

	/**
	 * Load bloom filter
	 * @param  array $filter array of filter values
	 * @return filter         object
	 */
	public function load($filter)
	{
		if( is_array($filter) )
			$this->filter = $filter;
		elseif ( is_string($filter) )
		{
			$this->filter = explode(',', $filter);
			$this->len = count($this->filter);
		}
		else
		{
			throw new \Exception("Invalid Bloom filter value to load");
		}
		return $this;
	}

	public function save()
	{
		return implode(",", $this->filter);
	}

	/**
	 * Calculate value hashes
	 * @param  string $str value
	 * @return array      array of seted bytes
	 */
	public function hash($str)
	{
		$ret = array();
		foreach ($this->hash_f as $key => $value) {
			$ret[] = abs(hexdec( substr(hash($value, $str), 0, 15 ) )% $this->len );
		}
		foreach ($this->hash_c as $key => $value) {
			if( is_callable( $value ) )
				$ret[] = call_user_func($value, $str);
		}
		return $ret;
	}

	/**
	 * Adds new value to filter
	 * @param string $str a non-object or array value to add to filter
	 */
	public function add($str)
	{
		foreach ((array)$str as $key => $value) 
		{
			foreach( $this->hash($value) AS $key => $value)
			{
				$this->filter[$value] = 1;
			}
			$this->cnt++;
		}
		return $this;
	}

	/**
	 * Check if string might be in SET, this can be false positive, BUT NEVER false negative.
	 * @param  string  $str string to check
	 * @return boolean      is or is not in set
	 */
	public function has($str)
	{
		// Check only one value
		if( is_string( $str ) )
		{
			return $this->check($str);
		}
		elseif( is_array( $str ) ) // check for more then one value, array values will become array keys
		{
			// $str = array_flip($str);
			foreach ($str as $key => &$value) {
				$value = $this->check($value);
			}
			return $str;
		}
		return false;
	}

	/**
	 * Check one value for existence
	 * @param  string $str value to check
	 * @return boolean      true/false
	 */
	private function check($str)
	{
		$tmp = array_intersect_key($this->filter, array_flip( $this->hash($str) ) );
		if ( array_sum($tmp) == count($tmp) )
			return true;
		return false;
	}

	public function fail_rate()
	{
		$h = count($this->hash_f);
		return pow((1-pow((1-1/$this->len),$h*$this->cnt)),$h);
	}

	public function __toString()
	{
		$str = "";
		$br = 0;
		foreach ($this->filter as $key => $value) {
			$str.=($value === 1 ? 'o' : '_');
			if( ++$br > 127 )
			{
				$str.="<br>";
				$br = 0;
			}
		}
		return $str;
	}


	public function murmur3($str, $len, $seed)
	{
		MurMur3::mur3_32($str, $len, $seed);
	}

}