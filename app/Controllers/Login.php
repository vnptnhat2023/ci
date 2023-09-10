<?php
declare(strict_types = 1);
# Event.
namespace App\Controllers;

use Red2Horse\R2h;

class Login extends BaseController
{
	private R2h $auth;
	private ?string $u;
	private ?string $p;
	private bool $r;
	private ?string $c;
	private ?string $e;

	private bool $dump = true;

	public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
		$request = \Config\Services::request();

		$this->u = $request->getPostGet('username');
		$this->p = $request->getPostGet('password');
		$this->r = null !== $request->getPostGet('remember_me');
		$this->c = $request->getPostGet('captcha');
		$this->e = $request->getPostGet('email');

		helper( [ 'form', 'form_recaptcha' ] );
	}

	public function index()
	{
		$u = $this->u;
		$p = $this->p;
		$r = null !== $this->r;
		$c = $this->c;

		$this->auth->login( $u, $p, $r, $c );

		$this->_dumpIt( [ 'username' => $u, 'password' => $p, 'captcha' => $c, 'remember_me' => $r ] );

		return view( 'login/login', (array) $this->auth->getMessage() );
	}

	public function forgot ()
	{
		$u = $this->u;
		$e = $this->e;
		$c = $this->c;

		$this->_dumpIt( [ 'username' => $u, 'email' => $e, 'captcha' => $c ] );

		$this->auth->requestPassword( $u, $e, $c );

		return view( 'login/forgot', (array) $this->auth->getMessage() );
	}

	public function logout ()
	{
		$this->auth->logout();
		return view( 'login/login', ( array ) $this->auth->getMessage() );
	}

	private function _dumpIt( array $form ) : void
	{
		if ( $this->dump )
		{
			// d( _debugInfo( RegistryClass::class ), 'RegistryClass' );
			// d( _debugInfo( RegistryEventClass::class ), 'RegistryEventClass' );
			d( $this->auth->getMessage( $form ) );
			// d( $this->auth->getMessage() );
			// echo '<pre>';
			// var_dump( $this->auth->getMessage()->result );
			// echo '</pre>';
		}
	}
}