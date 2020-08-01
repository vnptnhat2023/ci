<?php namespace Config;

class Validation
{
	//--------------------------------------------------------------------
	// Setup
	//--------------------------------------------------------------------

	/**
	 * Stores the classes that contain the
	 * rules that are available.
	 *
	 * @var array
	 */
	public $ruleSets = [
		\CodeIgniter\Validation\Rules::class,
		\CodeIgniter\Validation\FormatRules::class,
		\CodeIgniter\Validation\FileRules::class,
		\CodeIgniter\Validation\CreditCardRules::class,
		\BAPI\Validation\BAPIRules::class,
	];

	/**
	 * Specifies the views that are used to display the
	 * errors.
	 *
	 * @var array
	 */
	public $templates = [
		'list'   => 'CodeIgniter\Validation\Views\list',
		'single' => 'CodeIgniter\Validation\Views\single',
	];

	//--------------------------------------------------------------------
	// Rules
	//--------------------------------------------------------------------

	/**
	 * Default rule: is_natural_no_zero|max_length[11]
	 * @param null|string $args[0] $labelName, default = 'Id'
	 * @param null|string $args[1] $before, default null
	 * @param null|string $args[2] $after, default null
	 * @param boolean $args[3] $isZero
	 *
	 * 1. Default false
	 * 2. true $isZero = is_natural
	 * 3. false $isZero = is_natural_no_zero
	 * @return array
	 */
	public static function ruleInt ( ...$arg ) : array
	{
		$before = empty( $arg[ 1 ] ) ? '' : $arg[ 1 ] . '|';
		$after = empty( $arg[ 2 ] ) ? '' : '|' . $arg[ 2 ];
		$isZero = empty( $arg[ 3 ] ) ? 'is_natural_no_zero' : 'is_natural';

		$rule = [
			'label' => ( $arg[ 0 ] ?? 'id' ),
			'rules' => $before . "{$isZero}|max_length[11]" . $after
		];

		return $rule;
	}

	/**
	 * Default rule: permit_empty
	 * @param null|string $labelName, default = 'Undelete'
	 * @param null|string $before
	 * @param null|string $after
	 * @return array
	 */
	public static function ruleUndelete ( ...$arg ) : array
	{
		$before = empty( $arg[ 1 ] ) ? '' : $arg[ 1 ] . '|';
		$after = empty( $arg[ 2 ] ) ? '' : '|' . $arg[ 2 ];

		$rule = [
			'label' => ( $arg[ 0 ] ?? 'undelete' ),
			'rules' => "{$before}permit_empty{$after}"
		];

		return $rule;
	}

	/**
	 * @param array $[0] rule data
	 * @param null|string $[1], Label default = 'Undefined'
	 * @param null|string $[2], Before rule
	 * @param null|string $[3] After rule
	 * @return array
	 */
	public static function modifier ( ...$arg ) : array
	{
		if ( empty( $arg[ 0 ][ 'label' ] ) OR empty( $arg[ 0 ][ 'rules' ] ) ) {
			return $arg[ 0 ] ?? [];
		}

		$before = empty( $arg[ 2 ] ) ? '' : $arg[ 2 ] . '|';
		$after = empty( $arg[ 3 ] ) ? '' : '|' . $arg[ 3 ];

		$rule = [
			'label' => ( $arg[ 1 ] ?? $arg[ 0 ][ 'label' ] ?? 'undefined' ),
			'rules' => "{$before}{$arg[ 0 ][ 'rules' ]}{$after}"
		];

		return $rule;
	}

	public static function ruleEmail ( string $label = 'email' ) : array
	{
		return [
			'label' => $label,
			'rules' => 'trim|min_length[5]|max_length[32]|valid_email'
		];
	}
}
