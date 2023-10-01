<?php

namespace Red2Horse\Adapter\CodeIgniter\System;

use function Red2Horse\Mixins\Functions\getHashPass;

class ValidCap
{
	public function ci_captcha ( string $str ) : bool
	{
		$ss = \Config\Services::session() ->get( 'ci_captcha' );
		$ss[ 'word' ] ??= null;

		return strtoupper( $ss[ 'word' ] ) === $str;
	}

	public function r2h_password_hash ( string $str ) : string
	{
		return getHashPass( $str );
	}
}