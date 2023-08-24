<?php

declare( strict_types = 1 );

namespace Red2Horse\Facade\Auth;

use Red2Horse\Mixins\TraitSingleton;

use Red2Horse\Facade\{
	Common\CommonFacade as common,
	Database\UserFacade as userModel
};

class SessionHandle
{
	use TraitSingleton;

	protected Config $config;
	protected CookieHandle $cookieHandle;

	protected common $common;
	protected userModel $userModel;

	protected Authentication $authentication;

	public function __construct( Config $config )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->common()
		->database_user()
		->build();

		$this->common = $builder->common;
		$this->userModel = $builder->user;

		$this->authentication = Authentication::getInstance( $config );
		$this->cookieHandle = CookieHandle::getInstance( $config );
	}

	public function regenerateSession ( array $userData ) : bool
	{
		if ( false === $this->authentication->isLogged() ) {
			return false;
		}

		$isUpdated = $this->userModel->updateUser(
			$userData[ 'id' ],
			[ 'session_id' => session_id() ]
		);

		if ( false === $isUpdated ) {
			$errStr = "The session_id of {$userData[ 'id' ]} update failed";
			$this->common->log_message( 'error', $errStr );

			return false;
		}

		$this->cookieHandle->regenerateCookie();

		return true;
	}
}