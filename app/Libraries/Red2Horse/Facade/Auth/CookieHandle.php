<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Auth;

use App\Libraries\Red2Horse\Mixins\TraitSingleton;

use App\Libraries\Red2Horse\Facade\{
	Common\CommonFacade as common,
	Cookie\CookieFacade as cookie,
	Database\UserFacade as userModel,
	Session\SessionFacade as session,
	Request\RequestFacade as request
};

class CookieHandle
{
	use TraitSingleton;

	protected Config $config;

	protected common $common;
	protected cookie $cookie;
	protected userModel $userModel;
	protected session $session;
	protected request $request;

	public function __construct( Config $config )
	{
		$this->config = $config;

		$builder = AuthComponentBuilder::createBuilder( $config )
		->common()
		->cookie()
		->database_user()
		->session()
		->request()
		->build();

		$this->common = $builder->common;
		$this->cookie = $builder->cookie;
		$this->userModel = $builder->user;
		$this->session = $builder->session;
		$this->request = $builder->request;
	}

	public function regenerateCookie () : void
	{
		$cookieValue = password_hash( session_id(), PASSWORD_DEFAULT );
		$ttl = (string) $this->config->sessionTimeToUpdate;
		$cookieName = $this->config->cookie . '_test';

		$this->cookie->set_cookie( $cookieName , $cookieValue, $ttl );
	}

	public function cookieHandler () : bool
	{
		/**
		 * Cookie-Todo
		 * Components: [ config, cookie, message, session, userModel, request ]
		 * Methods: [
		 * Authentication->isMultiLogin( $user[ 'session_id' ] )
		 * Cookie->setCookie( $user[ 'id' ], [], $logErr )
		 * Cookie->regenerateCookie()
		 * ]
		 */
		$userCookie = $this->cookie->get_cookie( $this->config->cookie );

		if ( empty( $userCookie ) || ! is_string( $userCookie ) ) {
			return false;
		}

		$separate = explode( ':', $userCookie, 2 );
		$incorrectCookie = function  () : bool {
			$this->cookie->delete_cookie( $this->config->cookie );
			return false;
		};

		if ( empty( $separate[ 0 ] ) || empty( $separate[ 1 ] ) ) {
			return $incorrectCookie();
		}

		$selector = $separate[ 0 ];
		$token = $separate[ 1 ];

		$user = $this->userModel->getUserWithGroup(
			$this->config->getColumString(),
			[ 'selector' => $selector ]
		);

		if ( empty( $user ) ) {
			return $incorrectCookie();
		}

		$isValid = hash_equals( $user[ 'token' ], hash( 'sha256', $token ) );
		$isUserIp = $user[ 'last_login' ] == $this->request->getIPAddress();

		if ( false === $isValid || false === $isUserIp ) {
			return $incorrectCookie();
		}

		# --- Check status
		if ( in_array( $user[ 'status' ] , [ 'inactive', 'banned' ] ) ) {
			Message::getInstance( $this->config )->denyStatus( $user[ 'status' ], false, false );

			return $incorrectCookie();
		}

		# --- Todo: declare inside the config file: is using this feature
		if ( false === Authentication::getInstance( $this->config )->isMultiLogin( $user[ 'session_id' ] ) ) {
			Message::getInstance( $this->config )->denyMultiLogin( true, [], false );

			return false;
		}

		# --- refresh new cookie
		$logErr = "Validated cookie, but error when update userId: {$user[ 'id' ]}";
		$this->setCookie( $user[ 'id' ], [], $logErr );

		$user[ 'permission' ] = json_decode( $user[ 'permission' ] );
		$this->session->set( $this->config->session, $user );

		$this->regenerateCookie();

		return true;
	}

	public function setCookie ( int $userId, array $updateData = [], string $logError = null ) : void
	{
		if ( $userId <= 0 ) {
			$errArg = [ 'field' => 'user_id', 'param' => $userId ];
			throw new \Exception( $this->common->lang( 'Validation.greater_than', $errArg ), 1 );
		}

		if ( ! empty( $updateData ) && false === $this->common->isAssocArray( $updateData ) ) {
			throw new \Exception( $this->common->lang( 'Red2Horse.isAssoc' ), 1 );
		}

		$selector = bin2hex( random_bytes( 8 ) );
		$token = bin2hex( random_bytes( 20 ) );

		$cookieValue = "{$selector}:{$token}";
		$data = [
			'selector' => $selector,
			'token' => hash( 'sha256', $token )
		];
		$data = array_merge( $data, $updateData );

 		if ( true === Authentication::getInstance( $this->config )->loggedInUpdateData( $userId, $data ) )
		{
			$ttl = time() + $this->config->ttl;
			setcookie( $this->config->cookie, $cookieValue, $ttl, '/' );
		}
		else
		{
			$logErr = $logError ?: "{$userId} LoggedIn with remember-me, but update failed";
			$this->common->log_message( 'error', $logErr );
		}
	}
}