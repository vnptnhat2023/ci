<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Validation;

use CodeIgniter\Validation\ValidationInterface;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
class ValidationAdapter implements ValidationAdapterInterface
{
	protected ValidationInterface $validate;

	protected array $rules = [
		'login' => [ 'username', 'password' ],
		'login_captcha' => [ 'username', 'password', 'ci_captcha' ],
		'forget' => [ 'username', 'email' ],
		'forget_captcha' => [ 'username', 'email', 'ci_captcha' ]
	];

	public function __construct ( ValidationInterface $validate )
	{
		$this->validate = $validate;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate
		->withRequest( \Config\Services::request() )
		->setRules( $rules )
		->run( $data );
	}

	public function getErrors( string $field = null ) : array
	{
		return ! empty( $field )
		? $this->validation->getErrors()
		: [ $this->validation->getError( $field ) ];
	}

	public function reset(): void
	{
		$this->validate->reset();
	}

	/**
	 * @param string|array $needed
	 * @return string|array
	 */
	public function getRules ( $needed )
	{
		$generalRules = [

			'username' => [
				'label' => lang( 'Red2Horse.labelUsername' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_dash'
			],

			'password' => [
				'label' => lang( 'Red2Horse.labelPassword' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct'
			],

			'email' => [
				'label' => lang( 'Red2Horse.labelEmail' ),
				'rules' => 'trim|required|min_length[5]|max_length[128]|valid_email'
			],

			'ci_captcha' => [
				'label' => lang( 'Red2Horse.labelCaptcha' ),
				'rules' => 'trim|required|min_length[5]|ci_captcha'
			]
		];

		if ( is_string( $needed ) ) {
			$result = dot_array_search( $needed, $generalRules );
		}

		if ( is_array( $needed ) ) {
			$result = [];

			foreach ( $needed as $need ) {
				if ( isset( $generalRules[ $need ] ) )
				$result[ $need ] = $generalRules[ $need ];
			}
		}

		if ( empty( $result ) ) throw new \Exception( "Error rule not found", 1 );

		return $result;
	}
}