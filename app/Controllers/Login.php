<?php

# --- Todo: Move current file to [Auth-folder]
namespace App\Controllers;

use \App\Libraries\Red2Horse\Facade\Auth\AuthFacade as r2hAuth;

class Login extends BaseController
{
	private r2hAuth $auth;

	public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
		helper( [ 'form', 'form_recaptcha' ] );
	}

	public function index()
	{
		$username = $this->request->getPostGet( 'username' );
		$password = $this->request->getPostGet( 'password' );
		$rememberMe = null !== $this->request->getPostGet( 'remember_me' );
		$captcha = $this->request->getPostGet( 'captcha' );

		$this->auth->login( $username, $password, $rememberMe, $captcha );

		$form = [
			'form' => [
				'username' => $username,
				'password' => $password,
				'captcha' => $captcha,
				'remember_me' => $rememberMe
			]
		];

		d( $this->auth->getMessage( $form ) );

		return view( 'login/login', (array) $this->auth->getMessage() );
	}

	public function forgot ()
	{
		$username = $this->request->getPostGet( 'username' );
		$email = $this->request->getPostGet( 'email' );
		$captcha = $this->request->getPostGet( 'captcha' );

		$this->auth->requestPassword( $username, $email, $captcha );
		return view( 'login/forgot', (array) $this->auth->getMessage() );
	}

	public function logout ()
	{
		$this->auth->logout();
		return view( 'login/login', (array) $this->auth->getMessage() );
	}
}