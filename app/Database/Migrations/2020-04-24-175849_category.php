<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Category extends Migration
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
				'type' => 'ENUM("ca","pa","po","cc")',
				'null' => false,
				'default' => 'pa'
			],

			'name_id' => [
				'type' => 'BIGINT',
				'unsigned' => true,
				'null' => false,
        'default' => 0
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
				'default' => 'unknown'
			],

      'keyword' => [
				'type' => 'TEXT',
				'constraint' => 128,
        'null' => true,
				'default' => null
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
				'type' => 'BIGINT',
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
		->createTable( 'category', true );
	}

	public function down()
	{
		$this->forge->dropTable( 'category', true );
	}
}