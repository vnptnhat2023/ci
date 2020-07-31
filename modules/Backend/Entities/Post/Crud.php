<?php

namespace BAPI\Entities\Post;

use CodeIgniter\Entity;

class Crud extends Entity
{
  protected $attributes = [];

  protected $datamap = [
		'category' => 'name_id',
		'cat_id' => 'name_id',
		'custom_post_id' => 'general_relation',
		'media' => 'media_relation'
	];

	/**
   * Fillable data for create
   */
  public function createFillable () : self
  {
		$configFill = ( new \BAPI\Config\Post() ) ->getSetting( 'db.fill' );

    foreach ( $configFill as $field => $value) {
      $this->attributes[ $field ] ??= $value;
		}

		$this->attributes[ 'general_relation' ] ??= [];
		$this->attributes[ 'media_relation' ] ??= [];
		$this->attributes[ 'user_id' ] = service('NknAuth') ->get_userdata( 'id' );

    return $this;
  }

  protected function setSlug ( $str )
  {
		helper( [ 'url', 'text' ] );

		$str = convert_accented_characters( $str );

    $this->attributes[ 'slug' ] = url_title(
			$str,
			config( '\BAPI\Config\Entities' )::slugSeparate,
			true
    );
  }

  protected function setTitle ( $str )
  {
		helper( 'text' );

    $this->attributes[ 'title' ] = mb_strtolower(
      reduce_multiples( $str, ' ', true )
    );

    if ( empty( $this->attributes[ 'slug' ] ) ) {
      $this->setSlug( $this->attributes[ 'title' ] );
    }
  }

  protected function setExcerpt ( $str )
  {
		helper('text');

    $this->attributes[ 'excerpt' ] = empty( $str )
		? null
		: reduce_multiples( $str, ' ', true );
	}

	protected function setContent ( $str )
  {
		helper('text');

    $this->attributes[ 'content' ] = reduce_multiples( $str, ' ', true );
  }

	protected function setName ( $str )
  {
		$this->attributes['name'] = empty( $str )
		? config( '\BAPI\Config\Post' ) -> getSetting('db.fill.name')
		: $str;
  }

	protected function setGeneralRelation ( $data )
	{
		helper( 'array ');

		$kFirst = array_key_first( $data );
		$notValid = empty( $data ) || ! is_array( $data ) || ! is_string( $kFirst );

		if ( $notValid ) {
			return $this->attributes[ 'general_relation' ] = [];
		}

		$kFirstNext = array_key_first( $data[ $kFirst ] );

		$more = isset( $data[ $kFirst ][ $kFirstNext ][ 'id' ] )
		? (string) $data[ $kFirst ][ $kFirstNext ][ 'id' ]
		: null;

		$last = '0' === $more || ! ctype_digit( $more ) || $more < 0 ? [] : $data;

		$this->attributes[ 'general_relation' ] = $last;
	}

	// protected function setMediaRelationId ( $number )
  // protected function setNameId ( $number )
	// protected function setUserId ( $number )

	protected function setStatus ( $str )
	{
		$this->attributes['status'] = empty( $status )
		? config( '\BAPI\Config\Post' )->getSetting( 'db.fill.status' )
		: $str;
	}

	protected function setTypeof ( $str )
	{
		$this->attributes['typeof'] = empty( $str )
		? config( '\BAPI\Config\Post' )->getSetting( 'db.fill.typeof' )
		: $str;
	}
}