<?php

# --- Todo: Move current file to [Auth-folder]
namespace App\Controllers;

use Config\Services;

class Login extends BaseController
{

	public function __construct()
	{
		helper( [ 'form', 'form_recaptcha' ] );
		// dd( Services::NknAuth()->getConfig() );
	}

	public function index()
	{
		$message = Services::NknAuth() ->login() ->getMessage();
		// d( Services::NknAuth()->getConfig() );
		d( $message );

		return view( 'login/login', (array) $message );
	}

	public function forgot ()
	{
		$message = Services::NknAuth() ->forgetPass() ->getMessage();
		d( $message );

		return view( 'login/forgot', (array) $message );
	}

	public function logout ()
	{
		$auth = Services::NknAuth() ->logout();
		d($auth->getMessage());

		return view( 'login/login', (array) $auth->getMessage() );
	}
}