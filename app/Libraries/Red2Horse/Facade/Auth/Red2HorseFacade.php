<?php

namespace App\Libraries\Red2Horse\Facade\Auth;

class Red2HorseFacade
{
	public Config $config;

	protected bool $incorrectResetPassword = false;
	protected bool $incorrectLoggedIn = false;
	protected bool $successfully = false;
	protected bool $showCaptcha = false;
	protected bool $limited = false;
	protected int $attempts = false;
	protected bool $hasBanned = false;
	protected bool $accountInactive = false;

	protected array $errors = [];
	protected array $success = [];

	public function __construct ( Config $config = null )
	{
		$this->model = model( '\App\Models\Login' );
		$this->user = db_connect() ->table( 'user' );

		$this->config = $config ?: new Config;

		helper( 'cookie' );
	}

}