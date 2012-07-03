<?php
//-----------------------------------------------------------------------------
// MurmurHash3 was written by Austin Appleby, and is placed in the public
// domain. The author hereby disclaims copyright to this source code.

// Note - The x86 and x64 versions do _not_ produce the same results, as the
// algorithms are optimized for their respective platforms. You can still
// compile and run any of them on any platform, but your performance with the
// non-native version will be less than optimal.

//-----------------------------------------------------------------------------
// THIS IS STILL EXPEREMENTAL, AND THIS SHOULD BE DEVELOPED NOT WITH PHP BUT RATHER IN PHP EXTENSION
//
class MurMur3
{
	
	private static function multiply($x, $y, $p)
	{
		$rez = 1;
		if( $x === 0 || $y === 0)
			return 1;
		else
		{
			do
			{
				if( ($y & 1) == 1)
					$rez = ($rez * $x) % $p;
				$y = ($y >> 1) & 0x7FFFFFFF;
				$x = ( $x * $x ) % 0xFFFFFFF;
			} while( $y != 0);
		}
		return $rez;
	}

	private static function rotl32( $x, $r )
	{
		return ($x << $r) | ($x >> ( 32 - $r ) );
	}

	private static function rotl64( $x, $r )
	{
		return ($x << $r) | ($x >> ( 64 - $r ) );
	}

	private static function fmix32( $h )
	{
		$h ^= $h >> 16;
		$h *= 0x85ebca6b;
	  	$h ^= $h >> 13;
	  	$h *= 0xc2b2ae35;
	  	$h ^= $h >> 16;
	  	return $h;
	}

	private static function fmix64( $h )
	{
		$h ^= $h >> 33;
		$h *= 0xff51afd7ed558ccd;
	  	$h ^= $h >> 33;
	  	$h *= 0xc4ceb9fe1a85ec53;
	  	$h ^= $h >> 33;
	  	return $h;
	}

	public static function mur3_32($str, $seed)
	{
		$data = array_values(unpack("C*", $str));
		$len = count($data);
		$nblocks = ($len & 0xfffffffc)/4;
		$h1 = $seed;
		$k1 = 0;
		$c1 = 0xcc9e2d51;
		$c2 = 0x1b873593;
		for( $i=0; $i < $nblocks*4; $i+=4 )
		{
			$k1 = ($data[$i] & 0xff) ;
			$k1 |= (($data[$i+1] & 0xff) << 8);
			$k1 |= (($data[$i+2] & 0xff) << 16);
			$k1 |= ($data[$i+3] << 24);
			$k1 = ($k1 * $c1) & 0xFFFFFFFF;
			$k1 = static::rotl32($k1, 15);
			$k1 *= $c2;
			// var_dump(dechex($k1));

			$h1 ^= $k1;
			$h1 = static::rotl32($h1, 13);
			$h1 = $h1*5+0xe6546b64;
		}


		$tail = $nblocks*4;
		$k1 = 0;

		switch($len & 0x03)
		{
			case 3 : $k1 = ($data[$tail+2] & 0xFF) << 16;
			case 2 : $k1 |= ($data[$tail+1] & 0xFF) << 8;
			case 1 : 
				$k1 |= ($data[$tail] & 0xFF);
				$k1 *= $c1;
				$k1 = static::rotl32($k1, 15);
				$k1 *= $c2;
				$h1 ^= $k1;
		}

		$h1 ^= $len;

		$h1 = static::fmix32($h1);

		return $h1;

	}

}