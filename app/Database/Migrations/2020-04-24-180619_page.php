<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Page extends Migration
{

	public function up()
	{

		$fields = [
      'id' => [
        'type' => 'BIGINT',
        'unsigned' => true,
        'auto_increment' => true
			],

      'title' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
				'null' => false,
				'default' => 'unknown'
			],

      'slug' => [
        'type' => 'VARCHAR',
        'constraint' => 48,
        'null' => false,
				'default' => 'unknown'
			],

      'icon' => [
        'type' => 'VARCHAR',
        'constraint' => 32,
				'null' => true,
				'default' => null
			],

      'content' => [
        'type' => 'TEXT',
        'null' => true,
				'default' => null
			],

      'advanced_content' => [
        'type' => 'TEXT',
				'null' => true,
				'default' => null
			],

      'advanced_position' => [
				'type' => 'ENUM("top","bottom")',
				'null' => false,
        'default' => 'bottom'
			],

      'parent_id' => [
        'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
        'default' => 0
			],

      'status' => [
				'type' => 'ENUM("publish","private","draff")',
				'null' => false,
        'default' => 'draff'
			],

      'sort' => [
        'type' => 'INT',
				'unsigned' => true,
				'null' => false,
        'default' => 0
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
		->addKey( 'slug', false, true )
		->createTable( 'page', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'page', true );
	}
}
