<?php

namespace BAPI\Entities\GeneralItem;

class Crud extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [
    'cat_custom_id' => 'ggid'
  ];

  public function createFillable() : \Codeigniter\Entity
  {
		$config = ( new \BAPI\Config\GeneralItem() );

    $this->attributes['status'] ??= $config->getSetting('db.fill.status');

    return $this;
  }

  protected function setSlug(string $slug)
  {
    helper( [ 'url', 'text' ] );
    $slug = convert_accented_characters($slug);
    $this->attributes['slug'] = url_title(
      $slug, config('\BAPI\Config\Entities')::slugSeparate, true
    );
  }

  protected function setTitle(string $title)
  {
    helper( [ 'text' ] );
    $this->attributes['title'] = mb_strtolower(
      reduce_multiples( $title, ' ', true )
    );

    if ( empty( $this->attributes['slug'] ) ) {
      $this->setSlug( $this->attributes['title'] );
    }
  }

}