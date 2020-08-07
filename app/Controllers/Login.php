<?php

namespace App\Controllers;

use Config\Services;

class Login extends BaseController
{

	public function __construct()
	{
		// dd( Services::NknAuth()->getConfig() );
	}

	public function index()
	{
		helper( 'form' );

		$message = Services::NknAuth()->login()->getMessage();

		return view( 'login/login', (array) $message );
	}

	public function forgot ()
	{
		helper( 'form' );

		$auth = Services::NknAuth()->login();

		return view( 'login/forgot', (array) $auth->getMessage() );
	}

	public function logout ()
	{
		$auth = Services::NknAuth()->logout();
		return view( 'login/login', (array) $auth->getMessage() );
	}
}