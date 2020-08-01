<?php

namespace BAPI\Controllers\Mixins;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Exceptions\ModelException;


/**
 * @method array ___updateMultiExist() Callable count instead of multiPatch do default
 */

trait BAPITrait
{
	# Args: 3
	private array $searchValueRequire = [
		'whereIn' => null,
		'whereNotIn' => null,
		'havingIn' => null,
		'havingNotIn' => null
	];

	# Args: 5
	private array $searchValueNotRequire = [
		'having' => null,# Args: 3
		'notHavingLike' => null,
		'havingLike' => null,
		'notLike' => null,
		'like' => null,
		'where' => null# Args: 3
	];

	private array $searchStoreMethod = [
		'having' => true,
		// 'groupBy' => null,
		'notHavingLike' => true,
		'havingLike' => true,
		'notLike' => null,
		'like' => null,
		'where' => null,
		'whereIn' => null,
		'whereNotIn' => null,
		'havingIn' => true,
		'havingNotIn' => true
	];

	/**
	 * @var string $returnTrait array | object
	 * @default: object When object => responseTrait::class
	 */
	protected string $returnTrait = 'object';

	/**
	 * @var boolean $protectFirstId
	 * @default: true, protected for $id = 1
	 */
	protected bool $protectFirstId = true;

	protected ?bool $useSoftDelete = null;

	/**
	 * @var mixed $updateTemp
	 * Is the name of the column, if it exists, will be selected
	 * from the database instead of the default
	 *
	 * ```$this->updateTemp = 'title, slug';```
	 * @return mixed array | string ( isset ? array : string )
	 */
	protected $updateTemp;

	/**
	 * @var mixed $deleteTemp
	 * Is the name of the column, if it exists, will be selected
	 * from the database instead of the default
	 *
	 * ```$this->deleteTemp = 'title, slug';```
	 * @return mixed array | string ( isset ? array : string )
	 */
	protected $deleteTemp;

	/**
	 * @var int $maximumCreate Maximum number of rows can be update
	 * @default 100
	 */
	protected int $maximumCreate = 1;

	/**
	 * @var int $maximumUpdate Maximum number of rows can be update
	 * @default 100
	 */
	protected int $maximumUpdate = 1;

	/**
	 * @var int $maximumDelete Maximum number of rows can be delete
	 * @default 100
	 */
	protected int $maximumDelete = 1;

	# --- Index
	/**
	 * **Controller** method
	 * @var array $indexRun
	 */
	protected array $indexRun = [];

	# --- Model
	/**
	 * **Model** method
	 * @var array $ruleSearch
	 * @return array rules
	 */
	protected array $ruleSearch = [];

	# --- Create
	/**
	 * **Controller** method
	 * @var array $beforeCreate
	 */
	protected array $beforeCreate = [];

	/**
	 * **Controller** method
	 * @var array $afterCreate
	 */
	protected array $afterCreate = [];

	/**
	 * **Controller** method
	 * @var array $beforeValidateCreate
	 */
	protected array $beforeValidateCreate = [];

	/**
	 * **Controller** method
	 * @var array $afterValidateCreate
	 */
	protected array $afterValidateCreate = [];

	# Model
	/**
	 * **Model** method
	 * @var array $ruleCreate
	 * @return array rules
	 */
	protected array $ruleCreate = [];

	# --- Delete
	/**
	 * **Controller** method
	 * @var array $beforeDelete
	 */
	protected array $beforeDelete = [];

	/**
	 * **Controller** method
	 * @var array $afterDelete
	 */
	protected array $afterDelete = [];

	# --- Update
	/**
	 * **Controller** method
	 * @var array $beforeUpdate
	 */
	protected array $beforeUpdate = [];

	/**
	 * **Controller** method
	 * @var array $beforeValidateUpdate
	 */
	protected array $beforeValidateUpdate = [];

	/**
	 * **Controller** method
	 * @var array $afterValidateUpdate
	 */
	protected array $afterValidateUpdate = [];

	/**
	 * **Controller** method
	 * @var array $afterUpdate
	 */
	protected array $afterUpdate = [];

	/**
	 * **Controller** method
	 * @var array $beforeSinglePut
	 */
	protected array $beforeSinglePut = [];

	/**
	 * **Controller** method
	 * @var array $beforeMultiPatch
	 */
	protected array $beforeMultiPatch = [];
	// protected function ___updateMultiExist(){}

	/**
	 * **Controller** method
	 * @var array $beforeSinglePatch
	 */
	protected array $beforeSinglePatch = [];

	# Model
	/**
	 * **Model** method
	 * @var array $rulePut
	 * @return array rules
	 */
	protected array $rulePut = [];

	/**
	 * **Model** method
	 * @var array $rulePatchUndelete
	 * @return array rules
	 */
	protected array $rulePatchUndelete = [];

	/**
	 * **Model** method
	 * @var array $rulePatch
	 * @return array rules
	 */
	protected array $rulePatch = [];

	# --- Show && Edit
	/**
	 * **Controller** method
	 * @var array $showRun
	 */
	protected array $showRun = [];

	/**
	 * **Controller** method
	 * @var array $editRun
	 */
	protected array $editRun = [];

	private function _searchTraitChunk ( array $data, string $methodName, array $params )
	{
		# --- [ title => Something great <^,^'> !Meow! ]
		if ( array_key_exists( $methodName, $this->searchValueRequire ) ) {
			$errStr = lang( 'Api.errorIncorrectMethodArgsArray', [ $methodName ] );

			return [ 'error' => $errStr ];
		}

		$data = $data[0] ?? $data;
		$this->model->$methodName( $data, ...$params );

		return $data;
	}

  /**
	 * Read about the indexTrait method
   */
  private function _searchTrait (
    array $except = [],
    string $methodName = 'like',
		array $params = [],
		bool $withOr = true
  ) : array {

    $req = \Config\Services::request()->getGet();
    if ( $reqCount = count( $req ) ) {
      helper('array');
			$except[] = 'page';

			if ( ! array_key_exists( $methodName, $this->searchStoreMethod ) ) {
				return [ 'error' => lang('Api.errorNotSupportMethod', [ $methodName ]) ];
			}

      if ( $reqCount > 1 )
      {
        foreach ( $req as $fieldName => $value ) {
          if ( in_array( $fieldName, $except, true ) )
          {
            continue;
          }
          else if ( false === strpos( $fieldName, '-' ) )
          {
						$data[] = [ $fieldName => $value ];
          }
          else
          {
            $keyFirstLast = \array_key_first_last( explode( '-', $fieldName ) );
            $data[] = [ "{$keyFirstLast[ 'first' ]}.{$keyFirstLast[ 'last' ]}" => $value ];
          }
				}
      }
      else
      {
        $fieldName = array_key_first($req);
        if ( in_array( $fieldName, $except, true ) ) { return []; }
        $data = $req;
        $value = $req[ $fieldName ];

        if ( false !== strpos( $fieldName, '-' ) ) {
					# --- First = table-name; Last = field-name
          $keyFirstLast = \array_key_first_last( explode( '-', $fieldName ) );
          $data = [ "{$keyFirstLast[ 'first' ]}.{$keyFirstLast[ 'last' ]}" => $value ];
        }
      }

      $modelRules = $this->triggerModel( 'ruleSearch', [] );
			$rules = $this->_findRules( $req, $modelRules, $except );

      if ( 0 === count( $rules ) )
      {
				$errArray = [ 'error' => 'Forget the search rule' ];

        if ( $reqCount <= count( $except ) ) {
          $exceptCounter = 0;

          foreach ( $except as $value ) {
            if ( array_key_exists( $value, $req ) ) {
              $exceptCounter++;
            }
          }
          return $exceptCounter >= $reqCount ? [] : $errArray;
        }

        return $errArray;
      }
      else if ( isset( $rules['error'] ) )
      {
        return [ 'error' => $rules['error'] ];
      }
      else if ( ! service('validation')->setRules($rules)->run($req) )
      {
        return [ 'error' => service('validation')->getErrors() ];
      }
      else if ( count($data) >= 2 )
      {
				if ( true === $this->searchStoreMethod[ $methodName ] ) {
					$groupBy = $this->groupBy ?: str_replace( ['.', '*'], '', array_keys($rules) );
					$this->model->groupBy( $groupBy );
				}

				$methodName = ( $withOr === true ) ? 'or' . ucfirst($methodName) : $methodName;

				foreach ( $data as $key => $value ) {

					if ( is_array( $value ) AND ( count($value) >= 2 ) )
					{
						# --- Will never meet these cases, because has been break in method:
						# --- [_findRules] line: if ( isAssoc( $value ) )
						foreach ( $value as $deepKey => $deepValue ) {
							if ( is_array( $deepValue ) AND ! isAssoc( $value ) ) {
								$this->model->$methodName( $deepKey, $deepValue, ...$params );
							} else { break 2; }
						}
					}
					else
					{
						$realFieldName = array_key_first($value);
						$realValue =  $value[ $realFieldName ];

						if ( is_array( $realValue ) )
						{
							# --- [ id => [ 0, 1, 2, ... ] ]
							if ( array_key_exists( $methodName, $this->searchValueNotRequire ) ) {
								$errStr = lang( 'Api.errorIncorrectMethodArgsNotArray', [ $methodName ] );

								return [ 'error' => $errStr ];
							}

							foreach ( $value as $deepKey => $deepValue ) {
								if ( is_array( $deepValue ) AND ! isAssoc( $deepValue ) ) {
									$this->model->$methodName( $deepKey, $deepValue, ...$params );
								} else { break 2; }
							}
						}
						else
						{
							$value = $this->_searchTraitChunk( $value, $methodName, $params );
							if ( isset( $value[ 'error' ] ) ) { return $value; }

							/*
								if ( array_key_exists( $methodName, $this->searchValueRequire ) ) {
									return [ 'error' => lang( 'Api.errorIncorrectMethodArgsArray', [ $methodName ] ) ];
									break;
								}

								$value = $value[0] ?? $value;
								$this->model->$methodName( $value, ...$params );
							*/
						}
					}
				}

        return $data;
      }
      else
      {
				$key = array_key_first($data);
				$value =  $data[ $key ];

				if ( true === $this->searchStoreMethod[ $methodName ] ) {
					$groupBy = $this->groupBy ?: str_replace( ['.', '*'], '', array_keys($rules) );
					$this->model->groupBy( $groupBy );
				}

				if ( is_array( $value ) )
				{
					if ( $key === 0 || ctype_digit( $key ) ) {
						$key = array_key_first( $value );
						$value = $value[ $key ];
					}

					if ( is_array( $value ) )
					{
						# --- [ id => [ 0, 1, 2, ... ] ]
						if ( array_key_exists( $methodName, $this->searchValueNotRequire ) ) {
							$errStr = lang( 'Api.errorIncorrectMethodArgsNotArray', [ $methodName ] );

							return [ 'error' => $errStr ];
						}

						$this->model->$methodName( $key, $value, ...$params );
					}
					else
					{
						$data = $this->_searchTraitChunk( $data, $methodName, $params );

						if ( isset( $data[ 'error' ] ) ) { return $data; }
					}
				}
				else
				{
					$data = $this->_searchTraitChunk( $data, $methodName, $params );

					if ( isset( $data[ 'error' ] ) ) { return $data; }
				}

        return $data;
      }
    }

    return [];
  }

  /**
   * Run some methods before fetching data
   * @param boolean $search
   * @param array $except what data does not need validation, Default skip [page]
   * @param string $methodName like, where, whereIn, ...
	 * @param array $params start with 3rd argument of the $methodName ( in model CI system );
	 * @param boolean $withOr default true: when $MethodName = 'where', with multiple => orWhere
   * @return ResponseTrait|array
	 * @example array $params with like method [ true, true, true ]
	 * @example array $params will be ($side = 'true, $escape = true, $insensitiveSearch = true)
   * @eventController indexRun, return empty[]
   * @eventModel ruleSearch, (Elias first if exist | table_name)-fieldName
   */
  protected function indexTrait (
    bool $search = false,
    array $except = [],
		string $methodName = 'like',
		array $params = [],
    bool $withOr = true
  ) {
		# Can use many "Method" searches like here ...
    if ( $search ) {
      $search = $this->_searchTrait( $except, $methodName, $params, $withOr );
      if ( isset( $search['error'] ) ) { return $this->resErr( $search['error'] ); }
		}

    $data = $this->trigger( 'indexRun', [] );
    if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

    return $this->res( $data ?: [], null, '' );
  }

  /**
   * Event-controller: beforeCreate, afterCreate - return []
   * @beforeCreate [ array data ]
   * @afterCreate  [ [ string | int ] id, array data ]
   * @return mixed ResponseTrait | array; Prop: returnTypeTrait, default: ResponseTrait
   */
  protected function createTrait ()
  {
    if ( 0 === count( $this->request->getPost() ) ) {
      return $this->resErr( lang( 'api.errorEmptyData' ) );
    }

		$beforeCreateData = [ 'data' => $this->request->getPost() ];
		# Triggering before create
		$data = $this->trigger( 'beforeCreate', $beforeCreateData );
    if ( isset( $data[ 'error' ] ) ) return $this->resErr( $data[ 'error' ] );

		# Takes rules
		$rules = $this->triggerModel( 'ruleCreate', $data );

		# Triggering before validate create
		$data = $this->trigger( 'beforeValidateCreate', $data );
		if ( isset( $data[ 'error' ] ) ) return $this->resErr( $data[ 'error' ] );

		# Handle validation
    if ( ! service( 'validation' )->setRules( $rules )->run( $data[ 'data' ] ) ) {
      return $this->resErr( service( 'validation' )->getErrors() );
		}

		# Triggering after validate create
		$data = $this->trigger( 'afterValidateCreate', $data );
		if ( isset( $data['error'] ) ) return $this->resErr( $data[ 'error' ] );

		if ( ! $id = $this->model->insert( $data[ 'data' ] ) )
    {
      return $this->resErr( $this->model->errors() );
    }
    else
    {
			$afterCreatedArgs = [ 'id' => $id, 'data' => $data[ 'data' ] ];

			# Triggering after create
			$data = $this->trigger( 'afterCreate', $afterCreatedArgs );
      if ( isset( $data[ 'error' ] ) ) return $this->resErr( $data[ 'error' ] );

			$responseArgs = [
				( $data[ 'success' ] ?? null ),
				lang( 'api.createSuccess' )
			];

      return $this->res( $responseArgs, 'respondCreated' );
    }
  }

  /**
   * In the "Router" make sure "placeholder" checked type.
   * @param string $id placeholder ":num|:dotID" see more in \Config\Routes
   * @param bool $purge default: false
   * @return ResponseTrait|array Prop: returnTypeTrait, default: ResponseTrait
   * @evenController beforeDelete | afterDelete, return [array id, bool purge]
   * @property protectFirstId default: true
   * @property maximumDelete default: 1
   */
  protected function deleteTrait ( $id = null, bool $purge = false )
  {
    if ( ! $id ) {
			$resErr = lang( 'Validation.required', [ 'field' => 'id' ] );

			return $this->resErr( $resErr );
		}

		if ( is_bool( $this->useSoftDelete ) && $this->model->useSoftDeletes !== $this->useSoftDelete ) {
			$str = 'Declaration of ' . $this->modelName . '::useSoftDelete ';
			$str .= 'must be set to ' . ( $this->useSoftDelete ? 'true' : 'false' );
			throw new ModelException( $str );
		}

		# --- Format id to String-Array [ '1', '2', '3' ]
		$ids = $this->_idToArray($id);

		$IdsCounter = count($ids);

		$rawArray = $this->request->getRawInput('purge');

    $purge = ( $purge === true || isset( $rawArray['purge'] ) ) ? true : false;

    if ( 0 === $IdsCounter )
    {
      return $this->resErr( lang('api.errorEmptyData') );
		}
    # --- Do "protect first ID"
    else if ( ( true === $this->protectFirstId ) && in_array( 1, $ids ) )
    {
      return $this->resErr( lang('api.errorFirstMem') );
    }

    # --- Do "Limit Record"
    if ( $IdsCounter > $this->maximumDelete )
    {
			$errParam = [ 'field' => 'Id', 'param' => $this->maximumDelete ];

      return $this->resErr( lang( 'Validation.less_than_equal_to', $errParam ) );
    }
    else
    {
      # --- Trigger beforeDelete
			$data = $this->trigger( 'beforeDelete', [ 'id' => $ids, 'purge' => $purge ] );

      if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

      if ( ( true === $purge ) && ( true === $this->model->useSoftDeletes ) ) {
				$this->model->onlyDeleted();
			}

			/**
			 * Find-All rows in the array $ids
			 * @var array $available
			 */
			$available = $this->model
			->select( $this->deleteTemp ?? '1' )
			->whereIn( $this->model->primaryKey, $ids )
			->findAll();

      if ( ( null === $available ) || ( count( $available ) != $IdsCounter ) )
      {
        # Something wrong from client $this->model->deletedField
        return $this->resErr( lang( 'Validation.is_not_unique', [ 'field' => 'Id' ] ) );
			}
      else if ( false === $this->model->delete( $ids, $purge ) )
      {
        return $this->resErr( $this->model->errors() );
			}
      else
      {
				$this->deleteTemp = $available;

        #Trigger afterDelete
				$data = $this->trigger( 'afterDelete', $data );

        if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

				$respondSuccess = [
					$data['success'] ?? null,
					lang('api.deleteSuccess')
				];

        return $this->res( $respondSuccess, 'respondDeleted' );
      }
    }
  }

  /**
   * In the "Router" make sure "placeholder" checked type.
   * @param string $id placeholder ":num|:dotID" see more in \Config\Routes
   * @param bool $unDelPatch "Patch" only ( "But" both multiple and single )
   * @return ResponseTrait|array Prop: returnTypeTrait, default: ResponseTrait
   * @method ___updateMultiExist Callable count instead of multiPatch do default
   * @eController beforeSinglePatch, ModelEvent: rulePatch, rulePatchUndelete - [ array 'id' => $id ]
   * @eController beforeSinglePut, ModelEvent: rulePut - [ array 'id' => $id ]
   * @eController beforeMultiPatch, ModelEvent: rulePatch, rulePatchUndelete - [ array 'id' => $id ]
   * @eController beforeUpdate, afterUpdate - [ ( put ? int $id : array $id ), array $data, string $method]
   */
  protected function updateTrait ( $id = null, bool $unDelPatch = false )
  {
		$unDelPatch = ( $unDelPatch === true ) AND ( $this->model->useSoftDeletes === true );

    if ( empty( $id ) )
    {
      return $this->resErr( lang( 'Validation.required', [ 'field' => 'id' ] ) );
    }
    else if ( 0 === count( $this->request->getRawInput() ) )
    {
      $method = $this->request->getMethod();
      if ( 'patch' !== $method OR ( 'patch' === $method AND false === $unDelPatch ) ) {
        return $this->resErr( lang('api.errorEmptyData') );
      }
    }

    $rawArray = $this->request->getRawInput();
    $method = $this->request->getMethod();
    $unDelPatch = ( isset( $rawArray['undelete'] ) OR $unDelPatch ) ? true : false;

    # --- Check method
    if ( $method === 'patch' )
    {
      $IdsCounter = count( $this->_idToArray($id) );
      $rules = ( $IdsCounter >= 2 )
        ? $this->_multiPatch( $id, $rawArray, $unDelPatch )
        : $this->_singlePatch( $id, $rawArray, $unDelPatch );
    }
    else if ( $method === 'put' )
    {
      $rules = $this->_singlePut( $id, $rawArray );
    }
    else
    {
      return $this->res(
        [ 'Bad Request', null, 'Request must one in: "Put, Patch"' ],
        'failValidationError'
      );
    }
    # After checked method, all $id will inside as Array [ id => $id ]
		if ( isset( $rules['error'] ) ) { return $this->resErr( $rules['error'] ); }

		/**
		 * @var int|array $idByMethod **put** = int $id, **other-wire** = array
		 */
		$idByMethod = ( $method === 'put' ) ? $id[0] : $id;

		# --- Trigger beforeUpdate
    $data = $this->trigger( 'beforeUpdate', [
      'id' => $idByMethod, 'data' => $rawArray, 'method' => $method
    ] );

    if ( isset( $data['error'] ) OR empty( $data['data'] ) ) {
      return $this->resErr( $data['error'] ?? 'Something wrong!, data is empty...' );
    }

		# --- Trigger before validations
		$data = $this->trigger( 'beforeValidateUpdate', $data );
		if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

    # --- Validate
    $validation = service('validation');
    if ( ! $validation->setRules( $rules )->run( $data['data'] ) )
    {
      return $this->resErr( $validation->getErrors() );
		}

		# --- Trigger after validations
		$data = $this->trigger( 'afterValidateUpdate', $data );
		if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

    if ( false === $this->model->update( $id, $data['data'] ) )
    {
      return $this->resErr( $this->model->errors() );
    }
    else
    {
			# --- Trigger after updated
      $data = $this->trigger( 'afterUpdate', $data );
      if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

      return $this->res( $data['success'] ?? lang('api.updateSuccess') );
    }
  }

  /**
   * @eventController::showRun | editRun, return [id]
   * @param string $id
   * @param string $methodName = show|edit, default show
   * @return ResponseTrait|array
   */
  protected function _editShowTrait ( $id = null, string $methodName = 'show' )
  {
    if ( ! $id ) {
			return $this->resErr( lang( 'Validation.required', [ 'field' => 'id' ] ) );
		}

    $validation = \Config\Services::validation()->setRules(
			[ 'id' => \Config\Validation::ruleInt() ]
		);
    $idArray = [ 'id' => $id ];

    if ( ! $validation->run( $idArray ) )
    {
      return $this->resErr( $validation->getError('id') );
    }
    else if ( $methodName === 'show' )
    {
      $data = $this->trigger( 'showRun', $idArray );
      if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

      return $this->res( $data, null, '' );
    }
    else if ( $methodName === 'edit' )
    {
      $data = $this->trigger( 'editRun', $idArray );
      if ( isset( $data['error'] ) ) { return $this->resErr( $data['error'] ); }

      return $this->res( $data, null, '' );
    }
    else
    {
			$errField = [ 'Bad method', null, 'Method must in: "showRun" or "editRun"' ];

      return $this->res( $errField, 'failValidationError' );
    }
  }

  /**
	 * Create some rules for the update method
   * @eventController beforeSinglePatch, params [ string id, array data ]
   * @param integer|string &$id
   * @param array &$rawArray
   * @param bool $unDelPatch restore softDeleted
   * @return array
   */
  private function _singlePatch ( &$id, array &$rawArray, bool $unDelPatch ) : array
  {
    if ( ( true === $unDelPatch ) AND ( true === $this->model->useSoftDeletes ) )
    {
      if ( ! $this->model->select('1')->onlyDeleted()->find($id) ) {
        return [ 'error' => lang( 'Validation.is_not_unique', [ 'field' => 'Id' ] ) ];
      }

      $rules = $this->triggerModel( 'rulePatchUndelete', [ 'id' => $id ] );

      if ( ! isset( $rules[ $this->model->deletedField ] ) ) {
        $rules[ $this->model->deletedField ] = \Config\Validation::ruleUndelete();
      }

      $rawArray[ $this->model->deletedField ] = null;
    }
    else
    {
      if ( ! $tempData = $this->model->select( $this->updateTemp ?? '1' )->find($id) ) {
        return [ 'error' => lang( 'Validation.is_not_unique', [ 'field' => 'Id' ] ) ];
      }

      $this->updateTemp = $tempData;

      $data = $this->trigger( 'beforeSinglePatch', [ 'id' => $id, 'data' => $rawArray ] );
      if ( isset( $data['error'] ) ) { return [ 'error' => $data['error'] ]; }

      $rawArray = $data['data'];

      $rules = $this->_findRules( $rawArray, $this->triggerModel( 'rulePatch', [ 'id' => $id ] ) );
    }

    $id = [ $id ];
    return $rules;
  }

  /**
	 * Create some rules for the update method
   * @eventController beforeMultiPatch, params [ string id, array data ]
   * @method ___updateMultiExist Callable count instead of multiPatch do default
   * @param integer|array &$id
   * @param array &$rawArray
   * @param bool $unDelPatch restore softDeleted
   * @return array
   */
  private function _multiPatch ( &$id, array &$rawArray, bool $unDelPatch ) : array
  {
    $IdsCounter = count( $this->_idToArray($id) );
    if ( $IdsCounter <= $this->maximumUpdate )
    {
      $id = $this->_idToArray($id);
      if ( ( true === $unDelPatch ) AND ( true === $this->model->useSoftDeletes ) ) {
				$this->model->onlyDeleted();
			}

      if ( method_exists( $this, '___updateMultiExist' ) )
      {
        $available = $this->___updateMultiExist( [ 'id' => $id ] );
        $available = (int) $available;
      }
      else
      {
        $available = $this->model->select('1')
        ->whereIn( $this->model->primaryKey, $id )
        ->countAllResults();
      }

      if ( $IdsCounter != $available )
      {
        return ['error' => lang( 'Validation.is_not_unique', [ 'field' => 'Id' ] ) ];
      }
      else if ( true === $unDelPatch )
      {
        $rules = $this->triggerModel( 'rulePatchUndelete', [ 'id' => $id ] );

        if ( ! isset( $rules[ $this->model->deletedField ] ) ) {
          $rules[ $this->model->deletedField ] = \Config\Validation::ruleUndelete();
        }

        $rawArray[ $this->model->deletedField ] = null;
      }
      else
      {
        $data = $this->trigger( 'beforeMultiPatch', [ 'id' => $id, 'data' => $rawArray ] );
        if ( isset( $data['error'] ) ) { return [ 'error' => $data['error'] ]; }

        $rawArray = $data['data'];

        $rules = $this->_findRules( $rawArray, $this->triggerModel( 'rulePatch', [ 'id' => $id ] ) );
      }

      return $rules;
    }
    else
    {
			$errFiled = [ 'field' => 'Id', 'param' =>  $this->maximumUpdate ];

      return [ 'error' => lang( 'Validation.less_than_equal_to', $errFiled ) ];
    }
  }

  /**
	 * Create some rules for the update method
   * @eventController beforeSinglePut, params [id, data]
   * @param integer|string &$id
   * @param array &$rawArray
   * @return array
   */
  private function _singlePut ( &$id, array &$rawArray ) : array
  {
    if ( ! $tempData = $this->model->select( $this->updateTemp ?? '1' )->find($id) ) {
      return [ 'error' => lang( 'Validation.is_not_unique', [ 'field' => 'Id' ] ) ];
    }

    $this->updateTemp = $tempData;

    $data = $this->trigger( 'beforeSinglePut', [ 'id' => $id, 'data' => $rawArray ] );
    if ( isset( $data['error'] ) ) { return [ 'error' => $data['error'] ]; }

    $rawArray = $data['data'];

    $rules = $this->triggerModel( 'rulePut', [ 'id' => $id ] );
    $id = [ $id ];

    return $rules;
  }

  /**
   * Format string: '1.2.3...' to array: ['1', '2',' 3', ...]
   * @param string $id
   * @param string @separate
   * @param int $length
   * @return array
   */
  public function _idToArray ( string $id, string $sep = '.', int $len = 100 ) : array
  {
    if ( empty($id) ) return [];

    $id = explode( $sep, $id, $len );
    $ids = [];
    foreach ( $id as $value ) {
      if ( $value !== '0' && ctype_digit($value) ) {
        $ids[] = $value;
      }
    }

    return $ids;
  }

  /**
   * Find some rules when we need them.
   */
  private function _findRules ( array $rawArray, array $ruleConfig, array $except = [] ) : array
  {
		helper( 'array' );

    $rules = [];

    foreach ( $rawArray as $fieldName => $value ) {

      if ( in_array( $fieldName, $except, true ) )
      {
        continue;
      }
      else if ( ! array_key_exists( $fieldName, $ruleConfig ) )
      {
        return [ 'error' => lang( 'Validation.ruleNotFound', [ $fieldName ] ) ];
      }
      else
      {
				# --- Last if else
				if ( is_array( $value ) AND ! empty( $value ) )
				{
					if ( isAssoc( $value ) ) {
						return [ 'error' => lang('Api.errorAssociationNotSupported') ];
					}

					$rules[ "{$fieldName}.*" ] = $ruleConfig[ $fieldName ];
				}
				else
				{
					$rules[ $fieldName ] = $ruleConfig[ $fieldName ];
				}
				# --- Last if else
      }
    }

    return $rules;
  }

  private function trigger ( string $event, array $eventData )
	{
		if (! isset($this->{$event}) || empty($this->{$event}))
		{
			return $eventData;
		}

		foreach ($this->{$event} as $callback)
		{
			if (! method_exists($this, $callback))
			{
				throw DataException::forInvalidMethodTriggered($callback);
			}

			$eventData = $this->{$callback}($eventData);
		}

		return $eventData;
  }

  private function triggerModel ( string $event, array $eventData )
	{
		if (! isset($this->{$event}) || empty($this->{$event}))
		{
			return $eventData;
		}

		foreach ($this->{$event} as $callback)
		{
			if (! method_exists($this->model, $callback))
			{
				throw DataException::forInvalidMethodTriggered($callback);
			}

			$eventData = $this->model->{$callback}($eventData);
		}

		return $eventData;
  }

  public function resErr ( $data = null, string $key = 'error' )
  {
    if ( $this->checkReturnTypeTrait() === 'array' )
    {
      return [ $key => $data ];
    }
    else
    {
      if ( isset( $data['methodCallback'], $data['methodArgs'] ) ) {

        $callback = (string) $data['methodCallback'];

        if ( method_exists( $this, $callback ) )
        {
          $data = (array) $data['methodArgs'];
          return $this->{$callback}(...$data);
        }
        else
        {
          throw DataException::forInvalidMethodTriggered($callback);
        }
      }

      return $this->response->setJSON( [ $key => $data ] );
    }
  }

  /**
   * When use $methodName, $data must be ARRAY, because it will unpack to args
   */
  public function res ( $data = null, $methodName = null, string $key = 'success' )
  {
    if ( $this->checkReturnTypeTrait() === 'array' )
    {
      return [ $key ?: 'data' => $data ];
    }
    else if ( empty($methodName) )
    {
      return $this->response->setJSON( ( $key !== '' ) ? [ $key => $data ] : $data );
    }
    else
    {
      $data = (array) $data;

      if ( method_exists( $this->response, $methodName ) )
      {
        return $this->response->{$methodName}( ...$data );
      }
      else if ( method_exists( $this, $methodName ) )
      {
        return $this->{$methodName}( ...$data );
      }
      else
      {
        return $this->respond( $data, 400, 'Unsupported Response Type');
      }
    }
  }

  /**
   * Check return Type of BAPITrait
   * @return string array|object
   */
  private function checkReturnTypeTrait () : string
  {
    return $this->returnTrait === 'array' ? 'array' : 'object';
  }

}