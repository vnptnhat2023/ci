<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Throttle extends Migration
{

	public function up () : void
	{
		$fields = [

      'id' => [
        'type' => 'BIGINT',
        'unsigned' => true,
        'auto_increment' => true
			],

			'ip' => [
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => true,
				'default' => null
			],

			'type' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => false,
				'default' => 0
			],

			'created_at'=> [
        'type' => 'DATETIME',
        'null' => true,
        'default' => null
			],

      'updated_at'=> [
        'type' => 'DATETIME',
        'null' => true,
        'default' => null
			]
		];

		$this->forge
		->addField( $fields )
		->addKey( 'id', true )
		->createTable( 'throttle', true );
	}

	public function down () : void
	{
		$this->forge->dropTable( 'throttle' );
	}
}