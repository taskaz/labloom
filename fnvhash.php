<?php

class FnvHash
{

	private static $FNV_prime_32 = 16777619;
	private static $FNV_prime_64 = 1099511628211;
	private static $FNV_prime_128 = 309485009821345068724781371;

	private static $FNV_offset_basis_32 = 2166136261;
	private static $FNV_offset_basis_64 = 14695981039346656037;
	private static $FNV_offset_basis_128 = 144066263297769815596495629667062367629;


	public static function fnv1($str)
	{
		$h = static::$FNV_offset_basis_32;
		foreach (str_split($str) as $key => $value) {
			$h += ($h << 1) + ($h << 4) + ($h << 7) + ($h << 8) + ($h << 24);
			$h ^= ord($value);
		}
		$h &= 0x7fffffff;
		return $h;
	}
}