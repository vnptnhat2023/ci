<?php

declare(strict_types=1);
namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\Traits\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Password
{
	use TraitSingleton;

	private const PW_HASH_TYPE = 'sha384';

	private function __construct () {}

	private function typeOfHash ( string $string, $raw_output = true ) : string
	{
		return base64_encode( hash( SELF::PW_HASH_TYPE, $string, $raw_output ) );
	}

	public function getHashPass ( string $password, bool $binary = true ) : string
	{
		return password_hash( $this->typeOfHash( $password, $binary ), PASSWORD_DEFAULT );
	}

	public function getVerifyPass ( string $password, string $hashed, bool $binary = true ) : bool
	{
		return password_verify( $this->typeOfHash( $password, $binary ), $hashed );
	}
}
