<?php

# --- Todo: Move current file to [Auth-folder]
namespace App\Controllers;

class Login extends BaseController
{
	private \App\Libraries\NknAuth $auth;

	public function __construct()
	{
		$this->auth = \Config\Services::NknAuth();
		helper( [ 'form', 'form_recaptcha' ] );
	}

	public function index()
	{
		d( $this->auth->config );
		return view( 'login/login', $this->auth->login() );
	}

	public function forgot ()
	{
		return view( 'login/forgot', $this->auth->requestPassword() );
	}

	public function logout ()
	{
		return view( 'login/login', $this->auth->logout() );
	}
}