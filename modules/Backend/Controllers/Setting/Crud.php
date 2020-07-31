<?php

# --- Todo: not working, need write more
namespace BAPI\Controllers\Setting;

use CodeIgniter\RESTful\ResourceController;

class Crud extends ResourceController
{
	/**
	 * @var \BAPI\Models\Setting\Crud $modelName
	 */
  protected string $modelName = '\BAPI\Models\Setting\Crud';

  /**
	 * @true get value only
	 * @false get full row
	 */
  protected bool $settingValue = true;

	/**
	 * Select setting.name
	 */
  protected string $settingName = null;

  # ==========================================================
  public function show($id = null)
  {
    if ( $data = $this->model->_find($id) ) {
      return $this->response->setJSON( $this->settingValue ? $data['setting_value'] : $data );
    }

    return $this->failNotFound();
  }

  # ==========================================================
  public function create()
  {
    $this->model->setCreateRules();

    if ( 0 === count( $this->request->getPost() ) )
      return $this->response->setJSON ( [ 'error' => lang('api.errorEmptyData') ] );

    else if ( $id = $this->model->insert( $this->request->getPost() ) )
      return $this->respondCreated( [ 'id' => $id ], lang('api.createSuccess') );

    else
      return $this->response->setJSON( [ 'error' => $this->model->errors() ] );
  }

  # ==========================================================
  public function update($id = null, bool $unDelete = false)
  {
    // die(var_dump(['id' => $id, 'unDelete' => $unDelete]));
    if ($unDelete)
    {
      $find = $this->model
      ->select('setting_name')
      ->getWhere( [ 'setting_name' => $id, 'deleted_at !=' => null ], 1 )
      ->getRowArray();

      if ( ! $find )
      {
        return $this->failNotFound();
      }
      else if ( ! $this->model->update( $id, [ 'deleted_at' => null ] ) )
      {
        return $this->response->setJSON( [ 'error' => $this->model->errors() ] );
      }
      else
      {
        return $this->response->setJSON( [ 'success' => lang('api.updateSuccess') ] );
      }
    }
    else if ( $this->model->_find($id) )
    {
      $this->model->setUpdateRules($id);
      if ( 0 === count( $this->request->getRawInput() ) )
        return $this->response->setJSON ( lang('api.errorEmptyData') );

      else if ( ! $this->model->update( $id, $this->request->getRawInput() ) )
        return $this->response->setJSON( [ 'error' => $this->model->errors() ] );

      else
        return $this->response->setJSON( [ 'success' => lang('api.updateSuccess') ] );
    }

    return $this->failNotFound();
  }

  # ==========================================================
  public function delete($id = null, bool $purge = false)
  {
    if ( ! $data = $this->model->_find($id) ) {
      return $this->failNotFound();
    }

    $this->model->changeDeleteKey();

    if ( ! $this->model->delete( $data['id'], $purge ) ) {
      return $this->response->setJSON( [ 'error' => $this->model->errors() ] );
    }
    else
    {
      return $this->respondDeleted( [ 'id' => $id ], lang('api.deleteSuccess') );
    }
  }
}