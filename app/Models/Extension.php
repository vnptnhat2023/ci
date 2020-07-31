<?php namespace App\Models;

use CodeIgniter\Model;

class Extension extends Model
{
  protected $returnType = 'array';
  protected $table = 'extension';

  protected $useSoftDeletes = true;
  protected $useTimestamps = true;
  protected $dateFormat = 'date';

  public function enabled(bool $cache = true)
  {
    $cacheId = config('Extension')->getSetting( 'cache.name' );
    $cacheName = $this->_getCacheName($cacheId);
    if ( cache($cacheName) ) { return cache($cacheName); }

		$queryWhere = [
			"{$this->table}.status" => 'enable',
			'Gr.name' => 'ex',
			'Gg.status' => 'active',
			'Gg.slug' => 'extension',
			'Gi.status' => 'active',
    ];
    $querySelect = "{$this->table}.slug,
    Gi.slug as event_name,Gi.title as method";

		# Gg.title as groupTitle,Gg.slug as groupSlug,
		$data = $this
		->select($querySelect)
		->join('general_relation Gr', "Gr.name_id = {$this->table}.id")
		->join('general_group Gg', 'Gg.id = Gr.ggid')
		->join('general_item Gi', 'Gi.id = Gr.giid')
		->where( $queryWhere )
    ->findAll();

    if ( $data )
    {
			$data = [
				'full' => $data,
				'uniqueClass' => array_flip( array_column( $data, 'slug' ) )
			];
      if ( $cache === true ) { $data = $this->_saveCache( $cacheId, $data ); }

      return $data;
    }
    else { return null; }
  }

	/** WARNING: this method is NOT using ... delete late */
  public function _findByEvent(string $eventName, $cache = true)
  {
    $cacheName = $this->_getCacheName($eventName);
    $findWHere = [
      'event_name' => $eventName,
      'name' => 'ex',
      'status' => 'enable'
    ];

    if ( cache($cacheName) ) { return cache($cacheName); }
    else if ( $data = $this->select('slug')->where($findWHere)->findAll() )
    {
      if ( $cache === true ) { $data = $this->_saveCache( $eventName, $data ); }

      return $data;
    }
    else { return null; }
  }

  public function _getCacheName(string $eventName) : string
  {
		$prefix = config('Extension')->getSetting( 'cache.prefix' );

    return $prefix . "{$eventName}";
  }

  public function _saveCache(string $eventName, $data)
  {
    if ( empty($data) ) return $data;

		$cacheName = $this->_getCacheName($eventName);
		$timeToLife = config('Extension')->getSetting( 'cache.ttl' );

    cache()->save( $cacheName, $data, $timeToLife );

    return $data;
	}

	public function _deleteCache(string $eventName) : bool
	{
		return cache()->delete( $this->_getCacheName( $eventName ) );
	}
}