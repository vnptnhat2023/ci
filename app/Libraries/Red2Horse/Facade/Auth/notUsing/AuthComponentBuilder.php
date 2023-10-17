<?php

declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\Facade\{
	Event\EventFacadeInterface as event,
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

use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getInstance;

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
	public event $event;

	public function createBuilder ()
	{
		return getInstance( AuthBuilder::class );
	}

	public function build ( array $builder )
	{
		$config = getConfig();

		foreach ( $builder as $class => $component )
		{
			if ( empty( $this->$class ) )
			{
				$classAdapter = ucfirst( $class );

				if ( in_array( $class, [ 'user', 'throttle' ], true ) )
				{
					$facade = $config->facade( 'Database', $classAdapter );
				}
				else
				{
					$facade = $config->facade( $classAdapter );
				}

				$this->$class = $facade::getInstance( new $component );
			}
		}

		return $this;
	}
}