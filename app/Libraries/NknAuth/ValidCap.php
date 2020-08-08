<?php

namespace App\Libraries\NknAuth;

class ValidCap
{
  public function ci_captcha($str)
	{
		$ss = \Config\Services::session()->getFlashdata( 'ci_captcha' );

		return $ss[ 'word' ] === $str;
	}
}