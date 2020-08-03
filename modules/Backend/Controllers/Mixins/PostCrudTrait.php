<?php

namespace BAPI\Controllers\Mixins;

/**
 * Contain all controller hook, called by trigger in BAPITrait
 * Named 'hook: "__name"', 'ext of hook: "_name"'
 */
trait PostCrudTrait
{

  # __________________________________________________________
  private function __indexRun () : array
  {
		# --- Todo: need declare [ "category", "user" ] model string
		$config = ( new \BAPI\Config\Post() ) ->getSetting( 'db.fetch' );

		$selectQuery = [
			'post.id',
			'post.typeof as type',
			'post.title',
			// 'category.title as category_name',
			// 'category.slug as category_url',
			'user.username as author',
			'post.created_at',
			'post.status'
		];

    $post = $this->model
    ->select( implode( ',', $selectQuery ) )

		// ->join( 'category', 'category.id = post.name' )
		->join( 'user', 'user.id = post.user_id' )

		->orderBy( $config[ 'orderBy' ], $config[ 'direction' ] )
    ->paginate( $config[ 'record' ] );

    $data = [
			'data' => $post ?: [],

      'pager' => [
				'current_page' => $this->model->pager->getcurrentPage(),
        'page_count' => $this->model->pager->getpageCount()
      ]
    ];

    return $data;
  }

	# __________________________________________________________
	/**
	 * Handle general-group and media
	 */
  private function __beforeCreate ( array $data ) : array
  {
		$entity = new $this->entity( $data[ 'data' ] );
		$entity = $entity ->createFillable() ->toRawArray();

		$maxId = $this->model
		->select( 'id' )
		->orderBy( 'id', 'desc' )
		->limit( 1 )
		->first();
		$maxId = isset( $maxId[ 'id' ] ) ? ( ++$maxId[ 'id' ] ) : '1';

		$entity['max_id'] = $maxId;
		$data[ 'data' ] = $entity;

		return $data;
	}

	# __________________________________________________________
	private function __createGeneralRelation ( array $data ) : array
	{
		helper( 'generalRelation' );

		$relationData = handleGeneralRelation(
			$data[ 'data' ][ 'max_id' ],
			$data[ 'data' ][ 'general_relation' ],
			'po'
		);

		if ( isset( $relationData[ 'error' ] ) ) return $relationData;

		# --- Are all valid ?
		$idsCounter = function( \CodeIgniter\Model $ns, array $data ) {
			$countExist = $ns
			->select( '1' )
			->where( 'name', 'po' )
			->whereIn( $ns->primaryKey, $data )
			->countAllResults();

			if ( $countExist !== count( $data ) ) {
				$errArgs = [ 'field' => $ns->table . '.' . $ns->primaryKey ];

				return [ 'error' => lang( 'Validation.is_not_unique', $errArgs ) ];
			}
		};

		# --- Are all valid ?
		$ggCounter = $idsCounter( new $this->modelGG(), $relationData[ 'group_id' ] );
		if ( isset( $ggCounter[ 'error' ] ) ) return $ggCounter;

		# --- Are all valid ?
		$giCounter = $idsCounter( new $this->modelGI(), $relationData[ 'item_id' ] );
		if ( isset( $giCounter[ 'error' ] ) ) return $giCounter;

		# --- Override general_data
		$data[ 'data' ]['general_data'] = $relationData['data'];

		return $data;
		// print_r($data[ 'data' ]); die;
	}

	# __________________________________________________________
	private function __createMediaRelation ( array $data ) : array
	{
		helper( 'mediaRelation' );
		$handled = handleMedia( $data[ 'data' ][ 'media_relation' ] );

		if ( empty( $handled ) ) return $data;

		$relation = generateMediaRelation(
			$data[ 'data' ][ 'max_id' ],
			count( $handled ),
			'post_id',
			$data[ 'data' ][ 'max_id' ]
		);

		# --- Are all valid ?
		if ( ! empty( $relation ) ) {

			$model = new $this->modelMediaRelation();

			$counter = $model
			->select( '1' )
			->whereIn( 'media_id', $relation[ 'media_id' ] )
			->countAllResults();

			if ( count( $relation[ 'media_id' ] ) !== $counter ) {
				$errArg = [ 'field' => "{$model->table}.media_id" ];

				return [ 'error' => lang( 'Validation.is_not_unique', $errArg ) ];
			}

			# --- Override media_data
			$data[ 'data' ][ 'media_data' ] = $relation[ 'data' ];
		}

		 return $data;
		// print_r( $relation ); die;
	}

  # __________________________________________________________
  private function __afterCreate ( array $data )
  {
		helper( [ 'mediaRelation', 'generalRelation' ] );

		$id = $data[ 'id' ];

		$reqData = $this->request->getPost();

		/**
		 * Need run beforeValidationCreate
		 * @var array $relationData
		 */
    $relationData = handleGeneralRelation(
			$id,
			$reqData[ 'custom_post_id' ] ?: [],
			'po'
		);

		if ( ! empty( $relationData[ 'data' ] ) ) {
      ( new $this->modelGeneralRelation() )->insertBatch( $relationData[ 'data' ], null, 20 );
    }
		# --- =============================================================
		$mediaData = handleMedia( $reqData[ 'media' ] ?: [] );

		if ( ! empty( $mediaData ) ) {
			$mediaModel = new $this->modelMedia();
      $mediaModel->insertBatch( $mediaData, null, config( '\BAPI\Config\Post' )->mediaMaxLen );

			# --- Todo: need write more for: [ virtual Id increment ]
			# --- Todo: need create a function named: [ handleGeneralRelation => mediaRelationData ]

			$mediaCounter = count( $mediaData );
			$mediaLastId = ( $mediaModel->getInsertID() + ( $mediaCounter - 1 ) );
      $countFor = ( $mediaLastId - $mediaCounter );

			for ( $i = $mediaLastId; $i > $countFor; $i-- )
			{
        $mediaRelationData[] = [
					'media_id' => $i,
					'post_id' => $id,
					'user_id' => \Config\Services::NknAuth()->getUserdata( 'id' )
				];
      }

      ( new $this->modelMediaRelation() )->insertBatch(
				$mediaRelationData,
				null,
				config( '\BAPI\Config\Post' )->mediaMaxLen
			);
    }
  }

  # __________________________________________________________
  private function __beforeUpdate ( array $data ) : array
  {
		$entity = new $this->entityPostCrud( $data[ 'data' ] );

    $data['data'] = $entity->toArray();

    return $data;
  }

  # __________________________________________________________
  private function __afterUpdate ( array $data ) : array
  {
    if ( $data[ 'method' ] === 'put' )
    {
			helper( [ 'array', 'mediaRelation', 'generalRelation' ] );

			$id = $data[ 'id' ];

			$customModel = new $this->modelGeneralRelation();

			$reqPost = $this->request->getPost();

			$relationData = handleGeneralRelation( $id, $reqPost[ 'custom_post_id' ] ?: [] );

      if ( ! empty( $relationData[ 'data' ] ) )
      {
				$customModel->delete($id);

        $customModel->insertBatch( $relationData[ 'data' ], null, 20 );
      }
      else
      {
        $customModel->delete($id);
      }

			$media_info_id = $reqPost('media_info_id') ?: [];

			// $infoModel = model('\BAPI\Models\Category\MediaInfo');
      $mediaModel = ( new $this->modelMediaRelation() );

      if ( count( $media_info_id ) ) {
				$multi_media_id = implode( ',', $media_info_id );

        $sql = "DELETE media,media_info FROM media
				JOIN media_info ON media_info.id = media.media_info_id
				WHERE media.media_info_id IN ( $multi_media_id )";

				db_connect() ->query( $sql );
      }

			$infoData = handleMedia( $reqPost( 'media' ) ?: [] );

      if ( $countInfo = count( $infoData ) ) {

				$infoModel = new $this->modelMedia();

        $infoModel->insertBatch( $infoData, null, config( '\BAPI\Config\Post' )->mediaMaxLen );

				$media_last_id = ( $infoModel->getInsertID() + ( $countInfo - 1 ) );

				$count = $media_last_id - $countInfo;

        for ( $i = $media_last_id; $i > $count; $i-- ) {

          $mediaData[] = [
						'media_info_id' => $i,
						'post_id' => $id
					];

				}

        // $mediaModel = model('\BAPI\Models\Category\Media');
        $mediaModel->insertBatch(
					$mediaData,
					null,
					config( '\BAPI\Config\Post' )->mediaMaxLen
				) ;
      }
		}

    return $data;
  }

  # __________________________________________________________
  private function __bothShowEdit ( array $data ) : array
  {
		$id = $data[ 'id' ];

    $data = [
			'data' => $this->model->withDeleted() ->find( $id ),

      'custom_cat' => $this->_customCategory( $id ),

      'media' => [
        'image' => $this->_fetchMedia( $id, 'image' ),
        'video' => $this->_fetchMedia( $id, 'video' )
      ]
    ];

    return $data;
  }

  # __________________________________________________________
  private function _fetchMedia ( int $id, string $name = 'image' ) : array
  {
    $mediaData = $this->model
		->select( 'media_info.id as media_info_id, media_info.value' )

		->join( 'media', 'media.post_id = post.id' )
		->join( 'media_info', 'media_info.id = media.media_info_id' )

		->where( 'media_info.name', $name )
		->where( 'post.id', $id )

		->findAll();

    return $mediaData;
  }

  # __________________________________________________________
  private function _customCategory ( int $id ) : array
  {
		$selectQuery = [
			'cat_item.title',
			'cat_item.id',
			'cat_item.cat_custom_id',
			'cat_item.slug',
			'cat_custom.id as custom_id',
			'cat_custom.title as custom_title',
			'cat_custom.slug as custom_slug',
			'cat_custom.status as custom_status'
		];

    $data = $this->model
    ->select( implode( ',', $selectQuery ) )

		->join('category_custom_relationship as relation', 'relation.post_id = post.id')
		->join('category_custom as cat_custom', 'cat_custom.id = relation.cat_custom_id')
		->join('category_custom_item as cat_item', 'cat_item.id = relation.item_id')

    ->where('post.id', $id)
    ->findAll();

    if ( is_array($data) ) {

			helper('array');

      $data2 = [];
			$data3 = [];

      foreach ( $data as $value ) {

        $cid = 'id_' . $value['custom_id'];
				$column = array_column( $data3, 'id' );

        if ( false === arraySearchCustom( $cid, $column ) ) {

          $data3[] = [
            'id' => $cid,
            'title' => $value['custom_title'],
            'slug' => $value['custom_slug'],
            'status' => $value['custom_status']
					];

				}

        unset(
          $value['custom_id'],
          $value['custom_title'],
          $value['custom_slug'],
          $value['custom_status']
				);

        $data2[ $cid ][] = $value;
      }

      return [
        'custom_cat' => $data3,
        'custom_cat_item' => $data2
      ];
    }
  }

}