<?php

namespace BAPI\Controllers\User;

use \BAPI\Controllers\Mixins\BAPITrait;
use CodeIgniter\RESTful\ResourceController;

class Group extends ResourceController
{
  use BAPITrait;

	/**
	 * @var \BAPI\Models\User\Group
	 */
  protected $modelName = '\BAPI\Models\User\Group';

	/**
	 * Model for delete::allowedGroupField
	 * @var \BAPI\Models\User\Crud $modelUserCrud
	 */
  protected string $modelUserCrud = '\BAPI\Models\User\Crud';

	/**
	 * Entity for create, update
	 * @var \BAPI\Entities\User\Group $entityUserGroup
	 */
  protected string $entityUserGroup = '\BAPI\Entities\User\Group';


	# ==========================================================
	public function __construct()
	{
		$this->maximumCreate = 1;
		$this->maximumUpdate = 1;
		$this->maximumDelete = 1;
		$this->useSoftDelete = true;
	}

  # ==========================================================
  public function index()
  {
		# --- Fetch groups from db
		$groups['groups'] = $this->model->findAll( 1000 );

		# --- Get settings from config file
		$groups['group_setting'] = config('\BAPI\Config\User')->getSetting('db.option');

		# --- store permission config file to $permission variable
    foreach ( config('\BAPI\Config\User')->getSetting('permission') as $value ) {
      $permission[] = [ 'name' => $value ];
		}

		# --- Set data
    $groups['permission_db'] = $permission;

		return $this->response->setJSON( $groups );
  }

  # ==========================================================
  public function create()
  {
		$this->ruleCreate[] = 'ruleCreate';

		$this->beforeCreate[] = '__beforeCreate';

    return $this->createTrait();
  }

  # ==========================================================
  public function update($id = null, bool $unDelPatch = false)
  {
		$this->rulePut[] = 'rulePut';
		$this->rulePatch[] = 'rulePatch';
		$this->rulePatchUndelete[] = 'rulePatchUndelete';

		$this->beforeUpdate[] = '__beforeUpdate';

    return $this->updateTrait( $id, $unDelPatch );
  }

  # ==========================================================
  public function delete($id = null, bool $purge = false)
  {
		$this->beforeDelete[] = '__beforeDelete';
		$this->afterDelete[] = '__afterDelete';

    return $this->deleteTrait( $id, $purge );
  }

  # __________________________________________________________
  private function __beforeCreate(array $data) : array
  {
		$entity = new $this->entityUserGroup( $data['data'] );

		$data['data'] = $entity->toRawArray();

    return $data;
  }

  # __________________________________________________________
  private function __beforeUpdate(array $data) : array
  {
    # --- route placeholder (:num)
    $id = $data[ 'id' ][ 0 ] ?? $data['id'];

    if ( $id == 1 )
    {
      return [ 'error' => lang('api.errorFirstMem') ];
    }
    else if ( ( $id == 2 ) AND isset( $data[ 'data' ][ 'permission' ] ) )
    {
      return [ 'error' => lang('api.errorFirstMem') ];
    }
    else
    {
      $entity = new $this->entityUserGroup( $data['data'] );
      $data['data'] = $entity->toRawArray();

      return $data;
    }
  }

  # __________________________________________________________
  private function __beforeDelete(array $data) : array
  {
    if ( $data['id'] <= 2 ) {
			return [ 'error' => lang('api.errorFirstMem') ];
		}

    return $data;
  }

  # __________________________________________________________
  private function __afterDelete(array $data)
  {
		# --- Find all current user in this group
		# --- and change this user to default group
    $userModel = new $this->modelUserCrud();
    $userData = $userModel
    ->select( $userModel->primaryKey )
    ->where( 'group_id', $data['id'] )
    ->findAll();

    if ( $userData ) {
			$userIDS = array_column( $userData, $userModel->primaryKey );

      $userUpdate = [
				'group_id' => config('\BAPI\Config\User')->getSetting('db.option.default_group')
			];

			# --- Set allow update field group_id
      $userModel->allowedGroupField();

      if ( ! $userModel->update( $userIDS, $userUpdate ) ) {

				log_message('error', 'Deleted group but cannot update user to default group');

        return [ 'error' => $userModel->errors() ];
      }
    }
  }

}
