<?php

declare( strict_types = 1 );

namespace App\Libraries\Red2Horse\Adapter\Codeigniter\Validation;

use App\Libraries\Red2Horse\Facade\Auth\Config;
use Config\Services;

/**
 * @package Red2ndHorseAuth
 * @author Red2Horse
 */
class ValidationAdapter implements ValidationAdapterInterface
{
	public function isValid ( array $data, array $rules ) : bool
	{
		return Services::validation()
		->withRequest( \Config\Services::request() )
		->setRules( $rules )
		->run( $data );
	}

	public function getErrors( string $field = null ) : array
	{
		return ! empty( $field )
		? Services::validation()->getErrors()
		: [ Services::validation()->getError( $field ) ];
	}

	public function reset(): void
	{
		Services::validation()->reset();
	}

	/**
	 * @param string|array $needed
	 * @return string|array
	 */
	public function getRules ( $needed )
	{
		$generalRules = $this->ruleStore();

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

		if ( empty( $result ) ) throw new \Exception( "Error the rule is required", 403 );

		return $result;
	}

	public function ruleStore() : array
	{
		$config = Config::getInstance();
		$generalRules = [

			$config::USERNAME => [
				'label' => lang( 'Red2Horse.labelUsername' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_dash'
			],

			$config::PASSWORD => [
				'label' => lang( 'Red2Horse.labelPassword' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct'
			],

			$config::EMAIL => [
				'label' => lang( 'Red2Horse.labelEmail' ),
				'rules' => 'trim|required|min_length[5]|max_length[128]|valid_email'
			],

			$config::CAPTCHA => [
				'label' => lang( 'Red2Horse.labelCaptcha' ),
				'rules' => 'trim|required|min_length[5]|ci_captcha'
			]
		];

		return $generalRules;
	}
}