<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Facade\{
	Session\SessionFacadeInterface as session,
	Validation\ValidationFacadeInterface as validation,
	Cookie\CookieFacadeInterface as cookie,
	Cache\CacheFacadeInterface as cache,
	Mail\MailFacadeInterface as mail,
	Request\RequestFacadeInterface as request,
	Database\ThrottleFacadeInterface as throttleModel,
	Database\UserFacadeInterface as userModel,
	Common\CommonFacadeInterface as common,
};

use App\Libraries\Red2Horse\Facade\Auth\Config;
use App\Libraries\Red2Horse\Mixins\TraitSingleton;

class AuthComponentBuilder
{
	use TraitSingleton;

	public throttleModel $throttle;
	public userModel $user;
	public session $session;
	public cookie $cookie;
	public validation $validation;
	public cache $cache;
	public mail $mail;
	public request $request;
	public common $common;

	public static function createBuilder ( Config $config )
	{
		return AuthBuilder::getInstance( $config );
	}

	public function build ( array $builder )
	{
		foreach ( $builder as $class => $component )
		{
			if ( empty( $this->$class ) ) {
				$this->$class = new $component;
			}
		}

		return $this;
	}
}