<?php

namespace BAPI\Controllers\GeneralItem;

use \CodeIgniter\RESTful\ResourceController;
use \BAPI\Controllers\Mixins\BAPITrait;


class Crud extends ResourceController
{
  use BAPITrait;

  protected $modelName = '\BAPI\Models\GeneralItem\Crud';

	/**
	 * Entity for create[before]
	 * @var \BAPI\Entities\GeneralItem\Crud $entityGICrud
	 */
  private string $entityGICrud = '\BAPI\Entities\GeneralItem\Crud';

	/**
	 * Model: update[before], check relation exist before update
	 * @var \BAPI\Models\GeneralRelation\GroupItem $modelGrCrud
	 */
  private string $modelGrCrud = '\BAPI\Models\GeneralRelation\GroupItem';

	# ==========================================================
	public function __construct()
	{
		$config = ( new \BAPI\Config\GeneralItem() )->getSetting('db');

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
  public function delete( $id = null, bool $purge = false )
  {
    return $this->deleteTrait($id, $purge);
  }

  # ==========================================================
  public function update( $id = null, bool $unDelete = false )
  {
		$this->rulePut[] = 'rulePut';
		$this->rulePatch[] = 'rulePatch';
		$this->rulePatchUndelete[] = 'rulePatchUndelete';

		$this->beforeUpdate[] = '__beforeUpdate';

		return $this->updateTrait($id, $unDelete);
  }

  # __________________________________________________________
  private function __beforeCreate(array $data) : array
  {
		$entity = new $this->entityGICrud( $data['data'] );

		$data['data'] = $entity->createFillable()->toRawArray();

    return $data;
  }

  # __________________________________________________________
  private function __afterCreate(array $data) : array
  {
    return [ 'success' => $data['id'] ];
  }

  # __________________________________________________________
  private function __beforeUpdate(array $data) : array
  {
		# For patch case use: ___updateMultiExist
		if ( $data['method'] === 'put' ) {

      $findRelation = ( new $this->modelGrCrud() )
			->select('1')
			->where( 'ggid', $data['id'] )
			->find();

      if ( ! $findRelation ) {
				$errArgs = [ 'field' => 'General item id' ];

        return [ 'error' => lang( 'Validation.is_not_unique', $errArgs ) ];
      }
    }

		$entity = new $this->entityGICrud( $data['data'] );

		$data['data'] = $entity->toRawArray();

    return $data;
  }
}