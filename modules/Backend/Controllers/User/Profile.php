<?php

namespace BAPI\Controllers\User;

use CodeIgniter\RESTful\ResourceController;
use BAPI\Controllers\Mixins\BAPITrait;

class Profile extends ResourceController
{
  use BAPITrait;

	protected $modelName = '\BAPI\Models\User\Profile';

	# --- Relation Model: __afterUpdate, __showRun

	/**
	 * @var \BAPI\Models\User\Detail $modelUserDetail
	 */
	protected string $modelUserDetail = '\BAPI\Models\User\Detail';

	/**
	 * @var \BAPI\Models\User\Group $modelUserGroup
	 */
	protected string $modelUserGroup = '\BAPI\Models\User\Group';

	/**
	 * Relation Entity: __beforeUpdate
	 * @var \BAPI\Entities\User\Profile $entityUserDetail
	 */
  protected string $entityUserDetail = '\BAPI\Entities\User\Profile';

  # ==========================================================
	public function show($id = null)
  {
		$this->showRun[] = '__showRun';

    return $this->_editShowTrait($id);
  }

  # ==========================================================
  public function update($id = null)
  {
		$this->rulePut[] = 'rulePut';

		$this->beforeUpdate[] = '__beforeUpdate';
		$this->afterUpdate[] = '__afterUpdate';

    return $this->updateTrait($id);
  }

  # __________________________________________________________
  private function __showRun(array $data)
  {
		$userId = \Config\Services::NknAuth()->getUserdata('id');

    if ( $data['id'] != $userId ) {
			return [ 'error' => lang('api.errorViewMem') ];
		}

    $selectQuery = [
			# --- user
			'user.group_id',
			'user.username',
			'user.email',
			'user.status',
			'user.created_at',
			'user.updated_at',
			# --- user_detail
			'user_detail.fullname',
			'user_detail.phone',
			'user_detail.gender',
			'user_detail.birthday'
		];

		$userData = $this->model
		->select( implode( ',', $selectQuery ) )
		->join( 'user_detail', 'user_detail.user_id = user.id' )
		->find( $data['id'] );

    if ( ! $userData ) {
      # Something wrong here, # Have session but not found inDB?; Cookie cache problem?
      # Logout, sent mail or do something like that !
      return ['error' => [ 'methodCallback' => 'failNotFound', 'methodArgs' => [] ]];
    }

    $profileData = [
			'data' => $userData,
			# --- Todo: write more config\UserGroup
      'group' => ( new $this->modelUserGroup() )->select( 'id,name' )->findAll( 100 )
    ];

    return $profileData;
  }

  # __________________________________________________________
  private function __beforeUpdate(array $data) : array
  {
		$userId = \Config\Services::NknAuth()->getUserdata( 'id' );

    if ( $data['id'] != $userId ) {
			return [ 'error' => lang('api.errorEditMem') ];
		}

		$entity = new $this->entityUserDetail( $data['data'] );

    $data['data'] = $entity->toRawArray();

    return $data;
  }

  # __________________________________________________________
  private function __afterUpdate(array $data)
  {
    foreach ( [ 'fullname', 'phone', 'gender', 'birthday' ] as $fieldName ) {

      if ( array_key_exists( $fieldName, $data[' data' ] ) ) {

				$detailData[ $fieldName ] = $data[ 'data' ][ $fieldName ];

			}

    }

    if ( isset($detailData) AND count($detailData) ) {

      $detailModel = ( new $this->modelUserDetail() );

      if ( ! $detailModel->update( $data['id'], $detailData ) ) {

				return [ 'error' => $detailModel->errors() ];

			}

		}

  }
}