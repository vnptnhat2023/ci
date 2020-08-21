<?php

declare( strict_types = 1 );

namespace App\Libraries\NknAuth\Facade;

use App\Libraries\NknAuth\Validation\CodeIgniterValidationAdapter as CiValidateAdapter;

/**
 * Facade pattern class
 * @author <altayalp@gmail.com>
 * @url https://gist.github.com/altayalp/b4ee10c560d41dc197c2b86cc20ee54e
 */
class SignUpFacade
{

	private ValidateInterface $validate;
	private UserInterface $user;
	private AuthInterface $auth;
	private MailInterface $mail;

	public function __construct(
		ValidateInterface $validate,
		UserInterface $user,
		AuthInterface $auth,
		MailInterface $mail
	)
	{
		$this->validate = $validate;
		$this->user = $user;
		$this->auth = $auth;
		$this->mail = $mail;
	}

	public function signUpUser ( string $userName, string $userPass, string $userMail ) : void
	{
		$data = [
			'name' => $userName,
			'password' =>$userPass,
			'mail' => $userMail
		];

		if ( $this->validate->isValid( $data, [] ) )
		{
			$this->user ->create( $data );
			$this->auth ->login( $userName, $userPass );
			$this->mail ->to( $userMail ) ->subject( 'Welcome' ) ->send();
		}
	}

}
# --- New instanced ValidationInterface, with any other constructor
$CiValidate = new CiValidateAdapter( service( 'validation' ), service( 'request' ) );
// Create instance of classes
$validate = new Validate( $CiValidate );


$user = new User();
$auth = new Auth();
$mail = new Mail();

# --- witFacade.php
// Simple sign up process with facade pattern
$facade = new SignUpFacade( $validate, $user, $auth, $mail );
$facade->signUpUser( $userName, $userPass, $userMail );