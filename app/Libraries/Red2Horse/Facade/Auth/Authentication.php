<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class Authentication
{
	use TraitSingleton;

	public function __construct()
	{
		$Props = [
			'username',
			'email',
			'password',
			'rememberMe',
			'captcha'
		];

		$Method = [
			'typeChecker',
			'isLogged',
			'cookieHandler',
			'getVerifyPass',
			'isMultiLogin',
			'loginInvalid',
			'loginAfterValidation',
			'loggedInUpdateData',
			'setLoggedInSuccess',
			'setCookie',
			'regenerateCookie'
		];

		$Components = [
			'config',
			'common',
			'cookie',
			'message',
			'throttleModel',
			'userModel',
			'session',
			'validation',
			'request'
		];
	}
}
