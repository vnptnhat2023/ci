<?php

declare( strict_types = 1 );

namespace Red2Horse\Adapter\Codeigniter\Validation;
use Config\Services;
use function Red2Horse\Mixins\Functions\getConfig;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ValidationAdapter implements ValidationAdapterInterface
{
	public function isValid ( array $data, array $rules ) : bool
	{
		return Services::validation()
			->withRequest( Services::request() )
			->setRules( $rules )
			->run( $data );
	}

	public function getErrors( string $field = null ) : array
	{
		return ! empty( $field )
			? Services::validation() ->getErrors()
			: [ Services::validation() ->getError( $field ) ];
	}

	public function reset(): void
	{
		Services::validation()->reset();
	}

	/**
	 * @param string|array $needed
	 * @return mixed string|array
	 */
	public function getRules ( $keys )
	{
		$generalRules = $this->ruleStore();

		if ( is_string( $keys ) )
		{
			$result = dot_array_search( $keys, $generalRules );
		}

		if ( is_array( $keys ) )
		{
			$result = [];

			foreach ( $keys as $key )
			{
				if ( isset( $generalRules[ $key ] ) )
				{
					$result[ $key ] = $generalRules[ $key ];
				}
			}
		}

		if ( empty( $result ) )
		{
			throw new \Exception( "Error the rule is required", 403 );
		}

		return $result;
	}

	public function ruleStore() : array
	{
		$config = getConfig( 'validation' );

		$generalRules = [
			$config::$username => [
				'label' => lang( 'Red2Horse.labelUsername' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_dash'
			],
			$config::$password => [
				'label' => lang( 'Red2Horse.labelPassword' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct'
			],
			$config::$email => [
				'label' => lang( 'Red2Horse.labelEmail' ),
				'rules' => 'trim|required|min_length[5]|max_length[64]|valid_email'
			],
			$config::$captcha => [
				'label' => lang( 'Red2Horse.labelCaptcha' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct|ci_captcha'
			]
		];

		return $generalRules;
	}
}