<?php

namespace App\Controllers;

use App\Libraries\DesignPattern as StateGyPattern;
use CodeIgniter\Events\Events;

use function Red2Horse\Mixins\Functions\Model\model;

class Test extends BaseController {

	public function index()
	{
		
	}

	public function migrate()
	{
		$migrate = \Config\Services::migrations();

    try {
        $migrate->latest();
    } catch (\Throwable $e) {
		var_dump($e);
        // Do something with the error here...
    }
	}

	public function seed()
	{
		$seeder = \Config\Database::seeder();
		$seeder->call('Seed');
	}

	public function scan_more (string $class = 'Book' )
	{
		helper( 'filesystem_helper' );
		$class = ucfirst( $class );
		$classPath = EXTPATH . "{$class}/{$class}.php";
		$class = "\\Ext\\{$class}\\{$class}";

		$file = get_file_info( set_realpath( $classPath ), 'date' );
		if ( ! empty( $file[ 'date' ] ) ) {
			$extHashed = password_hash( $file[ 'date' ], PASSWORD_DEFAULT );

			$map = $class::getMap();
			$map[ 'hashed_file' ] = $extHashed;

			$client = service( 'curlrequest' );
			$response = $client->request(
				'POST',
				base_url( 'bapi/extension/crud' ),
				[ 'form_params' => $map ]
			);

			var_dump([
				'getStatusCode' => $response->getStatusCode(),
				'getBody' => $response->getBody(),
				'getHeader' => $response->getHeader( 'Content-Type' )
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
		$config = config( 'Post' );

		Events::trigger( 'add-post-relation', 'book', 'Simple book extension' );
		Events::trigger( 'add-post-relation', 'health', 'Basic health extension' );

		if ( $errors = $config->getErrors() )
		{
			print_r( $errors );
		}
		else
		{
			print_r( $config->getRelationShip() );

			$config->removeRelationShip( 'book' );
			$config->removeRelationShip( 'page' );
			$config->removeRelationShip( 'category' );
			$config->removeRelationShip( 'health' );

			print_r( $config->getRelationShip() );
		}
	}

	public function test_locator ()
	{
		$ext = \Config\Services::locator()->search( 'ShopTest/ShopTest' );
		var_dump($ext);
	}

	public function index3434 ( $params = [ 'a' => 'A', 'b' => 'B' ] )
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

		$loveIndex = runExtension( 'Love' );
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
		echo '<pre style="margin: 50px 0">';
		print_r( runExtension( 'curkit', $param1 ) );
		echo '</pre>';

		$c = handleExtension( 'curkit-event' );
		echo '<pre style="margin: 50px 0">';
		print_r( runExtension( 'curkit', $param2 ) );
		echo '</pre>';

		handleExtension( 'Book-event' );
		echo '<pre style="margin: 50px 0">';
		$bookExt1 = runExtension( 'book', $param2 );
		print_r($bookExt1);

		$bookExt2 = runExtension( 'book', $param1 );
		print_r($bookExt2);

		print_r(runExtension()->getLoaded());
		echo '</pre>';

		// handleExtension( 'curkit-event', $param2, false );
		// echo '<pre>'; print_r( runExtension( 'curkit', [ 'another' => 'Parameters' ]) ); echo '</pre>';
		// echo '<pre>'; print_r( runExtension()->getLoaded() ); echo '</pre>';
	}

	public function shop ($eventName = 'shop-test' )
	{
		$extData = runExtension( $eventName )->index();

		# ----------------------

		if ( ! empty( $extData[ 'inputComponents' ] ) )
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
		$model = model( '\BAPI\Models\Post\Crud' );
		$data = [ 'cat_id' => 'a' ];# This is validate title

		$rules = $model->rulePut( [ 'id' => $id ] );

		die(var_dump($rules));
		// $validation = service( 'validation' );

		if ( ! service( 'validation' )->setRules($rules)->run($data) ) {
			echo var_dump( service( 'validation' )->getErrors() );
		} else {
			echo 'pass';
		}
	}

}