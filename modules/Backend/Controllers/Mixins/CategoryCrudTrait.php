<?php namespace BAPI\Controllers\Mixins;

/**
 * Chunk controllers Category::Crud
 */
trait CategoryCrudTrait
{
  # __________________________________________________________
  private function __indexRun(array $data)
  {
		$config = config('\BAPI\Config\Category')->getSetting('db.fetch');

    $catData = $this->model
		->select( 'id,title,name,name_id,parent_id' )
		->orderBy( $config['orderBy'], $config['direction'] )
		->findAll( $config['record'] );

    if ( ! $catData ) { return []; }

		helper('array');

    return [
      'select_option' => $catData,
      'data' => buildTree($catData)
    ];
  }

  # __________________________________________________________
  private function __beforeCreate(array $data) : array
  {
		$entity = new $this->entityCategoryCrud( $data['data'] );

    $data['data'] = $entity->createFillable()->toRawArray();

    $cName = $data[ 'data' ][ 'name' ];
    $Nid = $data[ 'data' ][ 'name_id' ];
    $Pid = $data[ 'data' ][ 'parent_id' ];

		$hasError =  $this->___isValidCategory( $cName, $Nid, $Pid );

    return isset( $hasError['error'] ) ? $hasError : $data;
  }

  # __________________________________________________________
  private function __afterCreate(array $data) : array
  {
    return [ 'success' => $data['id'] ];
  }

  # __________________________________________________________
  private function __beforeUpdate(array $data) : array
  {
		$entity = new $this->entityCategoryCrud( $data['data'] );

		$data['data'] = $entity->toRawArray();

		if ( $data['method'] !== 'put' )
		{
			return $data;
		}

		if ( ! isset( $data[ 'data' ][ 'name' ] ) )
		{
      return [ 'error' => lang( 'Validation.required', [ 'field' => 'name' ] ) ];
    }

		$needCheck = isset( $data['data'][ 'name_id' ] ) ?: isset( $data['data'][ 'parent_id' ] );

		if ( ! $needCheck )
		{
			return $data;
		}

		$hasError = $this->__isValidUpdate( $data['id'], $data['data'] );

    return isset( $hasError['error'] ) ? $hasError : $data;
  }

  /**
   * Validate "Update" only, no data changes
   * @param int $id [ name, name_id, parent_id ]
   * @param array $data
   * @return array
   */
  private function __isValidUpdate(int $id, array $data) : array
  {
    $Nid = $data[ 'name_id' ] ?? $this->updateTemp[ 'name_id' ];
    $Pid = $data[ 'parent_id' ] ?? $this->updateTemp[ 'parent_id' ];

    # "Children" Itself
    if ( $Pid == $id )
    {
      return [ 'error' => lang( 'Page.errorChildrenItself' ) ];
    }
    # Ignore different column "name"
    else if ( $this->updateTemp[ 'name' ] !== $data[ 'name' ] )
    {
			$errArgs = [
				'field' => $this->updateTemp[ 'name' ],
				'param' => $data[ 'name' ]
			];

      return [ 'error' => lang( 'Validation.not_equals', $errArgs ) ];
    }
    # The category cannot be its "descendants"
    else if ( ! empty( $data[ 'parent_id' ] ) && ( $data[ 'parent_id' ] > 0 ) )
    {
      $findColum = $this->model
			->select( 'id,parent_id' )
			->where( [ 'name' => $data[ 'name' ], 'name_id' => $Nid ] )
			->findAll( 100 );

			if ( ! $findColum )
			{
				$errArgs = [ 'field' => 'name, name_id' ];

        return [ 'error' => lang( 'Validation.is_not_unique', $errArgs ) ];
      }

			helper('array');

      $flatChild = buildTree( $findColum, $id, true );
      $IdsColumn = array_column( $flatChild, 'id' );

			if ( in_array( $Pid, $IdsColumn ) )
			{
				return [ 'error' => lang('Page.errorDescendants') ];
			}
    }

    # Validate "one more time", send error back to __beforeUpdate
		$hasError =  $this->___isValidCategory( $data['name'], $Nid, $Pid );

    return isset( $hasError['error'] ) ? $hasError : [];
  }

  /**
   * Validate both "Update" and "Create", no data changes
   * @param string $cName column name
   * @param int $Nid column name_id
   * @param int $Pid column parent_id
   * @return array
   */
  private function ___isValidCategory(string $cName, int $Nid, int $Pid) : array
  {
    # Is "Parent" exist ?
		if ( $Pid > 0 )
		{
      $nameCol = $this->updateTemp['name'] ?? $cName;
      $nameIdCol = $this->updateTemp['name_id'] ?? $Nid;

      $findColum = $this->model
			->select( 'name,name_id' )
			->where( [ 'name' => $nameCol, 'name_id' => $nameIdCol ] )
			->find( $Pid );

			if ( ! $findColum )
			{
				$errStr = lang( 'Validation.is_not_unique' , [ 'field' => 'id, name or name_id' ] );

        return [ 'error' => $errStr ];
      }
    }

    # "name = ca" then name_id = 0
    if ( $cName === 'ca')
    {
			if ( $Nid !== 0 )
			{
				$errStr = lang( 'Validation.equals', [ 'field' => 'name_id', 'param' => '0' ] );

        return [ 'error' => $errStr ];
			}

      return [];
    }
    # "name != ca" then name_id > 0; Check "Relation" exist
    else
    {
			if ( $Nid === 0 )
			{
				$errArgs = [ 'field' => 'name_id', 'param' => '0' ];
				$errStr = lang( 'Validation.greater_than', $errArgs );

        return [ 'error' => $errStr ];
      }

      #Have more case "CC": write soon, because "custom_cat" has not been written
      $modelName = ( $cName == 'pa' ) ? $this->modelPageCrud : $this->modelPostCrud;
      $model = new $modelName();

      # Find "Relation" exist on OTHER-TABLE [ nameId == thatTable.primaryKey ]
			if ( ! $model->select('1')->find( $Nid ) )
			{
				$errArgs = [ 'field' => "({$model->table}.{$model->primaryKey} = {$Nid})" ];
				$errStr = lang( 'Validation.is_not_unique', $errArgs );

        return [ 'error' => $errStr ];
      }

      return [];
    }
  }
}
