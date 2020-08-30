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


class AuthBuilderComponent
{
	private throttleModel $throttleModel;
	private userModel $userModel;
	private session $session;
	private cookie $cookie;
	private validation $validation;
	private cache $cache;
	private mail $mail;
	private request $request;
	private common $common;

	public static function createBuilder ()
	{
		return new AuthBuilder();
	}

	function __construct( AuthBuilder $builder )
	{
		$this->throttleModel = $builder->database_throttle();
		$this->userModel = $builder->database_user();
		$this->session = $builder->session();
		$this->cookie = $builder->cookie();
		$this->validation = $builder->validation();
		$this->cache = $builder->cache();
		$this->mail = $builder->mail();
		$this->request = $builder->request();
		$this->common = $builder->common();
	}
}