<?php

namespace BAPI\Controllers\User;

use \BAPI\Controllers\Mixins\{ BAPITrait, UserCrudTrait };
use \CodeIgniter\RESTful\ResourceController;

class Crud extends ResourceController
{
  use BAPITrait, UserCrudTrait;

  protected $modelName = '\BAPI\Models\User\Crud';

  /**
	 * Model: __afterCreate
	 * @var \BAPI\Models\User\Detail $modelUserDetail
	 */
  private string $modelUserDetail = '\BAPI\Models\User\Detail';

  /**
	 * Entity: __beforeCreate, __beforeUpdate
	 * @var \BAPI\Entities\User\Crud $entityUserCrud
	 */
  private string $entityUserCrud = '\BAPI\Entities\User\Crud';

	# ==========================================================
	public function __construct()
	{
		$config = ( new \BAPI\Config\User() )->getSetting('db');

		$this->maximumCreate = $config[ 'create' ][ 'maximum_rows' ];
		$this->maximumUpdate = $config[ 'update' ][ 'maximum_rows' ];
		$this->maximumDelete = $config[ 'delete' ][ 'maximum_rows' ];
		$this->useSoftDelete = $config[ 'delete' ][ 'soft' ];
	}

  # ==========================================================
  public function index()
  {
		$this->ruleSearch[] = 'ruleSearch';

		$this->indexRun[] = '__indexRun';

    return $this->indexTrait(true);
  }

  # ==========================================================
  public function edit($id = null)
  {
		$this->editRun[] = '__bothShowEdit';

    return $this->_editShowTrait( $id, 'edit' );
  }

  # ==========================================================
  public function show($id = null)
  {
		$this->showRun[] = '__bothShowEdit';

    return $this->_editShowTrait( $id, 'show' );
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
  public function delete($id = null, bool $purge = false)
  {
    return $this->deleteTrait( $id, $purge );
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

}
