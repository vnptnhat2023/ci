<?php

declare( strict_types = 1 );
namespace Red2Horse\Config;

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class Session
{
	use TraitSingleton;

    private 	const 		SESSION_NAME 			= 'r2h';
	public 		string 		$session 				= self::SESSION_NAME;
	public 		string 		$sessionSavePath 		= '';
	public 		string 		$sessionCookieName 		= 'r2h';
	public 		int 		$sessionExpiration 		= 3600;
	public 		int 		$sessionTimeToUpdate 	= 300;

	private function __construct ()
	{

	}
}