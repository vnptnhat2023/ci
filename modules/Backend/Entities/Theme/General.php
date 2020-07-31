<?php namespace BAPI\Entities\Theme;

class General extends \CodeIgniter\Entity
{
  protected $attributes = [];

  protected $datamap = [];

  protected function setAssetPath($str)
  {
    helper('text');
    $this->attributes['asset_path'] = reduce_multiples( $str, '/', true );
  }

  protected function setAuthor($str)
  {
    helper('text');
    $this->attributes['author'] = mb_strtolower( reduce_multiples( $str, ' ', true ) );
  }

  protected function setContent($str)
  {
    helper('text');
    $this->attributes['content'] = mb_strtolower( reduce_multiples( $str, ' ', true ) );
  }

  protected function setEmail($str)
  {
    $this->attributes['email'] = mb_strtolower($str);
  }

  protected function setName($str)
  {
    helper('text');
    $this->attributes['name'] = mb_strtolower( reduce_multiples( $str, ' ', true ) );
  }

  protected function setViewPath($str)
  {
    helper('text');
    $this->attributes['view_path'] = reduce_multiples( $str, '/', true );
  }

  protected function setVersion(float $num)
  {
    $this->attributes['version'] = $num;
  }

  protected function setSiteTitle($str)
  {
    helper('text');
    $this->attributes['site_title'] = mb_strtolower( reduce_multiples( $str, ' ', true ) );
  }

  protected function setSiteSlogan($str)
  {
    helper('text');
    $this->attributes['site_slogan'] = mb_strtolower( reduce_multiples( $str, ' ', true ) );
  }
}
