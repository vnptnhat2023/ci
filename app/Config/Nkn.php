<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Nkn extends BaseConfig
{
	# --- Default PAGE?
	# --- Default PAGE TYPE? "extension needed multiple pages and subPage"
	# --- Page TYPE, title, slug, id?
	# --- Relation using?
	# --- * Controller Or Content?

	/**
	 * NknAuth session name
	 * @var string $NKNss
	 */
	public $NKNss = 'oknkn';

	/**
	 * NknAuth cookie name
	 * @var string $NKNck
	 */
	public $NKNck = 'konkn';

	/**
	 * @var array $throttle
	 */
  public $throttle = [
  	'type' => 1,
  	'limit_one' => 4,
  	'limit' => 10,
  	'timeout' => 30
	];

	# --- Assign from extension
	protected $oneByMany = [
		[
			'name' => 'pa',
			'title' => 'Page',
			'content' => 'Default page content type'
		],
		[
			'name' => 'ca',
			'title' => 'Category',
			'content' => 'Category content type'
		],
		[
			'name' => 'po',
			'title' => 'Post',
			'content' => 'Post content type'
		],
		[
			'name' => 'ex',
			'title' => 'Extension',
			'content' => 'Extension content type'
		],
		[
			'name' => 'th',
			'title' => 'Theme',
			'content' => 'Theme content type'
		]
	];

	private $typeRules = [
		'name' => 'required|exact_length[2]|alpha',
		'title' => 'required|min_length[4]|max_length[32]|alpha_numeric_space',
		'content' => 'if_exist|max_length[64]|alpha_numeric_punct',
	];

	/** Set a pageType */
	public function AddOneByMany(string $name, string $title, string $content) : Nkn
	{
		$validation = \Config\Services::validation();
		$userData = [ 'name' => $name, 'title' => $title, 'content' => $content ];

		if ( ! $validation ->setRules( $this->typeRules ) ->run( $userData ) )
		{
			return $validation->getErrors();
		}
		else if ( count( $this->getOneByMany( $name ) ) === 1 )
		{
			return [ 'error' => lang( 'Validation.is_not_unique', [ 'field' => $name ] ) ];
		}

		$this->OneByMany = ( $this->OneByMany + $userData );

		return $this;
	}

	/**
	 * Find a pageType needle or all
	 * @param $needle string
	 * @return array
	 */
	public function getOneByMany(string $needle = '') : array
	{
		$col = array_column( $this->OneByMany, 'name' );
		$findNum = in_array( $needle, $col, true );

		if ( $needle AND $findNum ) {
			return $this->OneByMany[ $findNum ];
		}

		return $this->OneByMany;
	}

	public function removeOneByMany(string $needle) : array
	{
		$col = array_column( $this->OneByMany, 'name' );
		$findNum = in_array( $needle, $col, true );

		if ( $findNum ) {
			unset( $this->OneByMany[ $findNum ] );
		}

		return $this->OneByMany;
	}
}