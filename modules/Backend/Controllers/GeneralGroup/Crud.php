<?php

namespace BAPI\Controllers\GeneralGroup;

use \CodeIgniter\RESTful\ResourceController;
use \BAPI\Controllers\Mixins\BAPITrait;


class Crud extends ResourceController
{
  use BAPITrait;

  protected $modelName = '\BAPI\Models\GeneralGroup\Crud';

	/**
	 * Entity for create[before]
	 * @var \BAPI\Entities\GeneralGroup\Crud $entityGGCrud
	 */
  private string $entityGGCrud = '\BAPI\Entities\GeneralGroup\Crud';

	# ==========================================================
	public function __construct()
	{
		$config = ( new \BAPI\Config\GeneralGroup() )->getSetting('db');

		$this->maximumCreate = $config[ 'create' ][ 'maximum_rows' ];
		$this->maximumUpdate = $config[ 'update' ][ 'maximum_rows' ];
		$this->maximumDelete = $config[ 'delete' ][ 'maximum_rows' ];
		$this->useSoftDelete = $config[ 'delete' ][ 'soft' ];
	}

  # ==========================================================
  public function create()
  {
		$this->ruleCreate[] = 'ruleCreate';

		$this->beforeCreate[] = '__beforeCreate';
  	$this->afterCreate[] = '__afterCreate';

    return $this->createTrait();
  }

  # ==========================================================
  public function delete( $id = null )
  {
    return $this->deleteTrait( $id );
  }

  # ==========================================================
  public function update( $id = null, bool $unDelete = false )
  {
		$this->rulePut[] = 'rulePut';
		$this->rulePatch[] = 'rulePatch';
		$this->rulePatchUndelete[] = 'rulePatchUndelete';

		$this->beforeUpdate[] = '__beforeUpdate';

    return $this->updateTrait( $id, $unDelete );
  }

  # __________________________________________________________
  private function __beforeCreate(array $data) : array
  {
    $entity = new $this->entityGGCrud( $data['data'] );
    $data['data'] = $entity->createFillable()->toRawArray();

    return $data;
  }

  # __________________________________________________________
  private function __afterCreate(array $data) : array
  {
    return [ 'success' => $data['id'] ];
  }

  private function __beforeUpdate(array $data) : array
  {
    $entity = new $this->entityGGCrud( $data['data'] );
    $data['data'] = $entity->toRawArray();
    return $data;
  }

}
