<?php

declare( strict_types = 1 );
namespace App\Controllers;

use Red2Horse\Mixins\Classes\Sql\BaseBuilder;
use Red2Horse\Mixins\Classes\Sql\Model;

use function Red2Horse\Mixins\Functions\Config\getConfig;
use function Red2Horse\Mixins\Functions\Instance\getInstance;
use function Red2Horse\Mixins\Functions\Instance\getModel;
// use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Message\setInfoMessage;
use function Red2Horse\Mixins\Functions\Message\setSuccessMessage;
use function Red2Horse\Mixins\Functions\Model\BaseModel;
use function Red2Horse\Mixins\Functions\Model\model;
use function Red2Horse\Mixins\Functions\Sql\createDatabase;
use function Red2Horse\Mixins\Functions\Sql\createTable;
use function Red2Horse\Mixins\Functions\Sql\getField;
use function Red2Horse\Mixins\Functions\Sql\getTable;
use function Red2Horse\Mixins\Functions\Sql\getUserTableField;
use function Red2Horse\Mixins\Functions\Sql\seed;

class Install extends BaseController
{
    protected $auth;

    public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
        helper( [ 'form', 'url', 'filesystem' ] );
	}

	public function builder ()
	{
		$builder = new BaseBuilder();
		// d( $builder -> getCompilerProperties() );
	}
	
	function allowedFieldsFilter ()
    {
		$rawData = [ 'd', 'e', 'c', 'f', 'a', 'b' ];
		$allowedFields = [ 'a', 'b', 'c' ];

        if ( [] === $rawData || [] === $allowedFields )
        {
            var_dump( false );
        }

        $rs = \Red2Horse\Mixins\Functions\Instance\getComponents( 'common' )
            ->arrayInArray( $allowedFields, $rawData );
		var_dump( $rs );
    }

	public function model ()
	{
		$baseModel = BaseModel( 'user' );dd($baseModel);
		// $user = getTable( 'user_group', true );
		// dd( $user );
	}
	
	public function add ()
	{
		$add = [
			'username' => 'q_as_a_a_sdd',
			'email' => 'asd@dsa.qw',
			// 'created_at' => '123'
		];

		$model = model( 'User/UserModel' );# dd( $model );
		$model->add( $add );
		dd( $model );
	}
	

	public function update_compile ()
	{
		$roleJson = json_encode( [ 'role' => 'a', 'hash' => 'b' ] );
		$model = model( 'userGroup/UserGroupModel' );
		
		// dd( $model );

		$model->edit(
			[ 'user_group.role' => $roleJson ] ,
			[ 'user_group.id' => '1' ]
		);
		dd($model->getLastQueryString());
	}
	
	public function fetch ()
	{
		$first = model( 'User/UserModel', 'user' )
			->select( [
				'user.id', 
				'user.username', 
				'user.email', 
				'user.password', 
				'user.created_at', 
				[ 'user_group.id' ,'as', 'group_id' ],
				[ 'user_group.name' ,'as', 'group_name' ],
				'user_group.permission',
				'user_group.role',
			] )
			->join( [ 'user_group' ], [ 'group_id' => 'user.id' ] )
			->where( [
				'user.username' => 'member', 
				'user.email' => 'abc@sds.zc' 
			], function( $filter ) {
				// $filter->setNoExplode( 'kv', getUserTableField( 'email', true ) );
				$filter->setNoExplode( 'kv', 'user.email' );
				// dd($filter);
			} )
			->fetchFirst();
		dd( $first );
	}
	

	public function test_fetch_first ()
	{
		$model = model( 'User/UserModel' );
		$first = $model
			->select( [ 'user.*', 'user_group.*' ] )
			->from( [ 'user' => 'us ' ] )
			->join( [ 'user_group', 'ug' ], [ 'ug.id' => 'us.group_id', 'za.id' => 'us.group_id', 'zx.id' => 'cc.group_id' ] )
			->where( [ 'id' => 3213124141 ] )
			->orWhere( [ 'name' => 'wsx' ] )
			->orderBy( [ 'id' => 'desc' ] )
			->in( [ 'ad', 'lgk', 'obe' ] )
			->fetchFirst();
		dd( $first );
	}

	public function test_update()
	{
		$model = model( 'User/UserModel' );
		$model2 = model( 'UserGroup/UserGroupModel' );
		// die(var_dump($model, $model2));

		$data = [
			'username' => 'test_update', 
			'email' => 'email@email.ema', 
			'password' => 'password'
		];

		$model->limit(10)
			->add( $data, function( $filter ) {
				$filter->setNoExplode( 'kv', 'email' );
			} );

		$model2->select( [ '*' ] )->limit( 2 )->get();

		dd( $model->getLastQueryString(),  $model2->getLastQueryString() );
	}

	public function test ()
	{
		$rows = model( 'User/UserModel' );
		$rows
			->select( [ [ 'tbl.field', 'as', 'field' ], [ 'tbl2.field2', 'DISTINCT', 'field2' ] , 'user_group.*' ] )
			->from( [ 'user' => 'u', 'table 2' => 'table' ] )
			->join(
				// join
				['user_group' => 'userG' ],
				// on
				[ 'user_group.id' => 'user.id', 'user.email' => 'abc@ewq.kg', 'abc.loi' => 'fgh.bgt' ],
				'and',
				null,
				function( $filter ) {
					$filter->setNoExplode( 'kv', 'user.email' );
					// dd( $filter );
				}
			)
			->andWhere( [
					'user.username' => 'administrator',
					'user.email' => 'administrator@local.host',
					// 'userTable.email' => 'x\'za@asd.qw'
			], function( $filter ) {
				$filter->setNoExplode( 'kv', 'user.email' );
			} )
			->orWhere( [ 'u.selector' => 'b1nf_dt_hf_hf', 'user_group.name' => 'abc' ])
			->where( [ ' ab ' => '  BA  ', 'ttt' => 'asd jhg' ] )
			// ->set( [ ' ab ' => '  BA  ', 'ttt' => 'asd jhg' ] )
			->in( [ 1, 2, 5 , 4, 6] )
			->limit( 1 )
			->orderBy( [ 'id' => 'DESC', 'user.name' => 'ASC', 'user_group.id' => 'DESC' ] )
			->get();
		dd( $rows -> getLastQueryString() );

		print_r( $rows->__toArray() );
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