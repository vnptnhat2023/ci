<?php

namespace App\Libraries\Red2Horse\Sys;

class ValidCap
{

  public function ci_captcha ( $str )
	{
		$ss = \Config\Services::session() ->getFlashdata( 'ci_captcha' );

		return $ss[ 'word' ] === $str;
	}

}