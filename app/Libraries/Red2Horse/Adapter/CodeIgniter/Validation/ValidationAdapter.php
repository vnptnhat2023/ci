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

	public function getErrors( ?string $field = null ) : array
	{
		return null === $field
			? Services::validation() ->getErrors()
			: [ Services::validation() ->getError( $field ) ];
	}

	public function reset(): void
	{
		Services::validation()->reset();
	}

	/**
	 * @param string|array|null $keys
	 * @return mixed string|array
	 */
	public function getRules ( $keys = null )
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
			throw new \Error(
				sprintf( 'Error rule not found. %s:%s:%s', __FILE__, __METHOD__, __LINE__ ),
				404
			);
		}

		return $result;
	}

	public function ruleStore() : array
	{
		$config = getConfig( 'validation' );

		$generalRules = [
			/** User rules */
			$config::$id => [
				'label' => lang( 'Red2Horse.id' ),
				'rules' => 'trim|required|is_natural_no_zero'
			],
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
			$config::$status => [
				'label' => lang( 'Red2Horse.status' ),
				'rules' => 'in_list[active,inactive,banned]'
			],
			$config::$lastActivity => [
				'label' => lang( 'Red2Horse.activity' ),
				'rules' => 'permit_empty|exact_length[19]'
			],
			$config::$lastLogin => [
				'label' => lang( 'Red2Horse.last_login' ),
				'rules' => 'permit_empty|exact_length[19]'
			],
			$config::$createdAt => [
				'label' => lang( 'Red2Horse.created_at' ),
				'rules' => 'permit_empty|exact_length[19]'
			],
			$config::$updatedAt => [
				'label' => lang( 'Red2Horse.updated_at' ),
				'rules' => 'permit_empty|exact_length[19]'
			],
			$config::$sessionId => [
				'label' => lang( 'Red2Horse.session_id' ),
				'rules' => 'permit_empty|max_length[128]'
			],
			$config::$selector => [
				'label' => lang( 'Red2Horse.selector' ),
				'rules' => 'permit_empty|max_length[128]'
			],
			$config::$token => [
				'label' => lang( 'Red2Horse.token' ),
				'rules' => 'permit_empty|max_length[128]'
			],
			$config::$captcha => [
				'label' => lang( 'Red2Horse.labelCaptcha' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct|ci_captcha'
			],

			/** User group rules */
			$config::$groupId => [
				'label' => lang( 'Red2Horse.groupId' ),
				'rules' => 'trim|required|is_natural_no_zero'
			],
			$config::$groupName => [
				'label' => lang( 'Red2Horse.labelUserGroupName' ),
				'rules' => 'trim|required|min_length[5]|max_length[64]|alpha_numeric_punct'
			],
			$config::$groupPermission => [
				'label' => lang( 'Red2Horse.labelGroupPermission' ),
				'rules' => 'trim|required|min_length[5]|max_length[512]|alpha_numeric_punct'
			],
			$config::$groupRole => [
				'label' => lang( 'Red2Horse.labelGroupRole' ),
				'rules' => 'trim|required|min_length[5]|max_length[128]|alpha_numeric_punct'
			],
			$config::$groupDeletedAt => [
				'label' => lang( 'Red2Horse.labelGroupDeletedAt' ),
				'rules' => 'permit_empty|exact_length[19]'
			]
		];

		return $generalRules;
	}
}