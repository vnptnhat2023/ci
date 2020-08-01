<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Extension extends Migration
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

      'author' => [
				'type' => 'VARCHAR',
				'constraint' => 32,
        'null' => false,
				'default' => 'unknown'
			],

			'contact' => [
				'type' => 'VARCHAR',
				'constraint' => 128,
        'null' => false,
				'default' => 'unknown'
			],

      'category_name' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
				'null' => false,
				'default' => 'unknown'
			],

      'category_slug' => [# item_id
        'type' => 'VARCHAR',
        'constraint' => 48,
				'null' => false,
				'default' => 'unknown'
			],

      'description' => [
				'type' => 'VARCHAR',
				'constraint' => 512,
        'null' => true,
        'default' => null
			],

			'title' => [
				'type' => 'VARCHAR',
				'constraint' => 128,
        'null' => false,
				'default' => 'unknown'
			],

			'slug' => [
				'type' => 'VARCHAR',
				'constraint' => 64,
				'null' => false,
				'default' => 'unknown',
				'comment' => 'CamelCaseWithFirstChar'
			],

			'version' => [
				'type' => 'VARCHAR',
				'constraint' => 11,
        'null' => false,
        'default' => '1'
			],

			'status' => [
        'type' => 'ENUM("disable","enable")',
        'null' => false,
        'default' => 'enable'
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
		->addKey( 'title', false, true )
		->addKey( 'slug', false, true )
		->createTable( 'extension', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'extension', true );
	}
}
