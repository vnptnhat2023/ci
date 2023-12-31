<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class general_setting extends Migration
{
	public function up()
	{

		$fields = [

      'id' => [
        'type' => 'BIGINT',
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
		->createTable( 'general_setting', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'general_setting', true );
	}
}
