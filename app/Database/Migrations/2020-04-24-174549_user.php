<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class User extends Migration
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

      'group_id' => [
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => true
			],

      'username' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
        'unique' => true,
        'null' => false
			],

      'email' => [
        'type' => 'VARCHAR',
        'constraint' => 128,
        'unique' => true,
        'null' => false
			],

      'password' => [
        'type' => 'VARCHAR',
        'constraint' => 64,
        'null' => false
			],

      'status' => [
        'type' => 'ENUM("active", "inactive", "banned")',
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
        'null' => true
			],

      'last_activity' => [
        'type' => 'DATE',
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
		->addKey( 'group_id' )
		->createTable( 'user', true );

		# $this->forge->addKey( 'username', false, true );
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable( 'user', true );
	}
}
