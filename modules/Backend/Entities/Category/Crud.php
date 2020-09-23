<?php namespace BAPI\Entities\Category;

class Crud extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [
    'ten' => 'title',
    'name' => 'title',
    'cat_name' => 'title',
    'sub' => 'parent_id',
    'description' => 'keyword',
    'page_cat' => 'name',
    'page_id' => 'name_id'
  ];

  /**
   * Fillable data for create
   */
  public function createFillable () : self
  {
		$fillable = config( '\BAPI\Config\Category' )
		->getSetting( 'db.fill' );

    foreach ( $fillable as $field => $value ) {
      $this->attributes[ $field ] ??= $value;
    }

    return $this;
  }

  protected function setSlug ( string $slug )
  {
		helper( [ 'url', 'text' ] );

		$slug = convert_accented_characters( $slug );

    $this->attributes[ 'slug' ] = url_title(
			$slug,
			config( '\BAPI\Config\Entities' )::slugSeparate,
			true
    );
  }

  protected function setTitle ( string $title )
  {
		helper( 'text' );

    $this->attributes[ 'title' ] = mb_strtolower(
      reduce_multiples( $title, ' ', true )
    );

    if ( empty( $this->attributes[ 'slug' ] ) ) {
      $this->setSlug( $this->attributes[ 'title' ] );
    }
  }

  protected function setParentId ( $pid )
  {
    $this->attributes[ 'parent_id' ] = (int) $pid;
  }

  protected function setKeyword ( $str )
  {
		helper( 'text' );

    $this->attributes[ 'keyword' ] = empty( $str )
		? null
		: reduce_multiples( $str, ' ', true );
  }

  protected function setSort ( $sid )
  {
    $this->attributes[ 'sort' ] = (int) $sid;
  }

  protected function setNameId ( $nameId )
  {
    $this->attributes[ 'name_id' ] = (int) $nameId;
  }

  protected function setIcon ( $icon )
  {
    $this->attributes[ 'icon' ] = mb_strtolower( $icon );
  }
}