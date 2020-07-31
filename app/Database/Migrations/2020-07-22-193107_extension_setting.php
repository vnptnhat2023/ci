<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtensionSetting extends Migration
{
	public function up() : void
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
				'unique' => true
			],

      'setting_value' => [
				'type' => 'VARCHAR',
				'constraint' => 1000,
				'null' => false
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
		->createTable( 'extension_setting', true );
	}

	//--------------------------------------------------------------------

	public function down() : void
	{
		$this->forge->dropTable( 'extension_setting', true );
	}
}
