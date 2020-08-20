<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class User extends Migration
{

	public function up()
	{

		$fields = [

      'id' => [
        'type' => 'BIGINT',
        'unsigned' => true,
        'auto_increment' => true
			],

      'group_id' => [
        'type' => 'INT',
				'unsigned' => true,
				'null' => false,
				'default' => 0
			],

      'username' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
				'null' => false,
				'default' => 'unknown'
			],

      'email' => [
        'type' => 'VARCHAR',
        'constraint' => 128,
        'unique' => true,
				'null' => false,
				'default' => 'unknown'
			],

      'password' => [
        'type' => 'VARCHAR',
        'constraint' => 64,
				'null' => false,
				'default' => 'unknown'
			],

      'status' => [
				'type' => 'ENUM("active", "inactive", "banned")',
				'null' => false,
        'default' => 'inactive'
			],

      'cookie_token' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
        'null' => true,
        'default' => null
			],

      'last_login' => [
        'type' => 'VARCHAR',
        'constraint' => 64,
				'null' => true,
				'default' => null
			],

      'last_activity' => [
        'type' => 'DATETIME',
        'null' => true,
        'default' => null
			],

			'session_id' => [
				'type' => 'VARCHAR',
				'constraint' => 40,
        'null' => true,
        'default' => null
			],

      'created_at'=> [
        'type' => 'DATE',
        'null' => true,
        'default' => null
			],

      'updated_at'=> [
        'type' => 'DATE',
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
		->addKey( 'username', false, true )
		->addKey( 'group_id' )

		->createTable( 'user', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'user', true );
	}
}
