<?php

# --- Todo: When init an extension, run addRelationShip()
# --- Todo: Compare phpMyAdmin vs entity ✔
# --- Todo: Edit all controller ✔
# --- Todo: Edit model ✔

# --- Todo: Edit VueJs:
# --- Link: 1. https://jsfiddle.net/6evc921f/
# --- Link: 2. https://stackoverflow.com/a/46956030

namespace BAPI\Config;

use BAPI\Config\Mixins\ConfigTrait;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Events\Events;
use Config\Validation;

class Post extends BaseConfig
{
	use ConfigTrait;

	/**
	 * Containing all errors
	 * @var array $errors
	 */
	protected array $errors = [];

	/**
	 * Containing all relations
	 * @var array $relations
	 */
	private static array $relations = [];

  private const setting = [

		'db' => [

			'create' => [
				'maximum_rows' => 1
			],

			'update' => [
				'maximum_rows' => 100
			],

			'delete' => [
				'soft' => true,
				'maximum_rows' => 100
			],

			'fill' => [
				'excerpt' => null,
				'media_relation_id' => 0,
				'name' => 'category',
				'status' => 'draff',
				'typeof' => 'text',
			],

			'fetch' => [
				'record' => 10,
				'orderBy' => 'id',
				'direction' => 'DESC'
			],

			'option' => [

				'block' => [
					'relative' => true,
					'recent' => true,
					'prev_next' => true,
					'most_view' => true,
					'random' => true
				],

				'information' => [
					'except' => true,
					'thumbnail' => true,
					'type' => false,
					'created' => true,
					'updated' => false,
					'author' => false,
					'tag' => true,
					'social' => true,
					'like' => false,
					'rate' => false,
					'poll' => false,
					'counter_view' => false
				],

				'show_404' => 'The post not found',

				'show_message' => 'The post not found'
			],

			# Use events to push further
			'relation' => [
				'category' => 'Category',
				'page' => 'Page',
			]
		],

		// 'cache' => [ 'blah' => 'blah' ],
	];

	# --- Todo: will be move to the ConfigMedia
	public int $mediaMaxLen = 500;


	# ==========================================================
	/**
	 * Added event "add-post-relation",
	 * $this->addRelationShip( **string** $slug, **string** $title )
	 */
	public function __construct()
	{
		Events::on( 'add-post-relation', [ $this, 'addRelationShip' ] );
	}

	# __________________________________________________________
	public function addRelationShip ( string $slug, string $title ) : bool
	{
		$validation = \Config\Services::validation();

		$userData = [ 'slug' => $slug, 'title' => $title ];

		# --- Rules from config\extension
		$relateRules = [
			'title' => config('\BAPI\Config\Extension')->getRules('title'),
			'slug' => config('\BAPI\Config\Extension')->getRules('slug'),
		];

		# --- Not valid
		if ( ! $validation->setRules( $relateRules )->run( $userData ) ) {

			foreach ( $validation->getErrors() as $errStr) {
				$this->errors['error'][] = $errStr;
			}

			return false;
		}

		# --- Get all relationship
		$relationShip = $this->getRelationShip();

		if ( isset( $relationShip[ $slug ] ) )
		{
			$errStr = lang( 'Validation.is_unique', [ 'field' => $slug ] );
			$this->errors['error'][] = $errStr;

			return false;
		}
		else
		{
			$userData = [ $slug => $title ];
			$relateData = self::$relations ?: $this->getSetting('db.relation');

			self::$relations = ( $userData + $relateData );

			return true;
		}
	}

	# ==========================================================
	/**
	 * @param string|null $key when null,
	 * return array relationship
	 */
	public function getRelationShip ( string $key = null ) : array
	{
		$data = self::$relations[ $key ]
		?? self::$relations
		?: $this->getSetting('db.relation');

		# Sorted key before return
		ksort( $data );

		return $data;
	}

	# ==========================================================
	public function removeRelationShip ( string $key ) : bool
	{
		$get = $this->getSetting('db.relation');

		if ( empty( self::$relations[ $key ] ) || isset( $get[ $key ] ) ) {
			return false;
		}

		unset( self::$relations[ $key ] );

		return true;
	}

	# ==========================================================
	/**
	 * @return array
	 */
	public function getErrors() : array
	{
		return $this->errors;
	}

	# ==========================================================
	public function getRules ( string $key = null ) : array
	{
		return $rules = [

			'id' => Validation::ruleInt( 'id', 'required' ),

			'title' => [
				'label' => 'title',
				'rules' => 'trim|required|min_length[6]|max_length[128]|alpha_numeric_punct'
			],

			'slug' => [
				'label' => 'slug',
				'rules' => 'trim|required|min_length[6]|max_length[192]|alpha_dash'
			],

			'excerpt' => [
				'label' => 'excerpt',
				'rules' => 'if_exist|trim|permit_empty|max_length[512]|alpha_numeric_punct'
			],

			'content' => [
				'label' => 'content',
				'rules' => 'trim|required|min_length[20]'
			],

			'name' => [
				'label' => 'name',
				'rules' => 'trim|required|min_length[4]|max_length[64]|alpha_dash|'
					.'in_list[' . implode( ',', $this->getSetting('db.relation') ) . ']'
			],

			'name_id' => Validation::ruleInt( 'name id', 'required' ),

			'user_id' => Validation::ruleInt( 'user id', 'required' ),

			'media_relation_id' => Validation::ruleInt( 'media relation', 'if_exist', null, true ),

			'status' => [
				'label' => 'status',
				'rules' => 'required|in_list[publish,private,draff]'
			],

			'typeof' => [
				'label' => 'typeof',
				'rules' => 'trim|required|min_length[4]|max_length[64]|alpha'
			]
		];

		helper( 'array' );

		return empty( $key ) ? $rules : dot_array_search( $key, $rules );
	}
}