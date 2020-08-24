<?php

# --- Todo: Move current file to [Auth-folder]
namespace App\Controllers;

use \App\Libraries\Red2Horse\AuthInterface;

class Login extends BaseController
{
	// private \App\Libraries\NknAuth $auth;
	private AuthInterface $auth;

	public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
		// dd( $this->auth );
		helper( [ 'form', 'form_recaptcha' ] );
	}

	public function index()
	{
		// d( $this->auth->config );
		// return view( 'login/login', $this->auth->login() );

		$username = $this->request->getPostGet( 'username' );
		$password = $this->request->getPostGet( 'password' );
		$rememberMe = null !== $this->request->getPostGet( 'remember_me' ) ? true : false;
		$captcha = $this->request->getPostGet( 'ci_captcha' );

		d( $this->auth->getMessage() );
		$this->auth->login( $username, $password, $rememberMe, $captcha );

		return view( 'login/login', $this->auth->getMessage() );
	}

	public function forgot ()
	{
		// return view( 'login/forgot', $this->auth->requestPassword() );
	}

	public function logout ()
	{
		// return view( 'login/login', $this->auth->logout() );
	}
}