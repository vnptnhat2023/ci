<?php
namespace BAPI\Controllers\Extension;

// use BAPI\Controllers\BaseController;
use \CodeIgniter\RESTful\ResourceController;
use \BAPI\Controllers\Mixins\BAPITrait;

class Crud extends ResourceController
{
  use BAPITrait;

	protected $modelName = '\BAPI\Models\Extension\Crud';

	/**
	 * @var \BAPI\Models\GeneralItem\Crud $itemModel
	 */
	protected string $itemModel = '\BAPI\Models\GeneralItem\Crud';

	/**
	 * @var \BAPI\Models\GeneralRelation\GroupItem $relationModel
	 */
  protected string $relationModel = '\BAPI\Models\GeneralRelation\GroupItem';

	/**
	 * Entity
	 * @var \BAPI\Entities\Extension\Crud $entityExtension
	 */
	private string $entityExtension = '\BAPI\Entities\Extension\Crud';

	# ==========================================================
	public function __construct ()
	{
		// $this->returnTrait = 'array';
		$config = ( new \BAPI\Config\Extension() )->getSetting( 'db' );

		$this->maximumCreate = $config[ 'create' ][ 'maximum_rows' ];
		$this->maximumUpdate = $config[ 'update' ][ 'maximum_rows' ];
		$this->maximumDelete = $config[ 'delete' ][ 'maximum_rows' ];
		$this->useSoftDelete = $config[ 'delete' ][ 'soft' ];
	}

	# ==========================================================
  public function index ()
  {
		# --- Example: ?id[]=1&id[]=2&author[]=<administrator"&author[]=tester
		# --- When need search with other methods:
		# --- Pass what key want except into $except and change needed method
		# --- Maybe need to clear old data ( after do search )... write more
		$this->ruleSearch[] = 'ruleSearch';

		$this->indexRun[] = '__indexRun';

		$data = $this->indexTrait( true, [], 'whereIn', [], false );

		// echo $this->model->getLastQuery();

		return $data;
		// echo '<pre>', json_encode( $data, JSON_PRETTY_PRINT );
  }

	# ==========================================================
  public function create ()
  {
		$this->ruleCreate[] = 'ruleCreate';

		$this->beforeCreate[] = '__beforeCreate';
		$this->afterValidateCreate[] = '__afterValidateCreate';
		$this->afterCreate[] = '__afterCreate';

    return $this->createTrait();
	}

	# ==========================================================
	public function update ( $id = null )
  {
		$this->rulePatch[] = 'rulePatch';

		$this->beforeUpdate[] = '__beforeUpdate';
		$this->afterUpdate[] = '__afterUpdate';

    return $this->updateTrait($id);
	}

	# ==========================================================
	public function delete ( $id = null )
  {
		$this->afterDelete[] = '__afterDelete';

		# --- Get column value when checking $id exist
		$this->deleteTemp = 'slug';

    return $this->deleteTrait( $id, true );
	}

	# --- Todo: Not using now; Need more to code
	# ==========================================================
	public function scan ( string $name )
  {
		helper( 'filesystem_helper' );
		$file = get_file_info( set_realpath( EXTPATH . "{$name}/{$name}.php" ), 'date' );

		if ( empty( $file[ 'date' ] ) ) {
			return [ 'error' => lang( 'Files.fileNotFound', [ $name ] ) ];
		}

		$extHashed = password_hash( $file[ 'date' ], PASSWORD_DEFAULT );
		// var_dump( password_verify( '1594660675', $extHashed ) );
		return password_verify( '1594660675', $extHashed ); # Arg1 = post[hashedFile]
  }

  private function __indexRun ( array $data ) : array
  {
    $option = config( '\BAPI\Config\Extension' )->getSetting( 'db.fetch' );

    $extensionData = $this->model
      ->orderBy( $option[ 'orderBy' ], $option[ 'direction' ] )
      ->paginate( $option[ 'record' ] );

    $data = [
      'data' => $extensionData ?: [],

      'pager' => [
        'currentPage' => $this->model->pager->getcurrentPage(),
        'pageCount' => $this->model->pager->getpageCount(),
      ]
    ];

    return $data;
  }

	/**
	 * 1. Check for duplicates and limit event names
	 * 2. Ensure data entity before validation
	 */
	private function __beforeCreate ( array $data ) : array
	{
		$events = $data[ 'data' ][ 'events' ] ?? [];

		# Ensure we have an event
		if ( empty( $events[ 0 ][ 'method' ] ) || empty( $events[ 0 ][ 'name' ] ) ) {
			return [ 'error' => lang( 'Validation.invalidTemplate', [ 'events' ] ) ];
		}

		helper( 'array' );

		$eventColumn = array_column( $data[ 'data' ][ 'events' ], 'name' );
		$maxEvents = config( '\BAPI\Config\Extension' )->getSetting( 'db.create.maximum_events' );

		if ( arrayHasDupes( $eventColumn ) )
		{
			return [ 'error' => lang( 'Validation.is_unique', [ 'field' => 'event.name' ] ) ];
		}
		else if ( count( $eventColumn ) > $maxEvents )
		{
			$errField = [ 'field' => 'events', 'param' =>  $maxEvents ];

			return [ 'error' => lang( 'Validation.less_than_equal_to', $errField ) ];
		}

		/**
		 * @var \BAPI\Entities\Extension\Crud $entityExtension
		 */
		$entityExtension = new $this->entityExtension( $data[ 'data' ] );
		$data[ 'data' ] = $entityExtension->createFillable()->toRawArray();

		return $data;
	}

	/**
	 * Check file exists and hashing
	 */
	private function __afterValidateCreate ( array $data ) : array
	{
		helper( [ 'filesystem_helper', 'text' ] );

		$slug = $data[ 'data' ][ 'slug' ];
		$hashedFileData = strip_slashes( $data[ 'data' ][ 'hashed_file' ] );

		$file = get_file_info(
			set_realpath( EXTPATH . "{$slug}/{$slug}.php" ), 'date'
		);

		if ( empty( $file[ 'date' ] ) )
		{
			return [ 'error' => lang( 'Files.fileNotFound', [ $slug ] ) ];
		}
		else if ( false === password_verify( $file[ 'date' ], $hashedFileData ) )
		{
			return [ 'error' => lang( 'Api.errorHashCheckingFailed', [ 'extension' ] ) ];
		}

		unset( $data[ 'data' ][ 'hash' ] );

		return $data;
	}

	/**
	 * Add the extensions "event and relation" to the database
	 */
  private function __afterCreate ( array $data ) : array
  {
		$id = $data[ 'id' ];

		# --- The extension just have an only one EventName
		$itemModel = new $this->itemModel();

		# Ensure data is not empty
		$maxId = $itemModel->selectMax( 'id' )->first();
		if ( ! isset( $maxId[ 'id' ] ) ) { $maxId = [ 'id' => 0 ]; }

		# The expected id we can get
		helper( 'generalRelation' );
		$relationData = generateGeneralRelation(
			$maxId[ 'id' ], count( $data[ 'data' ][ 'events' ] ), 1, $id
		);

		if ( ! empty( $relationData ) )
		{
			if ( ! ( new $this->relationModel() )->insertBatch( $relationData ) ) {
				log_message( 'error',
				"Could not create relations extension, extension-id: {$id}");

				# No need to add events when the relationship is not possible
				return $data;
			}

			if ( ! $itemModel->insertBatch( $data[ 'data' ][ 'events' ] ) ) {
				log_message( 'error',
				"Could not create events extension, extension-id: {$id}");
			}
		}

		else
		{
			log_message( 'error',
			"Could not create relationship for extension {$data[ 'data' ][ 'name' ]}." );
		}

		# Delete the "event extension" stored in the cache named in: "extStore" event
		model( '\App\Models\Extension' )->_deleteCache(
			config( '\BAPI\Config\Extension' )->getSetting( 'cache.name' )
		);

		log_message( 'info',
		"The extension {$data[ 'data' ][ 'title' ]} has been created successfully." );

    return $data;
  }

	/**
	 * The $data has nothing changed in this method
	 * 1. Multiple supported
	 * 2. Remove the extension "events and relation" from the database
	 * 3. Deletes all "files" contained in the supplied directory path
	 * 4. Delete the "event extension" stored in the cache named in: "extStore" event
	 */
  private function __afterDelete ( array $data ) : array
  {
		$ids = $data[ 'id' ];
		$idsString = implode( ',', $ids );

		$itemModel = new $this->itemModel();
		$relationModel = new $this->relationModel();

		# Get [ relation_id, event_id ] from the DATABASE
		$findRelation = $relationModel
		->select( "{$relationModel->table}.id as relation_id, I.id as event_id")
		->join( "{$itemModel->table} I", "I.id = {$relationModel->table}.giid" )
		->where([ "{$relationModel->table}.name" => 'ex' ])
		->whereIn( "{$relationModel->table}.name_id", $ids )
		->findAll();

		if ( ! $findRelation ) {
			log_message( 'error',
			"Cannot find relations extension, event-ids: {$idsString}" . __METHOD__ );

			return $data;
		}

		$itemIds = array_column( $findRelation, 'event_id' );

		# Remove extension "events" from the database
		if ( false === $itemModel->delete( $itemIds, true ) ) {
			log_message( 'error',
			"Cannot find remove event relations, event-ids: {$idsString}" . __METHOD__);
		}

		$relationIds = array_column( $findRelation, 'relation_id' );

		# Remove extension "relation" from the database
		if ( false === $relationModel->delete( $relationIds, true ) ) {
			log_message( 'error',
			"Cannot delete extension, event-ids: {$idsString}" . __METHOD__);
		}

		helper( 'filesystem' );

		/** @var array $arrayPathFiles is also the "path and file" of the extension */
		$arrayPathFiles = array_column( $this->deleteTemp, 'slug' );

		# Deletes all "files" contained in the supplied directory path
		foreach ( $arrayPathFiles as $pathFile ) {
			$deletedFile = delete_files( EXTPATH . "{$pathFile}", TRUE );

			if ( ! $deletedFile ) {
				log_message( 'error',
				"Cannot delete file extension on path: \"{$pathFile}\"" . __METHOD__ );
			}
		}

		# Delete the "event extension" stored in the cache named in: "extStore" event
		model( '\App\Models\Extension' )->_deleteCache(
			config( '\BAPI\Config\Extension' )->getSetting( 'cache.name' )
		);

		$strPathFiles = implode( ',', $arrayPathFiles );
		log_message( 'info',
		"Deleted extension on path: \"{$strPathFiles}\"" . __METHOD__ );

    return $data;
	}

	/**
	 * Protected primary-extension changes
	 */
	private function __beforeUpdate ( array $data ) : array
	{
		$ids = $data[ 'id' ];

		if ( in_array( 1, $ids ) ) {
			return [ 'error' => lang( 'Api.errorFirstItem', [ 'extension' ] ) ];
		}

		return $data;
	}

	/**
	 * Delete the cache files
	 */
	private function __afterUpdate ( array $data ): array
	{
		model( '\App\Models\Extension' )->_deleteCache(
			config( '\BAPI\Config\Extension' )->getSetting( 'cache.name' )
		);

		return $data;
	}
}