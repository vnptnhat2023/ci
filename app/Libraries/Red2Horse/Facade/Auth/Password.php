<?php

declare(strict_types=1);

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;

class Password
{
	use TraitSingleton;

	private const PW_HASH_TYPE = 'sha384';

	private function typeOfHash ( string $string, $raw_output = true ) : string
	{
		return base64_encode( hash( SELF::PW_HASH_TYPE, $string, $raw_output ) );
	}

	public function getHashPass ( string $password ) : string
	{
		return password_hash( $this->typeOfHash( $password ), PASSWORD_DEFAULT );
	}

	public function getVerifyPass ( string $password, string $hashed ) : bool
	{
		return password_verify( $this->typeOfHash( $password ), $hashed );
	}
}
