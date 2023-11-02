<?php

declare( strict_types = 1 );
namespace Red2Horse\Model\User;

use Red2Horse\Mixins\Classes\Sql\Model;

defined( '\Red2Horse\R2H_BASE_PATH' ) or exit( 'Access is not allowed.' );

class UserModel extends Model
{
	public 		array $allowedFields 	= [ 'created_at', 'username', 'email' ];
	public 		array $createdAt 		= [ 'created_at' => 'Y-m-d H:i:s' ];
	protected 	string $table 			= 'user';

	public function __construct()
	{
		$this->init();
	}
	
    public function first ( array $where ) : array
	{
		$data = $this->builder
		->select( [
			'user.*',
			[ 'user_group.id' ,'as', 'group_id' ],
			[ 'user_group.name' ,'as', 'group_name' ],
			'user_group.permission',
			'user_group.role',
		] )
		->join(
			[ 'user_group' ], 
			['user_group.id'  => 'user.group_id' 
		] )
		->orWhere( $where, static function( $filter ) {
			$filter->setNoExplode( 'kv', [ 'user.username', 'user.email' ] );
		} )
		->fetchFirst();
		
		return $data;
	}
}