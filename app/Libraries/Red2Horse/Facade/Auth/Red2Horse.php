<?php
declare( strict_types = 1 );
namespace Red2Horse\Facade\Auth;

use Red2Horse\{
	Facade\Database\ThrottleFacade as throttleModel,
	Facade\Event\EventFacadeInterface,
	Mixins\TraitSingleton
};

class Red2Horse
{
	use TraitSingleton;

	public Config $config;
	public EventFacadeInterface $event;
	protected throttleModel $throttleModel;
	protected Authentication $authentication;
	protected Message $message;
	// protected ReflectClass___ $reflect;

	public function __construct ( ?Config $config = null )
	{
		$this->config = $config ?? Config::getInstance();

		$builder = AuthComponentBuilder::createBuilder( $this->config )
		->common()
		->database_throttle()
		->event()
		->build();

		$this->event = $builder->event;
		$throttle = $this->config->throttle;
		$this->throttleModel = $builder->throttle;

		$this->throttleModel->config(
			$throttle->type,
			$throttle->captchaAttempts,
			$throttle->maxAttempts,
			$throttle->timeoutAttempts
		);

		$this->authentication = Authentication::getInstance( $this->config );
		$this->message = Message::getInstance( $this->config );
	}

	public function login ( string $u = null, string $p = null, bool $r = false, string $c = null ) : bool
	{
		return $this->authentication->login( $u, $p, $r, $c );
	}

	public function logout () : bool
	{
		return $this->authentication->logout();
	}

	public function requestPassword ( string $u = null, string $e = null, string $c = null ) : bool
	{
		return ResetPassword::getInstance( $this->config )->requestPassword( $u, $e, $c );
	}

	/**
	 * @param string|null $key
	 * @return mixed
	 */
	public function getUserdata ( string $key = null )
	{
		return $this->authentication->getUserdata( $key );
	}

	public function getHashPass ( string $password ) : string
	{
		return Password::getInstance()->getHashPass( $password );
	}

	public function getVerifyPass ( string $p, string $hashed ) : bool
	{
		return Password::getInstance()->getVerifyPass( $p, $hashed );
	}

	/** @return object|array */
	public function getResult ()
	{
		return $this->message->getResult();
	}

	public function getMessage ( array $add = [], bool $asObject = true )
	{
		return $this->message->getMessage( $add, $asObject );
	}

	public function withPermission ( array $data, bool $or = true ) : bool
	{
		return Authorization::getInstance( $this->config )->run( $data );
	}

	# @Todo: not using: multiple filters on single route
	public function withGroup( array $data ) : bool
	{
		return Authorization::getInstance( $this->config )->run( $data );
	}

	public function withRole ( array $role, bool $or = true ) : bool
	{
		return Authorization::getInstance( $this->config )->run( $role );
	}

	public function isLogged ( bool $withCookie = false ) : bool
	{
		return $this->authentication->isLogged( $withCookie );
	}

	public function regenerateSession ( array $userData ) : bool
	{
		return SessionHandle::getInstance( $this->config )->regenerateSession( $userData );
	}

	public function regenerateCookie () : void
	{
		CookieHandle::getInstance( $this->config )->regenerateCookie();
	}
}