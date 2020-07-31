<?php
namespace BAPI\Models\Setting;

use CodeIgniter\Model;

class Crud extends Model
{
	protected $table = 'extension_setting';
	protected $primaryKey = 'setting_name';

	protected $useTimestamps = true;
	protected $dateFormat = 'date';

	protected $useSoftDeletes = true;

	protected $afterInsert = ['__afterInsert'];
	protected $beforeUpdate = ['__beforeUpdate'];
	protected $beforeDelete = ['__beforeDelete'];


	public function changeDeleteKey()
  {
		$this->primaryKey = 'id';

		return $this;
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

	protected function __afterInsert(array $data) : array
  {
    cache()->delete( $this->_getCacheName( $data['id'] ) );
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