<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Setting extends Migration
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

      'setting_name' => [
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => false,
				'default' => 'unknown'
			],

      'setting_value' => [
				'type' => 'VARCHAR',
				'constraint' => 1000,
				'null' => false,
				'default' => 'unknown'
			],

			'created_at' => [
				'type' => 'DATE',
				'null' => true,
				'default' => null
			],

      'updated_at' => [
				'type' => 'DATE',
				'null' => true,
				'default' => null
			],

      'deleted_at' => [
				'type' => 'DATE',
				'null' => true,
				'default' => null
			]
		];

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->addKey( 'setting_name', false, true )
		->createTable( 'setting', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'setting', true );
	}
}
