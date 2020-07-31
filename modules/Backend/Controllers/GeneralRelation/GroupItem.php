<?php

namespace BAPI\Controllers\GeneralRelation;

use \CodeIgniter\RESTful\ResourceController;
use \BAPI\Controllers\Mixins\BAPITrait;

class GroupItem extends ResourceController
{
  use BAPITrait;

	protected $modelName = '\BAPI\Models\GeneralRelation\GroupItem';

	/** @var \BAPI\Models\GeneralGroup\Crud $modelGG */
	protected string $modelGG = '\BAPI\Models\GeneralGroup\Crud';

	/** @var \BAPI\Models\GeneralItem\Crud $modelGI */
	protected string $modelGI = '\BAPI\Models\GeneralItem\Crud';


	# ==========================================================
	public function __construct()
	{
		// $config = config('\BAPI\Config\GeneralItem')->getSetting('db');

		// $this->maximumCreate = $config[ 'create' ][ 'maximum_rows' ];
		// $this->maximumUpdate = $config[ 'update' ][ 'maximum_rows' ];
		// $this->maximumDelete = $config[ 'delete' ][ 'maximum_rows' ];
	}

	private function __indexSelectQuery() : array
	{
		$model = $this->model;

		$indexSelectQuery = [
			'Item.id as item_id',
			'Item.title as item_title',
			'Item.slug as item_slug',
			'Item.status as item_status',
			'Group.id as group_id',
			'Group.title as group_title',
			'Group.slug as group_slug',
			"{$model->table}.{$model->primaryKey} as rel_id",
			"{$model->table}.name as rel_name",
			"{$model->table}.name_id as rel_name_id", # maybe store [ group_id : value ]
			// 'Group.status as group_status'
		];

		return $indexSelectQuery;
	}

  public function index()
  {
		$this->ruleSearch[] = 'ruleSearch';

		$this->indexRun[] = '__indexRun';

		# When search using table eliasName: ?name=ex&ggid=1&page=2
		$except = [ 'name', 'name_id', 'ggid' ];

    return $this->indexTrait( true, $except, 'where', [], false );
  }

  private function __indexRun(array $data) : array
  {
		# --- Ex: GetPost $name = ca; name_id = 1; $ggid = 2
		$model = $this->model;

		# --- Model relation
		$modelGG = new $this->modelGG();

		# --- Model relation
    $modelGI = new $this->modelGI();

		$validation = \Config\Services::validation();

		# --- Model method
		$rules = $model->ruleIndex();

    $rawArray = $this->request->getGet();

    if ( ! $validation->setRules( $rules )->run( $rawArray ) ) {
      return [ 'error' => $validation->getErrors() ];
    }

		# --- Model arguments
    $whereQuery = [
      "{$model->table}.name" => $rawArray['name'],
      "{$model->table}.ggid" => $rawArray['ggid'],
    ];

    if ( isset( $rawArray[ 'name_id' ] ) && ( $rawArray[ 'name_id' ] > 0 ) ) {
      # --- name = ca, name_id will be id of category
      $whereQuery[ "{$model->table}.name_id" ] = $rawArray['name_id'];
    }

		$config = config('\BAPI\Config\GeneralRelation');

    $limSortQuery = [
      'orderBy' => 'Item.' . $config->setting('fetch.orderBy'),
      'direction' => 'Item.' . $config->setting('fetch.direction'),
      'limit' => $config->setting('fetch.record')
		];

		# --- Fetch data
    $relationData = $model->select( implode( ',', $this->__indexSelectQuery() ) )

		->join( "{$modelGG->table} as Group", "Group.{$modelGG->primaryKey} = {$model->table}.ggid" )
		->join( "{$modelGI->table} as Item", "Item.{$modelGI->primaryKey} = {$model->table}.giid" )

		->where( $whereQuery )

		->orderBy( $limSortQuery['orderBy'], $limSortQuery['direction'] )
		->paginate( $limSortQuery['limit'] );

    $data = [
			'data' => $relationData ?: [],

      'pager' => [
				'currentPage' => $this->model->pager->getcurrentPage(),

        'pageCount' => $this->model->pager->getpageCount()
      ]
    ];

    return $data;
  }
}