<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserGroup extends Migration
{

	public function up()
	{

		$fields = [

			'id' => [
				'type' => 'BIGINT',
				'unsigned' => true,
				'auto_increment' => true
			],

			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => false,
				'default' => 'guest'
			],

			'permission' => [
				'type' => 'VARCHAR',
				'constraint' => '512',
				'null' => true,
				'default' => null
			],

			'deleted_at'=> [
				'type' => 'DATE',
				'null' => true,
				'default' => null
			]
		];

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->createTable( 'user_group', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'user_group', true );
	}
}
