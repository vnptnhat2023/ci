<?php

namespace Red2Horse\Adapter\CodeIgniter\System;

class ValidCap
{
# --- Using in CI\App\Config\Validation
	public function ci_captcha ( string $str ) : bool
	{
		$ss = \Config\Services::session() ->get( 'ci_captcha' );
		$ss[ 'word' ] ??= null;

		return $ss[ 'word' ] === $str;
	}

}