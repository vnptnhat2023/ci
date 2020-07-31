<?php

# --- Todo: create the RELATIONS before create the POST
# --- Todo: select max_id ...
namespace BAPI\Controllers\Post;

use \BAPI\Controllers\Mixins\{ BAPITrait, PostCrudTrait };
use \CodeIgniter\RESTful\ResourceController;

class Crud extends ResourceController
{
  use BAPITrait, PostCrudTrait;

  protected $modelName = '\BAPI\Models\Post\Crud';

	/**
	 * @var \BAPI\Models\GeneralRelation\GroupItem $modelCatCRelation
	 * @as __afterCreate
	 * @as __afterUpdate
	 */
	private string $modelGeneralRelation = '\BAPI\Models\GeneralRelation\GroupItem';

	/**
	 * @var \BAPI\Models\GeneralGroup\Crud $modelGeneralGroup
	 */
	private string $modelGeneralGroup = '\BAPI\Models\GeneralGroup\Crud';

	/**
	 * @var \BAPI\Models\GeneralItem\Crud $modelGeneralItem
	 */
	private string $modelGeneralItem = '\BAPI\Models\GeneralItem\Crud';

	/**
	 * @var \BAPI\Models\Category\MediaInfo $modelCatMediaInfo
	 */
	private string $modelMedia = '\BAPI\Models\Media\Crud';

	/**
	 * @var \BAPI\Models\Category\Media $modelCatMedia
	 */
  private string $modelMediaRelation = '\BAPI\Models\Media\Relation';

	/**
	 * @var \BAPI\Entities\Post\Crud $entityPostCrud
	 * @as __beforeCreate
	 * @as __beforeUpdate
	 */
  private string $entity = '\BAPI\Entities\Post\Crud';

	# ==========================================================
	public function __construct()
	{
		$config = ( new \BAPI\Config\Post() ) ->getSetting( 'db' );

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

    return $this->indexTrait( true );
  }

	# ==========================================================
  public function create()
  {
		# Take rules from model
		$this->ruleCreate[] = 'ruleCreate';

		# Before create
		$this->beforeCreate[] = '__beforeCreate';
		$this->beforeCreate[] = '__createGeneralRelation';
		$this->beforeCreate[] = '__createMediaRelation';

		# After create
  	$this->afterCreate[] = '__afterCreate';

    return $this->createTrait();
  }

  # ==========================================================
  public function update ( $id = null, bool $unDelete = false )
  {
		$this->rulePut[] = 'rulePut';
		$this->rulePatch[] = 'rulePatch';

		$this->beforeUpdate[] = '__beforeUpdate';
  	$this->afterUpdate[] = '__afterUpdate';

    return $this->updateTrait ( $id, $unDelete );
	}

	# ==========================================================
  public function delete ( $id = null, bool $purge = false )
  {
    return $this->deleteTrait( $id, $purge );
  }

  # ==========================================================
  public function show ( $id = null )
  {
		$this->showRun[] = '__bothShowEdit';

    return $this->_editShowTrait( $id, 'show' );
  }

  # ==========================================================
  public function edit ( $id = null )
  {
		$this->editRun[] = '__bothShowEdit';

    return $this->_editShowTrait( $id, 'edit' );
  }
}