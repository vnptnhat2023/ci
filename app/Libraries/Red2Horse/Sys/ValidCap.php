<?php

namespace App\Libraries\Red2Horse\Sys;

class ValidCap
{

  public function ci_captcha ( string $str ) : bool
	{
		$ss = \Config\Services::session() ->getFlashdata( 'ci_captcha' );
		$ss[ 'word' ] ??= null;

		return $ss[ 'word' ] === $str;
	}

}