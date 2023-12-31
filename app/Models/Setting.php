<?php namespace App\Models;

use \CodeIgniter\Model;

class Setting extends Model
{
	protected $table = 'general_setting';

  protected $primaryKey = 'setting_name';
  protected $returnType = '\App\Entities\Setting'; // protected $returnType = 'object';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;
  protected $dateFormat = 'date';

  protected $allowedFields = ['setting_value', 'deleted_at'];
  protected $validationRules = [
    'setting_value' => 'trim|required|valid_json|min_length[5]|max_length[512]'
  ];

  private $cacheTTL = 0;

  protected $beforeUpdate = ['__beforeUpdate'];
  protected $beforeDelete = ['__beforeDelete'];

  public function changeDeleteKey()
  {
    $this->primaryKey = 'id';
  }

  public function setCreateRules()
  {
    $this->validationRules['setting_name'] =
    "min_length[5]|max_length[32]|alpha_dash|is_unique[{$this->table}.setting_name]";
    $this->allowedFields[] = 'setting_name';
  }

  public function setUpdateRules(string $id)
  {
    $this->validationRules['setting_name'] =
    "min_length[5]|max_length[32]|alpha_dash|is_unique[{$this->table}.setting_name,setting_name,{$id}]";
    $this->allowedFields[] = 'setting_name';
  }

  public function _find(string $id)
  {
    $cacheName = $this->_getCacheName($id);

    if ( cache($cacheName) )
    {
      return cache($cacheName);
    }
    else if ( $data = $this->select('id,setting_value')->find($id) )
    {
      $saved = $this->_saveCache( $id, $data->toArray() );
      return $saved;
    }
    else
    {
      return false;
    }
  }

  /**
   * Add prefix for id, default: SETTING_
   */
  public function _getCacheName(string $id, string $prefix = 'SETTING_') : string
  {
    return "{$prefix}{$id}";
  }

  /**
   * Add prefix for id and save it, prefix default: SETTING_
   */
  public function _saveCache(string $id, $data)
  {
    if ( empty($data) ) return $data;

    $cacheName = $this->_getCacheName($id);
    cache()->save( $cacheName, $data, $this->cacheTTL );

    return $data;
  }

  protected function __beforeUpdate(array $data) : array
  {
    cache()->delete( $this->_getCacheName( $data['id'] ) );
    return $data;
  }

  protected function __beforeDelete(array $data) : array
  {
    cache()->delete( $this->_getCacheName( $data['id'] ) );
    return $data;
  }
}
