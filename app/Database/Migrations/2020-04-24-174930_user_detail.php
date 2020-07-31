<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UserDetail extends Migration
{
	public function up()
	{
		$fields = [

			'id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true
			],

			'user_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true
			],

			'fullname' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => true
			],

			'phone' => [
				'type' => 'VARCHAR',
				'constraint' => 20,
				'null' => true
			],

			'gender' => [
				'type' => 'ENUM("male","female")',
				'default' => 'male'
			],

			'birthday' => [
				'type' => 'DATE',
				'null' => true,
				'default' => null
			]
		];

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->addKey('user_id')
		->createTable('user_detail');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable( 'user_detail', true );
	}
}
