<?php

namespace BAPI\Entities\Extension;

class Crud extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [];

  public function createFillable() : \Codeigniter\Entity
  {
		return $this;
		// $config = ( new \BAPI\Config\GeneralGroup() );

    // $this->attributes['status'] ??= $config->getSetting('db.fill.status');

    // return $this;
  }

  // protected function setSlug(string $slug)
  // {
	// 	helper( [ 'url', 'text' ] );

	// 	$slug = convert_accented_characters($slug);

  //   $this->attributes['slug'] = url_title(
  //     $slug, config('\BAPI\Config\Entities')::slugSeparate, true
  //   );
  // }

  // protected function setTitle(string $title)
  // {
	// 	helper( [ 'text' ] );

  //   $this->attributes['title'] = mb_strtolower(
  //     reduce_multiples( $title, ' ', true )
  //   );

  //   if ( empty( $this->attributes['slug'] ) ) {
  //     $this->setSlug( $this->attributes['title'] );
  //   }
  // }

}