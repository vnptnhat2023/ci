<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Facade\Validation;

// use App\Libraries\Red2Horse\Adapter\Codeigniter\Validation\ValidationAdapterInterface;

class ValidationFacade implements ValidationFacadeInterface
{
	protected ValidationFacadeInterface $validationAdapter;

	protected $rules = [
		'login' => [ 'username', 'password' ],
		'login_captcha' => [ 'username', 'password', 'ci_captcha' ],
		'forget' => [ 'username', 'email' ],
		'forget_captcha' => [ 'username', 'email', 'ci_captcha' ]
	];

	public function __construct ( ValidationFacadeInterface $validate )
	{
		$this->validationAdapter = $validate;
	}

	public function isValid ( array $data, array $rules ) : bool
	{
		return $this->validate->isValid( $data, $rules );
	}

	public function getErrors ( string $field = null ) : array
	{
		return $this->validate->getErrors( $field );
	}

	public function rules ( $needed )
	{
		$generalRules = [

			'username' => [
				'label' => lang( 'NKnAuth.labelUsername' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_dash'
			],

			'password' => [
				'label' => lang( 'NKnAuth.labelPassword' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct'
			],

			'email' => [
				'label' => lang( 'NKnAuth.labelEmail' ),
				'rules' => 'trim|required|min_length[5]|max_length[128]|valid_email'
			],

			'ci_captcha' => [
				'label' => lang( 'NKnAuth.labelCaptcha' ),
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