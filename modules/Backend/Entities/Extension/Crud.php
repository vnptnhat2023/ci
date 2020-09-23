<?php

declare( strict_types = 1 );

namespace BAPI\Entities\Extension;

use CodeIgniter\Entity;

class Crud extends Entity
{
	protected $datamap = [
		'name' => 'title'
	];

  public function createFillable () : self
  {
		$config = config( '\BAPI\Config\Extension' )
		->getSetting( 'db.fill' );

		foreach ( $config as $key => $value )
		{
			if ( $key === 'events' )
			{
				$events = $this->attributes[ 'events' ] ?? [];
				$this->attributes[ 'events' ] = (array) $events;
			}
			else
			{
				$this->attributes[ $key ] ??= $value;
			}
		}

    return $this;
  }

	public function setAuthor ( string $str ) : void
	{
		$this->attributes[ 'author' ] = mb_strtolower( $str );
	}

	public function setContact ( string $str ) : void
	{
		helper( 'text' );

		$this->attributes[ 'contact' ] = mb_strtolower(
			reduce_multiples( $str, ' ', true )
		);
	}

	public function setCategoryName ( string $str ) : void
	{
		helper( 'text' );

		$this->attributes[ 'category_name' ] = mb_strtolower(
			reduce_multiples( $str, ' ', true )
		);
	}

	public function setCategorySlug ( string $str ) : void
  {
		helper( [ 'url', 'text' ] );

		$str = convert_accented_characters( $str );

    $this->attributes[ 'category_slug' ] = url_title(
      $str, config( '\BAPI\Config\Entities' )::slugSeparate, true
    );
	}

	public function setDescription( string $str ) : void
	{
		helper( 'text' );

		$this->attributes[ 'description' ] = mb_strtolower(
			reduce_multiples( $str, ' ', true )
		);
	}

  public function setTitle ( string $title ) : void
  {
		helper( 'text' );

    $this->attributes[ 'title' ] = mb_strtolower(
      reduce_multiples( $title, ' ', true )
    );

    if ( empty( $this->attributes[ 'slug' ] ) ) {
      $this->setSlug( $this->attributes[ 'title' ] );
    }
	}

	public function setSlug ( string $slug ) : void
  {
		helper( [ 'url', 'text', 'string' ] );

		$slug = convert_accented_characters( $slug );

    $str = url_title(
      $slug, config( '\BAPI\Config\Entities' )::slugSeparate, true
		);

		$this->attributes[ 'slug' ] = strCamelCase( $str );
	}

	public function setVersion ( string $str ) : void
	{
		helper( 'string' );

		$this->attributes[ 'version' ] = strValidVersion( $str )
		? $str
		: config( '\BAPI\Config\Extension' )->getSetting( 'db.fill.version' );
	}

	/**
	 * Format [ method, name ] to [ reduce_multiples( title ),
	 * url_title( slug ) ]
	 */
	public function setEvents( $events ) : void
	{
		$events = (array) $events;
		$data = [];

		if ( ! empty( $events ) )
		{
			helper( [ 'text', 'string' ] );

			foreach ( $events as $event )
			{
				if ( isset( $event[ 'method' ], $event[ 'name' ] ) )
				{
					$title = reduce_multiples( $event[ 'method' ], ' ', true );

					$data[] = [
						'title' => strCamelCase( $title ),
						'slug' => url_title( $event[ 'name' ], '-', true )
					];
				}
			}
		}

		$this->attributes[ 'events' ] = $data;
	}
}