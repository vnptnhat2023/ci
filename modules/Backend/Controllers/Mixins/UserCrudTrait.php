<?php namespace BAPI\Controllers\Mixins;

/**
 * Contain all controller hook, called by trigger in BAPITrait
 * Named 'hook: "__name"', 'ext of hook: "_name"'
 */
trait UserCrudTrait
{

  # __________________________________________________________
  private function __indexRun() : array
  {
		$config = ( new \BAPI\Config\User() )->getSetting('db.fetch');

		$selectQuery = [
			'user.id',
			'user.username',
			'user.email',
			'user.status',
			'user.created_at',
			# user_group
			'user_group.name as group'
		];

		$user = $this->model
		->select( implode( ',', $selectQuery ) )# , user_group.id as groupId
		->join( 'user_group', 'user_group.id = User.group_id' )
		->orderBy( $config['orderBy'], $config['direction'] )
		->paginate( $config['record'] );

    $data = [
			'data' => $user ?: [],

      'pager' => [
        'currentPage' => $this->model->pager->getcurrentPage(),
        'pageCount' => $this->model->pager->getpageCount()
      ]
    ];

    return $data;
  }

  # __________________________________________________________
  private function __bothShowEdit(array $data) : array
  {
		$selectQuery = [
			# user
			'user.group_id',
			'user.username',
			'user.email',
			'user.status',
			'user.created_at',
			'user.updated_at',
			# user_detail
			'user_detail.fullname',
			'user_detail.phone',
			'user_detail.gender',
			'user_detail.birthday'
		];

		$userData = $this->model
		->withDeleted()
    ->select( implode(',', $selectQuery) ) # , user_group.id as groupId
		->join( 'user_group', 'user_group.id = user.group_id' )
		->join(' user_detail', 'user_detail.user_id = user.id' )
    ->find( $data['id'] );

    if ( ! $userData) {
			$error = [ 'methodCallback' => 'failNotFound', 'methodArgs' => [] ];

			return [ 'error' => $error ];
    }

    return $userData;
  }

  # __________________________________________________________
  private function __beforeCreate(array $data) : array
  {
    $userEntity = new $this->entityUserCrud( $data['data'] );
    $data['data'] = $userEntity->createFillable()->toRawArray();

    return $data;
  }

  # __________________________________________________________
  private function __afterCreate(array $data)
  {
    # CI4 have db::seek, but now need learn more ^^
    $modelDetail = model( $this->modelUserDetail );

    $insertData = [
      'user_id' => $data['id'],
      'fullname' => $data['data']['fullname'] ?? null,
      'phone' => $data['data']['fullname'] ?? null,
      'gender' => $data['data']['fullname'] ?? 'Male',
      'birthday' => $data['data']['fullname'] ?? null
    ];

    if ( ! $modelDetail->insert($insertData) ) {
      return [ 'error' => $modelDetail->errors() ];
    }
  }

  # __________________________________________________________
  private function __beforeUpdate(array $data) : array
  {
    $id = $data['id'];

    if ( $data['method'] === 'put' )
    {
			$currentUser = service('NknAuth')->getUserdata();

      if ( $id == 1 && ( 1 != $currentUser['id'] ) ) {
        return [ 'error' => lang('api.errorFirstMem') ];
      }# Sent mail confirm | required password, email, ...

      $userEntity = new $this->entityUserCrud( $data['data'] );
      $data['data'] = $userEntity->toRawArray();

      return $data;
		}

    else if ( $data['method'] === 'patch' )
    {
      if ( in_array( 1, $id ) ) { return [ 'error' => lang('api.errorFirstMem') ]; }

      $userEntity = new $this->entityUserCrud( $data['data'] );
      $data['data'] = $userEntity->toRawArray();

      return $data;
		}

    else
    {
      return $data;
    }
  }

}