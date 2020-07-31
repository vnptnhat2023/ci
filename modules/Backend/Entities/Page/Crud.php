<?php

namespace BAPI\Entities\Page;

class Crud extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [
    'name' => 'title',
    'cat_name' => 'title',
    'sub' => 'parent_id'
  ];

  /**
   * Fillable data for create
   */
  public function createFillable() : self
  {
		$config = (new \BAPI\Config\Page() );

    foreach ( $config->getSetting('db.fill') as $field => $value) {
      $this->attributes[ $field ] ??= $value;
    }

    return $this;
  }

  protected function setSlug($slug)
  {
		helper( [ 'url', 'text' ] );

		$slug = convert_accented_characters($slug);

    $this->attributes['slug'] = url_title(
      $slug, config('\BAPI\Config\Entities')::slugSeparate, true
    );
    # Có lỗi ở chữ Đ đoạn nhưng mà cũng ok thôi
    # co-loi-o-chu-dj-djoan-nhung-ma-cung-ok-thoi
    # Error [ Đ => dj ]
  }

  protected function setTitle($title)
  {
		helper( [ 'text' ] );

    $this->attributes['title'] = mb_strtolower(
      reduce_multiples( $title, ' ', true )
    );

    if ( empty( $this->attributes['slug'] ) ) {
      $this->setSlug( $this->attributes['title'] );
    }
  }

  protected function setParentId($pid)
  {
    $this->attributes['parent_id'] = (int) $pid;
  }

  protected function setContent($str)
  {
		helper('text');

    $this->attributes['content'] = empty($str)
    ? null : reduce_multiples( $str, ' ', true );
  }

  protected function setAdvancedContent($str)
  {
		helper('text');

    $this->attributes['advanced_content'] = empty($str)
    ? null : reduce_multiples( $str, ' ', true );
  }

  protected function setSort($sid)
  {
    $this->attributes['sort'] = (int) $sid;
  }
}