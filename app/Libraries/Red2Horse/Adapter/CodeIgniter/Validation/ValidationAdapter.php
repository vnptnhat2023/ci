<?php

declare( strict_types = 1 );
namespace Red2Horse\Adapter\Codeigniter\Validation;

use Config\Services;
use Red2Horse\Exception\ErrorArrayException;
use Red2Horse\Mixins\Traits\Object\TraitSingleton;

use function Red2Horse\Mixins\Functions\Config\getConfig;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class ValidationAdapter implements ValidationAdapterInterface
{
	use TraitSingleton;
	
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
			$result = is_array( $result ) ? implode( ', ', $result ) : $result ?? 'null';
			throw new ErrorArrayException( 'Error rule not found' );
		}

		return $result;
	}

	private string $dateTimeRule = 'permit_empty|exact_length[19]';

	private function numericRules ( bool $required = false, ?int $minLen = null, ?int $maxLen = null ) : string
	{
		$text = 'trim';

		if ( $required )
		{
			$text .= sprintf( '%s|required', $text );
		}

		if ( null !== $minLen )
		{
			$text .= sprintf( '%s|min_length[%s]', $text, $minLen );
		}

		if ( null !== $maxLen )
		{
			$text .= sprintf( '%s|max_length[%s]', $text, $maxLen );
		}

		return sprintf( '%s%s', $text, 'is_natural_no_zero' );
	}

	public function ruleStore() : array
	{
		$configValidation = getConfig( 'validation' );

		$generalRules = [
			/** User rules */
			$configValidation->user_id => [
				'label' => lang( 'Red2Horse.id' ),
				'rules' => $this->numericRules( true, 1, 128 )
			],
			$configValidation->user_groupId => [
				'label' => lang( 'Red2Horse.groupId' ),
				'rules' => $this->numericRules( true, 1, 128 )
			],
			$configValidation->user_username => [
				'label' => lang( 'Red2Horse.labelUsername' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_dash'
			],
			$configValidation->user_password => [
				'label' => lang( 'Red2Horse.labelPassword' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct'
			],
			$configValidation->user_email => [
				'label' => lang( 'Red2Horse.labelEmail' ),
				'rules' => 'trim|required|min_length[5]|max_length[64]|valid_email'
			],
			$configValidation->user_status => [
				'label' => lang( 'Red2Horse.status' ),
				'rules' => 'in_list[active,inactive,banned]'
			],
			$configValidation->user_lastActivity => [
				'label' => lang( 'Red2Horse.activity' ),
				'rules' => $this->dateTimeRule
			],
			$configValidation->user_lastLogin => [
				'label' => lang( 'Red2Horse.last_login' ),
				'rules' => $this->dateTimeRule
			],
			$configValidation->user_createdAt => [
				'label' => lang( 'Red2Horse.created_at' ),
				'rules' => $this->dateTimeRule
			],
			$configValidation->user_updatedAt => [
				'label' => lang( 'Red2Horse.updated_at' ),
				'rules' => $this->dateTimeRule
			],
			$configValidation->user_deletedAt => [
				'label' => lang( 'Red2Horse.labelGroupDeletedAt' ),
				'rules' => $this->dateTimeRule
			],
			$configValidation->user_sessionId => [
				'label' => lang( 'Red2Horse.session_id' ),
				'rules' => 'permit_empty|max_length[128]'
			],
			$configValidation->user_selector => [
				'label' => lang( 'Red2Horse.selector' ),
				'rules' => 'permit_empty|max_length[128]'
			],
			$configValidation->user_token => [
				'label' => lang( 'Red2Horse.token' ),
				'rules' => 'permit_empty|max_length[128]'
			],
			$configValidation->user_captcha => [
				'label' => lang( 'Red2Horse.labelCaptcha' ),
				'rules' => 'trim|required|min_length[5]|max_length[32]|alpha_numeric_punct|ci_captcha'
			],

			/** User group rules */
			$configValidation->userGroup_id => [
				'label' => lang( 'Red2Horse.groupId' ),
				'rules' => $this->numericRules( true, 1, 128 )
			],
			$configValidation->userGroup_name => [
				'label' => lang( 'Red2Horse.labelUserGroupName' ),
				'rules' => 'trim|required|min_length[5]|max_length[64]|alpha_numeric_punct'
			],
			$configValidation->userGroup_permission => [
				'label' => lang( 'Red2Horse.labelGroupPermission' ),
				'rules' => 'trim|required|min_length[3]|max_length[512]|perms'
			],
			$configValidation->userGroup_role => [
				'label' => lang( 'Red2Horse.labelGroupRole' ),
				'rules' => 'trim|required|min_length[3]|max_length[128]|alpha_numeric_punct'
			],
			$configValidation->userGroup_deletedAt => [
				'label' => lang( 'Red2Horse.labelGroupDeletedAt' ),
				'rules' => $this->dateTimeRule
			],

			/** User Database */
			$configValidation->database_hostname => [
				'label' => lang( 'Red2Horse.db_hostname' ),
				'rules' => 'required|min_length[3]|max_length[128]|alpha_dash'
			],
			$configValidation->database_username => [
				'label' => lang( 'Red2Horse.db_username' ),
				'rules' => 'required|min_length[3]|max_length[128]|alpha_dash'
			],
			$configValidation->database_password => [
				'label' => lang( 'Red2Horse.db_password' ),
				'rules' => 'required|min_length[3]|max_length[128]|alpha_numeric'
			],
			$configValidation->database_database => [
				'label' => lang( 'Red2Horse.db_database' ),
				'rules' => 'required|min_length[3]|max_length[128]|alpha_dash'
			],
			$configValidation->database_port => [
				'label' => lang( 'Red2Horse.db_port' ),
				'rules' => $this->numericRules( false, 1, 128 )
			],

			/** User Database */
			$configValidation->throttle_id => [
				'label' => lang( 'Red2Horse.throttle_id' ),
				'rules' => $this->numericRules( true, 1, 32 )
			],
			$configValidation->throttle_attempt => [
				'label' => lang( 'Red2Horse.throttle_attempt' ),
				'rules' => 'required|max_length[32]|is_natural_no_zero'
			],
			$configValidation->throttle_ip => [
				'label' => lang( 'Red2Horse.throttle_ip' ),
				'rules' => 'required|min_length[1]|max_length[128]|alpha_numeric_punct'
			],
			$configValidation->throttle_createdAt => [
				'label' => lang( 'Red2Horse.throttle_createdAt' ),
				'rules' => $this->dateTimeRule
			],
			$configValidation->throttle_updatedAt => [
				'label' => lang( 'Red2Horse.throttle_updatedAt' ),
				'rules' => $this->dateTimeRule
			],
		];

		return $generalRules;
	}
}