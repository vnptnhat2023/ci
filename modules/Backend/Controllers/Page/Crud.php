<?php

namespace BAPI\Controllers\Page;

use \CodeIgniter\RESTful\ResourceController;
use \BAPI\Controllers\Mixins\BAPITrait;
use CodeIgniter\Events\Events;

class Crud extends ResourceController
{
  use BAPITrait;

  protected $modelName = '\BAPI\Models\Page\Crud';

	/**
	 * Entity: create[before]
	 * @var \BAPI\Entities\Page\Crud @entityPageCrud
	 */
  private string $entityPageCrud = '\BAPI\Entities\Page\Crud';

	# ==========================================================
	public function __construct()
	{
		$config = ( new \BAPI\Config\Page() )->getSetting('db');

		$this->maximumCreate = $config[ 'create' ][ 'maximum_rows' ];
		$this->maximumUpdate = $config[ 'update' ][ 'maximum_rows' ];
		$this->maximumDelete = $config[ 'delete' ][ 'maximum_rows' ];
		$this->useSoftDelete = $config[ 'delete' ][ 'soft' ];
	}

  # ==========================================================
  public function index()
  {
		$this->indexRun[] = '__indexRun';

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
  public function delete($id = null, bool $purge = false)
  {
		$this->beforeDelete[] = '__beforeDelete';

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
  private function __indexRun(array $data) : array
  {
		$selectQuery = [ 'id', 'title', 'slug', 'parent_id', 'icon, sort' ];

    $page = $this->model->select( implode( ',', $selectQuery ) )->findAll( 100 );
    if ( ! $page) { return []; }

    helper('array');

    Events::trigger( 'extStore', 'afterPageQuery', $page );

    return [
      'select_option' => $page,
      'data' => buildTree($page)
    ];
  }

  # __________________________________________________________
  private function __beforeCreate(array $data) : array
  {
		$entity = new $this->entityPageCrud( $data['data'] );

    $data['data'] = $entity->createFillable()->toRawArray();

    if ( ! empty( $data[ 'data' ][ 'parent_id' ] )  ) {

      $Pid  = $data[ 'data' ][ 'parent_id' ];

      if ( ( $Pid > 0 ) && ( null === $this->model->select('1')->find( $Pid ) ) ) {
				$errStr = lang( 'Validation.is_not_unique', [ 'field' => 'Parent Id' ] );

				return [ 'error' => $errStr ];
      }
    }

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
    $data['data'] = (  new $this->entityPageCrud( $data['data'] ) )->toRawArray();

    if ( $data['method'] === 'patch' ) { return $data; }

    if ( ! empty( $data[ 'data' ][ 'parent_id' ] )  ) {
      $Pid  = $data[ 'data' ][ 'parent_id' ];

      if ( $data['id'] == $Pid ) {
        return [ 'error' => lang('Page.errorChildrenItself') ];
      }

      if ( ( $Pid > 0 ) && ( null === $this->model->select('1')->find( $Pid ) ) ) {
				$errStr = lang( 'Validation.is_not_unique', [ 'field' => 'Parent Id' ] );

        return [ 'error' => $errStr ];
      }
    }

    return $data;
  }

  # __________________________________________________________
  private function __beforeDelete(array $data) {
    if ( false === $data['purge'] ) {
      return $data;
		}

    # --- Todo: should i using foreignKey or php?
    die(__LINE__ . PHP_EOL . __FILE__ . PHP_EOL . ' Need write more ...');
  }

}
