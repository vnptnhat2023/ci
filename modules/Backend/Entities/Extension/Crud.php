<?php

namespace BAPI\Entities\Extension;

class Crud extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [
		//
	];

	// public function updateFillable() : \Codeigniter\Entity;

  public function createFillable() : \Codeigniter\Entity
  {
		return $this;
		// $config = ( new \BAPI\Config\GeneralGroup() );

    // $this->attributes['status'] ??= $config->getSetting('db.fill.status');

    // return $this;
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
		$isVersionValid = preg_match( '/^(\d+\.)?(\d+\.)?(\*|\d+)$/', $str );

		$this->attributes[ 'version' ] = $isVersionValid
		? $isVersionValid
		: config( '\BAPI\Config\Extension' )->getSetting( 'db.fill.version' );
	}

	public function setEvents( array $events ) : void
	{
		# events.method = title, .name = url_title( slug )
	}
}