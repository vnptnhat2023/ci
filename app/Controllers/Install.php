<?php

declare( strict_types = 1 );
namespace App\Controllers;

use Red2Horse\R2h;

// use function Red2Horse\Mixins\Functions\getColumn;
// use function Red2Horse\Mixins\Functions\getField;
// use function Red2Horse\Mixins\Functions\getTable;
use function Red2Horse\Mixins\Functions\selectExports;
// use function Red2Horse\Mixins\Functions\sqlSelectColumn;

class Install extends BaseController
{
    protected R2h $auth;

    public function __construct()
	{
		$this->auth = \Config\Services::Red2HorseAuth();
        helper( [ 'form', 'url' ] );

		$this->auth->setConfig( 'validation', function( $valid ) {
			$valid->user_username = 'qwe_username';
			$valid->user_password = 'qwe_password';
			return $valid;
		} );
	}

	public function xoa()
	{
		// $sql = sqlSelectColumn( [ getField( 'id', 'user' )  => 'user_id' ] );
		// $data = [ 'user' => [ [ 'id', 'as', 'user_id' ] ], 'user_group' => [] ];
		$sec = selectExports( [ 'user' => [ [ 'id', 'as', 'user_id' ] ], 'user_group' => [] ] );
		echo $sec;
		// dd( $sql, $sec );
	}

	public function select ()
	{
		$this->auth->selectExport();
	}

    public function index ()
    {
        echo anchor( [ 'install', 'create_database' ], 'Create a database.' );
    }

	public function create_database ()
	{
		$s = env( 'database.default.hostname' );
		$u = env( 'database.default.username' );
		$p = env( 'database.default.password' );
		$d = env( 'database.default.database' );

		$this->auth->createDatabase( $s, $u, $p, $d );

		$msgInfo = [ anchor( [ 'install', 'table', 'user_group' ], 'Next' ) ];
		$this->auth->setInfoMessage( $msgInfo ); 

		$msg = $this->auth->getMessage();

		return view( 'login/seed', ( array ) $msg );
	}

    public function table ( string $param = 'user_group' )
    {
		if ( $param == 'user_group')
		{
			$sql = $this->auth->createTable( 'user_group', true );
			$msgInfo = [ anchor( [ 'install', 'table', 'user' ], 'Next' ) ];
		}
		else
		{
			$sql = $this->auth->createTable( 'user', true );
			$msgInfo = [ anchor( [ 'install', 'seed', 'user_group' ], 'Next' ) ];
		}

		$this->auth->setInfoMessage( $msgInfo );

		$msg = $this->auth->getMessage( [ 'sql' => $sql ] );

		return view( 'login/seed', ( array ) $msg );
    }

    public function seed ( string $param = 'user_group' )
	{
		if ( $param == 'user_group')
		{
			$seed = $this->auth->seed( $param, [], true );
			$msgInfo = [ anchor( [ 'install', 'seed', 'user' ], 'Next' ) ];
		}
		else
		{
			$seed = $this->auth->seed( $param, [], true );
			$msgInfo = [ 'Success finished.' ];
		}

		$msgArgs = [
			'postKeys' => $seed[ 'intersect' ],
			'sql' => $seed[ 'sql' ]
		];

		$this->auth->setInfoMessage( $msgInfo );

		$msg = $this->auth->getMessage( $msgArgs );

		return view( 'login/seed', ( array ) $msg );
	}
}