<?php

declare( strict_types = 1 );

namespace BAPI\Entities\Extension;

class Crud extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [
		//
	];

	// public function updateFillable() : \Codeigniter\Entity; # maybe not needed

  public function createFillable() : \Codeigniter\Entity
  {
		$config = config( '\BAPI\Config\Extension' )->getSetting( 'db.fill' );

		foreach ( $config as $key => $value )
		{
			$this->attributes[ $key ] ??= $value[ $key ];
		}

    return $this;
  }

	public function setAuthor ( string $str ) : void
	{
		$this->attributes[ 'author' ] = mb_strtolower( $str );
	}

	public function setContact ( string $str ) : void
	{
		$this->attributes[ 'contact' ] = mb_strtolower( $str );
	}

	public function setCategoryName ( string $str ) : void
	{
		$this->attributes[ 'category_name' ] = mb_strtolower( $str );
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
		$this->attributes[ 'description' ] = mb_strtolower( $str );
	}

  public function setTitle ( string $title ) : void
  {
		helper( [ 'text' ] );

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
		$this->attributes[ 'version' ] = preg_match( '/^(\d+\.)?(\d+\.)?(\*|\d+)$/', $str )
		? $str
		: config( '\BAPI\Config\Extension' )->getSetting( 'db.fill.version' );
	}

	public function setEvents( array $events ) : void
	{
		# events.method = title, .name = url_title( slug )
	}
}