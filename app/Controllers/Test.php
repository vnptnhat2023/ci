<?php namespace App\Controllers;

use App\Libraries\DesignPattern as StateGyPattern;
use \Config\Services;
use CodeIgniter\Events\Events;

class customException extends \Exception
{
	public function errorMessage() {
		$errorMsg = $this->getMessage().' is not a valid E-Mail address.';

		return $errorMsg;
	}
}


class Test extends BaseController {

	public function ci_ss ()
	{
		// $z = Services::NknAuthSession()->regenerate();
		// $ss = Services::session();
		echo session_id() . PHP_EOL;
		// echo date('d-m-Y H:i:s', '1596996174');
	}

	protected object $anonymousClass;

	public function anonymous_class()
	{
		$this->anonymousClass = new class {
			public $lorem = 'lorem ip sum';
		};

		var_dump($this->anonymousClass->lorem);
	}

	public function formatTimelineData()
 {
		$data = [];
		$benchmark = Services::timer(true);
		$rows = $benchmark->getTimers(6);

		foreach ($rows as $name => $info) {
			if ($name == 'total_execution') {
				continue;
			}
			$data[] = ['name' => ucwords(str_replace('_', ' ', $name)), 'component' => 'Timer', 'start' => $info['start'], 'duration' => $info['end'] - $info['start']];
		}
		d( $data );
 }

	public function test2 ( $ssId = 'rt2men4ot0uqfkpnnfoe9c235o203qo5' )
	{
		$config = config( '\Config\App' );
		$pathFile = $config->sessionSavePath;
		$pathFile .= '/' . $config->sessionCookieName . $ssId;

		helper( 'filesystem' );

		if ( empty( $date = get_file_info( $pathFile, 'date' ) ) ) {
			return true;
		}

		if ( $hash = get_cookie( $config->sessionCookieName . '_test' ) ) {
			return password_verify( $ssId, $hash );
		}

		$time = ( time() - $date[ 'date' ] );
		$sessionExp = (int) $config->sessionExpiration;

		if ( $sessionExp > 0 )
		{
			return $time < $sessionExp ? false : true;
		}
		elseif ( $sessionExp === 0 )
		{
			return $time < $config->sessionTimeToUpdate ? false : true;
		}
		else
		{
			return false;
		}
	}

	public function test()
	{
		Services::session()->remove('oknkn');
	}

	public function ci_tl()
	{
		$cleanPath = function (string $file): string {
			switch (true) {
				case strpos($file, APPPATH) === 0:
					$file = 'APPPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(APPPATH));
					break;
				case strpos($file, SYSTEMPATH) === 0:
					$file = 'SYSTEMPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(SYSTEMPATH));
					break;
				case strpos($file, FCPATH) === 0:
					$file = 'FCPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(FCPATH));
					break;
				case defined('VENDORPATH') && strpos($file, VENDORPATH) === 0:
					$file = 'VENDORPATH' . DIRECTORY_SEPARATOR . substr($file, strlen(VENDORPATH));
					break;
			}

			return $file;
		};

		$rawFiles  = get_included_files();
		$coreFiles = [];
		$userFiles = [];

		foreach ($rawFiles as $file)
		{
			$path = $cleanPath($file);

			if (strpos($path, 'SYSTEMPATH') !== false)
			{
				$coreFiles[] = [
					'name' => basename($file),
					'path' => $path,
				];
			}
			else
			{
				$userFiles[] = [
					'name' => basename($file),
					'path' => $path,
				];
			}
		}

		// sort($userFiles);
		// sort($coreFiles);

		// $data = [
		// 	'coreFiles' => $coreFiles,
		// 	'userFiles' => $userFiles,
		// ];

		d( $coreFiles );
		d( $userFiles );
	}

	public function createMediaRelation ()
	{
		$data = [
			'data' => [
				'media_relation' => [
					'images' => [
						[
							'title' => 'test',
							'url' => 'test url'
						],
						[
							'title' => 'lorem',
							'url' => 'ipsum-dolor-sit-armet'
						]
					],
					'videos' => [
						[
							'title' => 'test',
							'type' => 'unknown',
							'link' => 'https://abcsda.com/a42'
						],
						[
							'title' => 'lorem',
							'type' => 'unknown',
							'link' => 'https://example.biz/af42ae82'
						]
					]
				],

				'max_id' => '5'
			]
		];

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

			$model = new \BAPI\Models\Media\Relation();

			$counter = $model
			->select( '1' )
			->whereIn( 'media_id', $relation[ 'media_id' ] )
			->countAllResults();

			if ( count( $relation[ 'media_id' ] ) !== $counter ) {
				$errArg = [ 'field' => "{$model->table}.media_id" ];
				$err = [ 'error' => lang( 'Validation.is_not_unique', $errArg ) ];

				print_r($err); die;
			}

			# --- Override media_data
			$data[ 'data' ][ 'media_data' ] = $relation[ 'data' ];
		}

		print_r( $relation );
	}

	public function media_relation ()
	{
		$time = microtime(true);
		helper( [ 'mediaRelation' ] );

		$media = [
			'images' => [
				[
					'title' => 'test',
					'url' => 'test url'
				],
				[
					'title' => 'lorem',
					'url' => 'ipsum-dolor-sit-armet'
				]
			],
			'videos' => [
				[
					'title' => 'test',
					'type' => 'unknown',
					'link' => 'https://abcsda.com/a42'
				],
				[
					'title' => 'lorem',
					'type' => 'unknown',
					'link' => 'https://example.biz/af42ae82'
				]
			]
		];

		$mediaData = handleMedia( $media );
		if ( ! empty( $mediaData ) ) {
			// var_dump($mediaData); die;
			$rows = count( $mediaData );

			$mediaRelation = generateMediaRelation( 5, 15, 'post_id', 7 );
			# --- Todo: store to $entity['media_data'] = $mediaData
			# --- Todo: check existing in DB $mediaData['media_id']
			# --- Todo: whereIn( media.id, $mediaRelation[ media_id ] )

			print_r( $mediaRelation );
		}

		echo microtime(true) - $time;
	}

	public function general_relation ()
  {
		# --- Apart of post::create::general-group
		$time = microtime( true );
		$maxId = ( new \BAPI\Models\Extension\Crud() )
		->select( 'id' )
		->orderBy( 'id', 'desc' )
		->limit( 1 )
		->first();

		$id = isset( $maxId[ 'id' ] ) ? ( ++$maxId[ 'id' ] ) : '1';

		$data = [
			'custom_post_id' => [
				'id_1' => [ [ 'id' => '3' ], [ 'id' => '4' ] ] ,
				'id_2' => [ [ 'id' => '5' ], [ 'id' => '6' ] ]
			]
		];

		$entity = ( new \BAPI\Entities\Post\Crud( $data ) )
		->toRawArray();

		helper( [ 'generalRelation' ] );

		$haystack = $entity[ 'general_relation' ];
		$relationData = handleGeneralRelation( $id, $haystack, 'po' );

		if ( isset( $relationData[ 'error' ] ) ) {
			die( print_r( $relationData[ 'error' ] ) );
		}

		$counterModel = function( \CodeIgniter\Model $ns, array $data )
		{
			$md = new $ns();

			$countExist = $md
			->select( '1' )
			// ->where( 'name', 'po' )
			->whereIn( $md->primaryKey, $data )
			->countAllResults();

			if ( $countExist != count( $data ) ) {
				$errArgs = [ 'field' => $md->table . '.' . $md->primaryKey ];

				return [ 'error' => lang( 'Validation.is_not_unique', $errArgs ) ];
			}
		};

		$ggCounter = $counterModel(
			new \BAPI\Models\GeneralGroup\Crud(),
			$relationData[ 'group_id' ]
		);
		if ( isset( $ggCounter[ 'error' ] ) ) {
			print_r( $ggCounter[ 'error' ] ); # return ['error]
		}

		$giCounter = $counterModel(
			new \BAPI\Models\GeneralItem\Crud(),
			$relationData[ 'item_id' ]
		);
		if ( isset( $giCounter[ 'error' ] ) ) {
			print_r( $giCounter[ 'error' ] ); # return ['error]
		}

		// print_r( $relationData[ 'data' ] );


		echo microtime(true) - $time;
	}

	public function type_hint ()
	{
		$data = [];
		$userFn = function( int $num ) use ( $data ) {
			$data[] = $num;
		};
		$test = range( 0, 1000 );
		// $count = count($test);

		$time=microtime(true);

		$userFn(...$test);

		var_dump($data);
		$time=microtime(true)-$time;
		echo 'testFn: '.$time;
	}

	public function scan_new ()
	{
		$data = [
      'author' => 'Curkit',
      'contact' => 'ex@local.host',
      'category_name' => 'unknown',
      'description' => 'unknown',
      'name' => 'Curkit extension',
			'version' => '0.1',
			'hashed_file' => '$2y$10$D9CTQ2q8VwAYGWUv6pRKnuUBIEv1IH6aZHV8cWfi5mwLz24LWmkOO',
      'events' => [
        [
          'method' => 'index',
          'name' => 'curkit-event'
        ],
        [
          'method' => 'map',
          'name' => 'curkit-map'
        ]
      ]
    ];

		$query = http_build_query($data, 'flags_');
		$query = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query);
		echo $query;
	}

	public function scan_extension (string $path = EXTPATH, string $currExtension = 'Book')
  {
		#Validate
		$validation = \Config\Services::validation();
		$ruleName = \Config\Validation::modifier( config('Extension')->rules('slug'), 'extension name' );

		if ( ! $validation->setRules( $ruleName )->run( [ 'slug' => $currExtension ] ) ) {
			return [ 'error' => $validation->getErrors() ];
		}

		# When no haven't "cache":
		helper('filesystem');

		if ( empty( $maps = directory_map( set_realpath( $path ), 1 ) ) ) {
			$logStr = 'The directory containing extension is empty.';
			log_message( 'error', $logStr );

			return [ 'error' => $logStr ];
		}

		if ( array_key_exists( "{$currExtension}\\", array_flip($maps) ) ) {
			echo 'Found';
		}
		// echo '<pre>'; print_r($map); echo '</pre>';
	}

	public function scan_more (string $class = 'Book')
  {
		helper('filesystem_helper');
		$class = ucfirst($class);
		$classPath = EXTPATH . "{$class}/{$class}.php";
		$class = "\\Ext\\{$class}\\{$class}";

		$file = get_file_info( set_realpath( $classPath ), 'date' );
		if ( ! empty( $file['date'] ) ) {
			$extHashed = password_hash( $file['date'], PASSWORD_DEFAULT );

			$map = $class::getMap();
			$map['hashed_file'] = $extHashed;

			$client = service('curlrequest');
			$response = $client->request(
				'POST',
				base_url('bapi/extension/crud'),
				[ 'form_params' => $map ]
			);

			var_dump([
				'getStatusCode' => $response->getStatusCode(),
				'getBody' => $response->getBody(),
				'getHeader' => $response->getHeader('Content-Type')
			]);
		}
  }

	public function more_ev ()
	{
		$dataDB = [ 'mot' => 'one', 'hai' => 'two' ];
		$musicEventTriggerSystem = handleExtension( 'music-map', $dataDB , true );

		// $events = $musicEventTriggerSystem[ 'Music::map' ][ 'events' ];
		// $eventColumn = array_column( $events, 'name' );

		echo '<pre>'; var_dump($musicEventTriggerSystem); echo '</pre>';
	}

	public function more ()
	{
		$config = config('Post');

		Events::trigger( 'add-post-relation', 'book', 'Simple book extension' );
		Events::trigger( 'add-post-relation', 'health', 'Basic health extension' );

		if ( $errors = $config->getErrors() )
		{
			print_r( $errors );
		}
		else
		{
			print_r( $config->getRelationShip() );

			$config->removeRelationShip('book');
			$config->removeRelationShip('page');
			$config->removeRelationShip('category');
			$config->removeRelationShip('health');

			print_r( $config->getRelationShip() );
		}
	}

	public function test_locator ()
	{
		$ext = \Config\Services::locator()->search('ShopTest/ShopTest');
		var_dump($ext);
	}

	public function index ( $params = [ 'a' => 'A', 'b' => 'B' ] )
	{
		# before-page-input-render
		# "HandleExt" just once time
		# "RunExt" anytime
		# Usually adding the key event_name
		$dataDB = [ 'data' => $params, 'current_event' => 'love' ];
		$loveDefaultMethod = handleExtension( 'love', $dataDB , true );
		echo '<pre>'; print_r($loveDefaultMethod); echo '</pre>';

		// $dataDB = [ 'data' => $params, 'current_event' => 'book-map' ];
		// $bookDefaultMethod = handleExtension( 'book-map', $dataDB , true );
		// echo '<pre>'; print_r($bookDefaultMethod); echo '</pre>';

		$dataDB = [ 'data' => $params, 'current_event' => 'before-page-input-render' ];
		$testDefaultMethod = handleExtension( 'before-page-input-render', $dataDB , true );
		echo '<pre>'; print_r($testDefaultMethod); echo '</pre>';

		// $dataDB = [ 'data' => $params, 'current_event' => 'movie' ];
		// $movieDefaultMethod = handleExtension( 'movie', $dataDB , true );
		// echo '<pre>'; print_r($movieDefaultMethod); echo '</pre>';


		$dataDB = [ 'data' => 'Changed params', 'current_event' => 'another handleEvent Love' ];
		handleExtension( 'love', $dataDB , true );

		$loveIndex = runExtension('Love');
		echo '<pre>'; print_r( $loveIndex->index() ); echo '</pre>';

		// echo '<pre>'; print_r( runExtension()->getLoaded() ); echo '</pre>';

		# Note: "EventNamed" different than "ExtensionNamed" ( urlTitle, camelCase )
		# EventNamed must be lower-case-and-separator-with-space-or-dash
		# Many events to many extension

		/*
		$bookExt = runExtension( 'book' );
		echo '<pre>'; print_r($bookExt); echo '</pre>';

		# Change parameters
		$bookExt = runExtension( 'book', $params );
		echo '<pre>'; print_r($bookExt->map());
		*/
	}

	public function extension ()
	{
		$param1 = [ 'title' => 'Book A', 'slug' => 'Book-a' ];
		$param2 = [ 'title' => 'Download C', 'slug' => 'download-c' ];

		$a = handleExtension( 'curkit-map' );
		echo '<pre style="margin: 50px 0">'; print_r( runExtension('curkit', $param1) ); echo '</pre>';

		$c = handleExtension( 'curkit-event' );
		echo '<pre style="margin: 50px 0">'; print_r( runExtension('curkit', $param2) ); echo '</pre>';

		handleExtension( 'Book-event' );
		echo '<pre style="margin: 50px 0">';
		$bookExt1 = runExtension( 'book', $param2 );
		print_r($bookExt1);

		$bookExt2 = runExtension( 'book', $param1 );
		print_r($bookExt2);

		print_r(runExtension()->getLoaded());
		echo '</pre>';

		// handleExtension( 'curkit-event', $param2, false );
		// echo '<pre>'; print_r( runExtension('curkit', ['another' => 'Parameters']) ); echo '</pre>';
		// echo '<pre>'; print_r( runExtension()->getLoaded() ); echo '</pre>';
	}

	public function shop ($eventName = 'shop-test')
	{
		$extData = runExtension( $eventName )->index();

		# ----------------------

		if ( ! empty( $extData['inputComponents'] ) )
		{
			$data = $extData[ 'inputComponents' ][ 'data' ];
			$vueTemplate = $extData[ 'inputComponents' ][ 'vueTemplate' ];
			$template = $extData[ 'inputComponents' ][ 'template' ];

			helper([ 'text', 'array' ]);

			echo highlight_code( $vueTemplate ) . '<br>' . PHP_EOL;
			echo arrayPrint( $data, true ) . PHP_EOL;
			echo highlight_code( $template );
		}
		else
		{
			var_dump($extData) . PHP_EOL;
			echo '<br>';
		}

		# ----------------------

		$extLoaded = \Config\Services::extension()->getLoaded();
		if ( ! empty( $extLoaded ) ) {
			echo 'Loaded: ';
			foreach ($extLoaded as $key => $value) {
				echo "{$key}, ";
			}
		}
	}

	public function strategy ()
	{
		$t = new StateGyPattern\StateGy();
		echo $t->run( new StateGyPattern\t1class ) . PHP_EOL;
		echo $t->run( new StateGyPattern\t2class ) . PHP_EOL;
	}

	public function validEmail ()
	{
		$id = 40;
		$model = model('\BAPI\Models\Post\Crud');
		$data = ['cat_id' => 'a'];# This is validate title

		$rules = $model->rulePut( [ 'id' => $id ] );

		die(var_dump($rules));
		// $validation = service('validation');

		if ( ! service('validation')->setRules($rules)->run($data) ) {
			echo var_dump( service('validation')->getErrors() );
		} else {
			echo 'pass';
		}
	}

	public function throttle ()
	{
		$throttler = \Config\Services::throttler();
		// $getIPAddress = \Config\Services::request();
		$throttler->check($this->request->getIPAddress(), 5, MINUTE);
	}

}