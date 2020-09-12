<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class Password
{
	use TraitSingleton;

	public function getHashPass ( string $password ) : string
  {
		$hash = password_hash(
			base64_encode( hash( 'sha384', $password, true ) ),
			PASSWORD_DEFAULT
		);

		return $hash;
  }

  public function getVerifyPass ( string $password, string $hashed ) : bool
  {
		$result = password_verify(
			base64_encode( hash( 'sha384', $password, true ) ),
			$hashed
		);

		return $result;
	}
}