<?php

namespace App\Controllers;

use Config\Services;

class Login extends BaseController
{

	public function __construct()
	{
		helper( 'form' );
		// dd( Services::NknAuth()->getConfig() );
	}

	public function index()
	{
		$message = Services::NknAuth()->login()->getMessage();
		d($message);

		return view( 'login/login', (array) $message );
	}

	public function forgot ()
	{
		$auth = Services::NknAuth()->login();

		return view( 'login/forgot', (array) $auth->getMessage() );
	}

	public function logout ()
	{
		$auth = Services::NknAuth()->logout();
		return view( 'login/login', (array) $auth->getMessage() );
	}
}