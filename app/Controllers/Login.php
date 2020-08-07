<?php

namespace App\Controllers;

use Config\Services;

class Login extends BaseController
{
	public function __construct ()
	{
		helper( [ 'form', 'array' ] );
	}

	public function index()
	{
		$message = Services::NknAuth()->login()->getMessage();

		// if ( false === $message->result->success )
		return view( 'login/login', (array) $message );

		// return anchor( base_url(), implode( ', ', $message->success ) );
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