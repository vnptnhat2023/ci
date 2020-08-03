<?php

namespace App\Controllers;

class Login extends BaseController
{
	private $NknAuth;

	public function __construct ()
	{
		helper( [ 'form', 'array' ] );

		$this->NknAuth = new \App\Libraries\NknAuth();
	}

	public function index ()
	{
		$data = $this->NknAuth ->login() ->asArray();

		if ( true === $data[ 'load_view' ] )
		{
			if ( true === $data[ 'wrong' ] ) {
				$data[ 'error' ][ ] = lang( 'NknAuth.errorIncorrectInformation' );
			}

			return view( 'login/login', $data );
		}

		if ( true === $data[ 'banned' ] || true === $data[ 'inactive' ] )
		{
			$status = $data[ 'banned' ] ? lang( 'NknAuth.banned' ) : lang( 'NknAuth.inActive' );

			echo lang( 'NknAuth.errorNotReadyYet', [ $status ] );
		}
		else if ( true === $data[ 'success' ] )
		{
			echo anchor( base_url(), lang( 'NknAuth.successLogged' ) );
		}
		else if ( true === $data[ 'was_limited' ] )
		{
			$errArgs = [ $this->NknAuth->throttle_config[ 'timeout' ] ];

			echo lang( 'NknAuth.errorThrottleLimitedTime', $errArgs );
		}
	}

	public function forgot ()
	{
		$data = $this->NknAuth ->forgetPass() ->asArray();

		if ( true === $data[ 'load_view' ] )
		{
			if ( true === $data[ 'wrong' ] ) {
				$data[ 'error' ][ ] = lang( 'NknAuth.errorIncorrectInformation' );
			}

			return view( 'login/forgot', $data );
		}

		if ( true === $data[ 'success' ] )
		{
			echo lang( 'NknAuth.successResetPassword' );
		}
		else if ( true === $data[ 'forgot_password_denny' ] )
		{
			echo lang( 'NknAuth.noteDenyRequestPassword' );
		}
		else if ( true === $data[ 'was_limited' ] )
		{
			$errArgs = [ $this->NknAuth->throttle_config[ 'timeout' ] ];

			echo lang( 'NknAuth.errorThrottleLimitedTime', $errArgs );
		}
	}

	public function logout ()
	{
		$this->NknAuth->logout();

		echo anchor( base_url(), lang( 'NknAuth.successLogout' ) );
	}

}