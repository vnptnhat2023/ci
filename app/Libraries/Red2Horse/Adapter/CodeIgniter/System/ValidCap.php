<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\CodeIgniter\System;

class ValidCap
{
	public function ci_captcha ( string $str ) : bool
	{
		$ss = \Config\Services::session() ->get( 'ci_captcha' );
		$ss[ 'word' ] ??= null;

		return strtoupper( $ss[ 'word' ] ) === $str;
	}

	// public function r2h_password_hash ( string $str ) : string
	// {
	// 	return getHashPass( $str );
	// }

	public function perms ( string $str ) : bool
	{
		return ( bool ) preg_match( '/[a-zA-Z0-9-_\s,]/', $str );
	}
	
}