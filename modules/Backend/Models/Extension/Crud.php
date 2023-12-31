<?php

namespace BAPI\Models\Extension;

use Config\Validation;

class Crud extends \CodeIgniter\Model
{
  protected $table = 'extension';
	protected $primaryKey = 'id';

  protected $returnType = 'array';
  protected $dateFormat = 'date';

  protected $useTimestamps = true;

  public function ruleSearch () : array
  {
		$configRules = config( '\BAPI\Config\Extension' ) ->getRules();

    $rules = [];

    foreach ( [ 'id', 'author', 'slug', 'status', 'category_slug' ] as $rule ) {
      $rules[ $rule ] = $configRules[ $rule ];
    }

    return $rules;
  }

  public function ruleCreate ( array $data ) : array
  {
		$rules = config( '\BAPI\Config\Extension' )->getRules();
		$events = $data[ 'data' ][ 'events' ] ?? [];

		# Add more unique-slug
		$rules[ 'slug' ] = Validation::modifier(
			$rules[ 'slug' ],
			null,
			null,
			"is_unique[{$this->table}.slug]"
		);

		foreach ( $events as $key => $value ) {
			$key = $key ?: '*';
			$rules[ "events.{$key}.title" ] = $rules[ 'method' ];
			$rules[ "events.{$key}.slug" ] = $rules[ 'name' ];
		}

		unset(
			$rules[ 'id' ],
			$rules[ 'method' ],
			$rules[ 'name' ]
		);

		$this->allowedFields = [
			'author',
			'contact',
			'category_name',
			'category_slug',
			'description',
			'title',
			'slug',
			'version',
			'status'
		];

    return $rules;
	}

	public function rulePatch () : array
	{
		$this->allowedFields = [ 'status' ];

		return [ 'status' => config( '\BAPI\Config\Extension' )
		->getRules( 'status' ) ];
	}
}