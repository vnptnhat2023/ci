<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Media extends Migration
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
        'constraint' => 32,
				'null' => false,
				'default' => 'po'
			],

      'value' => [
        'type' => 'TEXT',
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
		->createTable( 'media', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'media', true );
	}
}
