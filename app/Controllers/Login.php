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
		$auth = $this->NknAuth->login( false );

		return view( 'login/login', $auth->getMessage() );
	}

	public function forgot ()
	{
		$auth = $this->NknAuth->login( false );

		return view( 'login/forgot', $auth->getMessage() );
	}

	public function logout ()
	{
		$auth = $this->NknAuth->logout( false );

		return view( 'login/login', $auth->getMessage() );
	}

	private function index_old ()
	{
		$auth = $this->NknAuth->login();

		if ( true === $auth->view ) {
			if ( true === $auth->login_incorrect ) {
				$data[ 'error' ][] = lang( 'NknAuth.errorIncorrectInformation' );
			}

			return view( 'login/login', $data );
		}

		if ( true === $auth->banned || true === $auth->inactive )
		{
			$status = $auth->banned ? lang( 'NknAuth.banned' ) : lang( 'NknAuth.inActive' );

			echo lang( 'NknAuth.errorNotReadyYet', [ $status ] );
		}
		else if ( true === $auth->success )
		{
			echo anchor( base_url(), lang( 'NknAuth.successLogged' ) );
		}
		else if ( true === $auth->limit_max )
		{
			$errArgs = [ $this->NknAuth->throttle_config[ 'timeout' ] ];

			echo lang( 'NknAuth.errorThrottleLimitedTime', $errArgs );
		}
	}
}