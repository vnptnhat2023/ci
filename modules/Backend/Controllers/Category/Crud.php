<?php namespace BAPI\Controllers\Category;

use \CodeIgniter\RESTful\ResourceController;
use \BAPI\Controllers\Mixins\{ BAPITrait, CategoryCrudTrait };

class Crud extends ResourceController
{
  use BAPITrait, CategoryCrudTrait;

  protected $modelName = '\BAPI\Models\Category\Crud';

	# --- Entity: create[before]
	/** @var \BAPI\Entities\Category\Crud $entityCategoryCrud */
  private string $entityCategoryCrud = '\BAPI\Entities\Category\Crud';

	# --- Model: create[before]
	/** @var \BAPI\Models\Page\Crud $modelPageCrud */
	private string $modelPageCrud = '\BAPI\Models\Page\Crud';

	/** @var \BAPI\Models\Post\Crud $modelPostCrud */
  private string $modelPostCrud = '\BAPI\Models\Post\Crud';

	# ==========================================================
	public function __construct()
	{
		$config = ( new \BAPI\Config\Category() )->getSetting('db');

		$this->maximumCreate = $config[ 'create' ][ 'maximum_rows' ];
		$this->maximumUpdate = $config[ 'update' ][ 'maximum_rows' ];
		$this->maximumDelete = $config[ 'delete' ][ 'maximum_rows' ];
		$this->useSoftDelete = $config[ 'delete' ][ 'soft' ];
	}

  # ==========================================================
  public function index()
  {
		$this->indexRun[] = '__indexRun';
    # When select category: [name = ca, name_id = 0] column name
    # Which other: [other = other, name_id > 0] column name;
		# Other = [page,category,post,cc]

    return $this->indexTrait();
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
  public function update($id = null, bool $undel = false)
  {
		$this->rulePut[] = 'rulePut';
		$this->rulePatch[] = 'rulePatch';
		$this->rulePatchUndelete[] = 'rulePatchUndelete';

		$this->beforeUpdate[] = '__beforeUpdate';

		# --- Set column name when checking $id exist
		$this->updateTemp = 'name,name_id,parent_id';

    return $this->updateTrait( $id, $undel );
  }

  # ==========================================================
  public function delete($id = null, bool $purge = false)
  {
    # Must write more for $purge, cause too much thing from "relation"
    return $this->deleteTrait( $id );
  }
}