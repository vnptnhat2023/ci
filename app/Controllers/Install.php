<?php

declare( strict_types = 1 );
namespace App\Controllers;

use Red2Horse\Mixins\Classes\Data\DataAssocKeyMap;
use Red2Horse\Mixins\Classes\Data\DataKeyMap;
use Red2Horse\Mixins\Classes\Sql\SqlClassQueryRows;

use function Red2Horse\Mixins\Functions\add;
use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\edit;
use function Red2Horse\Mixins\Functions\editIn;
use function Red2Horse\Mixins\Functions\delete;
use function Red2Horse\Mixins\Functions\deleteIn;
use function Red2Horse\Mixins\Functions\getInstance;
use function Red2Horse\Mixins\Functions\selectExports;
use function Red2Horse\Mixins\Functions\setInfoMessage;
use function Red2Horse\Mixins\Functions\setSuccessMessage;
use function Red2Horse\Mixins\Functions\Sql\createDatabase;
use function Red2Horse\Mixins\Functions\Sql\createTable;
use function Red2Horse\Mixins\Functions\Sql\queries;
use function Red2Horse\Mixins\Functions\Sql\seed;

class Install extends BaseController
{
    protected $auth;

    public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
        helper( [ 'form', 'url', 'filesystem' ] );
	}

	public function test_update()
	{
		$rows = getInstance( SqlClassQueryRows::class );
		$rows = $rows
			->limit( 1 )
			->insert( [ 
				'user.name' => 'name',
				'usd.asd' => 'asd_.gf_.dk'
			] );

		print_r( $rows->__toArray() );
	}

	public function test ()
	{
		$rows = getInstance( SqlClassQueryRows::class );
		$rows
			->select( [ [ 'tbl.field', 'as', 'field' ], [ 'tbl2.field2', 'DISTINCT', 'field2' ] , 'user_group.*' ] )
			->from( [ 'user' => 'u', 'table 2' => 'table' ] )
			->join(
				// join
				['user_group' => 'userG' ],
				// on
				[ 'user_group.id' => 'user.id', 'user.email' => 'abc@ewq.kg', 'abc.loi' => 'fgh.bgt' ],
				'and'
			)
			->andWhere( [
					'user.username' => 'administrator',
					'user.email' => 'administrator@local.host',
					// 'userTable.email' => 'x\'za@asd.qw'
			], function( $filter ) {
				// print_r( get_object_vars( $filter ) );
				$filter->keyValueNoExplode[] = 'user.email';
				// $filter->keyNoExplode[] = 'userTable.email';
				// $filter->valueNoExplode[] = 'x\'za@asd.qw';
				// dd( $filter->keyValueNoExplode );
			} )
			->orWhere( [ 'u.selector' => 'b1nf_dt_hf_hf', 'user_group.name' => 'abc' ])
			->where( [ ' ab ' => '  BA  ', 'ttt' => 'asd jhg' ] )
			->set( [ ' ab ' => '  BA  ', 'ttt' => 'asd jhg' ] )
			->in( [ 1, 2, 5 , 4, 6] )
			->limit( 1 )
			->orderBy( [ 'id' => 'DESC', 'user.name' => 'ASC', 'user_group.id' => 'DESC' ] )
			->get();
		dd( $rows -> getLastQueryString() );

		print_r( $rows->__toArray() );
	}

	public function queries ()
	{
		// $q = queries();
		// dd( $q );
	}
	

    public function index ()
    {
        echo anchor( [ 'install', 'create_database' ], 'Create a database.' );
    }

	public function create_database ()
	{		
		$databaseConfig = getConfig( 'validation' );
		$intersect = [
			$databaseConfig->database_hostname,
			$databaseConfig->database_username,
			$databaseConfig->database_password,
			$databaseConfig->database_database,
			$databaseConfig->database_port
		];
		$posts = $this->request->getPost();

		if ( ! empty( $posts ) )
		{
			$s = $posts[ $intersect[ 0 ] ];
			$u = $posts[ $intersect[ 1 ] ];
			$p = $posts[ $intersect[ 2 ] ];
			$d = $posts[ $intersect[ 3 ] ];
			$port = $posts[ $intersect[ 4 ] ];

			if ( createDatabase( $s, $u, $p, $d, $port, $intersect ) )
			{
				$msgInfo = [ anchor( [ 'install', 'table', 'user_group' ], 'Next' ) ];
				setInfoMessage( $msgInfo ); 
				setSuccessMessage( 'Database created successfully: '. $d );
			}
		}

		$postKey = [
			'title' => 'Create a database.',
			'postKeys' => $intersect
		];
		$msg = $this->auth->getMessage( $postKey );

		return view( 'login/seed', ( array ) $msg );
	}

    public function table ( string $param = 'user_group' )
    {
		if ( $param == 'user_group' && $sql = createTable( 'user_group', true ) )
		{
			$msgInfo = [ anchor( [ 'install', 'table', 'user' ], 'Next' ) ];
			setInfoMessage( $msgInfo );
			setSuccessMessage( 'Table created successfully: user_group' );
		}
		
		if ( $param == 'user' && $sql = createTable( 'user', true ) )
		{
			$msgInfo = [ anchor( [ 'install', 'table', 'throttle' ], 'Next' ) ];
			setInfoMessage( $msgInfo );
			setSuccessMessage( 'Table created successfully: user' );
		}

		if ( $param == 'throttle' && $sql = createTable( 'throttle', true ) )
		{
			$msgInfo = [ anchor( [ 'install', 'seed', 'user_group' ], 'Next' ) ];
			setInfoMessage( $msgInfo );
			setSuccessMessage( 'Table created successfully: throttle' );
		}

		$msg = $this->auth->getMessage( [ 'sql' => $sql, 'title' => 'Create table: ' . $param ] );

		return view( 'login/seed', ( array ) $msg );
    }

    public function seed ( string $param = 'user_group' )
	{
		$seed = seed( $param, [], true );
		$msg = $this->auth->getMessage();

		if ( $param == 'user_group' && ! $msg->result->show->form )
		{
			$msgInfo = [ anchor( [ 'install', 'seed', 'user' ], 'Next' ) ];
			setInfoMessage( $msgInfo );
			setSuccessMessage( 'Seed added successfully: '. $param, false );
		}
		
		if (  $param == 'user' && ! $msg->result->show->form )
		{
			$msgInfo = 'Success finished.';
			setInfoMessage( $msgInfo );
			setSuccessMessage( 
				[ 'Seed added successfully: '. $param, lang( 'Red2Horse.homeLink' ) ],
				false
			);
		}
		
		$msg = $this->auth->getMessage( [
			'postKeys' => $seed[ 'intersect' ],
			'sql' => $seed[ 'sql' ],
			'title' => 'Seed ' . $param
		] );

		return view( 'login/seed', ( array ) $msg );
	}
}