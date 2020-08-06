<?php

namespace App\Controllers;

use App\Libraries\NknAuth;

class Login extends BaseController
{
	private \App\Libraries\NknAuth $NknAuth;

	public function __construct ()
	{
		helper( [ 'form', 'array' ] );

		$this->NknAuth = new NknAuth();
	}

	public function index()
	{
		$message = $this->NknAuth->login()->getMessage();

		if ( false === $message->result->success )
		return view( 'login/login', (array) $message );

		return anchor( base_url(), implode( ', ', $message->success ) );
	}

	public function forgot ()
	{
		$auth = $this->NknAuth->login();

		return view( 'login/forgot', (array) $auth->getMessage() );
	}

	public function logout ()
	{
		$auth = $this->NknAuth->logout();

		return view( 'login/login', (array) $auth->getMessage() );
	}
}